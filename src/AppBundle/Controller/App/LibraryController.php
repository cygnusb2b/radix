<?php

namespace AppBundle\Controller\App;

use \CssMin;
use \DateTime;
use \JSMin;
use AppBundle\Security\User\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LibraryController extends Controller
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
        $format   = $request->attributes->get('_format');
        $response = $this->loadFileResponseFor($name, $format);
        if (true == $minify) {
            $this->minify($response, $format);
        }
        return $response;
    }

    /**
     * Creates the response for the file contents.
     *
     * @param   string  $contents
     * @param   int     $modifiedTime
     * @return  Response
     */
    private function createFileResponseFor($contents, $modifiedTime)
    {
        $caching  = $this->get('app_bundle.caching.response_cache');
        $response = new Response($contents, 200);
        $modified = new DateTime();
        $modified->setTimestamp($modifiedTime);
        return $caching->addStandardHeaders($response, $modified, self::TTL);
    }

    /**
     * Loads the file response for the file or throws not found.
     *
     * @param   string  $filename
     * @param   string  $format
     * @return  Response
     */
    private function loadFileResponseFor($filename, $format)
    {
        $file = sprintf('%s.%s', $filename, $format);
        $path = sprintf('@AppBundle/Resources/library/%s', $format);
        $path = $this->get('kernel')->locateResource($path);

        $finder = new Finder();
        $files  = $finder->files()->in($path)->name($file);
        foreach ($files as $file) {
            return $this->createFileResponseFor($file->getContents(), filemtime($file));
        }
        throw $this->createNotFoundException();
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
