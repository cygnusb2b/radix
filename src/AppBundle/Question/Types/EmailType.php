<?php

namespace AppBundle\Question\Types;

use AppBundle\Question\TypeInterface;

class EmailType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAnswerType()
    {
        return 'string';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'email';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'An email address answer';
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
