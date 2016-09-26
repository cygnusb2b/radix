<?php

namespace AppBundle\Customer;

use \JsonSerializable;
use AppBundle\Core\AccountManager;
use AppBundle\Utility\HelperUtility;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

/**
 * A customer cookie.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class CustomerCookie
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var int
     */
    private $expires;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $type;

    /**
     * @param   string  $identifier
     * @param   string  $type
     * @param   array   $data
     */
    public function __construct($name, $expires, $identifier, $type, array $data = [])
    {
        $this->name       = trim($name);
        $this->expires    = (int) $expires;

        $this->identifier = trim($identifier);
        $this->type       = trim($type);
        $this->data       = $data;
    }

    /**
     * Gets the expiration of the cookie, in seconds.
     *
     * @return  int
     */
    public function getExpires()
    {
        return time() + $this->expires;
    }

    /**
     * Gets the customer identifier.
     *
     * @return  string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Gets the name of the cookie.
     *
     * @return  int
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the customer type.
     *
     * @return  string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'id'    => $this->getIdentifier(),
            'type'  => $this->getType(),
            'data'  => (object) $this->data
        ];
    }

    /**
     * Gets the cookie instance as a Symfony cookie.
     *
     * @return  Cookie
     */
    public function toCookie()
    {
        return new Cookie($this->getName(), $this->toJson(), $this->getExpires(), AccountManager::APP_PATH);
    }

    /**
     * Gets the cookie instance as a JSON value.
     *
     * @return  string
     */
    public function toJson()
    {
        return json_encode($this->jsonSerialize());
    }
}
