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
     * @todo    Need to return a sensible set of key/values for front-end purposes... maybe.
     * @param   Model   $model
     * @return  array
     */
    public function serializeModel(Model $model)
    {
        return [
            'data' => ['roles' => $model->get('roles')],
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
