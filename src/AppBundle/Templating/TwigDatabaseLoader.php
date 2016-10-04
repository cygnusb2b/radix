<?php

namespace AppBundle\Templating;

use As3\Modlr\Store\Store;
use Twig_LoaderInterface;

class TwigDatabaseLoader implements Twig_LoaderInterface
{
    /**
     * @var     Store
     */
    private $store;

    /**
     *
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * {@inheritdoc}
     *
     * @param   $name       The template name, e.g: `template-notification/customer-account/verify-email`
     * @return  string|null
     */
    public function getSource($name)
    {
        return $this->getModel($name)->get('contents');
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time)
    {
        $model = $this->getModel($name);
        if (!$model->get('updatedDate') instanceof \DateTime) {
            return false;
        }
        return $date->getTimestamp() >= $time;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        return sprintf('db:%s', $name);
    }

    /**
     * Retrieves the model designated by the naem
     */
    private function getModel($name)
    {
        if (false === stripos($name, '/') || 0 !== stripos($name, 'template')) {
            throw new \Twig_Error_Loader(sprintf('Template "%s" is not supported.', $name));
        }
        try {
            list($type, $template) = explode('/', $name, 3);
            $template = str_replace('.html.twig', '', $template);
            $model = $this->store->findQuery($type, ['deleted' => false, 'template' => $template])->getSingleResult();
            if (null === $model) {
                throw new \Twig_Error_Loader(sprintf('Unable to retrieve "%s" template.', $template));
            }
        } catch (\Exception $e) {
            throw new \Twig_Error_Loader(sprintf('Template "%s" is not supported.', $name));
        }
        return $model;
    }
}
