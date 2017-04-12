<?php

namespace AppBundle\Import\Segment\Merrick\Identity;

use AppBundle\Import\Segment\Merrick\Identity;

class Account extends Identity
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer_identity_account';
    }

    /**
     * {@inheritdoc}
     */
    protected function formatModel(array $doc)
    {
        if ($this->isSocial($doc)) {
            return;
        }

        $transformer = new Transformer\Account('omeda', $this->getPushIntegration());
        return $transformer->toApp($doc);
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

    /**
     * {@inheritdoc}
     */
    protected function getModelType()
    {
        return 'identity-account';
    }

    /**
     * Determines if the passed data concerns a social account
     *
     * @param   array   $doc    The legacy source data
     * @return  bool
     */
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
