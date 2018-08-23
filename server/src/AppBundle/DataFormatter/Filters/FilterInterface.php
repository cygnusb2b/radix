<?php

namespace AppBundle\DataFormatter\Filters;

interface FilterInterface
{
    public function getKey();

    public function replace($value);
}
