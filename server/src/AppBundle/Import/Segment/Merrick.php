<?php

namespace AppBundle\Import\Segment;

use As3\SymfonyData\Import\Segment;

abstract class Merrick extends Segment
{
    /**
     * {@inheritdoc}
     */
    public function modify($limit = 200, $skip = 0)
    {
        $kvs = [];
        $docs = $this->getDocuments($limit, $skip);

        foreach ($docs as $doc) {
            $kv = $this->formatModel($doc);
            if (null !== $kv) {
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
        return $this->importer->getPersister()->batchInsert($this->getModelType(), $items);
    }

    /**
     * Returns formatted key-values for the passed legacy document
     *
     * @param   array   $doc    The legacy key values
     * @return  mixed   array of key values or null
     */
    abstract protected function formatModel(array $doc);

    /**
     * Returns the source collection
     *
     * @return  string
     */
    abstract protected function getCollection();

    /**
     * Returns documents from the source in a standard way
     *
     * @param   int     $limit
     * @param   int     $skip
     * @return  array
     */
    protected function getDocuments($limit = 200, $skip = 0)
    {
        return $this->source->retrieve($this->getCollection(), $this->getCriteria(), $this->getFields(), $this->getSort(), $limit, $skip);
    }

    /**
     * Returns the model type in use for this segment.
     *
     * @return  string
     */
    abstract protected function getModelType();
}
