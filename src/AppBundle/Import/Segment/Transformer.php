<?php

namespace AppBundle\Import\Segment;

use Aol\Transformers\Transformer as BaseTransformer;

/**
 * Provides baseline transformation logic
 * @author  Josh Worden <jworden@southcomm.com>
 */
class Transformer extends BaseTransformer
{
    /**
     * @var     array   The original data being transformed
     */
    protected $originalData = [];

    /**
     * @var     array   Virtual fields that are appended to the data to trigger global transformations
     */
    protected $globalTriggers = [];

    /**
     * @var     array   Required fields
     */
    protected $requiredFields = [
        'legacy.id',
        'legacy.source'
    ];

    /**
     * @var     array   Values that should be set after transformation
     */
    protected $static = [];

    /**
     * @var     mixed   The source document's primary key field
     */
    protected $primaryKey;

    /**
     * Defines a callable method against a method in an extending class.
     *
     * @see BaseTransformer::define
     *
     * @param   string  $appKey     The application field key
     * @param   string  $extKey     The legacy field key
     * @param   string  $method     The method name to be called
     * @return  null
     */
    public function defineCallable($appKey, $extKey, $method)
    {
        return $this->define($appKey, $extKey, [$this, $method]);
    }

    /**
     * Defines the external source data's primary key.
     *
     * @see BaseTransformer::defineVirtual
     *
     * @param   string  $key        The source data's primarykey field
     * @return  null
     */
    public function defineId($key)
    {
        $this->primaryKey = $key;
    }

    /**
     * Creates a definition that applies a value after transformation
     *
     * @param   string  $key        The application field key
     * @param   string  $value      The value to be applied
     * @return  null
     */
    public function defineStatic($key, $value)
    {
        $this->static[$key] = $value;
    }

    /**
     * Creates a definition that can work against multiple legacy keys.
     *
     * @param   string  $appKey     The application field key
     * @param   string  $extKey     The legacy field key
     * @param   string  $method     The method name to be called
     * @return  null
     */
    public function defineGlobal($appKey, $method)
    {
        $this->globalTriggers[] = $id = sprintf('transform_%s', uniqid());
        $this->define($appKey, $id, function($value) use ($method) {
            return call_user_func_array([$this, $method], [$this->originalData, $value]);
        });
    }

    /**
     * This method is called before the data is translated into the application context.
     *
     * @see     BaseTransformer::beforeApp
     *
     * @param   mixed   $data       The data to be transformed
     * @return  array
     */
    protected function beforeApp($data)
    {
        $this->originalData = $data;

        foreach ($this->globalTriggers as $key) {
            $data[$key] = true;
        }

        foreach ($this->requiredFields as $key) {
            if (!in_array($key, $this->getKeysApp()) && !array_key_exists($key, $this->static)) {
                throw new \InvalidArgumentException(sprintf('Transformer key "%s" MUST be defined!', $key));
            }
        }

        // $data = $this->flatten($data);
        return $data;
    }

    /**
     * This method is called after the data is translated into the application context.
     *
     * @see     BaseTransformer::afterApp
     *
     * @param   mixed   $data       The transformed data
     * @return  array
     */
    protected function afterApp($data)
    {
        // Set static properties
        foreach ($this->static as $k => $v) {
            $data[$k] = $v;
        }

        if (null !== $this->primaryKey && array_key_exists($this->primaryKey, $this->originalData)) {
            $data['_id'] = $this->originalData[$this->primaryKey];
        }

        // Ensure required fields are present
        foreach ($this->requiredFields as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \RuntimeException(sprintf('Required field "%s" is not present in transformed data!', $key));
            }
        }

        // Remove virtual keys
        foreach ($this->globalTriggers as $key) {
            unset($data[$key]);
        }

        // Translate dot-notated fields
        foreach ($data as $k => $v) {
            if (false !== stripos($k, '.')) {
                $keys = explode('.', $k);
                $c = &$data[array_shift($keys)];
                foreach ($keys as $key) {
                    if (isset($c[$key]) && $c[$key] === $v) {
                        $c[$key] = [];
                    }
                    $c = &$c[$key];
                }
                if ($c === null) {
                    $c = $v;
                }
                unset($data[$k]);
            }
        }
        return $data;
    }

    private function flatten(array $data = [], $prefix = '')
    {
        $out = [];
        foreach ($data as $k => $v) {
            $accessor = empty($prefix) ? $k : sprintf('%s.%s', $prefix, $k);
            if (is_array($v)) {
                $out = array_merge($out, $this->flatten($v, $accessor));
            } else {
                $out[$accessor] = $v;
            }
        }
        return $out;
    }
}
