<?php

namespace AppBundle\Command\Integration;

use AppBundle\Integration\IntegrationManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QuestionPullCommand extends AbstractIntegrationCommand
{
    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return 'question-pull';
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument('integration-id', InputArgument::OPTIONAL, 'Question integration ID.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('integration-id');
        if ($id) {
            $msg = sprintf('Running integration pull for %s...', $id);
        } else {
            $msg = 'Running all integration pulls...';
        }

        $output->writeLn($msg);
        $this->getManager()->questionPull($id);
        $output->writeLn('Operation complete.');
    }
}
