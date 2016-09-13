<?php

namespace AppBundle\Question\AnswerTypes;

use AppBundle\Question\AnswerTypeInterface;

class ChoiceType implements AnswerTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($value)
    {
        if (null === $value) {
            return;
        }
        return $value;
    }
}
