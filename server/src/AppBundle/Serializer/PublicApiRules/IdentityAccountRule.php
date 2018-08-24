<?php

namespace AppBundle\Serializer\PublicApiRules;

use AppBundle\Serializer\PublicApiRuleInterface;

class IdentityAccountRule extends AbstractRule implements PublicApiRuleInterface
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
        return 'identity-account';
    }

    /**
     * {@inheritdoc}
     */
    protected function getExcludeFields()
    {
        return [
            'settings'    => true,
            'credentials' => true,
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
