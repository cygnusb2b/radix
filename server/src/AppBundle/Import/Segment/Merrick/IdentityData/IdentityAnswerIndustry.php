<?php

namespace AppBundle\Import\Segment\Merrick\IdentityData;

use AppBundle\Import\Segment\Merrick\IdentityData;

class IdentityAnswerIndustry extends Identity
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer_identity_data_identity_answer_industry';
    }

    /**
     * {@inheritdoc}
     */
    public function modify($limit = 200, $skip = 0)
    {
        $kvs = [];
        $docs = $this->getDocuments($limit, $skip);

        foreach ($docs as $doc) {
            $kv = $this->formatModel($doc);
            if (null !== $kv) {
                $kvs[] = $kv;
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
        $choice = $this->retrieveChoice($doc['legacy']['industry']);

        if (null === $choice) {
            var_dump(sprintf('Could not find answer using "%s" (question %s)', $doc['answer'], $doc['question']));
            return;
        }

        $questionId = $choice['question'];
        return [
            'legacy'    => [
                'id'        => (string) $doc['_id'],
                'source'    => 'identity-industry'
            ],
            'identity'  => ['id' => (string) $doc['_id'], 'type' => $doc['_type']],
            'question'  => ['id' => $questionId, 'type' => 'question'],
            'value'     => ['id' => $choice['_id'], 'type' => 'question-choice']
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        return ['legacy.industry' => ['$exists' => true]];
    }

    protected function retrieveChoice($legacyId)
    {
        $legacyId = (string) $legacyId;
        if (!array_key_exists($legacyId, $this->choices)) {
            $this->choices[$legacyId] = $this->getCollectionForModel('question-choice')->findOne(['alternateId' => $legacyId]);
        }
        return $this->choices[$legacyId];
    }
}
