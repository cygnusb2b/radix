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
        $this->segments[] = new Segment\Account($this, $source);
        $this->segments[] = new Segment\External($this, $source);
        $this->segments[] = new Segment\Internal($this, $source);
        $this->segments[] = new Segment\AccountEmail($this, $source);
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_identity';
    }
}
