<?php

namespace AppBundle\DataFormatter\Filters;

class PatternFilter implements FilterInterface
{
    protected $key;

    protected $patterns = [];

    protected $closure;

    public function __construct($key, $patterns, \Closure $closure)
    {
        $this->key = $key;
        $this->patterns = (array) $patterns;
        $this->closure = $closure;
    }

    public static function create($key, $patterns, \Closure $closure)
    {
        return new self($key, $patterns, $closure);
    }

    public function getKey()
    {
        return $this->key;
    }

    public function replace($value)
    {
        $closure = $this->closure;
        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return $closure($value);
            }
        }
        return $value;
    }
}
