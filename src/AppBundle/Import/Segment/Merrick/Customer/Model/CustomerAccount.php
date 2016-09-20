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
        if ($this->isSocial($doc)) {
            return;
        }

        $transformer = new Transformer\Customer();
        return $transformer->toApp($doc);
        $kv = $transformer->toApp($doc);

        var_dump($doc, $kv);
        die(__METHOD__);
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
        $fields = ['gigya_id', 'facebook_id', 'linkedin_id', 'google_id', 'twitter_id'];

        foreach ($fields as $field) {
            if (isset($doc[$field])) {
                $val = trim($doc[$field]);
                if (!empty($val)) {
                    return true;
                }
            }
        }
        return false;
    }
}
