<?php

namespace AppBundle\Import\Segment\Merrick\IdentityData;

use AppBundle\Import\Segment\Merrick\IdentityData;

abstract class Identity extends IdentityData
{

    /**
     * @var     array
     */
    protected $choices = [];

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

    protected function retrieveChoice($legacyId)
    {
        $legacyId = (string) $legacyId;
        if (!array_key_exists($legacyId, $this->choices)) {
            $this->choices[$legacyId] = $this->getCollectionForModel('question-choice')->findOne(['integration.pull.identifier' => $legacyId]);
        }
        return $this->choices[$legacyId];
    }
}
