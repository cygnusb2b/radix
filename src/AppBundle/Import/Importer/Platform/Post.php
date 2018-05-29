<?php

namespace AppBundle\Import\Importer\Platform;

use AppBundle\Core\AccountManager;
use AppBundle\Import\Importer\Platform;
use AppBundle\Import\Segment\Platform as Segment;
use As3\SymfonyData\Import\PersisterInterface;
use As3\SymfonyData\Import\SourceInterface;

/**
 * Imports poststream/post from Platform data
 *
 */
class Post extends Platform
{
    /**
     * {@inheritdoc}
     */
    public function __construct(AccountManager $accountManager, PersisterInterface $persister, SourceInterface $source)
    {
        parent::__construct($accountManager, $persister, $source);

        $this->segments[] = new Segment\PostStream($this, $source);
        $this->segments[] = new Segment\PostComment($this, $source);
        $this->segments[] = new Segment\PostReview($this, $source);
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'platform_post';
    }
}
