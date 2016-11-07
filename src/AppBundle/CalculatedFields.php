<?php

namespace AppBundle;

use As3\Modlr\Models\Embed;
use As3\Modlr\Models\Model;

class CalculatedFields
{
    public static function identityFirstName(Model $model)
    {
        return $model->get('givenName');
    }

    public static function identityLastName(Model $model)
    {
        return $model->get('familyName');
    }

    public static function identityEmailEmbeddablePrimaryEmail(Model $model)
    {
        $primary = null;
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
        $buildAddress = function(Embed $model) {
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
}
