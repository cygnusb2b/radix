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
     * Retrieves a CSS library.
     *
     * @param   string  $name
     * @param   bool    $minify
     * @return  Response
     */
    public function cssAction($name, $minify)
    {
        $response = $this->loadFileResponseFor($name, 'css', 'text/css');
        if (true == $minify) {
            $response->setContent(CssMin::minify($response->getContent()));
        }
        return $response;
    }

    /**
     * Retrieves a JS library.
     *
     * @param   string  $name
     * @param   bool    $minify
     * @return  Response
     */
    public function jsAction($name, $minify)
    {
        $response = $this->loadFileResponseFor($name, 'js', 'application/javascript');
        if (true == $minify) {
            $response->setContent(JSMin::minify($response->getContent()));
        }
        return $response;
    }

    /**
     * Creates the response for the file contents.
     *
     * @param   string  $contents
     * @param   int     $modifiedTime
     * @param   string  $contentType
     * @return  Response
     */
    private function createFileResponseFor($contents, $modifiedTime, $contentType)
    {
        $modified = $expires = new DateTime();
        $response = new Response($contents, 200, ['Content-Type' => $contentType]);

        $modified->setTimestamp($modifiedTime);
        $expires->setTimestamp($expires->getTimestamp() + self::TTL);

        $response
            ->setPublic()
            ->setExpires($expires)
            ->setMaxAge(self::TTL)
            ->setSharedMaxAge(self::TTL)
            ->setLastModified($modified)
        ;
        return $response;
    }

    /**
     * Loads the file response for the file and extension (or throws not found).
     *
     * @param   string  $filename
     * @param   string  $extension
     * @param   string  $contentType
     * @return  Response
     */
    private function loadFileResponseFor($filename, $extension, $contentType)
    {
        $file = sprintf('%s.%s', $filename, $extension);
        $path = sprintf('@AppBundle/Resources/library/%s', $extension);
        $path = $this->get('kernel')->locateResource($path);

        $finder = new Finder();
        $files  = $finder->files()->in($path)->name($file);
        foreach ($files as $file) {
            return $this->createFileResponseFor($file->getContents(), filemtime($file), $contentType);
        }
        throw $this->createNotFoundException();
    }
}
