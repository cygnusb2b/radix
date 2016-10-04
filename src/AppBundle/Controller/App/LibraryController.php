<?php

namespace AppBundle\Controller\App;

use \CssMin;
use \DateTime;
use \JSMin;
use AppBundle\Core\AccountManager;
use AppBundle\Security\User\Customer;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LibraryController extends AbstractAppController
{
    /**
     * Sets the max age and calcs the expiration of the library response.
     */
    const TTL = 7200;

    /**
     * Retrieves a library file.
     *
     * @param   string  $name
     * @param   bool    $minify
     * @return  Response
     */
    public function indexAction($name, $minify, Request $request)
    {
        $caching  = $this->get('app_bundle.caching.response_cache');
        $format   = $request->attributes->get('_format');
        $path     = '@AppBundle/Resources/library/js';
        $file     = sprintf('%s.%s', $name, $format);
        $response = $this->render(sprintf('@AppBundle/Resources/library/%s/%s', $format, $file));
        $modified = new DateTime();

        $modified->setTimestamp($this->findMostRecentModified($path));
        $caching->addStandardHeaders($response, $modified, self::TTL);

        if (true == $minify) {
            $this->minify($response, $format);
        }

        // Append the build version to the Vary
        $response->setVary(AccountManager::BUILD_PARAM, false);
        return $response;
    }

    /**
     * Finds the most recently modified time for a provided resource path.
     *
     * @param   string  $resource
     * @return  int
     */
    private function findMostRecentModified($resource)
    {
        $path = $this->get('kernel')->locateResource($resource);
        $finder = new Finder();
        $files  = $finder->files()->in($path);

        $modified = 0;
        foreach ($files as $file) {
            $mtime = filemtime($file);
            if ($mtime > $modified) {
                $modified = $mtime;
            }
        }
        return $modified;
    }

    /**
     * Minifies a response.
     *
     * @param   Response    $response
     * @param   string      $format
     */
    private function minify(Response $response, $format)
    {
        $content = $response->getContent();
        switch ($format) {
            case 'css':
                $content = CssMin::minify($content);
                break;
            case 'js':
                $content = JSMin::minify($content);
                break;
        }
        $response->setContent($content);
    }
}
