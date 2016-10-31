<?php

namespace AppBundle\Security\Auth;

use AppBundle\Security\User\Account;
use AppBundle\Serializer\PublicApiSerializer;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Generates auth data for an account.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class AccountGenerator implements AuthGeneratorInterface
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
     */
    public function generateFor(UserInterface $user)
    {
        $model = $user->getModel();
        return $this->serializer->serialize($model);
    }

    /**
     * @return  PublicApiSerializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UserInterface $user)
    {
        return $user instanceof Account;
    }
}
