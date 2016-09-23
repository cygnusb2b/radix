<?php

namespace AppBundle\Security\Auth;

use AppBundle\Security\User\Customer;
use AppBundle\Serializer\PublicApiSerializer;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Generates auth data for a customer.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class CustomerGenerator implements AuthGeneratorInterface
{
    /**
     * @var PublicApiSerializer
     */
    private $serializer;

    /**
     * @param   PublicApiSerializer     $serializer
     */
    public function __construct(PublicApiSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     *
     * @todo    The auth response will likely need to retrieve the customer model and fully serialize it.
     */
    public function generateFor(UserInterface $user)
    {
        $model = $user->getModel();
        return $this->serializer->serialize($model);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UserInterface $user)
    {
        return $user instanceof Customer;
    }
}
