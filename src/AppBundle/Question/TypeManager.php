<?php

namespace AppBundle\Question;

class TypeManager
{
    /**
     * @var TypeInterface[]
     */
    private $questionTypes = [];

    /**
     * @var array
     */
    private $questionChoiceTypes = [
        'standard' => 'A standard choice.',
        'other'    => 'An other choice.',
        'none'     => 'A none-of-the-above choice.',
        'hidden'   => 'A hidden (internal) choice.',
    ];

    /**
     * @var AnswerTypeInterface[]
     */
    private $answerTypes = [];

    /**
     * @param   AnswerTypeInterface     $type
     * @return  self
     */
    public function addAnswerType(AnswerTypeInterface $type)
    {
        $this->answerTypes[$type->getKey()] = $type;
        return $this;
    }

    /**
     * @param   TypeInterface   $type
     * @return  self
     */
    public function addQuestionType(TypeInterface $type)
    {
        $this->questionTypes[$type->getKey()] = $type;
        return $this;
    }

    /**
     * @return  AnswerTypeInterface[]
     */
    public function getAnswerTypes()
    {
        return $this->answerTypes;
    }

    /**
     * @param   string  $key
     * @return  AnswerTypeInterface
     * @throws  \InvalidArgumentException
     */
    public function getAnswerTypeFor($key)
    {
        if (!isset($this->answerTypes[$key])) {
            throw new \InvalidArgumentException(sprintf('No question answer type exists for key "%s"', $key));
        }
        return $this->answerTypes[$key];
    }

    /**
     * @return  array
     */
    public function getQuestionChoiceTypes()
    {
        return $this->questionChoiceTypes;
    }

    /**
     * @return  TypeInterface[]
     */
    public function getQuestionTypes()
    {
        return $this->questionTypes;
    }

    /**
     * @param   string  $key
     * @return  TypeInterface
     * @throws  \InvalidArgumentException
     */
    public function getQuestionTypeFor($key)
    {
        if (!isset($this->questionTypes[$key])) {
            throw new \InvalidArgumentException(sprintf('No question type exists for key "%s"', $key));
        }
        return $this->questionTypes[$key];
    }

    /**
     * @param   string  $key
     * @return  bool
     */
    public function hasAnswerTypeFor($key)
    {
        return isset($this->answerTypes[$key]);
    }

    /**
     * @param   string  $key
     * @return  bool
     */
    public function hasQuestionTypeFor($key)
    {
        return isset($this->questionTypes[$key]);
    }

    /**
     * Normalizes an answer value for the provided question type.
     *
     * @param   string  $questionType
     * @param   mixed   $value
     * @param   bool    $allowHtml
     * @return  mixed
     */
    public function normalizeAnswerFor($questionType, $value, $allowHtml = false)
    {
        $type  = $this->getQuestionTypeFor($questionType);
        $value = $this->getAnswerTypeFor($type->getAnswerType())->normalize($value);
        $value = $type->normalizeAnswer($value);

        if (null === $value) {
            return;
        }

        if (true === $type->supportsHtml() || false == $allowHtml) {
            $value = trim(strip_tags($value));
            if (empty($value)) {
                return;
            }
        }

        $type->validateAnswer($value);
        return $value;
    }
}
