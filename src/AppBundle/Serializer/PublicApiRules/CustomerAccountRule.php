<?php

namespace AppBundle\Serializer\PublicApiRules;

use AppBundle\Serializer\PublicApiRuleInterface;

class CustomerAccountRule extends AbstractRule implements PublicApiRuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function getModelClass()
    {
        return 'As3\Modlr\Models\Model';
    }

    /**
     * {@inheritdoc}
     */
    public function getModelType()
    {
        return 'customer-account';
    }

    /**
     * {@inheritdoc}
     */
    protected function getExcludeFields()
    {
        return [
            'settings'    => true,
            'credentials' => true,
            'submissions' => true,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getIncludeFields()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function shouldIncludeAll()
    {
        return true;
    }
}
