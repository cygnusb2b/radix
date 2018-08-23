<?php

namespace AppBundle\Serializer\PublicApiRules;

use AppBundle\Serializer\PublicApiRuleInterface;

class QuestionLabelRule extends QuestionRule implements PublicApiRuleInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getIncludeFields()
    {
        return [
            'name'  => true,
            'label' => true,
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
