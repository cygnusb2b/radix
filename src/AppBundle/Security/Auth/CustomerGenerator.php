<?php

namespace AppBundle\Security\Auth;

use AppBundle\Security\User\Customer;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Generates auth data for a customer.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class CustomerGenerator implements AuthGeneratorInterface
{
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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UserInterface $user)
    {
        return $user instanceof Customer;
    }
}
