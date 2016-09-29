<?php

namespace AppBundle\Cors;

/**
 * Creates a CORs definition.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class CorsDefinition
{
    /**
     * @var bool
     */
    private $allowCredentials = false;

    /**
     * @var array
     */
    private $allowedHeaders = [];

    /**
     * @var array
     */
    private $allowedMethods = [];

    /**
     * @var array
     */
    private $allowedOrigins = [];

    /**
     * @var int
     */
    private $maxAge = 0;

    /**
     * @param   array   $origins
     * @param   array   $methods
     * @param   array   $headers
     * @param   int     $maxAge
     * @param   bool    $credentials
     */
    public function __construct(array $origins = [], array $methods = [], array $headers = [], $maxAge = 0, $credentials = false)
    {
        foreach ($origins as $origin) {
            $this->addAllowedOrigin($origin);
        }
        foreach ($methods as $method) {
            $this->addAllowedMethod($method);
        }
        foreach ($headers as $header) {
            $this->addAllowedHeader($header);
        }
        $this->setMaxAge($maxAge);
        $this->allowCredentials($credentials);
    }

    /**
     * Adds an allowed header.
     *
     * @param   string  $header
     * @return  self
     */
    public function addAllowedHeader($header)
    {
        $header = strtolower(trim($header));
        if (empty($header)) {
            return $this;
        }
        $this->allowedHeaders[$header] = true;
        return $this;
    }

    /**
     * Adds an allowed method.
     *
     * @param   string  $method
     * @return  self
     */
    public function addAllowedMethod($method)
    {
        $method = strtoupper(trim($method));
        if (empty($method)) {
            return $this;
        }
        $this->allowedMethods[$method] = true;
        return $this;
    }

    /**
     * Adds an allowed origin.
     *
     * @param   string  $origin
     * @return  self
     */
    public function addAllowedOrigin($origin)
    {
        $origin = trim($origin);
        if (empty($origin)) {
            return $this;
        }
        $this->allowedOrigins[$origin] = true;
        return $this;
    }

    /**
     * Enables/disables allowing credentials.
     *
     * @param   bool    $bit
     * @return  self
     */
    public function allowCredentials($bit = true)
    {
        $this->allowCredentials = (boolean) $bit;
        return $this;
    }

    /**
     * Gets the max age of the pre flight request.
     *
     * @return  int
     */
    public function getMaxAge()
    {
        return $this->maxAge;
    }

    /**
     * Determines if CORS credentials are allowed.
     *
     * @return  bool
     */
    public function areCredentialsAllowed()
    {
        return $this->allowCredentials;
    }

    /**
     * Determines if the provided origin is allowed by CORS.
     *
     * @param   string  $origin
     * @return  bool
     */
    public function isOriginAllowed($origin)
    {
        if (false === $this->hasAllowedOrigins()) {
            return false;
        }
        $origin = (string) $origin;
        foreach ($this->getAllowedOriginPatterns() as $pattern) {
            if (preg_match($pattern, $origin)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets the CORS preflight response headers.
     *
     * @param   string  $origin
     * @return  array
     */
    public function getPreFlightHeaders($origin)
    {
        $headers = [
            'Access-Control-Allow-Origin'       => $origin,
            'Access-Control-Allow-Credentials'  => $this->areCredentialsAllowed() ? 'true' : 'false',
        ];
        if (true === $this->hasAllowedMethods()) {
            $headers['Access-Control-Allow-Methods'] = implode(', ', array_keys($this->getAllowedMethods()));
        }
        if (true === $this->hasAllowedHeaders()) {
            $headers['Access-Control-Allow-Headers'] = implode(', ', array_keys($this->getAllowedHeaders()));
        }

        $maxAge = $this->getMaxAge();
        if ($maxAge > 0) {
            $headers['Access-Control-Max-Age'] = $maxAge;
        }
        return $headers;
    }

    /**
     * Gets the standard CORS response headers.
     *
     * @param   string  $origin
     * @return  array
     */
    public function getStandardHeaders($origin)
    {
        return [
            'Access-Control-Allow-Origin'       => $origin,
            'Access-Control-Allow-Credentials'  => $this->areCredentialsAllowed() ? 'true' : 'false',
            'Vary'                              => 'Origin',
        ];
    }

    /**
     * Gets the allowed CORS request methods.
     *
     * @return  array
     */
    public function getAllowedMethods()
    {
        return $this->allowedMethods;
    }

    /**
     * Gets the allowed CORS request headers.
     *
     * @return  array
     */
    public function getAllowedHeaders()
    {
        return $this->allowedHeaders;
    }

    /**
     * Gets the allowed CORS request origins.
     *
     * @return  array
     */
    public function getAllowedOrigins()
    {
        return $this->allowedOrigins;
    }

    /**
     * Determines if any allowed methods have been set.
     *
     * @return  bool
     */
    public function hasAllowedMethods()
    {
        return !empty($this->allowedMethods);
    }

    /**
     * Determines if any allowed headers have been set.
     *
     * @return  bool
     */
    public function hasAllowedHeaders()
    {
        return !empty($this->allowedHeaders);
    }

    /**
     * Determines if any allowed origins have been set.
     *
     * @return  bool
     */
    public function hasAllowedOrigins()
    {
        return !empty($this->allowedOrigins);
    }

    /**
     * Sets the max age of the pre-flight request, in seconds.
     *
     * @param   int     $seconds
     * @return  self
     */
    public function setMaxAge($seconds)
    {
        $this->maxAge = (integer) $seconds;
        return $this;
    }

    /**
     * Gets the allowed CORS request origins as regex match patterns.
     *
     * @return  array
     */
    private function getAllowedOriginPatterns()
    {
        $patterns = [];
        $origins  = array_keys($this->getAllowedOrigins());
        foreach ($origins as $origin) {
            $patterns[] = $this->convertOriginToPattern($origin);
        }
        return $patterns;
    }

    /**
     * Converts a CORS origin to a regex match pattern
     *
     * @param   string  $origin
     * @return  string
     */
    private function convertOriginToPattern($origin)
    {
        if (false !== strpos($origin, '*')) {
            $parts = explode('*', $origin);
            foreach ($parts as &$part) {
                $part = preg_quote($part, '/');
            }
            $origin = sprintf('/^%s$/i', implode('.*', $parts));
            return $origin;
        }
        return sprintf('/^%s$/i', preg_quote($origin, '/'));
    }
}
