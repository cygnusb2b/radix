<?php

namespace AppBundle\Question\AnswerTypes;

use AppBundle\Question\AnswerTypeInterface;

class BooleanType implements AnswerTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'boolean';
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($value)
    {
        if (null === $value) {
            return;
        }
        if (is_string($value)) {
            $value = strtolower($value);
            switch ($value) {
                case 'yes':
                    return true;
                case 'no':
                    return false;
                case 'true':
                    return true;
                case 'false':
                    return false;
                default:
                    return (boolean) $value;
            }
        } else {
            return (boolean) $value;
        }
    }
}
