<?php

namespace AppBundle\Import\Segment\Merrick\IdentityData;

class InputAnswerPurchaseIntent extends InputAnswer
{
    private $questionKey = 'purchase-intent';

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer_identity_data_input_answer_purchase_intent';
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
        $question = $this->retrieveQuestionId($this->questionKey);
        $answer = $this->retrieveAnswerId($doc['legacy']['answers']['purchaseIntent']);

        return [
            'legacy'    => [
                'id'            => (string) $doc['_id'],
                'source'        => 'input-submission_purchaseIntent'
            ],
            'createdDate'   => $doc['createdDate'],
            'touchedDate'   => $doc['createdDate'],
            'updatedDate'   => $doc['createdDate'],
            'question'      => ['id' => $question, 'type' => 'question'],
            'submission'    => ['id' => (string) $doc['_id'], 'type' => 'input-submission'],
            'value'         => ['id' => $answer, 'type' => 'question-choice'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function retrieveAnswerId($legacyId)
    {
        $legacyId = (string) $legacyId;
        switch ($legacyId) {
            case 'immediately':
                $legacyId = 'Immediately';
                break;
            case '_1to3':
                $legacyId = '1 - 3 Months';
                break;
            case '_4to6':
                $legacyId = '4 - 6 Months';
                break;
            case '_7to9':
                $legacyId = '7 - 9 Months';
                break;
            case '_12':
                $legacyId = '10 - 12 Months';
                break;
            case '':
            case 'none':
                $legacyId = 'No plans. Just researching.';
                break;
        }

        if (isset($this->answers[$legacyId])) {
            return $this->answers[$legacyId]['_id'];
        }

        throw new \InvalidArgumentException(sprintf('Could not find an answer by key `%s`!', $legacyId));
    }

    /**
     * {@inheritdoc}
     */
    protected function retrieveQuestionId($legacyId)
    {
        $question = $this->getCollectionForModel('question')->findOne(['key' => $legacyId]);
        if (null === $question) {
            throw new \InvalidArgumentException(sprintf('Could not find question with key `%s`. Was it created?', $legacyId));
        }
        $this->loadAnswers($question['_id']);
        return $question['_id'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        return ['legacy.answers.purchaseIntent' => ['$exists' => true]];
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelType()
    {
        return 'input-answer-choice';
    }

    private function loadAnswers($questionId)
    {
        if (empty($this->answers)) {
            $choices = $this->getCollectionForModel('question-choice')->find(['question' => $questionId]);
            foreach ($choices as $choice) {
                $this->answers[$choice['name']] = $choice;
            }
        }
    }
}
