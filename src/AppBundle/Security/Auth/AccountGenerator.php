<?php

namespace AppBundle\Security\Auth;

use AppBundle\Security\JWT\JWTGeneratorManager;
use AppBundle\Security\User\Account;
use As3\Modlr\Models\Model;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Generates auth data for an account.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class AccountGenerator implements AuthGeneratorInterface
{
    /**
     * @var JWTGeneratorManager
     */
    private $jwtManager;

    /**
     * @param   JWTGeneratorManager     $jwtManager
     * @param   PublicApiSerializer     $serializer
     */
    public function __construct(JWTGeneratorManager $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFor(UserInterface $user)
    {
        $serialized = $this->serializeModel($user->getModel());
        $serialized['data']['token'] = $this->jwtManager->createFor($user);
        return $serialized;
    }

    /**
     * Serializes an identity account model.
     *
     * @param   Model   $model
     * @return  array
     */
    public function serializeModel(Model $model)
    {
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
                    $values[$name] = $answer->get('value');
                    break;
            }
        }

        $optIns = $model->getStore()->findQuery('product-email-deployment-optin', ['email' => $model->get('primaryEmail')]);
        foreach ($optIns as $optIn) {
            $name = sprintf('submission:optIns.%s', $optIn->get('product')->getId());
            $values[$name] = $optIn->get('optedIn');
        }
        if (empty($values)) {
            // Ensure empty values are returned as an object.
            $values = new \stdClass();
        }
        return [
            'data' => ['values' => $values, 'roles' => $model->get('roles')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supports(UserInterface $user)
    {
        return $user instanceof Account;
    }
}
