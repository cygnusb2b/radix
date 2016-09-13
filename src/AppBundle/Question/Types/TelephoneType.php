<?php

namespace AppBundle\Question\Types;

use AppBundle\Question\TypeInterface;

class TelephoneType implements TypeInterface
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
        return 'telephone';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'A telephone number answer';
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
