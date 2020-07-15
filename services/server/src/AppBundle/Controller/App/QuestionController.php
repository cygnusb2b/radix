<?php

namespace AppBundle\Controller\App;

use \DateTime;
use AppBundle\Exception\HttpFriendlyException;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class QuestionController extends AbstractAppController
{
    const CACHE_TTL = 3600;

    /**
     * Retrieves a question (by key or id), serializes it, and returns a JSON reponse.
     *
     * @param   string  $keyOrId
     * @return  JsonResponse
     * @throws  HttpFriendlyException
     */
    public function retrieveAction($keyOrId)
    {
        if (preg_match('/^[a-f0-9]{24}$/i', $keyOrId)) {
            $question = $this->retrieveById($keyOrId);
        } else {
            $question = $this->retrieveByKey($keyOrId);
        }
        if (null === $question || true === $question->get('deleted')) {
            throw new HttpFriendlyException(sprintf('No question found for key or id `%s`', $keyOrId), 404);
        }
        return $this->createResponseFor($question);
    }

    /**
     * Retrieves a question by tag id, serializes it, and returns a JSON reponse.
     *
     * @param   string  $tagKeyOrId
     * @return  JsonResponse
     * @throws  HttpFriendlyException
     */
    public function retrieveByTagAction($tagKeyOrId)
    {
        if (preg_match('/^[a-f0-9]{24}$/i', $tagKeyOrId)) {
            $tag = $this->retrieveTagById($tagKeyOrId);
        } else {
            $tag = $this->retrieveTagByKey($tagKeyOrId);
        }

        if (null === $tag) {
            throw new HttpFriendlyException(sprintf('No question tag found for key or id `%s`', $tagKeyOrId), 404);
        }

        $question = $this->retrieveByTagId($tag->getId());
        if (null === $question || true === $question->get('deleted')) {
            throw new HttpFriendlyException(sprintf('No question found using tag id `%s`', $tag->getId()), 404);
        }
        return $this->createResponseFor($question);
    }

    /**
     * Creates a response for the provided question.
     *
     * @param   Model   $question
     * @return  JsonResponse
     */
    private function createResponseFor(Model $question)
    {
        $modified = $question->get('updatedDate');
        if (!$modified instanceof DateTime) {
            $modified = new DateTime();
        }

        $response = new JsonResponse($this->serializeQuestion($question));

        $this->get('app_bundle.caching.response_cache')->addStandardHeaders($response, $modified, self::CACHE_TTL);
        return $response;
    }

    /**
     * Retrieve a question model by key.
     *
     * @param   string  $key
     * @param   Model|null
     */
    private function retrieveByKey($key)
    {
        $criteria = ['key' => $key];
        return $this->get('as3_modlr.store')->findQuery('question', $criteria)->getSingleResult();
    }

    /**
     * Retrieve a question model by id.
     *
     * @param   string  $key
     * @param   Model|null
     */
    private function retrieveById($identifier)
    {
        $criteria = ['id' => $identifier];
        return $this->get('as3_modlr.store')->findQuery('question', $criteria)->getSingleResult();
    }

    /**
     * Retrieve a question model by tag id.
     *
     * @param   string  $key
     * @param   Model|null
     */
    private function retrieveByTagId($identifier)
    {
        $criteria = ['tags' => $identifier];
        return $this->get('as3_modlr.store')->findQuery('question', $criteria)->getSingleResult();
    }

    /**
     * Retrieve a question tag model by key.
     *
     * @param   string  $key
     * @param   Model|null
     */
    private function retrieveTagById($identifier)
    {
        $criteria = ['id' => $identifier];
        return $this->get('as3_modlr.store')->findQuery('question-tag', $criteria)->getSingleResult();
    }

    /**
     * Retrieve a question tag model by key.
     *
     * @param   string  $key
     * @param   Model|null
     */
    private function retrieveTagByKey($key)
    {
        $criteria = ['key' => $key];
        return $this->get('as3_modlr.store')->findQuery('question-tag', $criteria)->getSingleResult();
    }
}
