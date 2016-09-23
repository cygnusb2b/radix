<?php

namespace AppBundle\Controller\App;

use AppBundle\Utility\LocaleUtility;
use AppBundle\Exception\HttpFriendlyException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UtilityController extends Controller
{
    /**
     * Retrieves country options for use in a select form element.
     *
     * @param   Request $request
     * @return  array
     */
    public function countryOptionsAction(Request $request)
    {
        $options = [];
        foreach (LocaleUtility::getCountries() as $value => $label) {
            $options[$value] = ['value' => $value, 'label' => $label];
        }
        return new JsonResponse(['data' => array_values($options)]);
    }

    /**
     * Retrieves country options for use in a select form element.
     *
     * @param   Request $request
     * @return  array
     */
    public function regionOptionsAction($countryCode, Request $request)
    {
        $options     = [];
        $codes       = LocaleUtility::getCountries();
        $countryCode = strtoupper($countryCode);

        if (!isset($codes[$countryCode])) {
            throw new HttpFriendlyException(sprintf('No country found for "%s"', $countryCode), 404);
        }

        $country = $codes[$countryCode];
        $method  = sprintf('getRegions%s', str_replace(' ', '', $country));
        if (false === method_exists('AppBundle\Utility\LocaleUtility', $method)) {
            throw new HttpFriendlyException(sprintf('No regions found for "%s"', $country), 404);
        }

        foreach (LocaleUtility::{$method}() as $value => $label) {
            $options[$value] = ['value' => $value, 'label' => $label];
        }
        return new JsonResponse(['data' => array_values($options)]);
    }
}
