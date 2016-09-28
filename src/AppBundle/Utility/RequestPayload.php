<?php

namespace AppBundle\Utility;

use As3\Parameters\Parameters;
use Symfony\Component\HttpFoundation\Request;

/**
 * Standard object for representing a reqest submission paylod
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class RequestPayload
{
    /**
     * @var Parameters
     */
    private $customer;

    /**
     * @var Parameters
     */
    private $meta;

    /**
     * @var Parameters
     */
    private $submission;

    /**
     * @param   Request|null    $request
     */
    public function __construct(Request $request = null)
    {
        $this->customer   = new Parameters();
        $this->submission = new Parameters();
        $this->meta       = new Parameters();
        $this->extractFrom($request);
    }

    /**
     * Static factory create method.
     *
     * @param   Request|null    $request
     * @return  self
     */
    public static function createFrom(Request $request = null)
    {
        return new self($request);
    }

    /**
     * Creates/fill this request payload instance from a request.
     *
     * @param   Request|null    $request
     * @return  self
     */
    public function extractFrom(Request $request = null)
    {
        if (null === $request) {
            return;
        }
        $payload = RequestUtility::extractPayload($request, false);
        $data    = RequestUtility::parsePayloadData($payload['data']);

        if (isset($data['customer'])) {
            $this->customer->replace($data['customer']);
        }
        if (isset($data['submission'])) {
            $this->submission->replace($data['submission']);
        }
        if (isset($payload['meta'])) {
            $this->meta->replace($payload['meta']);
        }
        return $this;
    }

    /**
     * Clears all payload data.
     *
     * @return  self
     */
    public function clear()
    {
        $this->customer->clear();
        $this->submission->clear();
        $this->meta->clear();
        return $this;
    }

    /**
     * Gets the customer parameters.
     *
     * @return  Parameters
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Gets the meta parameters.
     *
     * @return  Parameters
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Gets the submission parameters.
     *
     * @return  Parameters
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * @return  array
     */
    public function toArray()
    {
        return [
            'customer'      => $this->customer->toArray(),
            'submission'    => $this->submission->toArray(),
            'meta'          => $this->meta->toArray(),
        ];
    }
}
