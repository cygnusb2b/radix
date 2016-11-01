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
     * @param   string  $externalId
     * @return  Model
     */
    public function run($externalId)
    {
        $handler = $this->getHandler();
        list($source, $identifier) = $handler->getSourceAndIdentifierFor($externalId);

        $source   = sprintf('identify:%s', $source);
        $identity = $this->getStore()->findQuery('identity-external', ['source' => $source, 'identifier' => $identifier])->getSingleResult();
        if (null === $identity) {
            // Immediately create. Will update the model data later.
            $identity = $this->getStore()->create('identity-external');
            $identity->set('source', $source);
            $identity->set('identifier', $identifier);
            // $identity->save(); // @todo uncomment once the application is done post-process.
        }

        // @todo At this point, the actual identification and updating of the identity model should be handled post-process.

        // Get all question-pull integrations that match this service.
        $definition = $handler->execute($identifier, $this->extractExternalQuestionIds());

        $this->applyIdentityValues($identity, $definition);

        $identity->save();

        // Handle question answers.
        $this->upsertAnswers($identity, $definition, $integrations);
        return $identity;
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

    private function upsertAnswers(Model $identity, ExternalIdentityDefinition $definition, array $integrations)
    {
        // Clear previous answers.
        foreach ($identity->get('answers') as $answer) {
            $answer->delete();
            $answer->save();
        }

        $answerDefs  = $definition->getAnswers();
        if (empty($integrations) || empty($answerDefs)) {
            // No answers provided by the service. Do not process.
            return;
        }

        $ids = [];
        foreach ($integrations as $integration) {
            // Get all integration model ids where a definition was returned.
            $identifier = $integration->get('identifier');
            if (isset($answerDefs[$identifier])) {
                $ids[] = $integration->getId();
            }
        }

        // Get all questions that have matches.
        $criteria  = ['pull' => ['$in' => $ids]];
        $questions = $this->getStore()->findQuery('question', $criteria);
        $answers   = [];
        foreach ($questions as $question) {
            if (true === $question->get('deleted')) {
                continue;
            }

            $answerType = $this->typeManager->getQuestionTypeFor($question->get('questionType'))->getAnswerType();
            $answer     = $this->getStore()->create(sprintf('identity-answer-%s', $answerType));
            $identifier = $question->get('pull')->get('identifier');
            $answerDef  = $answerDefs[$identifier];

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
