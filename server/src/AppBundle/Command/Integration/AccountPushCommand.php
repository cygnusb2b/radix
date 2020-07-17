<?php

namespace AppBundle\Command\Integration;

use AppBundle\Integration\IntegrationManager;
use AppBundle\Integration\Execution\AccountPushExecution;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AccountPushCommand extends AbstractIntegrationCommand
{
    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return 'account-push';
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument('identity-type', InputArgument::OPTIONAL, 'Question integration ID.', 'identity-internal');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $type = $input->getArgument('identity-type');
        AccountPushExecution::validateModelType($type);

        $criteria = [
            '_type'             => $type,
            'integration.push'  => ['$exists' => false],
            'legacy'            => ['$exists' => false],
            'deleted'           => false,
        ];
        if ('identity-account' !== $type) {
            $criteria['emails.value'] = ['$exists' => true];
        }

        $this->getManager()->enablePostProcess(false);

        $cursor = $this->getStore()->findQuery('identity', $criteria);
        $total = $cursor->count();
        $output->writeLn(sprintf('Found %s `%s` records to push.', $total, $type));
        $index = 1;
        $totalTime = 0;
        foreach ($cursor as $identity) {
            $start = microtime(true);
            $output->writeLn(sprintf('Beginning push for ID: %s (%s of %s) ...', $identity->getId(), $index, $total));
            $identity->set('touchedDate', new \DateTime());
            $identity->save();
            $time = microtime(true) - $start;
            $totalTime += $time;
            $index++;

            $avg = $totalTime / $index;
            $estimate = ($avg * ($total - $index)) / 60;
            $output->writeLn(sprintf('Complete. Took %ss, Avg %ss, Remain %sm', round($time, 3), round($avg, 3), round($estimate, 3)));
        }

        $this->getManager()->enablePostProcess(true);
    }
}
