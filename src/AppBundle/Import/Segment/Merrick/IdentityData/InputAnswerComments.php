<?php

namespace AppBundle\Import\Segment\Merrick\IdentityData;

class InputAnswerComments extends InputAnswer
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer_identity_data_input_answer_comments';
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
        $question = $this->retrieveQuestionId('comments');

        return [
            'legacy'    => [
                'id'            => (string) $doc['_id'],
                'source'        => 'input-submission_comments'
            ],
            'createdDate'   => $doc['createdDate'],
            'touchedDate'   => $doc['createdDate'],
            'updatedDate'   => $doc['createdDate'],
            'question'      => ['id' => $question, 'type' => 'question'],
            'submission'    => ['id' => (string) $doc['_id'], 'type' => 'input-submission'],
            'value'         => $doc['legacy']['answers']['comments'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function retrieveQuestionId($legacyId)
    {
        $question = $this->getCollectionForModel('question')->findOne(['key' => 'comments']);
        if (null === $question) {
            throw new \InvalidArgumentException('Could not find question with key `comments`. Was it created?');
        }
        return $question['_id'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        return ['legacy.answers.comments' => ['$exists' => true]];
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelType()
    {
        return 'input-answer-string';
    }
}
