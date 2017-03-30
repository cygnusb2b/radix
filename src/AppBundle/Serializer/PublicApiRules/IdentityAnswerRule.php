<?php

namespace AppBundle\Serializer\PublicApiRules;

use AppBundle\Serializer\PublicApiRuleInterface;

class IdentityAnswerRule extends AbstractRule implements PublicApiRuleInterface
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
        return 'identity-answer-choice';
    }

    /**
     * {@inheritdoc}
     */
    protected function getExcludeFields()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function getIncludeFields()
    {
        return [
            'question'  => 1,
            'value'     => 1,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function shouldIncludeAll()
    {
        return false;
    }
}
