<?php

namespace AppBundle\Import\Segment\Platform;

use AppBundle\Import\Segment\Platform;

class PostStream extends Platform
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'platform_comment_poststream';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCollection()
    {
        return 'PostStream';
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelType()
    {
        return 'post-stream';
    }

    /**
     * {@inheritdoc}
     */
    protected function formatModel(array $doc)
    {
        $transformer = new Transformer\PostStream();
        return $transformer->toApp($doc);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        $criteria = [];
        return array_merge(parent::getCriteria(), $criteria);
    }

}
