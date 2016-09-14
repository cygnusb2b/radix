<?php

namespace AppBundle\Input;

use AppBundle\Question\QuestionAnswerFactory;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;

class SubmissionManager
{
    /**
     * @var QuestionAnswerFactory
     */
    private $factory;

    /**
     * @param   QuestionAnswerFactory   $factory
     */
    public function __construct(QuestionAnswerFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Finds all question models for the provided id/key values.
     *
     * @param   array   $keys
     * @return  Model[]
     */
    public function findQuestionsFor(array $keys)
    {
        if (empty($keys)) {
            return [];
        }
        $match = [
            'id'   => [],
            'key'  => []
        ];
        foreach ($keys as $index => $key) {
            if (1 === preg_match('/[a-f0-9]{24}/i', $key)) {
                $match['id'][] = $key;
            } else {
                $match['key'][] = $key;
            }
        }

        $criteria = [];
        foreach ($match as $field => $keys) {
            if (!empty($keys)) {
                $criteria['$or'][] = [$field => ['$in' => $keys]];
            }
        }

        return $this->getStore()->findQuery('question', $criteria);
    }

    /**
     * @return  Store
     */
    public function getStore()
    {
        return $this->factory->getStore();
    }

    /**
     * Processes an input submission.
     *
     * @param   array   $payload
     */
    public function processSubmission(array $payload, Model $inputSource, $customerId = null, $ipAddress = null)
    {
        $answers = $this->createAnswersFor($payload);
        if (empty($answers)) {
            return;
        }

        $submission = $this->createInputSubmission($inputSource, $ipAddress);

        if (empty($customerId)) {
            // New customer. Attempt to match, else create new.
        } else {
            // Append submission to existing customer.
        }
        var_dump(__METHOD__, $answers);
        die();
    }

    /**
     * Creates a new, unsaved answer model for the provided question and value.
     * Will return null if the answer value was not found.
     *
     * @param   Model   $question
     * @param   mixed   $value
     * @return  Model|null
     */
    private function createAnswerFor(Model $question, $value)
    {
        return $this->factory->createAnswerFor($question, $value);
    }

    /**
     * Creates new, unsaved answer models for the provided key/value payload.
     *
     * @param   array   $payload
     * @return  Model[]
     */
    private function createAnswersFor(array $payload)
    {
        $answers   = [];
        $questions = $this->findQuestionsFor(array_keys($payload));

        foreach ($questions as $question) {
            $id  = $question->getId();
            $key = $question->get('key');

            if (isset($payload[$id])) {
                $answer = $this->createAnswerFor($question, $payload[$id]);
            } elseif (isset($payload[$key])) {
                $answer = $this->createAnswerFor($question, $payload[$key]);
            }

            if (!empty($answer)) {
                $answers[] = $answer;
            }
        }
        return $answers;
    }

    /**
     * Creates a new, unsaved input submission model.
     *
     * @param   Model       $inputSource
     * @param   string|null $ipAddress
     * @return  Model
     */
    private function createInputSubmission(Model $inputSource, $ipAddress)
    {
        $submission = $this->getStore()->create('input-submission');
        $submission->set('source', $inputSource);

        if (!empty($ipAddress) {
            $submission->set('ipAddress', $ipAddress);
            // @todo Get IP address info.
        }
        return $submission;
    }

}
