<?php

namespace AppBundle\Command\Integration;

use AppBundle\Integration\IntegrationManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IdentifyCommand extends AbstractIntegrationCommand
{
    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return 'identify';
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument('ident', InputArgument::REQUIRED, 'One of `id` `pull` or `upsert`');
        $this->addArgument('value', InputArgument::REQUIRED, 'The identification value.');
        $this->addOption('props', 'p', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'The upsert identity property values.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \BadMethodCallException(sprintf('%s is not yet implemented.', __METHOD__));
    }
}
