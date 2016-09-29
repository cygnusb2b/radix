<?php

namespace AppBundle\Notifications;

use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;
use As3\Parameters\Parameters;
use AppBundle\Core\AccountManager;
use AppBundle\Serializer\PublicApiSerializer;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use AppBundle\Utility\ModelUtility;
use AppBundle\Utility\RequestUtility;

/**
 * Handles sending notifications
 *
 * @author Josh Worden <jworden@southcomm.com>
 */
class NotificationManager
{
    /**
     * @var     NotificationFactoryInterface
     */
    private $defaultFactory;

    /**
     * @var     AccountManager
     */
    private $accountManager;

    /**
     * @var     NotificationFactoryInterface
     */
    private $factories = [];

    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var PublicApiSerializer
     */
    private $serializer;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @param   NotificationFactory $factory
     * @param   Store               $store
     * @param   Swift_Mailer        $mailer
     * @param   EngineInterface     $templating
     * @param   AccountManager      $accountManager
     * @param   PublicApiSerializer $serializer
     */
    public function __construct(NotificationFactoryInterface $factory, Store $store, Swift_Mailer $mailer, EngineInterface $templating, AccountManager $accountManager, PublicApiSerializer $serializer)
    {
        $this->defaultFactory = $factory;
        $this->store = $store;
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->accountManager = $accountManager;
        $this->serializer = $serializer;
    }

    /**
     * @param   NotificationFactoryInterface
     */
    public function addFactory(NotificationFactoryInterface $factory)
    {
        $this->factories[] = $factory;
        return $this;
    }

    /**
     * Sends a notification for the specified submission model
     *
     * @param   Model   $submission     The submission being processed
     * @param   string  $actionKey      The action key (template name)
     * @return  boolean                 If the notification was sent
     */
    public function sendNotificationFor(Model $submission, $actionKey, array $args = [])
    {
        $factory        = $this->defaultFactory;
        $template       = $this->getNotificationTemplate($submission, $actionKey);
        $templateKey    = $this->getTemplateKey($submission, $actionKey);

        foreach ($this->factories as $handler) {
            if (true === $handler->supports($submission, $actionKey, $template)) {
                $factory = $handler;
                break;
            }
        }

        if (false === $factory->supports($submission, $actionKey, $template)) {
            return false;
        }

        try {
            $args = $this->inject($args, $submission, $template);
            $notification = $factory->generate($submission, $actionKey, $template, $args);
            $sz = $this->serialize($notification->getArgs());
            $contents = $this->templating->render($templateKey, $sz);

            if (empty($contents)) {
                return false;
            }

            return $this->sendNotification(
                $contents,
                $notification->getSubject(),
                $notification->getFrom(),
                $notification->getTo(),
                $notification->getCc(),
                $notification->getBcc()
            );

        } catch (\Exception $e) {
            RequestUtility::notifyException($e);
            return false;
        }
    }

    /**
     * Sends a notification using the specified parameters
     *
     * @param   string  $contents       The email contents
     * @param   string  $subject        The email subject line (will be prefix with branding)
     * @param   array   $from           Tuple containing sender address [from@email.tld => 'My Fancy Name']
     * @param   array   $to             An array of addresses to send to. Can be associative [name -> email] or not [email, email2]
     * @param   array   $cc             An array of addresses to send to. Can be associative [name -> email] or not [email, email2]
     * @param   array   $bcc            An array of addresses to send to. Can be associative [name -> email] or not [email, email2]
     *
     * @return  bool    If the send was sucessfully queued.
     */
    private function sendNotification($contents, $subject, array $from, array $to, array $cc = [], array $bcc = [])
    {
        $message = $this->mailer->createMessage()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setCc($cc)
            ->setBcc($cc)
            ->setBody($contents, 'text/html')
        ;

        $instance = $this->mailer->send($message);
        return $instance;
    }

    /**
     * Injects required parameters for notification and templating
     *
     * @param   Model   $submission
     * @param   array   $args
     * @return  array
     */
    private function inject(array $args, Model $submission, Model $template = null)
    {
        $app = $this->accountManager->getApplication();
        $customer = $submission->get('customer');

        // Global notification settings
        $fromName  = (null === $value = ModelUtility::getModelValueFor($app, 'settings.notification.noreply.name')) ? $app->get('name') : $value;
        $fromEmail  = (null === $value = ModelUtility::getModelValueFor($app, 'settings.notification.noreply.email')) ? 'no-reply@radix.as3.io': $value;
        $args['from']           = [ $fromEmail => $fromName ];

        $args['application']    = $app;
        $args['customer']       = $submission->get('customer');
        $args['template']       = $template;

        // Routing
        if ($template) {
            foreach (['to', 'cc', 'bcc'] as $field) {
                if (!empty($template->get($field))) {
                    $args[$field] = $template->get($field);
                }
            }
            if ($template->get('subject')) {
                $args['subject'] = $template->get('subject');
            }
        }

        // Default to if not set by template
        if (!isset($args['to'])) {
            $args['to'] = [ $customer->get('primaryEmail') => $customer->get('fullName') ];
        }

        return $args;
    }

    /**
     * Serializes template args
     *
     * @param   Model   $submission
     * @param   array   $args
     * @return  Parameters
     */
    private function serialize(array $args)
    {
        foreach ($args as $k => $v) {
            if ($v instanceof Model) {
                $args[$k] = $this->serializer->serialize($v)['data'];
            }
        }
        return ['values' => new Parameters($args)];
    }

    /**
     * Returns the `notification-template`
     *
     * @param   Model   $submission
     * @param   string  $actionKey
     * @return  Model|null
     */
    private function getNotificationTemplate(Model $submission, $actionKey)
    {
        return $this->store->findQuery('notification-template', ['sourceKey' => $submission->get('sourceKey'), 'template' => $actionKey])->getSingleResult();
    }

    /**
     * Returns the namespaced template name for the specified submission
     *
     * @param   Model   $submission     The submission
     * @param   string  $actionKey      The action key
     * @return  string
     */
    private function getTemplateKey(Model $submission, $actionKey)
    {
        return sprintf('notification-template/%s/%s.html.twig', $submission->get('sourceKey'), $actionKey);
    }
}
