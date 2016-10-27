<?php

namespace AppBundle\Integration\Execution;

use AppBundle\Integration\Definition\QuestionChoiceDefinition;
use AppBundle\Integration\Definition\QuestionDefinition;
use AppBundle\Integration\Handler\HandlerInterface;
use AppBundle\Integration\Handler\QuestionPullInterface;
use As3\Modlr\Models\Model;

class QuestionPullExecution extends AbstractExecution
{
    /**
     * Executes the question-pull integration.
     * Any logic contained in this method will be run for ALL integration services!
     *
     * @return  Model
     */
    public function run()
    {
        $integration = $this->getIntegration();
        $externalId  = $integration->get('identifier');
        $handler     = $this->getHandler();
        $definition  = $handler->execute($externalId, (array) $integration->get('extra'));

        $definition->getAttributes()->set('key', sprintf('integration-%s-%s', $this->getService()->getKey(), $externalId));

        $this->upsertQuestion($definition);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedModelType()
    {
        return 'integration-question-pull';
    }

    /**
     * {@inheritdoc}
     */
    protected function validateImplements(HandlerInterface $handler)
    {
        if (!$handler instanceof QuestionPullInterface) {
            throw new \InvalidArgumentException('The handler is unsupported. Expected an implementation of QuestionPullInterface');
        }
    }

    /**
     * Applies the choices values from a definition to a model.
     *
     * @param   Question                    $question
     * @param   Model                       $choice
     * @param   QuestionChoiceDefinition    $definition
     */
    private function applyChoiceValues(Model $question, Model $choice, QuestionChoiceDefinition $definition)
    {
        $attributes = $definition->getAttributes();
        foreach (['name', 'choiceType', 'description', 'alternateId', 'sequence'] as $key) {
            $choice->set($key, $attributes->get($key));
        }

        $choice->set('question', $question);
        $choice->set('deleted', false);
    }

    /**
     * Applies the question values from a definition to a model.
     *
     * @param   Model               $question
     * @param   QuestionDefinition  $definition
     */
    private function applyQuestionValues(Model $question, QuestionDefinition $definition)
    {
        $attributes  = $definition->getAttributes();
        $integration = $this->getIntegration();

        $question->set('pull', $integration);
        $question->set('boundTo', $integration->get('boundTo'));

        foreach (['name', 'key', 'allowHtml', 'questionType'] as $key) {
            $question->set($key, $attributes->get($key));
        }
        if (null === $question->get('label')) {
            $question->set('label', $attributes->get('label'));
        }

        foreach ($integration->get('tagWith') as $tag) {
            $question->push('tags', $tag);
        }
    }

    /**
     * Creates a new question choice.
     *
     * @param   Model                       $question
     * @param   QuestionChoiceDefinition    $definition
     */
    private function createChoice(Model $question, QuestionChoiceDefinition $definition)
    {
        // Ensure external id was set on choice.
        $externalId = $definition->getAttributes()->get('externalId');
        if (empty($externalId)) {
            throw new \RuntimeException('All question choice definitions must contain an external identifier.');
        }

        $choice = $this->getStore()->create('question-choice');
        $this->applyChoiceValues($question, $choice, $definition);

        $meta = $choice->createEmbedFor('integration');
        $pull = $meta->createEmbedFor('pull');

        $choice->set('integration', $meta);
        $meta->set('pull', $pull);

        $pull->set('service', $this->getService()->getKey());
        $pull->set('identifier', $externalId);

        $choice->save();
    }

    /**
     * Creates a new question.
     *
     * @param   QuestionDefinition  $definition
     */
    private function createQuestion(QuestionDefinition $definition)
    {
        $question = $this->getStore()->create('question');
        $this->applyQuestionValues($question, $definition);

        $question->save();

        foreach ($definition->getChoices() as $choiceDef) {
            $this->createChoice($question, $choiceDef);
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
     * Updates an existing question.
     *
     * @param   Model               $question
     * @param   QuestionDefinition  $definition
     */
    private function updateQuestion(Model $question, QuestionDefinition $definition)
    {
        $this->applyQuestionValues($question, $definition);

        $question->save();

        $current = [];
        foreach ($question->get('choices') as $choice) {
            if (true === $choice->get('deleted')) {
                continue;
            }
            $meta = $choice->get('integration');
            if (null === $meta || null === $pull = $meta->get('pull')) {
                // Integration details are missing from the choice. Remove choice.
                $this->deleteChoice($choice);
                continue;
            }

            $identifier = $pull->get('identifier');
            if ($this->getService()->getKey() !== $pull->get('service') || empty($identifier)) {
                // Service mismatch or missing identifier. Remove choice.
                $this->deleteChoice($choice);
                continue;
            }
            $current[$identifier] = $choice;
        }

        foreach ($definition->getChoices() as $choiceDef) {
            $externalId = $choiceDef->getAttributes()->get('externalId');
            if (empty($externalId)) {
                throw new \RuntimeException('All question choice definitions must contain an external identifier.');
            }

            if (!isset($current[$externalId])) {
                // Create choice.
                $choice = $this->createChoice($question, $choiceDef);
            } else {
                // Update choice.
                $this->applyChoiceValues($question, $choice, $choiceDef);
                $choice->save();
                unset($current[$externalId]);
            }
        }

        // Any remaining models in the current set should now be deleted.
        foreach ($current as $choice) {
            $this->deleteChoice($choice);
        }
    }

    /**
     * Upserts a question (and applicable choices) based on the provided question definition.
     *
     * @param   QuestionDefinition  $definition
     */
    private function upsertQuestion(QuestionDefinition $definition)
    {
        $integration = $this->getIntegration();

        $question = $this->getStore()->findQuery('question', ['pull' => $integration->getId()])->getSingleResult();
        if (null === $question) {
            // Create.
            $this->createQuestion($definition);
        } else {
            // Update.
            $this->updateQuestion($question, $definition);
        }
    }
}
