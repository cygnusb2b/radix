<?php

namespace AppBundle\Notifications;

use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;
use As3\Parameters\Parameters;
use AppBundle\Core\AccountManager;
use AppBundle\Serializer\PublicApiSerializer;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * Handles sending notifications
 *
 * @author Josh Worden <jworden@southcomm.com>
 */
class Manager
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
            // Notify exception?
            throw $e;
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

        $fromName  = $this->getValue($app, 'settings.notification.noreply.name', $app->get('name'));
        $fromEmail = $this->getValue($app, 'settings.notification.noreply.email', 'no-reply@radix.as3.io');
        $cxName = $this->getCustomerName($customer);
        $cxEmail = $customer->get('primaryEmail');

        $args['application']    = $app;
        $args['customer']       = $submission->get('customer');
        $args['template']       = $template;

        // Global notification settings
        $args['from']           = [$fromEmail => $fromName];
        $args['to']             = null === $cxName ? [$cxEmail] : [$cxEmail => $cxName];
        $args['greeting']       = null === $cxName ? 'Hello!' : sprintf('Hello, %s!', $customer->get('givenName'));
        $args['supportName']    = $this->getValue($app, 'settings.notification.support.name', 'Support');
        $args['supportEmail']   = $this->getValue($app, 'settings.notification.support.email', 'support@radix.as3.io');
        $args['appName']        = $this->getValue($app, 'settings.notification.name', $app->get('name'));
        $args['appLogo']        = $this->getValue($app, 'settings.notification.logo');

        // Subject
        $args['subject']        = sprintf('Notification from %s', $app->get('name'));

        if ($template && $template->get('subject')) {
            $args['subject'] = $template->get('subject');
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
     * @todo remove when object array access for Parameters or modelr dot notation is available.
     */
    private function getValue($model, $path, $default = null)
    {
        $parts = explode('.', $path);
        $accessor = array_shift($parts);
        $value = $model->get($accessor);
        if (null !== $value && !empty($parts)) {
            $path = implode('.', $parts);
            return $this->getValue($value, $path, $default);
        } elseif (null !== $value) {
            return $value;
        }
        return $default;
    }

    private function getCustomerName(Model $customer = null)
    {
        if ($customer) {
            $name = '';
            if ($customer->get('givenName')) {
                $name = $customer->get('givenName');
            }
            if ($customer->get('familyName')) {
                $name = sprintf('%s %s', $name, $customer->get('familyName'));
            }
            $name = trim($name);
            if ($name) {
                return $name;
            }
        }
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
