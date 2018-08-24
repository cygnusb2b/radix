<?php

namespace AppBundle\Serializer\PublicApiRules;

use AppBundle\Serializer\PublicApiRuleInterface;

class QuestionChoiceSimpleRule extends AbstractRule implements PublicApiRuleInterface
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
            'choiceType'    => true,
            'description'   => true,
            'name'          => true,
            'sequence'      => true,
            'childQuestion' => true,
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
