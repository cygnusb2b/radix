<?php

namespace AppBundle\Import\Importer\Merrick;

use AppBundle\Core\AccountManager;
use AppBundle\Import\Importer\Merrick;
use AppBundle\Import\Segment\Merrick\Preimport as Segment;
use As3\SymfonyData\Import\PersisterInterface;
use As3\SymfonyData\Import\SourceInterface;

/**
 * Updates merrick data prior to import
 *
 * @author  Josh Worden <jworden@southcomm.com>
 */
class Preimport extends Merrick
{
    /**
     * {@inheritdoc}
     */
    public function __construct(AccountManager $accountManager, PersisterInterface $persister, SourceInterface $source)
    {
        parent::__construct($accountManager, $persister, $source);
        $source->setDatabase('merrick');

        // Identity data
        $this->segments[] = new Segment\SiteUserRel($this, $source);

    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_preimport';
    }
}
