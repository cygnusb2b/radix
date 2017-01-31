<?php

namespace AppBundle\Import\Segment\Merrick\IdentityData;

use AppBundle\Import\Segment\Merrick\IdentityData;

class GatedDownloads extends IdentityData
{
    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->getCollectionForModel($this->getCollection())->count($this->getCriteria());
    }

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
    protected function getCollection()
    {
        return 'identity';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        //return ['legacy.id' => '4e57e2dfb612b55a0100000f'];
        return ['legacy.questions' => ['$exists' => true]];
        return [];
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
        return 50;
    }

    /**
     * {@inheritdoc}
     */
    public function modify($limit = 200, $skip = 0)
    {
        $kvs = [];
        $docs = $this->getDocuments($limit, $skip);

        // looping per identity
        foreach ($docs as $doc) {
            // can return multiple downloads for this identity return an array of arrays
            $gatedDownloads = $this->getGatedDownloads($doc);

            // add this group to the kvs to create
            $kvs = array_merge($kvs,$gatedDownloads);
        }

        return $kvs;
    }

    /**
     * Returns legacy gated download data for a given radix identity
     *
     * @param   array   $doc    The legacy key values
     * @return  mixed   array of key values or null
     */
    public function getGatedDownloads($doc)
    {
        $docs = [];
        $criteria = ['user_id' => (String) $doc['legacy']['id']];
        $gatedDownloads = $this->source->retrieve('content_user_rel', $criteria);
        foreach ($gatedDownloads as $gatedDownload) {
            $criteria = ['content_id' => $gatedDownload['content_id']];
            $contents = $this->source->retrieve('content', $criteria);
            foreach ($contents as $content) {
                $criteria = ['content_id' => $gatedDownload['content_id']];
                $collection = sprintf('content_%s', $content['pubgroup']);
                $contents = $this->source->retrieve($collection, $criteria, ['_id', 'content_id', 'content_type', 'name']);
                foreach ($contents as $content) {
                    $gatedDownload['content_id'] = $content['content_id'];
                    $gatedDownload['type'] = $content['content_type'];
                    $gatedDownload['title'] = $content['name'];
                }
            }
            $docs[] = $this->formatModel($gatedDownload);
        }
        return $docs;
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

}
