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
     * {@inheritdoc}
     *
     * @todo    The auth response will likely need to retrieve the customer model and fully serialize it.
     */
    public function generateFor(UserInterface $user)
    {
        return [
            'id'            => $user->getUserName(),
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
