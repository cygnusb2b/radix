<?php

namespace AppBundle\Customer;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\ModelUtility;
use Lcobucci\JWT\Builder as JWTBuilder;
use Lcobucci\JWT\Parser as JWTParser;
use Lcobucci\JWT\Signer\Hmac\Sha256 as JWTSigner;
use Lcobucci\JWT\Token as JWTToken;
use Lcobucci\JWT\ValidationData;

/**
 * Generates (and parses) JWT tokens for customers.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
abstract class AbstractTokenGenerator
{
    const ISSUER   = 'radix-application';
    const AUDIENCE = 'customer-account';
    const TTL      = 86400;

    /**
     * @var JWTBuilder
     */
    protected $builder;

    /**
     * @var JWTParser
     */
    protected $parser;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var JWTSigner
     */
    protected $signer;

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
     * Creates a JWT token string for the provided customer id and parameters.
     *
     * @param   string  $token
     * @param   array   $parameters
     * @return  JWTToken
     */
    public function createFor($customerId, array $parameters)
    {
        $token = $this->createBasicTokenFor($customerId);
        $this->applyParametersToToken($token, $parameters);
        return $token;
    }

    /**
     * Parses a JWT string for the provided customer id and parameters.
     *
     * @param   string  $token
     * @param   string  $customerId
     * @param   array   $parameters
     * @return  JWTToken
     * @throws  AuthenticationException
     */
    public function parseFor($token, $customerId, array $parameters)
    {
        $token = $this->parseToken($token);
        $rules = $this->createValidationRulesFor($customerId);

        $this->applyParametersToRules($rules, $parameters);

        if (false === $this->isValid($token, $rules)) {
            throw $this->createExceptionFor($customerId, $parameters);
        }
        return $token;
    }

    /**
     * Applies parameters to a set of validation rules.
     *
     * @param   ValidationData  $rules
     * @param   array           $parameters
     * @return  ValidationData
     * @throws  \InvalidArgumentException   If parameters are missing or malformed.
     */
    protected abstract function applyParametersToRules(ValidationData $rules, array $parameters);

    /**
     * Applies parameters to a JWT token object.
     *
     * @param   JWTToken    $token
     * @param   array       $parameters
     * @return  JWTToken
     * @throws  \InvalidArgumentException   If parameters are missing or malformed.
     */
    protected abstract function applyParametersToToken(JWTToken $token, array $parameters);

    /**
     * Creates a basic JWT object for the provided customer id.
     *
     * @param   string  $customerId
     * @return  JWTToken
     */
    protected function createBasicTokenFor($customerId)
    {
        $now     = time();
        $expires = $now + self::TTL;

        $jwt = $this->builder
            ->setIssuer(self::ISSUER)
            ->setAudience(self::AUDIENCE)
            ->setId((string) $customerId)
            ->setExpiration($expires)
            ->setIssuedAt($now)
            ->sign($this->signer, $this->secret)
            ->getToken()
        ;
        return $jwt;
    }

    /**
     * Creates an exception on token validation failure.
     *
     * @param   string  $customerId
     * @param   array   $parameters
     * @return  HttpFriendlyException
     */
    protected abstract function createExceptionFor($customerId, array $parameters);

    /**
     * Creates the standard validation rules object.
     *
     * @param   string  $customerId
     * @return  ValidationData
     */
    protected function createValidationRulesFor($customerId)
    {
        $rules = new ValidationData();
        $rules->setIssuer(self::ISSUER);
        $rules->setAudience(self::AUDIENCE);
        $rules->setId((string) $customerId);
        return $rules;
    }

    /**
     * Parses a raw JWT token string into a token object.
     *
     * @return JWTToken
     */
    protected function parseToken($token)
    {
        return $this->parser->parse($token);
    }

    /**
     * Determines if the JWT token object is valid according to the provided rules.
     *
     * @param   JWTToken        $token
     * @param   ValidationData  $rules
     */
    protected function isValid(JWTToken $token, ValidationData $rules)
    {
        return $token->validate($rules) && $token->verify($this->signer, $this->secret);
    }
}
