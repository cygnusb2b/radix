<?php

namespace AppBundle\Import\Segment\Merrick\Identity\Model;

use AppBundle\Import\Segment\Merrick\Identity;

class IdentityAccountEmail extends Identity
{
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
        return 'merrick_customer_model_identity_account_email';
    }

    /**
     * Returns formatted key-values for the passed legacy document
     *
     * @param   array   $doc    The legacy key values
     * @return  mixed   array of key values or null
     */
    protected function formatModel(array $doc)
    {
        $email = [
            'id'        => (string) $doc['_id'],
            'value'     => $doc['legacy']['email'],
            'account'   => $doc['_id']
        ];
        $transformer = new Transformer\IdentityAccountEmail();
        return $transformer->toApp($email);
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
        return ['_type' => 'identity-account'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelType()
    {
        return 'identity-account-email';
    }
}
