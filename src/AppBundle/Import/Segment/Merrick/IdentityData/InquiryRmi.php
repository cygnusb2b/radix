<?php

namespace AppBundle\Import\Segment\Merrick\IdentityData;

/**
 * {@inheritdoc}
 */
class InquiryRmi extends Inquiry
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_identity_data_inquiry_rmi';
    }

    /**
     * {@inheritdoc}
     */
    protected function formatModel(array $doc)
    {
        $transformer = new Transformer\InquiryRmi();
        return $transformer->toApp($doc);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        $criteria = [
            'action'   => 'rmi'
        ];
        return array_merge(parent::getCriteria(), $criteria);
    }
}
