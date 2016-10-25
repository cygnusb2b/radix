<?php

namespace AppBundle\Integrations\Handlers;

use As3\Modlr\Models\Model;
use AppBundle\Definitions\QuestionChoiceDefinition;
use AppBundle\Definitions\QuestionDefinition;
use AppBundle\Integrations\HandlerInterface;
use AppBundle\Integrations\IntegrationManager;
use Cygnus\ModlrBundle\Component\Utility;

class QuestionPullHandler implements HandlerInterface
{
    /**
     * @var IntegrationManager
     */
    private $manager;

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'question-pull';
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $models = $this->getStore()->findQuery('question-integration-pull', []);
        foreach ($models as $model) {
            $this->doRunFor($model);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function runFor($identifier)
    {
        $model = $this->getStore()->find('question-integration-pull', $identifier);
        $this->doRunFor($model);
    }

    /**
     * {@inheritdoc}
     */
    public function setManager(IntegrationManager $manager)
    {
        $this->manager = $manager;
        return $this;
    }

    /**
     * Appends common/stand key values for a question choice.
     * Is used on both create and update.
     *
     * @param   Model                       $choice
     * @param   QuestionChoiceDefinition    $definition
     * @return  array
     */
    private function appendCommonChoiceValues(Model $choice, QuestionChoiceDefinition $definition)
    {
        $choice
            ->set('name', $definition->getName())
            ->set('choiceType', $definition->getType())
            ->set('description', $definition->getDescription())
            ->set('alternateId', $definition->getAlternateId())
            ->set('sequence', $definition->getSequence())
        ;
    }

    /**
     * Creates a new question choice.
     *
     * @param   Model                       $integration
     * @param   Model                       $question
     * @param   QuestionChoiceDefinition    $definition
     */
    private function createChoice(Model $integration, Model $question, QuestionChoiceDefinition $definition)
    {
        $choice = $this->getStore()->create('question-choice');
        $this->appendCommonChoiceValues($choice, $definition);

        $choice->set('question', $question);
        $choice->set('deleted', false);
        $embed = $choice->createEmbedFor('integration');
        $embed
            ->set('clientKey', $integration->get('client')->get('clientKey'))
            ->set('identifier', $definition->getExternalId())
        ;
        $choice->set('integration', $embed);
        $choice->save();
    }

    /**
     * Creates a new question.
     *
     * @param   Model               $integration
     * @param   QuestionDefinition  $definition
     */
    private function createQuestion(Model $integration, QuestionDefinition $definition)
    {
        $clientKey  = $integration->get('client')->get('clientKey');
        $identifier = $integration->get('identifier');

        if (empty($clientKey) || empty($identifier)) {
            throw new \RuntimeException('The question integration must contain an identifier and a client key.');
        }

        $question = $this->getStore()->create('question');
        $question
            ->set('name', $definition->getName())
            ->set('key', sprintf('%s-%s', $clientKey, $identifier))
            ->set('label', $definition->getLabel())
            ->set('pull', $integration)
            ->set('questionType', $definition->getType())
            ->set('builtIn', false)
            ->set('allowHtml', $definition->getAllowHtml())
            ->set('boundTo', $integration->get('boundTo'))
        ;
        foreach ($integration->get('tagWith') as $tag) {
            $question->push('tags', $tag);
        }

        $question->save();

        foreach ($definition->getChoiceDefinitions() as $choiceDef) {
            $this->createChoice($integration, $question, $choiceDef);
        }

    }

    /**
     * Deletes a question choice.
     *
     * @param   Model   $choice
     */
    private function deleteChoice(Model $choice)
    {
        if (true !== $choice->get('deleted')) {
            $choice->set('deleted', true);
            $choice->save();
        }
    }

    /**
     * Handles the pull integration for the provided integration details.
     *
     * @param   Integration     $model
     */
    private function doRunFor(Model $integration)
    {
        $clientModel = $integration->get('client');
        if (null === $clientModel) {
            throw new \RuntimeException(sprintf('No integration-client specified on question-integration model "%s"', $integration->getId()));
        }
        $client = $this->loadClient($clientModel);

        // Load the question definition from the third party.
        $definition = $client->executeQuestionPull($integration->get('identifier'));

        // Attempt to find any existing questions using this integration
        $questions = $this->manager->getStore()->findQuery('question', ['pull' => $integration->getId()]);

        if (0 === $questions->count()) {
            // No questions found. Insert new question using the definition from the third party.
            $this->createQuestion($integration, $definition);
        } else {
            // Existing questions found, update.
            foreach ($questions as $question) {
                $this->updateQuestion($integration, $question, $definition);
            }
        }
        // Update the integration details, such as last run, run count, etc.
        $this->updateIntegrationDetails($integration);
    }

    /**
     * Gets the model store.
     *
     * @return  \As3\Modlr\Store\Store
     */
    private function getStore()
    {
        return $this->manager->getStore();
    }

    /**
     * Loads and configures the integration client from the manager service.
     *
     * @param   Model   $clientModel
     * @return  \AppBundle\Integrations\ClientInterface
     */
    private function loadClient(Model $clientModel)
    {
        $key    = strtolower($clientModel->get('clientKey'));
        $client = $this->manager->getClientFor($key);
        $client->configure((array) $clientModel->get('config'));
        return $client;
    }

    /**
     * Updates a question choice.
     *
     * @param   Model                       $choice
     * @param   QuestionChoiceDefinition    $definition
     */
    private function updateChoice(Model $choice, QuestionChoiceDefinition $definition)
    {
        $this->appendCommonChoiceValues($choice, $definition);
        $choice->save();
    }

    /**
     * Updates the details of the question integration.
     *
     * @param   Model   $integration
     */
    private function updateIntegrationDetails(Model $integration)
    {
        $now       = new \DateTime();
        $integration
            ->set('lastRunDate', $now)
            ->set('timesRan', (integer) $integration->get('timesRan') + 1)
        ;

        if (null === $integration->get('firstRunDate')) {
            $integration->set('firstRunDate', $now);
        }
        $integration->save();
    }

    /**
     * Updates a question.
     *
     * @param   Model               $integratin
     * @param   Model               $question
     * @param   QuestionDefinition  $definition
     */
    private function updateQuestion(Model $integration, Model $question, QuestionDefinition $definition)
    {
        $question
            ->set('name', $definition->getName())
            ->set('allowHtml', $definition->getAllowHtml())
            ->set('boundTo', $integration->get('boundTo'))
        ;
        if (null === $question->get('label')) {
            $question->set('label', $definition->getLabel());
        }

        $question->clear('tags');
        foreach ($integration->get('tagWith') as $tag) {
            $question->push('tags', $tag);
        }

        $question->save();

        $current  = [];
        foreach ($question->get('choices') as $choice) {
            if (true === $choice->get('deleted')) {
                continue;
            }
            $intDetails = $choice->get('integration');

            if (null === $intDetails || $integration->get('client')->get('clientKey') !== $intDetails->get('clientKey')) {
                // A non-integration or different integration source added this choice. This shouldn't happen, but it should be removed.
                $this->deleteChoice($choice);
                continue;
            }
            $current[$intDetails->get('identifier')] = $choice;
        }

        foreach ($definition->getChoiceDefinitions() as $choiceDef) {
            $identifier = $choiceDef->getExternalId();
            if (!isset($current[$identifier])) {
                // Create choice.
                $choice = $this->createChoice($integration, $question, $choiceDef);
            } else {
                // Update choice.
                $choice = $this->updateChoice($current[$identifier], $choiceDef);
                unset($current[$identifier]);
            }
        }

        // Any remaining models in the current set should now be deleted.
        foreach ($current as $choice) {
            $this->deleteChoice($choice);
        }
    }
}
