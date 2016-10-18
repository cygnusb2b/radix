<?php

namespace AppBundle\Security\Auth;

use AppBundle\Core\AccountManager;
use AppBundle\Security\JWT\JWTGeneratorManager;
use AppBundle\Security\User\CoreUser;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Generates auth data for a core user.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class CoreUserGenerator implements AuthGeneratorInterface
{
    /**
     * @var JWTGeneratorManager
     */
    private $jwtManager;

    /**
     * @var AccountManager
     */
    private $manager;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param   JWTGeneratorManager     $jwtManager
     * @param   RequestStack            $requestStack
     * @param   AccountManager          $accountManager
     */
    public function __construct(JWTGeneratorManager $jwtManager, RequestStack $requestStack, AccountManager $manager)
    {
        $this->jwtManager   = $jwtManager;
        $this->requestStack = $requestStack;
        $this->manager      = $manager;
    }

    /**
     *{@inheritdoc}
     */
    public function generateFor(UserInterface $user)
    {
        $data = [
            'username'      => $user->getUserName(),
            'givenName'     => $user->getGivenName(),
            'familyName'    => $user->getFamilyName(),
            'roles'         => $user->getRoles(),
            'applications'  => $user->getApplications(),
            'token'         => $this->jwtManager->createFor($user),
        ];
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UserInterface $user)
    {
        return $user instanceof CoreUser;
    }
}
