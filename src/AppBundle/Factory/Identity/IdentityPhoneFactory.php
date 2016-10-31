<?php

namespace AppBundle\Factory\Identity;

use AppBundle\Factory\AbstractEmbedFactory;
use AppBundle\Factory\Error;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Embed;

/**
 * Factory for identity phones.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class IdentityPhoneFactory extends AbstractEmbedFactory
{
    /**
     * @var string[]
     */
    private $types = ['Work', 'Home', 'Mobile', 'Fax', 'Phone'];

    /**
     * {@inheritdoc}
     */
    public function canSave(AbstractModel $phone)
    {
        if (false === $this->supportsEmbed($phone)) {
            return $this->getUnsupportedError();
        }

        $this->preValidate($phone);

        $number = $phone->get('number');
        if (empty($number)) {
            return new Error(sprintf('The phone number cannot be empty.', $type), 400);
        }

        $type = $phone->get('phoneType');
        if (!in_array($type, $this->types)) {
            return new Error(sprintf('The phone type `%s` is not supported.', $type), 400);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function postValidate(AbstractModel $phone)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function preValidate(AbstractModel $phone)
    {
        if (null !== $type = $phone->get('phoneType')) {
            $type = ucfirst(strtolower($type));
            $phone->set('phoneType', $type);
        }
    }

    /**
     * {@inheritodc}
     */
    protected function getSupportsType()
    {
        return 'identity-phone';
    }
}
