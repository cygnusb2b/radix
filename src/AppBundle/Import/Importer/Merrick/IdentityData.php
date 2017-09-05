<?php

namespace AppBundle\Import\Importer\Merrick;

use AppBundle\Core\AccountManager;
use AppBundle\Import\Importer\Merrick;
use AppBundle\Import\Segment\Merrick\IdentityData as Segment;
use As3\SymfonyData\Import\PersisterInterface;
use As3\SymfonyData\Import\SourceInterface;

/**
 * Imports customers from Merrick data
 *
 * @author  Josh Worden <jworden@southcomm.com>
 */
class IdentityData extends Merrick
{
    /**
     * {@inheritdoc}
     */
    public function __construct(AccountManager $accountManager, PersisterInterface $persister, SourceInterface $source)
    {
        parent::__construct($accountManager, $persister, $source);
        $source->setDatabase('merrick');

        // Input submissions
        $this->segments[] = new Segment\InquiryRmi($this, $source);

        // Identity Answers
        $this->segments[] = new Segment\IdentityAnswerOmeda($this, $source);
        $this->segments[] = new Segment\IdentityAnswerIndustry($this, $source);

        $this->segments[] = new Segment\IdentityOptIn($this, $source);

        // Input Answers
        $this->segments[] = new Segment\InputAnswerOmeda($this, $source);
        $this->segments[] = new Segment\InputAnswerComments($this, $source);
        $this->segments[] = new Segment\InputAnswerPurchaseIntent($this, $source);
        $this->segments[] = new Segment\GatedDownloads($this, $source);
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_identity_data';
    }
}
