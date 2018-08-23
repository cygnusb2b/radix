<?php

namespace AppBundle\Security;

use AppBundle\Security\Auth\AuthGeneratorManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

/**
 * Returns an empty, non-redirected JSON response.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    /**
     * @var AuthGeneratorManager
     */
    private $authManager;

    /**
     * @param   AuthGeneratorManager    $authManager
     */
    public function __construct(AuthGeneratorManager $authManager)
    {
        $this->authManager = $authManager;
    }

    /**
     * {@inheritDoc}
     * @todo Need to determine how to show default auth object here.
     */
    public function onLogoutSuccess(Request $request)
    {
        return $this->authManager->createDefaultResponse();
    }
}
