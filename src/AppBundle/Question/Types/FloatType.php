<?php

namespace AppBundle\Question\Types;

use AppBundle\Question\TypeInterface;

class FloatType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAnswerType()
    {
        return 'float';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'float';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'A number answer with decimals (float)';
    }

    /**
     * {@inheritdoc}
     */
    public function normalizeAnswer($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsChoices()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsHtml()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAnswer($value)
    {
        return true;
    }
}
