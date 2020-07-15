<?php

namespace AppBundle\Integration\Definition;

class IdentityAnswerDefinition extends AbstractDefinition
{
    /**
     * @var string
     */
    private $questionIdentifier;

    /**
     * @var mixed
     */
    private $value;

    /**
     * Constructor.
     *
     * @param   string  $questionIdentifier The external question id.
     * @param   mixed   $value              The answer value. A choice answers should reference external choice ids
     */
    public function __construct($questionIdentifier, $value)
    {
        parent::__construct();
        if (empty($questionIdentifier)) {
            throw new \InvalidArgumentException('All answer definitions must contain an external question identifier.');
        }
        $this->questionIdentifier = $questionIdentifier;
        $this->value              = $value;
    }

    /**
     * @return  string
     */
    public function getQuestionIdentifier()
    {
        return $this->questionIdentifier;
    }

    /**
     * @return  mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return empty($this->value);
    }
}
