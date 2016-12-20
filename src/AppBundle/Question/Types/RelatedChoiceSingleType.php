<?php

namespace AppBundle\Question\Types;

use AppBundle\Question\TypeInterface;

class RelatedChoiceSingleType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAnswerType()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'related-choice-single';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'A list of choices from other questions, with a single answer';
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
        return true;
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
