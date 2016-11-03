<?php

namespace AppBundle\Integration\Execution;

use AppBundle\Integration\Definition\AbstractDefinition;
use AppBundle\Integration\Definition\ExternalIdentityDefinition;
use AppBundle\Integration\Definition\IdentityAnswerDefinition;
use AppBundle\Integration\Handler\HandlerInterface;
use AppBundle\Integration\Handler\IdentifyInterface;
use AppBundle\Question\TypeManager;
use As3\Modlr\Models\Model;

class IdentifyExecution extends AbstractExecution
{
    /**
     * @var TypeManager
     */
    private $typeManager;

    /**
     * Executes the identify integration.
     * Any logic contained in this method will be run for ALL integration services!
     *
     * @param   Model   $identity
     */
    public function run(Model $identity)
    {
        if ('identity-external' !== $identity->getType()) {
            throw new \InvalidArgumentException(sprintf('Expected a model type of `identity-external` but received `%s`', $identity->getType()));
        }

        // Get all question-pull integrations that match this service.
        $definition = $this->getHandler()->execute(
            $identity->get('identifier'),
            $this->extractExternalQuestionIds()
        );
        $this->applyIdentityValues($identity, $definition);
        $identity->save();

        // Handle question answers.
        $this->upsertAnswers($identity, $definition, $this->retrieveExternalQuestions());
        $this->updateIntegrationDetails();
    }

    /**
     * @return  TypeManager
     */
    public function setTypeManager(TypeManager $typeManager)
    {
        $this->typeManager = $typeManager;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedModelType()
    {
        return 'integration-identify';
    }

    /**
     * {@inheritdoc}
     */
    protected function validateImplements(HandlerInterface $handler)
    {
        if (!$handler instanceof IdentifyInterface) {
            throw new \InvalidArgumentException('The handler is unsupported. Expected an implementation of IdentifyInterface');
        }
    }

    /**
     * Applies the identity values from a definition to a model.
     *
     * @param   Model                       $identity
     * @param   ExternalIdentityDefinition  $definition
     */
    private function applyIdentityValues(Model $identity, ExternalIdentityDefinition $definition)
    {
        $attributes  = $definition->getAttributes();

        foreach (['givenName', 'familyName', 'middleName', 'salutation', 'suffix', 'gender', 'title', 'companyName', 'externalId'] as $key) {
            $identity->set($key, $attributes->get($key));
        }

        $this->applyEmbedManyFor($identity, 'emails', ['identifier', 'description', 'isPrimary', 'value', 'emailType'], $definition->getEmails());
        $this->applyEmbedManyFor($identity, 'phones', ['identifier', 'description', 'isPrimary', 'number', 'phoneType'], $definition->getPhones());
        $this->applyEmbedManyFor($identity, 'addresses', ['identifier', 'description', 'isPrimary', 'companyName', 'street', 'extra', 'city', 'regionCode', 'countryCode', 'postalCode'], $definition->getAddresses());
    }

    /**
     * @param   Model                   $identity
     * @param   string                  $fieldKey
     * @param   array                   $fields
     * @param   AbstractDefinition[]    $definitions
     */
    private function applyEmbedManyFor(Model $identity, $fieldKey, array $fields, array $definitions)
    {
        $identity->clear($fieldKey);
        foreach ($definitions as $definition) {
            $embed      = $identity->createEmbedFor($fieldKey);
            $attributes = $definition->getAttributes();
            foreach ($fields as $key) {
                $embed->set($key, $attributes->get($key));
            }
            $identity->pushEmbed($fieldKey, $embed);
        }
    }

    /**
     * Updates/inserts all question answers for an identity, based on it's definition.
     *
     * @param   Model                       $identity
     * @param   ExternalIdentityDefinition  $definition
     * @param   Model[]                     $questions
     */
    private function upsertAnswers(Model $identity, ExternalIdentityDefinition $definition, array $questions)
    {
        // Clear previous answers.
        foreach ($identity->get('answers') as $answer) {
            $answer->delete();
            $answer->save();
        }

        $answerDefs = $definition->getAnswers();
        if (empty($answerDefs)) {
            // No answers provided by the service. Do not process.
            return;
        }

        foreach ($questions as $question) {
            if (true === $question->get('deleted')) {
                continue;
            }
            $identifier = $question->get('pull')->get('identifier');
            if (!isset($answerDefs[$identifier])) {
                continue;
            }
            $answerDef  = $answerDefs[$identifier];
            $answerType = $this->typeManager->getQuestionTypeFor($question->get('questionType'))->getAnswerType();
            $answer     = $this->getStore()->create(sprintf('identity-answer-%s', $answerType));

            switch ($question->get('questionType')) {
                case 'choice-single':
                    foreach ($question->get('choices') as $choice) {
                        if (true === $choice->get('deleted')) {
                            continue;
                        }
                        $meta = $choice->get('integration');
                        if (null === $meta || null === $pull = $meta->get('pull')) {
                            continue;
                        }
                        if ($answerDef->getValue() == $pull->get('identifier')) {
                            $answer->set('value', $choice);
                        }
                    }
                    break;
                case 'choice-multiple':
                    $values      = (array) $answerDef->getValue();
                    $externalIds = array_flip($values);
                    $choices     = [];
                    foreach ($question->get('choices') as $choice) {
                        if (true === $choice->get('deleted')) {
                            continue;
                        }
                        $meta = $choice->get('integration');
                        if (null === $meta || null === $pull = $meta->get('pull')) {
                            continue;
                        }
                        if (isset($externalIds[$pull->get('identifier')])) {
                            $choices[] = $choice;
                        }
                    }
                    if (empty($choices)) {
                        continue;
                    }
                    foreach ($choices as $choice) {
                        $answer->push('value', $choice);
                    }
                    break;
                default:
                    $answer->set('value', $answerDef->getValue());
                    break;
            }

            if (empty($answer->get('value'))) {
                // Do not allow the answer to be saved. No value was specified.
                continue;
            }

            $answer->set('question', $question);
            $answer->set('identity', $identity);
            $answer->save();
        }
    }
}
