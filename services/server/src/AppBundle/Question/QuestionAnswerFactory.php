<?php

namespace AppBundle\Question;

use AppBundle\Utility\ModelUtility;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;

class QuestionAnswerFactory
{
    /**
     * @var array
     */
    private $answerContexts = [
        'identity'  => true,
        'input'     => true,
    ];

    /**
     * @var Store
     */
    private $store;

    /**
     * @var TypeManager
     */
    private $typeManager;

    /**
     * Flattens answer models into "human-readable" format.
     *
     * @param   array   $answers
     * @return  array
     */
    public static function humanizeAnswers(array $answers)
    {
        $formatted = [];
        foreach ($answers as $model) {
            $question = $model->get('question');
            $type     = preg_replace('/^[a-z]+-answer-/', '', $model->getType());
            $answer   = [
                'type'     => $type,
                'question' => $question->get('label') ?: $question->get('name'),
                'name'     => $question->get('name'),
            ];

            $value = $model->get('value');
            switch ($question->get('questionType')) {
                case 'choice-single':
                    $answer['value'] = $value->get('name');
                    break;
                case 'choice-multiple':
                    $values = [];
                    foreach ($value as $v) {
                        $values[] = $v->get('name');
                    }
                    $answer['value'] = $values;
                    break;
                case 'related-choice-single':
                    $answer['value'] = $value->get('name');
                    break;
                default:
                    $answer['value'] = $value;
                    break;
            }
            $formatted[] = $answer;
        }
        return $formatted;
    }

    /**
     * @param   Store           $store
     * @param   TypeManager     $typeManager
     */
    public function __construct(Store $store, TypeManager $typeManager)
    {
        $this->store       = $store;
        $this->typeManager = $typeManager;
    }

    /**
     * Creates a new, unsaved answer model for the provided question and value.
     * Will return null if the value normalizes to null, or if a question choice could not be found.
     *
     * @param   string  $context    The answer context: either customer or input.
     * @param   Model   $question
     * @param   mixed   $value
     * @return  Model|null
     */
    public function createAnswerFor($context, Model $question, $value)
    {
        if (!isset($this->answerContexts[$context])) {
            throw new \InvalidArgumentException(sprintf('The supplied answer context "%s" is not supported.', $context));
        }
        $questionType = $question->get('questionType');
        $typeObj      = $this->typeManager->getQuestionTypeFor($questionType);
        $answerType   = $typeObj->getAnswerType();
        $value        = $this->typeManager->normalizeAnswerFor($questionType, $value, $question->get('allowHtml'));

        if (null === $value) {
            return;
        }

        $modelType = sprintf('%s-answer-%s', $context, $answerType);
        if ($typeObj->supportsChoices()) {
            $answer = $this->createChoiceAnswer($modelType, $question, $value);
            if (null === $answer) {
                return;
            }
            return $answer->set('question', $question);
        } else {
            $answer = $this->store->create($modelType);
            $answer->set('value', $value);
        }
        $answer->set('question', $question);

        return $answer;
    }

    /**
     * @return  Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * Creates a new, unsaved answer-choice model for the provided question and value.
     * Will return null if the question choice(s) cannot be found.
     *
     * @param   string  $modelType
     * @param   Model   $question
     * @param   mixed   $value
     * @return  Model|null
     */
    private function createChoiceAnswer($modelType, Model $question, $value)
    {
        $order  = ['integration.identifier', 'name'];
        $values = [];
        $value  = (array) $value;
        $multi  = false !== stripos($modelType, '-choices');
        foreach ($value as $v) {
            $values[$v] = true;
        }

        $choiceKey = 'related-choice-single' === $question->get('questionType') ? 'relatedChoices' : 'choices';
        $choices   = [];
        foreach ($question->get($choiceKey) as $choice) {
            $id = $choice->getId();
            if (isset($values[$id])) {
                $choices[] = $choice;
                continue;
            }
            foreach ($order as $path) {
                $result = ModelUtility::getModelValueFor($choice, $path);
                if (isset($values[$result])) {
                    $choices[] = $choice;
                    continue 2;
                }
            }
        }
        if (empty($choices)) {
            return;
        }

        $answer = $this->store->create($modelType);
        if (true === $multi) {
            foreach ($choices as $choice) {
                $answer->push('value', $choice);
            }
        } else {
            $answer->set('value', reset($choices));
        }
        return $answer;
    }
}
