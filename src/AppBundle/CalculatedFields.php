<?php

namespace AppBundle;

use As3\Modlr\Models\Model;

class CalculatedFields
{
    public static function identityEmailEmbeddablePrimaryEmail(Model $model)
    {
        $primary = null;
        foreach ($model->get('emails') as $email) {
            if (null === $primary) {
                // Use first email as primary, as a default.
                $primary = [
                    'identifier' => $email->get('identifier'),
                    'value'      => $email->get('value'),
                ];
            }
            if (true === $email->get('isPrimary')) {
                $primary = [
                    'identifier' => $email->get('identifier'),
                    'value'      => $email->get('value'),
                ];
                break;
            }
        }
        return $primary;
    }

    public static function identityAccountPrimaryEmail(Model $model)
    {
        $primary = null;

        // Try verified emails first.
        foreach ($model->get('emails') as $email) {
            $verification = $email->get('verification');
            if (null === $verification || false === $verification->get('verified')) {
                continue;
            }
            if (null === $primary) {
                // Use first email as primary, as a default.
                $primary = [
                    'identifier' => $email->getId(),
                    'value'      => $email->get('value'),
                ];
            }
            if (true === $email->get('isPrimary')) {
                $primary = [
                    'identifier' => $email->getId(),
                    'value'      => $email->get('value'),
                ];
                break;
            }
        }
        if (!empty($primary)) {
            return $primary;
        }

        // Try again with non-verified.
        foreach ($model->get('emails') as $email) {
            if (null === $primary) {
                // Use first email as primary, as a default.
                $primary = $primary = [
                    'identifier' => $email->getId(),
                    'value'      => $email->get('value'),
                ];
            }
            if (true === $email->get('isPrimary')) {
                $primary = $primary = [
                    'identifier' => $email->getId(),
                    'value'      => $email->get('value'),
                ];
                break;
            }
        }

        return $primary;
    }

    public static function identityFullName(Model $model)
    {
        $name = '';
        if ($model->get('givenName')) {
            $name = $model->get('givenName');
        }
        if ($model->get('familyName')) {
            $name = sprintf('%s %s', $name, $model->get('familyName'));
        }
        $name = trim($name);
        if (!empty($name)) {
            return $name;
        }
    }

    public static function identityPrimaryAddress(Model $model)
    {
        $buildAddress = function(Model $model) {
            $fields = ['identifier', 'name', 'companyName', 'street', 'extra', 'city', 'region', 'regionCode', 'postalCode', 'country', 'countryCode'];
            foreach ($fields as $key) {
                $object[$key] = $model->get($key);
            }
            return $object;
        };

        $primary = null;
        foreach ($model->get('addresses') as $address) {

            if (null === $primary) {
                // Use first address as primary, as a default.
                $primary = $buildAddress($address);
            }
            if (true === $address->get('isPrimary')) {
                $primary = $buildAddress($address);
                break;
            }
        }
        return $primary;
    }

    public static function identityPrimaryPhone(Model $model)
    {
        $primary = null;
        foreach ($model->get('phones') as $phone) {
            $type = $phone->get('phoneType');
            if ('fax' === strtolower($type)) {
                continue;
            }
            if (null === $primary) {
                // Use first phone as primary, as a default.
                $primary = ['identifier' => $phone->get('identifier'), 'number' => $phone->get('number'), 'phoneType' => $phone->get('phoneType')];
            }
            if (true === $phone->get('isPrimary')) {
                $primary = ['identifier' => $phone->get('identifier'), 'number' => $phone->get('number'), 'phoneType' => $phone->get('phoneType')];
                break;
            }
        }
        return $primary;
    }


    /**
     * Calculates the email deployment optins for customer-account models.
     *
     * @param   Model   $model
     * @return  array|null
     */
    public static function customerAccountOptIns(Model $model)
    {
        $addresses = [];
        foreach ($model->get('emails') as $email) {
            $addresses[] = $email->get('value');
        }
        $optIns = $model->getStore()->findQuery('product-email-deployment-optin', ['email' => ['$in' => $addresses]]);
        $byEmail = [];
        foreach ($optIns as $optIn) {
            $byEmail[$optIn->get('email')]['address'] = $optIn->get('email');
            $byEmail[$optIn->get('email')]['products'][$optIn->get('product')->getId()] = $optIn->get('optedIn');
        }

        $objs = [];
        foreach ($byEmail as $optIns) {
            $objs[] = (object) $optIns;
        }
        return $objs;
    }

    /**
     * Calculates the email deployment optins for the primary email on customer-account models.
     *
     * @param   Model   $model
     * @return  object|null
     */
    public static function customerAccountPrimaryOptIns(Model $model)
    {
        $address = $model->get('primaryEmail');
        if (empty($address)) {
            return;
        }
        foreach ($model->get('optIns') as $optIn) {
            if ($optIn->address === $address) {
                return $optIn;
            }
        }
    }

    /**
     * Calculates the primary address field for customer models.
     *
     * @param   Model   $model
     * @return  string|null
     */
    public static function customerPrimaryAddress(Model $model)
    {
        $buildAddress = function(Model $model) {
            $fields = ['name', 'companyName', 'street', 'extra', 'city', 'region', 'regionCode', 'postalCode', 'country', 'countryCode'];
            $object = ['_id'   => $model->getId()];
            foreach ($fields as $key) {
                $object[$key] = $model->get($key);
            }
            return $object;
        };

        $primary = null;
        foreach ($model->get('addresses') as $address) {

            if (null === $primary) {
                // Use first address as primary, as a default.
                $primary = $buildAddress($address);
            }
            if (true === $address->get('isPrimaryMailing')) {
                $primary = $buildAddress($address);
                break;
            }
        }
        return $primary;
    }

    /**
     * Calculates the primary email field for a customer account model.
     *
     * @param   Model   $model
     * @return  string|null
     */
    public static function customerAccountPrimaryEmail(Model $model)
    {
        $primary = null;

        // Try verified emails first.
        foreach ($model->get('emails') as $email) {
            if (false === $email->get('verified')) {
                continue;
            }
            if (null === $primary) {
                // Use first email as primary, as a default.
                $primary = $email->get('value');
            }
            if (true === $email->get('isPrimary')) {
                $primary = $email->get('value');
                break;
            }
        }
        if (!empty($primary)) {
            return $primary;
        }

        // Try again with non-verified.
        foreach ($model->get('emails') as $email) {
            if (null === $primary) {
                // Use first email as primary, as a default.
                $primary = $email->get('value');
            }
            if (true === $email->get('isPrimary')) {
                $primary = $email->get('value');
                break;
            }
        }

        return $primary;
    }

    /**
     * Calculates the primary email field for customer models.
     *
     * @param   Model   $model
     * @return  string|null
     */
    public static function customerPrimaryPhone(Model $model)
    {
        $primary = null;
        foreach ($model->get('phones') as $phone) {
            $type = $phone->get('phoneType');
            if ('fax' === strtolower($type)) {
                continue;
            }
            if (null === $primary) {
                // Use first phone as primary, as a default.
                $primary = ['number' => $phone->get('number'), 'phoneType' => $phone->get('phoneType')];
            }
            if (true === $phone->get('isPrimary')) {
                $primary = ['number' => $phone->get('number'), 'phoneType' => $phone->get('phoneType')];
                break;
            }
        }
        return $primary;
    }

    /**
     * Calculates the username field for a customer account model.
     *
     * @param   Model   $model
     * @return  string|null
     */
    public static function customerAccountUsername(Model $model)
    {
        $credentials = $model->get('credentials');
        if (null === $credentials || null === $password = $credentials->get('password')) {
            return;
        }
        return $password->get('username');
    }

    /**
     * Calculates the full name of a customer
     *
     * @param   Model   $model
     * @return  string|null
     */
    public static function customerFullName(Model $model)
    {
        $name = '';
        if ($model->get('givenName')) {
            $name = $model->get('givenName');
        }
        if ($model->get('familyName')) {
            $name = sprintf('%s %s', $name, $model->get('familyName'));
        }
        $name = trim($name);
        if (!empty($name)) {
            return $name;
        }
    }
}
