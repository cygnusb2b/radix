<?php

namespace AppBundle\Question\AnswerTypes;

use AppBundle\Question\AnswerTypeInterface;

class ChoicesType implements AnswerTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'choices';
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($value)
    {
        if (null === $value) {
            return;
        }

        $formatted = [];
        if (is_array($value) || $value instanceof \Traversable) {
            foreach ($value as $v) {
                $formatted[] = $v;
            }
            return $formatted;
        }
        return (array) $value;
    }
}
