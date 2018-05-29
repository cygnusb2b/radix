<?php

namespace AppBundle\Import\Segment\Platform;

use AppBundle\Import\Segment\Platform;

class PostReview extends Platform
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'platform_comment_postreview';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCollection()
    {
        return 'Post';
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelType()
    {
        return 'post-review';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        $criteria = [ 'type' => 'Review'];
        return array_merge(parent::getCriteria(), $criteria);
    }

    /**
     * {@inheritdoc}
     */
    protected function formatModel(array $doc)
    {
        // some are empty - should we even import these?
        if (!empty($doc['customer'])) {
            
            // look up the customer, and replace the old component customer id with the radix identity id
            $criteria= ['_id' => $doc['customer']];
            $customer = $this->source->retrieve('Customer', $criteria, ['_id','import.userId'], [], 1, 0);
            foreach ($customer as $c) {
                if (!empty($c['import'])) {
                    if (!empty($c['import']['userId'])) {
                        $criteria = ['legacy.id' => $c['import']['userId']];
                        $identity = $this->getCollectionForModel('identity')->findOne($criteria, ['_id', '_type']);

                        if (!empty($identity)) {
                            $doc['account'] = [
                                'id' => $identity['_id'],
                                'type' => 'identity-account'
                            ];
                        }
                    }
                }
            }
        }

        // component post objects with parent elements do not contain the stream element, going to populate for radix consistency
        if (empty($doc['stream']) && !empty($doc['parent'])) {
            // get parent and check for stream there
            $criteria = ['_id' => $doc['parent']];
            $parent = $this->getCollectionForModel('post')->findOne($criteria, ['_id', 'parent', 'stream']);
            if (!empty($parent)) {
                // keep moving up the chain until you find a parent set
                while (!empty($parent) && empty($parent['stream'])) {
                    $criteria = ['_id' => $parent['parent']];
                    $parent = $this->getCollectionForModel('post')->findOne($criteria, ['_id', 'parent', 'stream']);
                }
                if (!empty($parent['stream'])) {
                    $doc['stream'] = $parent['stream'];
                }
            }
        }

        $transformer = new Transformer\PostReview();
        return $transformer->toApp($doc);
    }

}
