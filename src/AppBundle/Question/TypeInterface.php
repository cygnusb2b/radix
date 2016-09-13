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
}
