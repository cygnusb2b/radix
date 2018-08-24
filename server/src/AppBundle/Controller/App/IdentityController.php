<?php

namespace AppBundle\Controller\App;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\RequestUtility;
use AppBundle\Serializer\PublicApiRules;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentityController extends AbstractAppController
{
    /**
     * Gets information on the currently discovered identity, if applicable.
     *
     * @param   Request $request
     * @return  JsonResponse
     */
    public function indexAction(Request $request)
    {
        $manager    = $this->get('app_bundle.identity.manager');
        $serializer = $this->get('app_bundle.serializer.public_api');
        $identity   = $manager->getActiveIdentity();

        if (null === $identity) {
            return new JsonResponse(['data' => new \stdClass()]);
        }

        $serializedIdentity = $serializer->serialize($identity)['data'];

        $serializer->addRule(new PublicApiRules\IdentityAnswerRule());
        $serializer->addRule(new PublicApiRules\QuestionChoiceNameRule());
        $serializer->addRule(new PublicApiRules\QuestionLabelRule());
        $answers    = [];
        $simplified = [];

        foreach ($identity->get('answers') as $answer) {
            $item = [
                'question'  => $answer->get('question')->get('name'),
            ];
            $value = $answer->get('value');
            if ($value instanceof Model) {
                $item['answer'] = $value->get('name');
            } elseif (is_array($value)) {
                $item['answer'] = [];
                foreach ($value as $model) {
                    $item['answer'][] = $model->get('name');
                }
            } else {
                $item['answer'] = $value;
            }
            $simplified[] = $item;
            $answers[] = $serializer->serialize($answer)['data'];

        }

        $serializedIdentity['answers'] = $answers;
        $serializedIdentity['answersKeyValue'] = $simplified;
        return new JsonResponse(['data' => $serializedIdentity]);
    }

}
