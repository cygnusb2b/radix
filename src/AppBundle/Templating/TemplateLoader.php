<?php

namespace AppBundle\Templating;

use AppBundle\Serializer\PublicApiSerializer;
use As3\Parameters\Parameters;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class TemplateLoader
{
    /**
     * @var     Store
     */
    private $store;

    /**
     * @var     EngineInterface
     */
    private $templating;

    /**
     * @var PublicApiSerializer
     */
    private $serializer;

    /**
     * @param   Store   $store
     * @param   EngineInterface     $templating
     * @param   PublicApiSerializer $serializer
     */
    public function __construct(Store $store, EngineInterface $templating, PublicApiSerializer $serializer)
    {
        $this->store = $store;
        $this->templating = $templating;
        $this->serializer = $serializer;
    }

    /**
     * Returns a template model
     *
     * @param   string  $type       The type of template to be loaded
     * @param   string  $template   The template to load
     * @return  Model|null
     */
    public function getTemplateModel($type, $template)
    {
        return $this->store
            ->findQuery($type, ['_type' => $type, 'template' => $template])
            ->getSingleResult()
        ;
    }

    /**
     * Returns the template key/path
     *
     * @param   string  $type       The type of template to be loaded
     * @param   string  $template   The template to load
     * @return  string
     */
    public static function getTemplateKey($type, $template)
    {
        return sprintf('%s/%s.html.twig', $type, $template);
    }

    /**
     * Returns the template parts from the key
     *
     * @param   string  $name       The template name
     * @return  array|false         Triple containing $type, $namespace and $key
     */
    public static function getTemplateParts($name)
    {
        $parts = explode('/', $name);
        if (count($parts) !== 2) {
            return false;
        }
        $parts[1] = str_ireplace('.html.twig', '', $parts[1]);
        return $parts;
    }

    /**
     * Renders a template
     *
     * @param   string  $key        The key of template to be loaded
     * @param   array   $args       The template's arguments
     *
     * @return  string
     */
    public function render($key, array $args = [])
    {
        return $this->templating->render($key, $this->serialize($args));
    }

    /**
     * Returns a template model
     *
     * @param   string  $type       The type of template to be loaded
     * @param   string  $template   The template to load
     * @return  boolean
     */
    public function templateModelExists($type, $template)
    {
        return null !== $this->store
            ->findQuery($type, ['_type' => $type, 'template' => $template])
            ->getSingleResult()
        ;
    }

    /**
     * Serializes template args
     *
     * @param   Model   $submission
     * @param   array   $args
     * @return  Parameters
     */
    private function serialize(array $args = [])
    {
        foreach ($args as $k => $v) {
            if ($v instanceof Model) {
                $args[$k] = $this->serializer->serialize($v)['data'];
            }
        }
        return ['values' => new Parameters($args)];
    }
}
