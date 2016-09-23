<?php

namespace AppBundle;

use As3\Modlr\Models\Model;

class CalculatedFields
{
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
}
