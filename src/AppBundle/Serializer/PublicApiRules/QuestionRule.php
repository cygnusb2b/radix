<?php

namespace AppBundle\Serializer\PublicApiRules;

use AppBundle\Serializer\PublicApiRuleInterface;

class QuestionRule extends AbstractRule implements PublicApiRuleInterface
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
        return 'question';
    }

    /**
     * {@inheritdoc}
     */
    protected function getExcludeFields()
    {
        return [
            'pull'  => true,
            'push'  => true,
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
