<?php

namespace AppBundle\Serializer\PublicApiRules;

use AppBundle\Serializer\PublicApiRuleInterface;

class QuestionSimpleRule extends QuestionRule implements PublicApiRuleInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getIncludeFields()
    {
        return [
            'allowHtml'         => true,
            'boundTo'           => true,
            'choices'           => true,
            'relatedChoices'    => true,
            'key'               => true,
            'name'              => true,
            'label'             => true,
            'questionType'      => true,
            'hasChildQuestions' => true,
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
