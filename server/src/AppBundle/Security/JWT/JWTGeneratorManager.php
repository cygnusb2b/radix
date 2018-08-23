<?php

namespace AppBundle\Security\JWT;

use Lcobucci\JWT\Token as JWTToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTGeneratorManager
{
    /**
     * @var JWTGeneratorInterface[]
     */
    private $generators = [];

    /**
     * @param   JWTGeneratorInterface   $generator
     * @return  self
     */
    public function addGenerator(JWTGeneratorInterface $generator)
    {
        $this->generators[$generator->getAudienceKey()] = $generator;
        return $this;
    }

    /**
     * @param   UserInterface  $user
     * @return  JWTGeneratorInterface|null
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
     * @return  JWTGeneratorInterface[]
     */
    public function getGenerators()
    {
        return $this->generators;
    }

    /**
     * @param   string      $audienceKey
     * @return  JWTGeneratorInterface|null
     */
    public function getParserFor($audienceKey)
    {
        foreach ($this->getGenerators() as $generator) {
            if ($audienceKey === $generator->getAudienceKey()) {
                return $generator;
            }
        }
    }

    /**
     * @param   UserInterface  $user
     * @return  string
     * @throws  \RuntimeException
     */
    public function createFor(UserInterface $user)
    {
        $generator = $this->getGeneratorFor($user);
        if (null === $generator) {
            throw new \RuntimeException('No JWT token generator found for the provided user.');
        }
        return $generator->createFor($user);
    }

    /**
     * Extracts a stringified JWT token from a request object
     *
     * @param   Request     $request
     * @return  string|null
     */
    public function extractFrom(Request $request)
    {
        $header = $request->headers->get('Authorization');
        if (0 !== strpos($header, 'Bearer')) {
            return null;
        }
        $raw = trim(str_replace('Bearer ', '', $request->headers->get('Authorization')));
        return (empty($raw)) ? null : $raw;
    }

    /**
     * @param   string  $audienceKey
     * @param   string  $token
     * @return  JWTToken
     * @throws  \RuntimeException
     */
    public function parseFor($audienceKey, $token)
    {
        $generator = $this->getParserFor($audienceKey);
        if (null === $generator) {
            throw new \RuntimeException('No JWT token generator found for the provided audience.');
        }
        return $generator->parseFor($token);
    }
}
