<?php

namespace AppBundle\Import\Importer\Merrick;

use AppBundle\Import\Importer\Merrick;
use AppBundle\Import\Segment\Merrick\Customer as Segment;
use As3\SymfonyData\Import\ImporterInterface;
use As3\SymfonyData\Import\PersisterInterface;
use As3\SymfonyData\Import\SourceInterface;

/**
 * Imports customers from Merrick data
 *
 * @author  Josh Worden <jworden@southcomm.com>
 */
class Customer extends Merrick implements ImporterInterface
{
    /**
     * {@inheritdoc}
     */
    protected $supportedContexts = [
        'default'
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(PersisterInterface $persister, SourceInterface $source)
    {
        $source->setDatabase('merrick');
        parent::__construct($persister, $source);
        $this->segments[] = new Segment\Model\Customer($this, $source);
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer';
    }
}
