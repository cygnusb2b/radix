<?php

namespace AppBundle\Serializer\PublicApiRules;

use AppBundle\Serializer\PublicApiRuleInterface;

class QuestionChoiceNameRule extends AbstractRule implements PublicApiRuleInterface
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
        return 'question-choice';
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
            'name' => true,
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
