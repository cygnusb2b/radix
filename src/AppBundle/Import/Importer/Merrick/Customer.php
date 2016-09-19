<?php

namespace AppBundle\Import\Importer\Merrick;

use AppBundle\Core\AccountManager;
use AppBundle\Import\Importer\Merrick;
use AppBundle\Import\Segment\Merrick\Customer as Segment;
use As3\SymfonyData\Import\PersisterInterface;
use As3\SymfonyData\Import\SourceInterface;

/**
 * Imports customers from Merrick data
 *
 * @author  Josh Worden <jworden@southcomm.com>
 */
class Customer extends Merrick
{
    /**
     * {@inheritdoc}
     */
    public function __construct(AccountManager $accountManager, PersisterInterface $persister, SourceInterface $source)
    {
        parent::__construct($accountManager, $persister, $source);
        $source->setDatabase('merrick');

        $this->segments[] = new Segment\Model\CustomerAccount($this, $source);
        $this->segments[] = new Segment\Model\CustomerIdentity($this, $source);
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer';
    }
}
