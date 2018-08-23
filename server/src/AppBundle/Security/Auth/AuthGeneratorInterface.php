<?php

namespace AppBundle\Security\Auth;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Provides implementation details for generating user auth data.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
interface AuthGeneratorInterface
{
    /**
     * Generates a user auth data hash for the provided user.
     *
     * @param   UserInterface  $user
     * @return  array
     */
    public function generateFor(UserInterface $user);

    /**
     * Determines if this generator supports the provided user.
     *
     * @param   UserInterface  $user
     * @return  bool
     */
    public function supports(UserInterface $user);
}
