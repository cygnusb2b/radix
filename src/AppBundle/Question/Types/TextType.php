<?php

namespace AppBundle\Question\Types;

use AppBundle\Question\TypeInterface;

class TextType implements TypeInterface
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
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'A short, open-ended text answer (single line)';
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
