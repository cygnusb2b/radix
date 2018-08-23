<?php

namespace AppBundle\PostProcess;

use AppBundle\Core\AccountManager;
use As3\Bundle\PostProcessBundle\Plugins\PluginInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpFoundation\Response;

class NewRelicPlugin implements PluginInterface
{
    /**
     * @var AccountManager
     */
    private $manager;

    /**
     * @param   AccountManager  $manager
     */
    public function __construct(AccountManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Prevents transaction stat skew by ending the current transaction and starting a new one.
     *
     * {@inheritdoc}
     */
    public function execute(PostResponseEvent $event)
    {
        if (extension_loaded('newrelic')) {
            newrelic_end_transaction();
            newrelic_start_transaction($this->manager->getNewRelicAppName());
            $this->manager->configureNewRelic();
            newrelic_name_transaction('post_process_tasks');
            newrelic_ignore_apdex();
            newrelic_background_job(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function filterResponse(Response $response)
    {
    }
}
