<?php

namespace AppBundle\Import\Segment\Merrick\IdentityData;

use AppBundle\Import\Segment\Merrick\IdentityData;
use AppBundle\Import\Segment\Merrick\Identity\Transformer AS IdTransformer;

class GatedDownloads extends IdentityData
{

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer_identity_data_gated_downloads';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        $criteria = ['action' => 'download_content'];
        return array_merge(parent::getCriteria(), $criteria);
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelType()
    {
        return 'input-submission';
    }

    public function getLimit()
    {
        return 500;
    }

    /**
     * {@inheritdoc}
     */
    public function modify($limit = 200, $skip = 0)
    {
        $kvs = [];
        $docs = $this->getDocuments($limit, $skip);

        // looper per user_content_rel record
        foreach ($docs as $doc) {

            // is content for this publication so get the extra data needed for input-submission meta
            $contentInfo = $this->getContentInfo($doc);
            $doc['content_type'] = $contentInfo['content_type'];
            $doc['content_title'] = $contentInfo['name'];

            // finally grab radix identity to assocaite with, create if social account not imported
            $criteria = ['legacy.id' => $doc['user_id']];
            $identity = $this->getCollectionForModel('identity')->findOne($criteria, ['_id', '_type']);
            if (null === $identity) {
                //var_dump('missing identity for user_id:'.$doc['user_id']);
                $identity = $this->createSocialIdentity($doc);
                $doc['identity_id'] = (String) $identity['_id'];
                $doc['identity_type'] = $identity['_type'];
            } else {
                $doc['identity_id'] = $identity['_id'];
                if (!empty($identity['_type'])) {
                    $doc['identity_type'] = $identity['_type'];
                }
            }

            // finally format and save in kv for persist
            $kvs[] = $this->formatModel($doc);
        }

        return $kvs;
    }

        /**
     * Returns formatted key-values for the passed legacy document
     *
     * @param   array   $doc    The legacy key values
     * @return  mixed   array of key values or null
     */
    protected function formatModel(array $doc)
    {
        $transformer = new Transformer\GatedDownload();
        return $transformer->toApp($doc);
    }

    // get additional content informaiton we need for input-submission that does not exist in legacy content_user_rel collection
    public function getContentInfo(array $doc)
    {
        $criteria = ['content_id' => $doc['content_id']];
        $collection = sprintf('content_%s', $this->importer->getGroupKey());
        $contents = $this->source->retrieve($collection, $criteria, ['_id', 'content_id', 'content_type', 'name']);
        foreach ($contents as $content) {
            return $content;
        }
    }

    public function createSocialIdentity($doc)
    {
        $transformer = new IdTransformer\Social();
        $user = $transformer->toApp($doc);
        return $this->importer->getPersister()->insert('identity-internal', $user);
    }


}
