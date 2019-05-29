<?php

namespace AppBundle\Notifications\NotificationFactory\ProductEmailDeployment;

use As3\Modlr\Models\Model;
use AppBundle\Notifications\Notification;
use AppBundle\Notifications\NotificationFactoryInterface;
use AppBundle\Utility\ModelUtility;

/**
 * Creates a Notification
 *
 * @author Josh Worden <jworden@southcomm.com>
 */
class OptIn implements NotificationFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(Model $submission, Model $template = null, array $args)
    {
        $modifiedSubscriptions = [];
        $optIns = $submission->get('payload')->submission['optIns'];
        $ids = array_keys($optIns);
        foreach ($ids as $k => $id) {
            $ids[$k] = new \MongoId($id);
        }
        foreach ($submission->getStore()->findQuery('product', ['_id' => ['$in' => $ids]]) as $product) {
            $id = (string) $product->getId();
            $modifiedSubscriptions[$id] = ['name' => $product->get('name'), 'status' => ($optIns[$id] && $optIns[$id] !== "false") ? 'Subscribed' : 'Unsubscribed'];
        }

        $args['modifiedSubscriptions'] = $modifiedSubscriptions;
        if (!isset($args['subject'])) {
            $appName = $args['application']->get('name');
            if (null !== $name = ModelUtility::getModelValueFor($args['application'], 'settings.branding.name')) {
                $appName = $name;
            }
            $args['subject'] = sprintf('%s subscription preferences updated', $appName);
        }
        return new Notification($args);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $submission, Model $template = null)
    {
        $identity = $submission->get('identity');
        return 'product-email-deployment-optin' === $submission->get('sourceKey') && null !== $identity && null !== $identity->get('primaryEmail');
    }
}
