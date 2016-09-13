<?php

namespace AppBundle\Question\Types;

use AppBundle\Question\TypeInterface;

class IntegerType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAnswerType()
    {
        return 'integer';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'integer';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'A number answer without decimals (integer)';
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
