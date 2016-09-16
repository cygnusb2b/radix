<?php

namespace AppBundle\Security\JWT;

use Lcobucci\JWT\Token as JWTToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

interface JWTGeneratorInterface
{
    /**
     * Creates a stringified JWT token for the provided internal token.
     *
     * @param   UserInterface  $user
     * @return  string
     */
    public function createFor(UserInterface $user);

    /**
     * Gets the unique audience key that this generator handles.
     *
     * @return  string
     */
    public function getAudienceKey();

    /**
     * Parses and validates the provided token.
     * If valid, will return the token object.
     * If invalid, will throw an auth exception.
     *
     * @param   string  $token
     * @return  JWTToken
     * @throws  AuthenticationException
     */
    public function parseFor($token);

    /**
     * Determines if this generator supports the provided internal token.
     *
     * @param   UserInterface  $user
     * @return  bool
     */
    public function supports(UserInterface $user);
}
