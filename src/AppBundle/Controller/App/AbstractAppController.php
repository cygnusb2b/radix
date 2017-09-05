<?php

namespace AppBundle\Controller\App;

use AppBundle\Controller\AbstractController;
use AppBundle\Security\User\Customer;
use AppBundle\Serializer\PublicApiRules as Rules;
use AppBundle\Utility\ModelUtility;
use As3\Modlr\Models\Model;

abstract class AbstractAppController extends AbstractController
{
    /**
     * Gets the Customer security user instance, if available.
     *
     * @return  Customer|null
     */
    protected function getCustomer()
    {
        $user = $this->getUserToken()->getUser();
        return $user instanceof Customer ? $user : null;
    }

    /**
     * Loads opt-in values for the provided email address.
     * Is formatted for front-end form handling.
     *
     * @param   string  $emailAddress
     * @return  array
     */
    protected function loadOptInValues($emailAddress)
    {
        $data = [];
        $store = $this->get('as3_modlr.store');
        $emailAddress = ModelUtility::formatEmailAddress($emailAddress);

        $optIns = [];

        if (!empty($emailAddress)) {
            $collection = $store->findQuery('product-email-deployment-optin', ['email' => $emailAddress], ['email' => 1, 'optedIn' => 1, 'product' => 1]);
            foreach ($collection as $optIn) {
                $productId = $optIn->get('product')->getId();
                $optIns[$productId] = $optIn->get('optedIn') ? 'true' : 'false';
            }
        }

        $collection = $store->findQuery('product', ['_type' => 'product-email-deployment'], ['_id' => 1, '_type' => 1]);
        foreach ($collection as $product) {
            $productId = $product->getId();
            $name = sprintf('submission:optIns.%s', $productId);
            $data[$name] = isset($optIns[$productId]) ? $optIns[$productId] : 'false';
        }
        return $data;
    }

    /**
     * Serializes the provided question.
     *
     * @param   Model   $question
     * @return  array
     */
    protected function serializeQuestion(Model $question)
    {
        $serializer = $this->get('app_bundle.serializer.public_api');
        $serializer->setMaxDepth(2);
        $serializer->addRule(new Rules\QuestionSimpleRule());

        if (!in_array($question->get('questionType'), ['choice-single', 'choice-multiple', 'related-choice-single'])) {
            return $serializer->serialize($question);
        }

        $serializer->addRule(new Rules\QuestionChoiceSimpleRule());

        $serialized = $serializer->serialize($question);
        $sequence   = [];
        foreach (['choices', 'relatedChoices'] as $key) {
            foreach ($serialized['data'][$key] as $index => $choice) {
                $sequence[$index] = $choice['sequence'];
                $serialized['data'][$key][$index]['option'] = [
                    'value'     => $choice['_id'],
                    'label'     => $choice['name']
                ];
            }
        }

        if ('related-choice-single' !== $question->get('questionType')) {
            // Sort the choices by sequence, but only for non-related-choice answers.
            array_multisort($sequence, SORT_ASC, $serialized['data']['choices']);
        }

        return $serialized;
    }
}
