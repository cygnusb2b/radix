<?php

namespace AppBundle\Question;

interface TypeInterface
{
    /**
     * Gets the answer type key this question supports.
     *
     * @return  string
     */
    public function getAnswerType();

    /**
     * Gets the key that uniquely identifies this question type.
     *
     * @return  string
     */
    public function getKey();

    /**
     * Gets the question type description.
     *
     * @return  string
     */
    public function getDescription();

    /**
     * Normalizes an answer value after it has been processed by the answer type normalization.
     *
     * @param   mixed   $value
     * @return  mixed
     */
    public function normalizeAnswer($value);

    /**
     * Determines if this type supports choices.
     *
     * @return  bool
     */
    public function supportsChoices();

    /**
     * Determines if this type supports HTML values.
     *
     * @return  bool
     */
    public function supportsHtml();

    /**
     * Does low-level validation of the answer value.
     *
     * @param   mixed   $value
     * @throws  \InvalidArgumentException
     */
    public function validateAnswer($value);
}
