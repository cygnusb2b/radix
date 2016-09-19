<?php

namespace AppBundle\Import\Segment\Merrick\Customer\Model;

class CustomerAccount extends Customer
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer_model_customer_account';
    }

    /**
     * {@inheritdoc}
     */
    protected function formatModel(array $doc)
    {
        $kv = [
            'legacy'    => [
                'id'        => (string) $doc['_id'],
                'source'    => 'users_v2',
            ],
        ];
        if ($this->isSocial($doc)) {
            return;
        }

        $transformer = new Transformer\CustomerAccount();
        return array_merge($kv, $transformer->toApp($doc));
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        $criteria = [
            'pwd'   => [
                '$exists' => true
            ]
        ];
        return array_merge(parent::getCriteria(), $criteria);
    }

    protected function getModelType()
    {
        return 'customer-account';
    }

    private function isSocial(array $doc)
    {
        switch (true) {
            case isset($doc['gigya_id']):
            case isset($doc['facebook_id']):
            case isset($doc['linkedin_id']):
            case isset($doc['google_id']):
            case isset($doc['twitter_id']):
                return true;
        }
        return false;
    }
}
