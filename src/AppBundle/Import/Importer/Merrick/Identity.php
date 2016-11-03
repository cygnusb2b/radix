<?php

namespace AppBundle\Import\Importer\Merrick;

use AppBundle\Core\AccountManager;
use AppBundle\Import\Importer\Merrick;
use AppBundle\Import\Segment\Merrick\Identity as Segment;
use As3\SymfonyData\Import\PersisterInterface;
use As3\SymfonyData\Import\SourceInterface;

/**
 * Imports customers from Merrick data
 *
 * @author  Josh Worden <jworden@southcomm.com>
 */
class Identity extends Merrick
{
    /**
     * {@inheritdoc}
     */
    public function __construct(AccountManager $accountManager, PersisterInterface $persister, SourceInterface $source)
    {
        parent::__construct($accountManager, $persister, $source);
        $source->setDatabase('merrick');

        // Identity data
        $this->segments[] = new Segment\Model\IdentityAccount($this, $source);
        $this->segments[] = new Segment\Model\IdentityExternal($this, $source);
        $this->segments[] = new Segment\Model\IdentityInternal($this, $source);
        $this->segments[] = new Segment\Model\IdentityAccountEmail($this, $source);

        // Demographics
        $this->segments[] = new Segment\Model\IdentityAnswer($this, $source);
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_identity';
    }
}
