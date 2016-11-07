<?php

namespace AppBundle\Import\Segment\Merrick\IdentityData;

use AppBundle\Import\Segment\Merrick\IdentityData;

/**
 * Creates input-submission models from legacy data
 */
abstract class Inquiry extends IdentityData
{
    /**
     * {@inheritdoc}
     */
    protected function getModelType()
    {
        return 'input-submission';
    }
}
