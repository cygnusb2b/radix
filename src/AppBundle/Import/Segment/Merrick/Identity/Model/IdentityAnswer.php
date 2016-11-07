<?php

namespace AppBundle\Import\Segment\Merrick\Identity\Model;

use AppBundle\Import\Segment\Merrick\Identity;

class IdentityAnswer extends Identity
{
    /**
     * @var     array
     */
    private $questions = [];

    /**
     * @var     array
     */
    private $answers = [];

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
        return 'merrick_customer_model_identity_answer';
    }

    /**
     * {@inheritdoc}
     */
    public function modify($limit = 200, $skip = 0)
    {
        $kvs = [];
        $docs = $this->getDocuments($limit, $skip);

        foreach ($docs as $doc) {
            foreach ($doc['legacy']['questions'] as $question) {
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
        $question = $this->retrieveQuestion($doc['question']);
        $answer = $this->retrieveAnswer($doc['answer']);

        if (null === $question) {
            // var_dump(sprintf('Could not find question using "%s"', $doc['question']));
            return;
        }

        if (null === $answer) {
            // var_dump(sprintf('Could not find answer using "%s" (question %s)', $doc['answer'], $doc['question']));
            return;
        }

        return [
            'legacy'    => [
                'id'        => (string) $doc['identity']['id'],
                'source'    => sprintf('identity-omeda_%s', $doc['question'])
            ],
            'identity'  => $doc['identity'],
            'question'  => ['id' => $question['_id'], 'type' => 'question'],
            'value'     => ['id' => $answer['_id'], 'type' => 'question-choice']
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getCollection()
    {
        return 'identity';
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

    private function retrieveQuestion($legacyId)
    {
        if (!array_key_exists($legacyId, $this->questions)) {
            $this->questions[$legacyId] = $this->getCollectionForModel('question')->findOne(['key' => sprintf('integration-omeda-%s', $legacyId)]);
        }
        return $this->questions[$legacyId];
    }

    private function retrieveAnswer($legacyId)
    {
        $legacyId = (string) $legacyId;
        if (!array_key_exists($legacyId, $this->answers)) {
            $this->answers[$legacyId] = $this->getCollectionForModel('question-choice')->findOne(['integration.pull.identifier' => $legacyId]);
        }
        return $this->answers[$legacyId];
    }
}
