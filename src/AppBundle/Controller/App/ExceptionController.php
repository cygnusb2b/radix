<?php

namespace AppBundle\Controller\App;

use Symfony\Bundle\TwigBundle\Controller\ExceptionController as BaseExceptionController;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class ExceptionController extends BaseExceptionController
{
    /**
     * {@inheritdoc}
     */
    public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null)
    {
        $currentContent = $this->getAndCleanOutputBuffering($request->headers->get('X-Php-Ob-Level', -1));
        $showException = $request->attributes->get('showException', $this->debug); // As opposed to an additional parameter, this maintains BC

        $meta = [];
        $code = $exception->getStatusCode();


        if ('AppBundle\Exception\HttpFriendlyException' === $exception->getClass()) {
            $headers = $exception->getHeaders();
            foreach ($headers as $key => $value) {
                if (0 === stripos($key, 'radix.')) {
                    $metaKey = str_replace('radix.', '', $key);
                    $meta[$metaKey] = $value;
                    unset($headers[$key]);
                }
            }
            $exception->setHeaders($headers);
        }
        if ($showException) {
            $meta['exception'] = $exception->toArray();
        }
        if (empty($meta)) {
            $meta = new \stdClass();
        }

        return new Response($this->twig->render(
            (string) $this->findTemplate($request, $request->getRequestFormat(), $code, $showException),
            [
                'status_code'   => $code,
                'status_text'   => isset(Response::$statusTexts[$code]) ? Response::$statusTexts[$code] : '',
                'exception'     => $exception,
                'logger'        => $logger,
                'currentContent'=> $currentContent,
                'meta'          => $meta,
            ]
        ));
    }
}
