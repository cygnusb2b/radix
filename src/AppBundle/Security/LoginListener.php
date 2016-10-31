<?php

namespace AppBundle\Security;

use \MongoDate;
use AppBundle\Security\User\Account;
use AppBundle\Security\User\CoreUser;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Listens to security events (such as login) and performs actions.
 *
 * @author Jacob Bare <jacob.bare@cygnus.com>
 */
class LoginListener
{
    /**
     * @var SecurityContext
     */
    private $securityContext;

    /**
     * @var Store
     */
    private $store;

    /**
     * Constructor.
     *
     * @param   SecurityContext $securityContext
     * @param   Store           $store
     */
    public function __construct(AuthorizationChecker $securityContext, Store $store)
    {
        $this->securityContext = $securityContext;
        $this->store = $store;
    }

    /**
     * Performs actions when the customer logs in.
     *
     * @param   InteractiveLoginEvent   $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $token = $event->getAuthenticationToken();
        $user  = $token->getUser();

        if ($user instanceof CoreUser) {
            $this->updateCoreUser($user->getModel());
        } elseif ($user instanceof Account) {
            $this->updateAccount($user->getModel());
        }
    }

    /**
     * Updates the core user model on login.
     *
     * @param   Model   $model
     */
    protected function updateCoreUser(Model $model)
    {
        $now = new MongoDate();
        if ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $model
                ->set('lastLogin', $now)
                ->set('lastSeen', $now)
                ->set('logins', $model->get('logins') + 1)
                ->save()
            ;
        } elseif ($this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            // From remember me cookie
            $model
                ->set('lastSeen', $now)
                ->set('remembers', $model->get('remembers') + 1)
                ->save()
            ;
        }
    }

    /**
     * Updates the account auth model on login.
     *
     * @param   Model   $model
     */
    protected function updateAccount(Model $model)
    {
        $now = new MongoDate();

        $history = $model->get('history');
        if (null === $history) {
            $history = $model->createEmbedFor('history');
            $model->set('history', $history);
        }


        if ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $history
                ->set('lastLogin', $now)
                ->set('lastSeen', $now)
                ->set('logins', $history->get('logins') + 1)
            ;
            $model->save();
        } elseif ($this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            // From remember me cookie
            $history
                ->set('lastSeen', $now)
                ->set('remembers', $history->get('remembers') + 1)
            ;
            $model->save();
        }
    }
}
