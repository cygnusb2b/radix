<?php

namespace AppBundle\Customer;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\ModelUtility;
use Lcobucci\JWT\Builder as JWTBuilder;
use Lcobucci\JWT\Parser as JWTParser;
use Lcobucci\JWT\Signer\Hmac\Sha256 as JWTSigner;
use Lcobucci\JWT\ValidationData;

/**
 * Generates (and parses) JWT tokens for email verification purposes.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class EmailVerifyTokenGenerator
{
    const ISSUER = 'radix-application';
    const TTL = 86400;

    /**
     * @var JWTBuilder
     */
    private $builder;

    /**
     * @var JWTParser
     */
    private $parser;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var JWTSigner
     */
    private $signer;

    /**
     * @param   string  $secret
     */
    public function __construct($secret)
    {
        if (empty($secret)) {
            throw new \InvalidArgumentException('The token secret cannot be empty.');
        }

        $this->builder = new JWTBuilder();
        $this->parser  = new JWTParser();
        $this->secret  = $secret;
        $this->signer  = new JWTSigner();
    }

    /**
     * Creates a JWT token string for the provided email address and customer id.
     *
     * @param   string  $token
     * @param   string  $emailAddress
     * @return  string
     */
    public function createFor($emailAddress, $customerId)
    {
        $now     = time();
        $expires = $now + self::TTL;

        $jwt = $this->builder
            ->setIssuer(self::ISSUER)
            ->setSubject($emailAddress)
            ->setId($customerId)
            ->setExpiration($expires)
            ->setIssuedAt($now)
            ->sign($this->signer, $this->secret)
            ->getToken()
        ;
        return (string) $jwt;
    }

    /**
     * {@inheritdoc}
     */
    public function getAudienceKey()
    {
        return 'core-user';
    }

    /**
     * Parses a JWT string for the provided email address and customer account id.
     *
     * @param   string  $token
     * @param   string  $emailAddress
     * @param   string  $customerId
     * @return  \Lcobucci\JWT\Parser\Token
     * @throws  AuthenticationException
     */
    public function parseFor($token, $emailAddress, $customerId)
    {
        $token        = $this->parser->parse($token);
        $emailAddress = ModelUtility::formatEmailAddress($emailAddress);

        $rules = new ValidationData();
        $rules->setIssuer(self::ISSUER);
        $rules->setSubject($emailAddress);
        $rules->setId((string) $customerId);

        if (false === $token->validate($rules)) {
            throw $this->createExceptionFor();
        }
        if (false === $token->verify($this->signer, $this->secret)) {
            throw $this->createExceptionFor();
        }
        return $token;
    }

    /**
     * Creates the invalid token exception.
     *
     * @return  HttpFriendlyException
     */
    private function createExceptionFor()
    {
        // @todo Meta was removed from the exception. As such, resending the request should use the token.
        return new HttpFriendlyException('The provided token is either invalid or has expired.', 403);
    }
}
