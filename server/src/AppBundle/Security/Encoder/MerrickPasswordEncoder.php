<?php

namespace AppBundle\Security\Encoder;

use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * Legacy password encoder for Merrick users.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class MerrickPasswordEncoder extends BasePasswordEncoder implements LegacyEncoderInterface
{
    /**
     * {@inheritDoc}
     */
    public function encodePassword($raw, $salt)
    {
        if ($this->isPasswordTooLong($raw)) {
            throw new BadCredentialsException('Invalid password.');
        }
        return sha1(md5($raw).$salt);
    }

    /**
     * {@inheritDoc}
     */
    public function getMechanism()
    {
        return 'merrick';
    }

    /**
     * {@inheritDoc}
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        if ($this->isPasswordTooLong($raw)) {
            return false;
        }
        return $encoded === $this->encodePassword($raw, $salt);
    }
}
