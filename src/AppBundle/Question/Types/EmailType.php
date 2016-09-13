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
    public function normalizeAnswer($value)
    {
        if (empty($value)) {
            return;
        }
        return strtolower($value);
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
        if (false === stripos($value, '@')) {
            throw new \InvalidArgumentException('Invalid value for an email response.');
        }
    }
}
