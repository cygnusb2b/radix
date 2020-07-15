<?php

namespace AppBundle\Security\Encoder;

/**
 * Manages legacy/alternative password encoders.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class LegacyEncoderManager
{
    const CORE_MECHANISM = 'platform';

    private $encoders;

    public function addEncoder(LegacyEncoderInterface $encoder)
    {
        $mechanism = $encoder->getMechanism();
        if (self::CORE_MECHANISM === $mechanism) {
            throw new \InvalidArgumentException('You cannot assign a legacy password encoder as the core mechanism.');
        }
        $this->encoders[$mechanism] = $encoder;
        return $this;
    }

    public function getEncoder($mechanism)
    {
        if (isset($this->encoders[$mechanism])) {
            return $this->encoders[$mechanism];
        }
    }
}
