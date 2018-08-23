<?php

namespace AppBundle\Import\Segment\Merrick;

use AppBundle\Import\Segment\Merrick;

abstract class IdentityData extends Merrick
{
    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->source->count($this->getCollection(), $this->getCriteria());
    }

    /**
     * {@inheritdoc}
     */
    protected function getCollection()
    {
        return 'content_user_rel';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        return ['site' => $this->importer->getDomain()];
    }
}
