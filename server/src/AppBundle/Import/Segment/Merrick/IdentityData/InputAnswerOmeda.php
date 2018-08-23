<?php

namespace AppBundle\Import\Segment\Merrick\IdentityData;

class InputAnswerOmeda extends InputAnswer
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer_identity_data_input_answer_omeda';
    }

    /**
     * {@inheritdoc}
     */
    public function modify($limit = 200, $skip = 0)
    {
        $kvs = [];
        $docs = $this->getDocuments($limit, $skip);

        foreach ($docs as $doc) {
            foreach ($doc['legacy']['answers']['omeda'] as $question) {
                $question['submission'] = $doc['_id'];
                $question['date'] = $doc['createdDate'];
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
                    'id'            => (string) $doc['submission'],
                    'source'        => sprintf('input-submission_%s', $doc['question'])
                ],
                'createdDate'   => $doc['date'],
                'touchedDate'   => $doc['date'],
                'updatedDate'   => $doc['date'],
                'question'      => ['id' => $question, 'type' => 'question'],
                'submission'    => ['id' => $doc['submission'], 'type' => 'input-submission'],
                'value'         => ['id' => $answer, 'type' => 'question-choice']
            ];
        } catch (\Exception $e) {
            var_dump(__METHOD__, $e->getMessage(), __METHOD__);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        return ['legacy.answers.omeda' => ['$exists' => true]];
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelType()
    {
        return 'input-answer-choice';
    }
}
