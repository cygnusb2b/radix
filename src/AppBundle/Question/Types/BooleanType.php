<?php

namespace AppBundle\Question\Types;

use AppBundle\Question\TypeInterface;

class BooleanType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAnswerType()
    {
        return 'boolean';
    }

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
    public function getDescription()
    {
        return 'A yes or no answer (boolean)';
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
}
