<?php

namespace AppBundle\Question\AnswerTypes;

use \DateTime;
use \MongoDate;
use AppBundle\Question\AnswerTypeInterface;

class DateType implements AnswerTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'date';
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($value)
    {
        if (null === $value) {
            return;
        }
        if ($value instanceof DateTime) {
            return $value;
        }
        if ($value instanceof \MongoDate) {
            $dateStr = date('Y-m-d H:i:s.'.$value->usec, $value->sec);
            return new DateTime($dateStr);
        }
        if (is_numeric($value)) {
            // Supports microseconds
            $value = (Float) $value;
            $usec = round(($value - (Integer) $value) * 1000000, 0);
            $dateStr = date('Y-m-d H:i:s.'.$usec, (integer) $value);
            return new DateTime($dateStr);
        }
        return new DateTime((string) $value);
    }
}
