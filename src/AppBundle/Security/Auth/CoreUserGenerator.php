<?php

namespace AppBundle\Security\Auth;

use AppBundle\Security\User\CoreUser;
use AppBundle\Security\JWT\JWTGeneratorManager;
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
     * @param   JWTGeneratorManager     $jwtManager
     */
    public function __construct(JWTGeneratorManager $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    /**
     *{@inheritdoc}
     */
    public function generateFor(UserInterface $user)
    {
        return [
            'username'      => $user->getUserName(),
            'givenName'     => $user->getGivenName(),
            'familyName'    => $user->getFamilyName(),
            'roles'         => $user->getRoles(),
            'applications'  => $user->getPublicKeys(),
            'token'         => $this->jwtManager->createFor($user),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UserInterface $user)
    {
        return $user instanceof CoreUser;
    }
}
