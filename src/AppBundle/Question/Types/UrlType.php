<?php

namespace AppBundle\Question\Types;

use AppBundle\Question\TypeInterface;

class UrlType implements TypeInterface
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
        return 'url';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'A url/website answer';
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
