<?php

namespace AppBundle\Controller\App;

use \DateTime;
use AppBundle\Exception\HttpFriendlyException;
use As3\Modlr\Exception\MetadataException;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends AbstractAppController
{
    const CACHE_TTL = 3600;

    /**
     * Retrieves all products (optionally filter by a product type) as serialized response.
     *
     * @param   string|null     $type
     * @throws  HttpFriendlyException
     */
    public function allAction($type = null)
    {
        $modelType  = $this->getModelTypeFor($type);
        $products   = $this->retrieveAllFor($modelType)->allWithoutLoad();
        $updated    = $this->getNewestUpdatedFor($products);
        $serialized = $this->serializeProducts($products);
        return $this->createCachedResponseFor($serialized, $updated);
    }

    /**
     * Retrieves a product (by key or id) as a serialized response.
     *
     * @param   string  $type
     * @param   string  $keyOrId
     * @return  JsonResponse
     * @throws  HttpFriendlyException
     */
    public function retrieveAction($type, $keyOrId)
    {
        $modelType  = $this->getModelTypeFor($type);

        if (preg_match('/^[a-f0-9]{24}$/i', $keyOrId)) {
            $product = $this->retrieveById($modelType, $keyOrId);
        } else {
            $product = $this->retrieveByKey($modelType, $keyOrId);
        }
        if (null === $product) {
            throw new HttpFriendlyException(sprintf('No %s product found for key or id `%s`', $type, $keyOrId), 404);
        }
        return $this->createCachedResponseFor($this->serializeProduct($product), $product->get('updateDate'));
    }

    /**
     * Creates a response (with cache headers) for the provided data.
     *
     * @param   array           $data
     * @param   DateTime|null   $modified
     * @return  JsonResponse
     */
    private function createCachedResponseFor(array $data, DateTime $modified = null)
    {
        $response = new JsonResponse($data);
        if (null === $modified) {
            return $response;
        }

        $this->get('app_bundle.caching.response_cache')->addStandardHeaders($response, $modified, self::CACHE_TTL);
        return $response;
    }

    /**
     * Gets the newest updated date for a set of models.
     *
     * @param   Models[]    $models
     * @return  DateTime|null
     */
    private function getNewestUpdatedFor(array $models)
    {
        $updated = null;
        foreach ($models as $model) {
            $modelUpdated = $model->get('updatedDate');
            if (null === $modelUpdated) {
                continue;
            }
            if (null === $updated) {
                $updated = $modelUpdated;
                continue;
            }
            if ($modelUpdated->getTimestamp() > $updated->getTimestamp()) {
                $updated = $modelUpdated;
            }
        }
        return $updated;
    }

    /**
     * Gets the product model type for a type endpoint.
     *
     * @param   string  $type
     * @return  string
     * @throws  HttpFriendlyException
     */
    private function getModelTypeFor($type)
    {
        $store = $this->get('as3_modlr.store');
        if (null !== $type) {
            $modelType = sprintf('product-%s', $type);
            try {
                $metadata = $store->getMetadataForType($modelType);
            } catch (MetadataException $e) {
                throw new HttpFriendlyException(sprintf('The product type "%s" does not exist.', $type), 404);
            }
        } else {
            $modelType = 'product';
        }
        return $modelType;
    }

    /**
     * Retrieves all product models for the provided type.
     *
     * @param   string  $type
     * @return  Models[]
     */
    private function retrieveAllFor($type)
    {
        $store = $this->get('as3_modlr.store');
        return $store->findQuery($type, [], [], ['sequence' => 1]);
    }

    /**
     * Retrieve a product model by key.
     *
     * @param   string  $type
     * @param   string  $key
     * @param   Model|null
     */
    private function retrieveByKey($type, $key)
    {
        $criteria = ['key' => $key];
        return $this->get('as3_modlr.store')->findQuery($type, $criteria)->getSingleResult();
    }

    /**
     * Retrieve a product model by id.
     *
     * @param   string  $type
     * @param   string  $key
     * @param   Model|null
     */
    private function retrieveById($type, $identifier)
    {
        $criteria = ['id' => $identifier];
        return $this->get('as3_modlr.store')->findQuery($type, $criteria)->getSingleResult();
    }

    /**
     * Serializes a single product model.
     *
     * @param   Model   $model
     * @return  array
     */
    private function serializeProduct(Model $model)
    {
        $serializer = $this->get('app_bundle.serializer.public_api');
        return $serializer->serialize($model);
    }

    /**
     * Serializes multiple product models.
     *
     * @param   Model[] $models
     * @return  array
     */
    private function serializeProducts(array $models)
    {
        $serializer = $this->get('app_bundle.serializer.public_api');
        return ['data' => $serializer->serializeArray($models)];
    }
}
