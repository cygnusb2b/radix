<?php

namespace AppBundle\Import\Segment\Merrick\Preimport;

use AppBundle\Import\Segment\Merrick\Preimport;

class SiteUserRel extends Preimport
{

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer_preimport_site_userrel';
    }

    /**
     * {@inheritdoc}
     */
   protected function getCriteria()
    {
        return  ['site' => ['$exists' => false]];
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
        // skipping by virtue of the update
        $docs = $this->getDocuments($limit);
        foreach ($docs as $doc) {
            $criteria = ['content_id' => $doc['content_id']];
            $contents = $this->source->retrieve('content', $criteria);

            foreach ($contents as $content) {
                $kv = [
                    'criteria'  => ['_id' => $doc['_id']],
                    'update' => ['$set' => ['site' => $this->importer->getDomain($content['pubgroup'])]],  
                ];
                $kvs[] = $kv;
            }

            // some content_id in content_use_rel do not exist in content collection - adding generic site
            if ($contents->count() == 0) {
                $kv = [
                    'criteria'  => ['_id' => $doc['_id']],
                    'update' => ['$set' => ['site' => 'www.cygnusb2b.com']],  
                ];
                $kvs[] = $kv;
            }

        }
        return $kvs;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(array $items)
    {
        if (empty($items)) {
            return $items;
        }
        foreach ($items as $item) {
            $this->source->batchUpdate('content_user_rel', $item['criteria'], $item['update']);
        }
        return $items;
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

    /**
     * {@inheritdoc}
     */
    protected function getModelType()
    {
        return 'input-submission';
    }

}
