<?php

namespace AppBundle\Import\Segment\Merrick\Customer\Model;

use AppBundle\Import\Segment\Merrick\Customer;

class CustomerAddress extends Customer
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
        return 'merrick_customer_model_customer_address';
    }

    /**
     * Returns formatted key-values for the passed legacy document
     *
     * @param   array   $doc    The legacy key values
     * @return  mixed   array of key values or null
     */
    protected function formatModel(array $doc)
    {
        $transformer = new Transformer\CustomerAddress();
        $transformer->defineStatic('legacy.id', (string) $doc['_id']);
        $transformer->defineStatic('customer', ['id' => $doc['_id'], 'type' => $doc['_type']]);
        return $transformer->toApp($doc['legacy']['address']);
        var_dump($transformer->toApp($doc['legacy']['address']));
        die(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCollection()
    {
        return 'customer';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDocuments($limit = 200, $skip = 0)
    {
        return $this->getCollectionForModel($this->getCollection())->find($this->getCriteria(), $this->getFields())->sort($this->getSort())->limit($limit)->skip($skip);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        return ['legacy.address' => ['$exists' => true]];
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelType()
    {
        return 'customer-address';
    }
}
