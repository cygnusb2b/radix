<?php

namespace AppBundle\Import\Segment\Merrick\Customer\Model;

use As3\SymfonyData\Import\Segment;

abstract class Customer extends Segment
{
    /**
     * {@inheritdoc}
     */
    final public function count()
    {
        return $this->source->count('users_v2', $this->getCriteria());
    }

    /**
     * {@inheritdoc}
     */
    final public function modify($limit = 200, $skip = 0)
    {
        $kvs = [];
        $docs = $this->source->retrieve('users_v2', $this->getCriteria(), $this->getFields(), $this->getSort(), $limit, $skip);
        $now = new \DateTime();

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
    final public function persist(array $items)
    {
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
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        return ['site' => $this->importer->getDomain()];
    }
}
