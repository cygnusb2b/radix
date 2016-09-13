<?php

namespace AppBundle\Question\Types;

use AppBundle\Question\TypeInterface;

class DateTimeType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAnswerType()
    {
        return 'date';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'datetime';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'A date answer with time';
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
