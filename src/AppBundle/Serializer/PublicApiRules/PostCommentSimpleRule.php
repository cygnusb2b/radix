<?php

namespace AppBundle\Serializer\PublicApiRules;

use As3\Modlr\Models\Model;
use AppBundle\Serializer\PublicApiRuleInterface;

class PostCommentSimpleRule extends AbstractRule implements PublicApiRuleInterface
{
    /**
     * {@inheritdoc}
     */
     protected function getCustomFieldSerializers()
     {
        return [
            'createdDate' => function(Model $model, $value) {
                if ($value instanceof \DateTime) {
                    return $value->format('M d Y H:i');
                }
            },
        ];
     }

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
        return 'post-comment';
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
            'body' => true,
            'approved' => true,
            'displayName' => true,
            'picture' => true,
            'createdDate' => true,
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
