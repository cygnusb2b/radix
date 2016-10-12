<?php

namespace AppBundle\Security\Encoder;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * The legacy encoder implementation details
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
interface LegacyEncoderInterface extends PasswordEncoderInterface
{
    /**
     * Gets the credential mechanism this password encoder supports.
     *
     * @return  string
     */
    public function getMechanism();
}
