<?php

namespace AppBundle\Twig;

use AppBundle\Core\AccountManager;
use \Twig_Environment;
use \Twig_Error_Loader;
use \Twig_Extension;
use \Twig_SimpleFunction;

class LibraryExtension extends Twig_Extension
{
    const ROOT = '@AppBundle/Resources/library/js';

    /**
     * @var AccountManager
     */
    private $manager;

    /**
     * @var Twig_Environment
     */
    private $twigEnv;

    /**
     * @param   AccountManager  $manager
     */
    public function __construct(AccountManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return "TwigRadixLibraryExtension";
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        $options = ['is_safe' => ['js'], 'needs_environment' => true];
        return [
            new Twig_SimpleFunction('loadFile',      [$this, 'loadFile'],      $options),
            new Twig_SimpleFunction('loadForm',      [$this, 'loadForm'],      $options),
            new Twig_SimpleFunction('loadComponent', [$this, 'loadComponent'], $options),
            new Twig_SimpleFunction('loadModule',    [$this, 'loadModule'],    $options),
        ];
    }

    /**
     * Renders a library component file.
     *
     * @param   string  $name   The component name.
     * @return  string
     */
    public function loadComponent(Twig_Environment $env, $name)
    {
        $name = sprintf('components/%s', $name);
        return $this->loadFile($env, $name);
    }

    /**
     * Renders a library file.
     *
     * @param   string  $name   The file name.
     * @return  string|array
     */
    public function loadFile(Twig_Environment $env, $name)
    {
        $this->setTwigEnvironment($env);
        return $this->render($name);
    }

    /**
     * Renders a library form file.
     *
     * @param   string  $name   The file name.
     * @return  string
     */
    public function loadForm(Twig_Environment $env, $name)
    {
        $appKey = $this->manager->getDatabaseSuffix();
        $order  = [
            sprintf('forms/%s/%s', $appKey, $name),
            sprintf('forms/%s', $name)
        ];
        return $this->loadFile($env, $order);
    }

    /**
     * Renders a library module file.
     *
     * @param   string  $name   The module name.
     * @return  string
     */
    public function loadModule(Twig_Environment $env, $name)
    {
        $name = sprintf('modules/%s', $name);
        return $this->loadFile($env, $name);
    }

    /**
     * Gets the full resource name.
     *
     * @param   string  $name
     * @return  string
     */
    private function getResourcePath($name)
    {
        return sprintf('%s/%s.js', self::ROOT, $name);
    }

    /**
     * A helper method to render a template by name.
     * Supports "fallback" templates if template names are passed as an array.
     *
     * @param   string|array    $name
     * @return  string
     * @throws  \Exception  If the template cannot be found.
     */
    private function render($name)
    {
        $templates = (Array) $name;

        $lastException = null;
        foreach ($templates as $name) {
            try {
                return $this->twigEnv->render($this->getResourcePath($name));
            } catch (Twig_Error_Loader $e) {
                $lastException = $e;
                continue;
            }
        }
        throw $lastException;
    }

    /**
     * Sets the Twig environment.
     *
     * @param   Twig_Environment    $twigEnv
     * @return  self
     */
    private function setTwigEnvironment(Twig_Environment $twigEnv)
    {
        $this->twigEnv = $twigEnv;
        return $this;
    }
}
