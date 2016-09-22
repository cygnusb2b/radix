<?php

namespace AppBundle\EventSubscriber;

use AppBundle\DataFormatter\MongoDBFormatter;
use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\IpAddressUtility;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class InputSubmissionSubscriber implements EventSubscriberInterface
{
    /**
     * @var MongoDBFormatter
     */
    private $formatter;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param   RequestStack    $requestStack
     */
    public function __construct(RequestStack $requestStack, MongoDBFormatter $formatter)
    {
        $this->requestStack = $requestStack;
        $this->formatter    = $formatter;
    }

    /**
     * {@inheritDoc}
     */
    public function getEvents()
    {
        return [
            Events::preCommit,
        ];
    }

    /**
     * @param   ModelLifecycleArguments     $args
     */
    public function preCommit(ModelLifecycleArguments $args)
    {
        $model = $args->getModel();
        if (false === $this->shouldProcess($model)) {
            return;
        }

        $sourceKey = $model->get('sourceKey');
        $sourceKey = trim($sourceKey);
        if (empty($sourceKey)) {
            throw new HttpFriendlyException('The source key is required on all input submissions.', 400);
        }
        $model->set('sourceKey', $sourceKey);

        $formatted = $this->formatter->formatRaw((array) $model->get('payload'));
        $model->set('payload', $formatted);

        if (null !== $request = $this->requestStack->getCurrentRequest()) {
            $this->appendIpAddress($model, $request);
            $this->appendRequestDetails($model, $request);
        }
    }

    /**
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return 'input-submission' === $model->getType();
    }

    /**
     * @param   Model   $model
     * @param   Request $request
     */
    private function appendIpAddress(Model $model, Request $request)
    {
        $ip = $request->getClientIp();
        if (IpAddressUtility::isIpVersion4($ip)) {
            $model->set('ipFour', $ip);
        }
        if (IpAddressUtility::isIpVersion6($ip)) {
            $model->set('ipSix', $ip);
        }
        $ipInfo = IpAddressUtility::geoCodeIp($ip);
        if (!empty($ipInfo)) {
            $model->set('ipInfo', $ipInfo);
        }
    }

    /**
     * @param   Model   $model
     * @param   Request $request
     */
    private function appendRequestDetails(Model $model, Request $request)
    {
        $embed = $model->createEmbedFor('request');
        $embed
            ->set('host', $request->getHost())
            ->set('method', $request->getMethod())
            ->set('path', $request->getPathInfo())
            ->set('query', $request->getQueryString())
            ->set('headers', (string) $request->headers)
        ;
    }
}
