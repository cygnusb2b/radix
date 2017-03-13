<?php

namespace AppBundle\Controller\App;

use \DateTime;
use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Serializer\PublicApiRules as Rules;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class FormController extends AbstractAppController
{
    /**
     * Retrieves a form definition (by key or id).
     *
     * @param   string  $keyOrId
     * @return  JsonResponse
     * @throws  HttpFriendlyException
     */
    public function retrieveAction($keyOrId)
    {
        if (preg_match('/^[a-f0-9]{24}$/i', $keyOrId)) {
            $form = $this->retrieveById($keyOrId);
        } else {
            $form = $this->retrieveByKey($keyOrId);
        }
        if (null === $form || true === $form->get('deleted')) {
            throw new HttpFriendlyException(sprintf('No form definition found for key or id `%s`', $keyOrId), 404);
        }
        return $this->createResponseFor($form);
    }

    /**
     * Creates a response for the provided form.
     *
     * @param   Model   $form
     * @return  JsonResponse
     */
    private function createResponseFor(Model $form)
    {
        $data = [
            'form'   => [],
            'values' => new \stdClass(),
        ];
        $manager = $this->get('app_bundle.identity.manager');

        $typeMap = [
            'primaryEmail' => 'email',
            'primaryPhone.number' => 'tel',
        ];
        $requiresIdentifier = [
            'primaryPhone',
            'primaryAddress',
        ];

        $data['form']['id'] = $form->getId();
        foreach (['key', 'title', 'description'] as $key) {
            $data['form'][$key] = $form->get($key);
        }
        $data['form']['fields'] = [];

        $fields = [];
        foreach ($form->get('identityFields') as $field) {
            $fields[$field->get('sequence')] = $field;
        }
        ksort($fields);
        foreach ($fields as $field) {
            $key  = $field->get('key');
            if ('primaryAddress.countryCode' === $key) {
                $definition = [
                    'component'   => 'CountryPostalCode',
                    'countryCode' => 'identity:primaryAddress.countryCode',
                    'postalCode'  => 'identity:primaryAddress.postalCode',
                ];
            } else {
                $definition = [
                    'component' => 'FormInputText',
                    'name'      => sprintf('identity:%s', $key),
                    'type'      => isset($typeMap[$key]) ? $typeMap[$key] : 'text',
                    'label'     => $field->get('label'),
                ];
                if (true === $manager->isAccountLoggedIn() && 'primaryPhone.number' === $key) {
                    $phone = $manager->getActiveAccount()->get('primaryPhone');
                    if ($phone) {
                        $definition['label'] = sprintf('%s #', $phone->phoneType);
                    }
                }
            }

            $required = true === $manager->isAccountLoggedIn() && 'primaryEmail' === $key ? false : $field->get('required');
            $definition['required'] = $required;

            $readonly = true === $manager->isAccountLoggedIn() && 'primaryEmail' === $key ? true : $field->get('readonly');
            $definition['readonly'] = $readonly;

            foreach ($requiresIdentifier as $prefix) {
                if (0 === stripos($key, $prefix)) {
                    // Requires an hidden identifier field.
                    $data['form']['fields'][] = [
                        'component' => 'FormInputHidden',
                        'name'      => sprintf('identity:%s.identifier', $prefix),
                    ];
                }
            }
            $data['form']['fields'][] = $definition;
        }

        $fields = [];
        foreach ($form->get('questionFields') as $field) {
            $fields[$field->get('sequence')] = $field;
        }
        ksort($fields);
        foreach ($fields as $field) {
            $question = $field->get('question');
            $data['form']['fields'][] = [
                'component'  => 'FormQuestion',
                'name'       => sprintf('%s:answers.%s', $question->get('boundTo'), $question->getId()),
                'question'   => $this->serializeQuestion($question)['data'],
                'required'   => $field->get('required'),
                'readonly'   => $field->get('readonly'),
            ];
        }

        if (empty($data['form'])) {
            // Ensure empty forms are returned as an object.
            $data['form'] = new \stdClass();
        }

        if (true === $manager->isAccountLoggedIn()) {
            $data['values'] =  $this->serializeValues($manager->getActiveAccount(), $data['form']['fields']);
        }

        return new JsonResponse(['data' => $data]);
    }

    /**
     * Retrieve a form definition model by key.
     *
     * @param   string  $key
     * @param   Model|null
     */
    private function retrieveByKey($key)
    {
        $criteria = ['key' => $key];
        return $this->get('as3_modlr.store')->findQuery('form-definition', $criteria)->getSingleResult();
    }

    /**
     * Retrieve a form definition model by id.
     *
     * @param   string  $key
     * @param   Model|null
     */
    private function retrieveById($identifier)
    {
        $criteria = ['id' => $identifier];
        return $this->get('as3_modlr.store')->findQuery('form-definition', $criteria)->getSingleResult();
    }


    /**
     * Serializes the provided question.
     *
     * @todo    This needs to be consolidated into a service or helper so this and the question controller can share.
     * @param   Model   $question
     * @return  array
     */
    private function serializeQuestion(Model $question)
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

    private function serializeValues(Model $model, array $fields)
    {
        // @todo Values should only be returned when needed, not all the time.
        $values     = [];
        $attributes = ['givenName', 'familyName', 'middleName', 'salutation', 'suffix', 'gender', 'title', 'companyName', 'picture', 'displayName'];
        foreach ($attributes as $key) {
            $name = sprintf('identity:%s', $key);
            $value = $model->get($key);
            if (!empty($value)) {
                $values[$name] = $value;
            }
        }

        $email = $model->get('primaryEmail');
        if (!empty($email)) {
            $values['identity:primaryEmail'] = $email;
        }

        $phoneAttrs = ['identifier', 'description', 'phoneType', 'number', 'isPrimary'];
        if (null !== $item = $model->get('primaryPhone')) {
            $item = (array) $item;
            foreach ($phoneAttrs as $key) {
                $name = sprintf('identity:primaryPhone.%s', $key);
                if (isset($item[$key]) && !empty($item[$key])) {
                    $values[$name] = $item[$key];
                }
            }
        }

        $addrAttrs = ['identifier','description','isPrimary','companyName','street','extra','city','regionCode','countryCode','postalCode'];
        if (null !== $item = $model->get('primaryAddress')) {
            $item = (array) $item;
            foreach ($addrAttrs as $key) {
                $name = sprintf('identity:primaryAddress.%s', $key);
                if (isset($item[$key]) && !empty($item[$key])) {
                    $values[$name] = $item[$key];
                }
            }
        }

        foreach ($model->get('answers') as $answer) {
            if (null === $question = $answer->get('question')) {
                continue;
            }
            $name = sprintf('identity:answers.%s', $question->getId());
            switch ($answer->getType()) {
                case 'identity-answer-choice':
                    if (null !== $choice = $answer->get('value')) {
                        $values[$name] = $choice->getId();
                    }
                    break;
                case 'identity-answer-choices':
                    $choices = [];
                    foreach ($answer->get('value') as $choice) {
                        $choices[] = $choice->getId();
                    }
                    $values[$name] = implode(',', $choices);
                    break;
                default:
                    $value = $answer->get('value');
                    if (is_bool($value) && !$value) {
                        $value = 'false';
                    }
                    $values[$name] = (string) $value;
                    break;
            }
        }
        if (empty($values)) {
            // Ensure empty values are returned as an object.
            $values = new \stdClass();
        }
        return $values;
    }
}
