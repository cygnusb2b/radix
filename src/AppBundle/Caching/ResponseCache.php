<?php

namespace AppBundle\Caching;

use \DateTime;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Handles HTTP response caching concerns.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class ResponseCache
{
    private $kernel;

    private $finder;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->finder = new Finder();
    }

    public function addHeadersForFile(Response $response, $resourcePath, $file, $ttl = 600)
    {
        $modified = new \DateTime();
        $modified->setTimestamp($this->getFileModifiedTime($resourcePath, $file));

        return $this->addStandardHeaders($response, $modified, $ttl);
    }

    public function addStandardHeaders(Response $response, DateTime $modified, $ttl = 600)
    {
        $expires = new DateTime();
        $expires->setTimestamp($expires->getTimestamp() + $ttl);

        return $response
            ->setPublic()
            ->setExpires($expires)
            ->setMaxAge($ttl)
            ->setSharedMaxAge($ttl)
            ->setLastModified($modified)
        ;
    }

    private function getFileModifiedTime($resourcePath, $file)
    {
        $path  = $this->kernel->locateResource($resourcePath);
        $files = $this->finder->files()->in($path)->name($file);

        foreach ($files as $file) {
            return filemtime($file);
        }
    }
}
