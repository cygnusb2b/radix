<?php

namespace AppBundle\Notifications;

use As3\Modlr\Models\Model;
use As3\Parameters\Parameters;
use AppBundle\Core\AccountManager;
use AppBundle\Templating\TemplateLoader;
use AppBundle\Utility\ModelUtility;
use AppBundle\Utility\RequestUtility;
use AppBundle\Question\QuestionAnswerFactory;
use Swift_Mailer;
use \DateTime;

use ICanBoogie\Inflector;


/**
 * Handles sending notifications
 *
 * @author  Josh Worden <jworden@southcomm.com>
 * @author  Jacob Bare  <jacob.bare@gmail.com>
 */
class NotificationManager
{
    const DEFAULT_FROM_NAME  = 'Radix Notifications';
    const DEFAULT_FROM_EMAIL = 'no-reply@radix.as3.io';

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
     * @var TemplateLoader
     */
    private $templateLoader;

    /**
     * @param   NotificationFactory $factory
     * @param   Swift_Mailer        $mailer
     * @param   TemplateLoader      $templateLoader
     * @param   AccountManager      $accountManager
     */
    public function __construct(NotificationFactoryInterface $factory, Swift_Mailer $mailer, TemplateLoader $templateLoader, AccountManager $accountManager)
    {
        $this->defaultFactory = $factory;
        $this->mailer = $mailer;
        $this->templateLoader = $templateLoader;
        $this->accountManager = $accountManager;
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
     * Sends a notification for the specified submission model.
     * Used to send a notification to the email address of the user submitting, if applicable.
     *
     * @param   Model   $submission     The submission being processed
     * @param   string  $actionKey      The action key (template name)
     * @return  boolean                 If the notification was sent
     */
    public function sendNotificationFor(Model $submission)
    {
        $factory        = $this->defaultFactory;
        $template       = $this->getTemplate($submission);
        $templateKey    = $this->getTemplateKey($submission);

        foreach ($this->factories as $handler) {
            if (true === $handler->supports($submission, $template)) {
                $factory = $handler;
                break;
            }
        }

        if (false === $factory->supports($submission, $template)) {
            return false;
        }

        try {
            $args = $this->inject($submission, $template);
            $notification = $factory->generate($submission, $template, $args);

            $params = $notification->getArgs();
            $params['notification'] =  [
                'to'  => $this->formatSendToValues($notification->getTo()),
                'cc'  => $this->formatSendToValues($notification->getCc()),
                'bcc' => $this->formatSendToValues($notification->getBcc()),
            ];

            $contents = $this->templateLoader->render($templateKey, $params);

            if (empty($contents)) {
                return false;
            }

            return $this->sendNotification(
                $contents,
                $notification->getSubject(),
                $notification->getFrom(),
                $notification->getTo(),
                $notification->getCc(),
                $notification->getBcc(),
                $submission->get('createdDate')
            );

        } catch (\Exception $e) {
            RequestUtility::notifyException($e);
            return false;
        }
    }

    /**
     * Notifies recipients of the submission, if applicable.
     * This is an email notification in addition (and seperate to) the notification that is sent to the person submitting.
     *
     * @param   Model       $submission
     * @param   Parameters  $notify
     * @return  bool
     */
    public function notifySubmission(Model $submission, Parameters $notify)
    {
        $templateName = $notify->get('template');
        if (!$notify->get('enabled')) {
            return false;
        }
        $inflector      = Inflector::get('en');
        $templateName   = TemplateLoader::getTemplateKey('template-notify', $notify->get('template'));
        $default        = TemplateLoader::getTemplateKey('template-notify', 'default');
        $answers        = QuestionAnswerFactory::humanizeAnswers($submission->get('answers'));
        $identityValues = $this->humanizeIdentityValues($submission->get('identity'));
        $extra          = [];

        foreach ((array) $notify->get('extra') as $key => $value) {
            $extra[$inflector->titleize($key)] = strip_tags($value, '<a><strong><em><b><i><u>');
        }

        $params = [
            'application'    => $this->accountManager->getApplication(),
            'submission'     => $submission,
            'answers'        => $answers,
            'identityValues' => $identityValues,
            'extra'          => $extra,
            'title'          => $notify->get('subject', 'Submission Notification'),
            'notification'   => [
                'to'  => $this->formatSendToValues((array) $notify->get('to')),
                'cc'  => $this->formatSendToValues((array) $notify->get('cc')),
                'bcc' => $this->formatSendToValues((array) $notify->get('bcc')),
            ]
        ];

        try {
            // @todo The TemplateLoader should support passing multiple template names so this doesn't need to happen here.
            $contents = $this->templateLoader->render($templateName, $params);
        } catch (\Exception $e) {
            $contents = $this->templateLoader->render($default, $params);
        }

        if (empty($contents)) {
            return false;
        }
        try {
            return $this->sendNotification(
                $contents,
                $notify->get('subject', 'Submission Notification'),
                [ $this->getFromEmail() => $this->getFromName() ],
                (array) $notify->get('to'),
                (array) $notify->get('cc'),
                (array) $notify->get('bcc'),
                $submission->get('createdDate')
            );
        } catch (\Exception $e) {
            RequestUtility::notifyException($e);
            return false;
        }
    }

    /**
     * Conistently formats send to values.
     *
     * @param   array   $values     The send to values.
     * @return  array
     */
    private function formatSendToValues(array $values)
    {
        $formatted = [];
        foreach ($values as $index => $value) {
            $formatted[] = [
                'name'  => is_numeric($index) ? null   : $value,
                'email' => is_numeric($index) ? $value : $index,
            ];
        }
        return $formatted;
    }

    /**
     * Gets the from email.
     *
     * @param   string|null     $email
     * @return  string
     */
    private function getFromEmail($email = null)
    {
        if (!empty($email)) {
            return $email;
        }
        $app = $this->accountManager->getApplication();
        return (null === $value = ModelUtility::getModelValueFor($app, 'settings.notifications.email')) ? self::DEFAULT_FROM_EMAIL : $value;
    }

    /**
     * Gets the from name.
     *
     * @param   string|null     $name
     * @return  string
     */
    private function getFromName($name = null)
    {
        if (!empty($name)) {
            return $name;
        }
        $app  = $this->accountManager->getApplication();
        $name = (null === $value = ModelUtility::getModelValueFor($app, 'settings.notifications.name')) ? ModelUtility::getModelValueFor($app, 'settings.branding.name') : $value;
        if (empty($name)) {
            $name = $app->get('name') ?: self::DEFAULT_FROM_NAME;
        }
        return $name;
    }

    /**
     * Returns the `notification-template`
     *
     * @param   Model   $submission
     * @param   string  $actionKey
     * @return  Model|null
     */
    private function getTemplate(Model $submission)
    {
        return $this->templateLoader->getTemplateModel('template-notification', $submission->get('sourceKey'));
    }

    /**
     * Returns the namespaced template name for the specified submission
     *
     * @param   Model   $submission     The submission
     * @return  string
     */
    private function getTemplateKey(Model $submission)
    {
        return TemplateLoader::getTemplateKey('template-notification', $submission->get('sourceKey'));
    }

    /**
     * Flattens identity model values into "human-readable" format.
     *
     * @param   Model|null  $identity
     * @return  array
     */
    private function humanizeIdentityValues(Model $identity = null)
    {
        $values = [];
        if (null === $identity) {
            return $values;
        }

        // @todo Use a different inflector???
        $inflector = Inflector::get('en');
        foreach (['firstName', 'lastName', 'companyName', 'title', 'primaryEmail'] as $key) {
            $values[$inflector->titleize($key)] = $identity->get($key);
        }
        if (null !== $address = $identity->get('primaryAddress')) {
            $fields = ['street', 'extra', 'city', 'regionCode', 'postalCode', 'countryCode'];
            $parts  = [];
            foreach ($fields as $prop) {
                $value = $address->{$prop};
                if (!empty($value)) {
                    $parts[] = $value;
                }

            }
            $values[$inflector->titleize('primaryAddress')] = implode(' ', $parts);
        }
        if (null !== $phone = $identity->get('primaryPhone')) {
            $values[$inflector->titleize('primaryPhone')] = $phone->number;
        }
        return $values;
    }

    /**
     * Injects required parameters for notification and templateLoader
     *
     * @param   Model   $submission
     * @param   array   $args
     * @return  array
     */
    private function inject(Model $submission, Model $template = null)
    {
        $args     = [];
        $app      = $this->accountManager->getApplication();
        $identity = $submission->get('identity');

        // Global notification settings
        $fromName  = $this->getFromName();
        $fromEmail = $this->getFromEmail();
        $args['from']        = [ $fromEmail => $fromName ];
        $args['application'] = $app;
        $args['submission']  = $submission;
        $args['identity']    = $identity;
        $args['template']    = $template;

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
            $args['to'] = [ $identity->get('primaryEmail') => $identity->get('fullName') ];
        }

        return $args;
    }

    private function getTextContents($contents)
    {
        $contents = preg_replace('/<style.+?>.*<\/style>/i', '', $contents);
        $contents = preg_replace('/<\/td>/i', '&nbsp;</td>', $contents);
        $contents = preg_replace('/<a.*?href="mailto:(.+?)".*>(.+?)<\/a>/i', '$2 [$1]', $contents);
        $contents = preg_replace('/<a.*?href="(.+?)".*>(.+?)<\/a>/i', '$2 [$1]', $contents);
        $contents = strip_tags($contents);
        $lines = explode("\n", $contents);
        foreach ($lines as $i => $line) {
            $lines[$i] = trim($line);
            if (empty($lines[$i])) {
                unset($lines[$i]);
            }
        }
        $contents = html_entity_decode(implode("\r\n", $lines));
        return $contents;
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
     * @param   DateTime $date          The date the message was created
     *
     * @return  bool    If the send was sucessfully queued.
     */
    private function sendNotification($contents, $subject, array $from, array $to, array $cc = [], array $bcc = [], DateTime $date = null)
    {
        $message = $this->mailer->createMessage()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setCc($cc)
            ->setBcc($cc)
            ->setBody($contents, 'text/html')
            ->addPart($this->getTextContents($contents), 'text/plain')
        ;
        if (null !== $date) {
            $message->setDate($date->getTimestamp());
        }

        $instance = $this->mailer->send($message);
        return $instance;
    }
}
