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
    private $identity;

    /**
     * @var Parameters
     */
    private $meta;

    /**
     * @var Parameters
     */
    private $notify;

    /**
     * @var Parameters
     */
    private $submission;

    /**
     * @param   Request|null    $request
     */
    public function __construct(Request $request = null)
    {
        $this->identity   = new Parameters();
        $this->submission = new Parameters();
        $this->meta       = new Parameters();
        $this->notify     = new Parameters();
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

        foreach (['identity', 'submission'] as $key) {
            if (HelperUtility::isSetArray($data, $key)) {
                $this->{$key}->replace($data[$key]);
            }
        }
        foreach (['meta', 'notify'] as $key) {
            if (HelperUtility::isSetArray($payload, $key)) {
                $this->{$key}->replace($payload[$key]);
            }
        }

        // @jpdev - temp store some stuff
        $this->debug['time'] = time();
        $this->debug['ip'] = $request->getClientIp();
        $this->debug['referer'] = $request->headers->get('REQUEST_URI  referer');
        $this->debug['userAgent'] = $request->headers->get('user-agent');
        $this->debug['origin'] = $request->headers->get('origin');
        $this->debug['host'] = $request->getHost();
        $this->debug['path'] = $request->getPathInfo();
        $this->debug['querystring'] = $request->getQueryString();
        $this->debug['requestUri'] = $request->getRequestUri();
        $this->debug['method'] = $request->getMethod();
        $this->debug['body'] = $request->getContent();
        foreach ($request->headers AS $key => $value) {
            $this->debug['headers'][$key] = $value[0];
        }
        $this->identity->set('debug', $this->debug);

        return $this;
    }

    /**
     * Clears all payload data.
     *
     * @return  self
     */
    public function clear()
    {
        $this->identity->clear();
        $this->submission->clear();
        $this->meta->clear();
        return $this;
    }

    /**
     * Gets the identity parameters.
     *
     * @return  Parameters
     */
    public function getIdentity()
    {
        return $this->identity;
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
     * Gets the notify parameters.
     *
     * @return  Parameters
     */
    public function getNotify()
    {
        return $this->notify;
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
            'identity'      => $this->identity->toArray(),
            'submission'    => $this->submission->toArray(),
            'meta'          => $this->meta->toArray(),
            'notify'        => $this->notify->toArray(),
        ];
    }
}
