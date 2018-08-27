<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Security\User\CoreUser;
use AppBundle\Utility\ModelUtility;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class CoreUserSubscriber implements EventSubscriberInterface
{
    public $seen = false;
    /**
     * DI
     *
     * @param   EncoderFactory     $encoderFactory
     */
    public function __construct(EncoderFactory $encoderFactory)
    {
       $this->encoderFactory = $encoderFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getEvents()
    {
        return [
            Events::preCommit,
        ];
    }

    /**
     * @param   ModelLifecycleArguments     $args
     */
    public function preCommit(ModelLifecycleArguments $args)
    {
        $model = $args->getModel();
        if (false === $this->shouldProcess($model)) {
            return;
        }

        $this->formatEmailAddress($model);

        if (isset($model->getChangeSet()['attributes']['password'])) {

            $password = $model->getChangeSet()['attributes']['password'];
            // If the password was nulled out in the interface, ignore this.
            if (null === $password['new']) {
                $model->set('password', $password['old']);
                return;
            }

            $password = $model->get('password');
            if (null !== $password && 0 === preg_match('/^\$2[ayb]\$.{56}$/i', $password)) {
                $salt = $model->get('salt');
                // The password is currently clear text. Encode.
                $coreUser = new CoreUser($model);
                $encoded = $this->encoderFactory->getEncoder($coreUser)->encodePassword($password, $salt);
                $model->set('password', $encoded);
            }
        }

        if (empty($model->get('password'))) {
            throw new \InvalidArgumentException('All users must be assigned a password.');
        }
    }

    /**
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return 'core-user' === $model->getType();
    }

    /**
     * @param   Model   $model
     * @throws  \InvalidArgumentException
     */
    private function formatEmailAddress(Model $model)
    {
        $value = $model->get('email');
        $value = trim($value);
        if (empty($value)) {
            throw new \InvalidArgumentException('The user email value cannot be empty.');
        }
        $value = strtolower($value);
        if (false === stripos($value, '@')) {
            throw new \InvalidArgumentException(sprintf('The provided email address "%s" is invalid.', $value));
        }
        $model->set('email', $value);
    }
}
