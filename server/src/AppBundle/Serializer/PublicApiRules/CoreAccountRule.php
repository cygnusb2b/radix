<?php

namespace AppBundle\Serializer\PublicApiRules;

use AppBundle\Serializer\PublicApiRuleInterface;

class CoreAccountRule extends AbstractRule implements PublicApiRuleInterface
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
        return 'core-account';
    }

    /**
     * {@inheritdoc}
     */
    protected function getExcludeFields()
    {
        return [
            'applications'  => true,
            'createdDate'   => true,
            'updatedDate'   => true,
            'touchedDate'   => true,
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
