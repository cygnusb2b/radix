<?php

namespace AppBundle\Security;

use AppBundle\Identity\IdentityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

/**
 * Returns an empty, non-redirected JSON response.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class ApplicationLogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    /**
     * @var IdentityManager
     */
    private $manager;

    /**
     * @param   IdentityManager    $manager
     */
    public function __construct(IdentityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     *
     */
    public function onLogoutSuccess(Request $request)
    {
        $request->attributes->set('destroyCookies', true);
        return $this->manager->createDefaultAuthResponse();
    }
}
