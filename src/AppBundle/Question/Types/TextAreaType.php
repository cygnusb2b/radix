<?php

namespace AppBundle\Question\Types;

use AppBundle\Question\TypeInterface;

class TextAreaType implements TypeInterface
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
        return 'textarea';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'A long, open-ended text answer (multiple lines)';
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
        return true;
    }
}
