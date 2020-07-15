<?php

namespace AppBundle\Identity;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\ModelUtility;
use Lcobucci\JWT\Builder as JWTBuilder;
use Lcobucci\JWT\Parser as JWTParser;
use Lcobucci\JWT\Signer\Hmac\Sha256 as JWTSigner;
use Lcobucci\JWT\Token as JWTToken;
use Lcobucci\JWT\ValidationData;

/**
 * Generates (and parses) JWT tokens for identity accounts.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
abstract class AbstractTokenGenerator
{
    const ISSUER   = 'radix-application';
    const AUDIENCE = 'identity-account';
    const TTL      = 86400;

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
        $this->parser  = new JWTParser();
        $this->secret  = $secret;
        $this->signer  = new JWTSigner();
    }

    /**
     * Creates a JWT token string for the provided identity account id and parameters.
     *
     * @param   string  $accountId
     * @param   array   $parameters
     * @return  JWTToken
     */
    public function createFor($accountId, array $parameters)
    {
        $builder = $this->createBuilderFor($accountId);
        $this->applyParametersToBuilder($builder, $parameters);
        return $builder->sign($this->signer, $this->secret)->getToken();
    }

    /**
     * Parses a JWT string for the provided identity account id and parameters.
     *
     * @param   string  $token
     * @param   string  $accountId
     * @param   array   $parameters
     * @return  JWTToken
     * @throws  AuthenticationException
     */
    public function parseFor($token, $accountId, array $parameters)
    {
        $token = $this->parseToken($token);
        $rules = $this->createValidationRulesFor($accountId);

        $this->applyParametersToRules($rules, $parameters);

        if (false === $this->isValid($token, $rules)) {
            throw $this->createExceptionFor($accountId, $parameters);
        }
        return $token;
    }

    /**
     * Applies parameters to a JWT token object.
     *
     * @param   JWTBuilder  $builder
     * @param   array       $parameters
     * @throws  \InvalidArgumentException   If parameters are missing or malformed.
     */
    protected abstract function applyParametersToBuilder(JWTBuilder $builder, array $parameters);

    /**
     * Applies parameters to a set of validation rules.
     *
     * @param   ValidationData  $rules
     * @param   array           $parameters
     * @throws  \InvalidArgumentException   If parameters are missing or malformed.
     */
    protected abstract function applyParametersToRules(ValidationData $rules, array $parameters);

    /**
     * Creates a new JWT builder for the provided identity account id.
     *
     * @param   string  $accountId
     */
    protected function createBuilderFor($accountId)
    {
        $now     = time();
        $expires = $now + self::TTL;

        $builder = new JWTBuilder();
        $builder
            ->setIssuer(self::ISSUER)
            ->setAudience(self::AUDIENCE)
            ->setId((string) $accountId)
            ->setExpiration($expires)
            ->setIssuedAt($now)
        ;
        return $builder;
    }

    /**
     * Creates an exception on token validation failure.
     *
     * @param   string  $accountId
     * @param   array   $parameters
     * @return  HttpFriendlyException
     */
    protected abstract function createExceptionFor($accountId, array $parameters);

    /**
     * Creates the standard validation rules object.
     *
     * @param   string  $accountId
     * @return  ValidationData
     */
    protected function createValidationRulesFor($accountId)
    {
        $rules = new ValidationData();
        $rules->setIssuer(self::ISSUER);
        $rules->setAudience(self::AUDIENCE);
        $rules->setId((string) $accountId);
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
