<?php

namespace AppBundle\Question\AnswerTypes;

use AppBundle\Question\AnswerTypeInterface;

class StringType implements AnswerTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'string';
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($value)
    {
        if (null === $value) {
            return;
        }
        $value = trim($value);
        if ('' === $value) {
            return;
        }
        return $value;
    }
}
