<?php

namespace AppBundle\Controller\App;

use \DateTime;
use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Serializer\PublicApiRules as Rules;
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
     * Serializes the provided question.
     *
     * @param   Model   $question
     * @return  array
     */
    private function serializeQuestion(Model $question)
    {
        $serializer = $this->get('app_bundle.serializer.public_api');
        $serializer->addRule(new Rules\QuestionSimpleRule());
        $serializer->addRule(new Rules\QuestionChoiceSimpleRule());

        $serialized = $serializer->serialize($question);
        foreach ($serialized['data']['choices'] as $index => $choice) {
            $serialized['data']['choices'][$index]['option'] = [
                'value' => $choice['_id'],
                'label' => $choice['name'],
            ];
        }
        return $serialized;
    }
}
