<?php

namespace AppBundle\Security\JWT;

use AppBundle\Security\User\Account;
use Lcobucci\JWT\Builder as JWTBuilder;
use Lcobucci\JWT\Parser as JWTParser;
use Lcobucci\JWT\Signer\Hmac\Sha256 as JWTSigner;
use Lcobucci\JWT\ValidationData;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Generates (and parses) JWT tokens for indentity accounts.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class AccountGenerator implements JWTGeneratorInterface
{
    const ISSUER = 'radix.account';

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
     * {@inheritdoc}
     */
    public function createFor(UserInterface $user)
    {
        $jwt = $this->builder
            ->setSubject($user->getUsername())
            ->setIssuer(self::ISSUER)
            ->setIssuedAt(time())
            ->setAudience($this->getAudienceKey())
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
        return 'identity-account';
    }

    /**
     * {@inheritdoc}
     */
    public function parseFor($token)
    {
        $token = $this->parser->parse($token);

        $rules = new ValidationData();
        $rules->setIssuer(self::ISSUER);
        $rules->setAudience($this->getAudienceKey());

        if (false === $token->validate($rules)) {
            throw new AuthenticationException('Invalid token.');
        }
        if (false === $token->verify($this->signer, $this->secret)) {
            throw new AuthenticationException('Invalid token.');
        }
        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UserInterface $user)
    {
        return $user instanceof Account;
    }
}
