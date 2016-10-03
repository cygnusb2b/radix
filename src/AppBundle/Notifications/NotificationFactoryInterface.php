<?php

namespace AppBundle\Notifications;

use As3\Modlr\Models\Model;

/**
 * Creates a Notification
 *
 * @author Josh Worden <jworden@southcomm.com>
 */
interface NotificationFactoryInterface
{
    /**
     * Configures a Notification instance from the submission
     *
     * @param   Model   $submission     An `input-submission`
     * @param   Model   $template       A `template` model
     * @param   array   $args           Template arguments
     *
     * @return  Notification
     */
    public function generate(Model $submission, Model $template = null, array $args);

    /**
     * Determines if this handler supports the passed submission and action
     *
     * @param   Model   $submission     The submission
     *
     * @return  boolean
     */
    public function supports(Model $submission, Model $template = null);
}
