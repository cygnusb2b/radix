<?php

namespace AppBundle\Import\Segment\Merrick\IdentityData;

use AppBundle\Import\Segment\Merrick\IdentityData;

class IdentityAnswerOmeda extends Identity
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer_identity_data_identity_answer_omeda';
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
        // $question = $this->retrieveQuestion($doc['question']);
        $choice = $this->retrieveChoice($doc['answer']);

        // if (null === $question) {
        //     // var_dump(sprintf('Could not find question using "%s"', $doc['question']));
        //     return;
        // }

        if (null === $choice) {
            // var_dump(sprintf('Could not find answer using "%s" (question %s)', $doc['answer'], $doc['question']));
            return;
        }

        $questionId = $choice['question'];
        return [
            'legacy'    => [
                'id'        => (string) $doc['identity']['id'],
                'source'    => sprintf('identity-omeda_%s', $doc['question'])
            ],
            'identity'  => $doc['identity'],
            'question'  => ['id' => $questionId, 'type' => 'question'],
            'value'     => ['id' => $choice['_id'], 'type' => 'question-choice']
        ];
    }
}
