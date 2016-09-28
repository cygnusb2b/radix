<?php

namespace AppBundle\Factory;

use AppBundle\Utility\LocaleUtility;
use As3\Modlr\Models\Embed;

/**
 * Factory for customer phones.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class CustomerPhoneFactory extends AbstractModelFactory
{
    private $types = ['Work', 'Home', 'Mobile', 'Fax', 'Phone'];

    /**
     * Creates a new customer address for a customer and applies root attributes
     *
     * @param   Embed   $phone
     * @param   string  $number
     * @param   bool    $isPrimary
     * @return  Embed   $phone
     */
    public function apply(Embed $phone, $number, $type = null, $isPrimary = false)
    {
        if (false === $this->supportsEmbed($phone)) {
            $this->getUnsupportedError()->throwException();
        }

        if (null !== $type) {
            $phone->set('phoneType', $type);
        }
        $phone->set('number', $number);
        $phone->set('isPrimary', $isPrimary);
        return $phone;
    }

    /**
     * Determines if the customer address model can be saved.
     *
     * @param   Embed   $phone
     * @return  true|Error
     */
    public function canSave(Embed $phone)
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
     * Actions that always run (during save) before validation occurs.
     *
     * @param   Model   $phone
     */
    public function preValidate(Embed $phone)
    {
        if (null !== $type = $phone->get('phoneType')) {
            $type = ucfirst(strtolower($type));
            $phone->set('phoneType', $type);
        }
    }

    /**
     * Actions that always run (during save) after validation occurs.
     *
     * @param   Embed   $phone
     */
    public function postValidate(Embed $phone)
    {
    }

    /**
     * Gets the unsupported embed type error.
     *
     * @return  Error
     */
    private function getUnsupportedError()
    {
        return new Error('The provided embed model is not supported. Expected an instance of `customer-phone`');
    }

    /**
     * Determines if the embed is supported.
     *
     * @param   Embed   $phone
     * @return  bool
     */
    private function supportsEmbed(Embed $phone)
    {
        return 'customer-phone' === $phone->getName();
    }
}
