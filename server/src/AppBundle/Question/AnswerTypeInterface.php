<?php

namespace AppBundle\Question;

interface AnswerTypeInterface
{
    /**
     * Gets the key that uniquely identifies this question answer type.
     *
     * @return  string
     */
    public function getKey();

    /**
     * Normalizes an answer choice value.
     *
     * @param   mixed   $value
     * @return  mixed
     */
    public function normalize($value);
}
