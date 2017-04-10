<?php

namespace AppBundle\Import\Segment\Merrick\IdentityData;

use AppBundle\Import\Segment\Merrick\IdentityData;

abstract class InputAnswer extends IdentityData
{
    /**
     * @var     array
     */
    protected $questions = [];

    protected $mappedQuestions = [
        'ooh_job_title'         => null,
        'ooh_primary_business'  => null,
    ];

    /**
     * @var     array
     */
    protected $answers = [];

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->getCollectionForModel($this->getCollection())->count($this->getCriteria());
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer_identity_data_input_answer';
    }

    /**
     * {@inheritdoc}
     */
    public function modify($limit = 200, $skip = 0)
    {
        $kvs = [];
        $docs = $this->getDocuments($limit, $skip);

        foreach ($docs as $doc) {
            foreach ($doc['legacy']['answers'] as $question) {
                $question['identity'] = ['id' => (string) $doc['_id'], 'type' => $doc['_type']];
                $kv = $this->formatModel($question);
                if (null !== $kv) {
                    $kvs[] = $kv;
                }
            }
        }
        return $kvs;
    }

    /**
     * Returns formatted key-values for the passed legacy document
     *
     * @param   array   $doc    The legacy key values
     * @return  mixed   array of key values or null
     */
    protected function formatModel(array $doc)
    {
        try {
            $question = $this->retrieveQuestionId($doc['question']);
            if (null === $question) {
                return;
            }
            $answer = $this->retrieveAnswerId($doc['answer']);
            if (null === $answer) {
                return;
            }
            return [
                'legacy'    => [
                    'id'        => (string) $doc['identity']['id'],
                    'source'    => sprintf('identity-omeda_%s', $doc['question'])
                ],
                'identity'  => $doc['identity'],
                'question'  => ['id' => $question, 'type' => 'question'],
                'value'     => ['id' => $answer, 'type' => 'question-choice']
            ];
        } catch (\Exception $e) {
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getCollection()
    {
        return 'input-submission';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDocuments($limit = 200, $skip = 0)
    {
        return $this->getCollectionForModel($this->getCollection())->find($this->getCriteria(), $this->getFields())->sort($this->getSort())->limit($limit)->skip($skip);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        return ['legacy.questions' => ['$exists' => true]];
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelType()
    {
        return 'identity-answer-choice';
    }

    protected function retrieveQuestionId($legacyId)
    {
        if (array_key_exists($legacyId, $this->mappedQuestions)) {
            return $this->mappedQuestions[$legacyId];
        }
        $legacyId = (string) $legacyId;
        if (!array_key_exists($legacyId, $this->questions)) {
            $question = $this->getCollectionForModel('question')->findOne(['key' => sprintf('integration-omeda-%s', $legacyId)]);
            if (null === $question) {
                throw new \Exception(sprintf('Could not find question using "%s"', $legacyId));
            }
            $this->questions[$legacyId] = $question;
        }
        return $this->questions[$legacyId]['_id'];
    }

    protected function retrieveAnswerId($legacyId)
    {
        $legacyId = (string) $legacyId;
        if (!array_key_exists($legacyId, $this->answers)) {
            $answer = $this->getCollectionForModel('question-choice')->findOne(['integration.pull.identifier' => $legacyId]);
            if (null === $answer) {
                $answer = $this->getCollectionForModel('question-choice')->findOne(['alternateId' => $legacyId]);
            }
            if (null === $answer) {
                throw new \Exception(sprintf('Could not find answer using "%s"', $legacyId));
            }
            $this->answers[$legacyId] = $answer;
        }
        return $this->answers[$legacyId]['_id'];
    }
}
