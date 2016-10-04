<?php

namespace AppBundle\Serializer\PublicApiRules;

use AppBundle\Serializer\PublicApiRuleInterface;

class CoreApplicationRule extends AbstractRule implements PublicApiRuleInterface
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
        return 'core-application';
    }

    /**
     * {@inheritdoc}
     */
    protected function getExcludeFields()
    {
        return [
            'allowedOrigins' => true,
            'users'          => true,
            'createdDate'    => true,
            'updatedDate'    => true,
            'touchedDate'    => true,
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
