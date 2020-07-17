<?php

namespace AppBundle\Notifications;

use As3\Parameters\Parameters;

/**
 * Container for notification settings
 *
 * @author Josh Worden <jworden@southcomm.com>
 */
final class Notification
{
    private $args = [];
    private $bcc = [];
    private $cc = [];
    private $from = [];
    private $subject;
    private $to = [];

    /**
     *
     */
    public function __construct(array $args)
    {
        $this->args = $args;
        foreach (['subject', 'from', 'to', 'cc', 'bcc'] as $k) {
            if (isset($args[$k])) {
                $this->$k = $args[$k];
            }
        }
    }

    /**
     * @return  array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Gets addresses that should be blind copied
     *
     * @return  array
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * Gets addresses that should be copied
     *
     * @return  array
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * Gets the address the email should be sent from
     *
     * @return  array
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Gets the email subject line
     *
     * @return  string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Gets addresses that should be sent to
     *
     * @return  array
     */
    public function getTo()
    {
        return $this->to;
    }
}
