<?php

namespace AppBundle\Factory\Identity;

use AppBundle\Factory\Error;
use AppBundle\Utility\HelperUtility;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;

/**
 * External identity factory.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class IdentityExternalFactory extends IdentityInternalFactory
{
    /**
     * {@inheritdoc}
     */
    public function canSave(AbstractModel $identity)
    {
        if (true !== $result = parent::canSave($identity)) {
            return $result;
        }
        if (empty($identity->get('source')) || empty($identity->get('identifier'))) {
            return new Error('All external identities must contain a source and identifier.', 400);
        }

        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function supports(Model $model)
    {
        return 'identity-external' === $model->getType();
    }

    /**
     * {@inheritdoc}
     */
    protected function createEmptyInstance()
    {
        return $this->getStore()->create('identity-external');
    }
}
