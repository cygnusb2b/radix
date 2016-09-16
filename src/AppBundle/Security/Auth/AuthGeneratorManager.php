<?php

namespace AppBundle\Security\Auth;

use AppBundle\Security\JWT\JWTGeneratorManager;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Generates auth data hashes for users.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class AuthGeneratorManager
{
    /**
     * @var AuthGeneratorInterface[]
     */
    private $generators = [];

    /**
     * @param   AuthGeneratorInterface   $generator
     * @return  self
     */
    public function addGenerator(AuthGeneratorInterface $generator)
    {
        $this->generators[] = $generator;
        return $this;
    }

    /**
     * @param   UserInterface   $user
     * @return  array
     * @throws  \RuntimeException
     */
    public function generateFor(UserInterface $user)
    {
        $generator = $this->getGeneratorFor($user);
        if (null === $generator) {
            throw new \RuntimeException('No auth hash generator found for the provided user.');
        }
        return $generator->generateFor($user);
    }

    /**
     * @param   UserInterface  $user
     * @return  AuthGeneratorInterface|null
     */
    public function getGeneratorFor(UserInterface $user)
    {
        foreach ($this->getGenerators() as $generator) {
            if (true === $generator->supports($user)) {
                return $generator;
            }
        }
    }

    /**
     * @return  AuthGeneratorInterface[]
     */
    public function getGenerators()
    {
        return $this->generators;
    }
}
