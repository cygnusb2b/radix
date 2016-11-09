<?php

namespace AppBundle\Command\Notifications;

use AppBundle\Core\AccountManager;
use AppBundle\Notifications\NotificationManager;
use As3\Modlr\Store\Store;
use As3\Parameters\Parameters;
use As3\SymfonyData\Console\Command;
use Doctrine\MongoDb\Connection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use \Swift_Mailer;
use \Swift_Transport_EsmtpTransport;

class ResendCommand extends Command
{
    /**
     * @var NotificationManager
     */
    private $notificationManager;

    /**
     * @var AccountManager
     */
    private $accountManager;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var Swift_Transport_EsmtpTransport
     */
    private $transport;

    /**
     * @var Array
     */
    private $modelCache = [];

    /**
     * @var Connection
     */
    private $platformConnection;

    /**
     * Constructor.
     *
     * @param   string                  command name
     * @param   NotificationManager     $notificationManager
     * @param   AccountManager          $accountManager
     * @param   Store                   $store
     */
    public function __construct($name, NotificationManager $notificationManager, AccountManager $accountManager, Store $store, Swift_Mailer $mailer, Swift_Transport_EsmtpTransport $transport)
    {
        $this->notificationManager = $notificationManager;
        $this->accountManager = $accountManager;
        $this->store = $store;
        $this->mailer = $mailer;
        $this->transport = $transport;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument('domain', InputArgument::REQUIRED, 'HTTP host domain');
        $this->addArgument('to', InputArgument::REQUIRED, 'The address notifications should be sent to by default.');
        $this->addArgument('bcc', InputArgument::OPTIONAL, 'The address notifications should be bccd by default.', 'emailactivity@cygnus.com');
        $this->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Number of items to process at once', 10);
        parent::configure();
    }

    protected function doCommandResend()
    {
        $this->writeln('Starting resend');
        $this->indent();
        $criteria = [
            'sourceKey'                 => 'inquiry',
            'payload.notify.enabled'    => ['$exists' => false],
            'legacy.resent'             => ['$exists' => false]
        ];

        $counter = function() use ($criteria) {
            return $this->store->findQuery('input-submission', $criteria)->count();
        };

        $retriever = function($limit, $skip) use ($criteria) {
            $skip = 'prod' === $this->input->getOption('env') ? 0 : $skip;
            return $this->store->findQuery('input-submission', $criteria, [], [], $skip, $limit);
        };

        $modifier = function($submission) {

            $notify = $this->buildNotify($submission);
            $result = $this->notificationManager->notifySubmission($submission, $notify);

            if (false !== $result) {
                return $submission;
            }
        };

        $persister = function($items) {
            $this->mailer->getTransport()->getSpool()->flushQueue($this->transport);
            $this->markResent($items);
        };

        $this->loop($counter, $retriever, $modifier, $persister, null, $this->input->getOption('limit'));
    }

    private function buildNotify($submission)
    {
        $id = $submission->get('payload')->meta['model']['identifier'];
        $model = $this->getModelFor($id);

        $notification = [
            'enabled'   => true,
            'subject'   => 'Request for More Information',
        ];
        $notification = $this->buildNotifyEmails($model, $notification);
        $notification = $this->buildNotifyExtra($model, $notification);

        $notification = new Parameters($notification);
        return $notification;
    }

    private function buildNotifyEmails($model, array $notification)
    {
        $contacts               = [];
        $notification['to']     = $this->input->getArgument('to');
        $notification['cc']     = [];
        $notification['bcc']    = explode(',', $this->input->getArgument('bcc'));

        if ('Company' === $model['type'] && isset($model['salesContacts'])) {
            $contacts = $model['salesContacts'];
        } elseif (isset($model['company']['salesContacts'])) {
            $contacts = $model['company']['salesContacts'];
        }

        if (!empty($contacts)) {
            $notification['cc'] = $notification['to'];
            $notification['to'] = [];
            foreach ($contacts as $contact) {
                $contact = $this->getModelFor($contact);
                if (!isset($contact['email'])) {
                    continue;
                }
                $notification['to'][] = $contact['email'];
            }
        }
        return $notification;
    }

    private function buildNotifyExtra($model, array $notification)
    {
        $url         = sprintf('http://%s/%s', $this->input->getArgument('domain'), $model['_id']);
        $description = sprintf('<a href="/%s">%s</a>', $model['_id'], $model['name']);

        if (isset($model['company'])) {
            $notification['extra']['company'] = $model['company']['name'];
            $description = sprintf('%s from <a href="/%s">%s</a>', $description, $model['company']['_id'], $model['company']['name']);
        }

        $notification['extra'][$model['type']] = $model['name'];
        $notification['extra']['url'] = sprintf('<a href="%s">%s</a>', $url, $url);

        return $notification;
    }

    private function getModelFor($id)
    {
        if (array_key_exists($id, $this->modelCache)) {
            return $this->modelCache[$id];
        }
        $collection = $this->getPlatformCollection('Content');
        $model = $collection->findOne(['_id' => (int) $id], ['name', 'email', 'company', 'type', 'mutations.Website.name', 'salesContacts']);

        if ('Product' === $model['type']) {
            $model['company'] = $this->getModelFor($model['company']);
        }
        if (isset($model['mutations']['Website']['name']) && !empty($model['mutations']['Website']['name'])) {
            $model['name'] = $model['mutations']['Website']['name'];
        }
        $this->modelCache[$id] = $model;
        return $model;
    }

    private function getPlatformCollection($collection)
    {
        $this->platformConnection = $this->platformConnection ?: new Connection('mongo.platform.baseplatform.io');
        $database = sprintf(
            '%s_%s_platform',
            $this->accountManager->getApplication()->get('account')->get('key'),
            $this->accountManager->getApplication()->get('key')
        );
        return $this->platformConnection->selectCollection($database, $collection);
    }

    private function markResent(array $items = [])
    {
        $ids = [];
        foreach ($items as $item) {
            $ids[] = new \MongoId($item->getId());
        }
        if (!empty($ids)) {
            $em = $this->store->getMetadataForType('input-submission');
            $collection = $this->store->getPersisterFor('input-submission')->getQuery()->getModelCollection($em);

            if ('prod' === $this->input->getOption('env')) {
                $collection->update(
                    ['_id'  => ['$in' => $ids]],
                    ['$set' => ['legacy.resent' => true]],
                    ['multiple' => true]
                );
            }
        }
    }
}
