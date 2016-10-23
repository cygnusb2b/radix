<?php

namespace AppBundle\Factory\Identity;

use AppBundle\Factory\AbstractEmbedFactory;
use AppBundle\Factory\Error;
use AppBundle\Utility\LocaleUtility;
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
     * Applies attribute key/value data to the provided phone.
     *
     * @param   Embed   $phone
     * @param   array   $attributes
     * @return  Embed   $phone
     */
    public function apply(Embed $phone, array $attributes = [])
    {
        if (false === $this->supportsEmbed($phone)) {
            $this->getUnsupportedError()->throwException();
        }
        $metadata = $phone->getMetadata();
        foreach ($attributes as $key => $value) {
            if (true === $metadata->hasAttribute($key)) {
                $phone->set($key, $value);
            }
        }
        return $phone;
    }

    /**
     * {@inheritdoc}
     */
    public function canSave(AbstractModel $phone)
    {
        if (false === $this->supportsEmbed($phone)) {
            return $this->getUnsupportedError();
        }

        $this->preValidate($phone);

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
        return 'identity-phone'
    }
}
