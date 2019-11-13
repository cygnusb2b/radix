<?php

namespace AppBundle\Cloning\Transformer;

use As3\Modlr\Models\Model;

class AccountIdentityTransformer extends AbstractTransformer
{
    /**
     * @api     Interface?
     */
    public function transform(Model $account, Model $identity)
    {

    }

    /**
     * @api     Interface?
     */
    public function createAndTransformFor(Model $account)
    {

    }

    /**
     * @api     Interface?
     */
    public function getTransformTypes()
    {
        return ['customer-account', 'customer-identity'];
    }
}
