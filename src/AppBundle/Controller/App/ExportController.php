<?php

namespace AppBundle\Controller\App;

use AppBundle\Question\QuestionAnswerFactory;
use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\RequestUtility;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends AbstractAppController
{
    /**
     * Exports users to CSV, based on the provided identifiers.
     *
     * @param   Request $request
     * @return  BinaryFileResponse
     */
    public function indexAction(Request $request)
    {
        $identifiers = RequestUtility::extractPayload($request);

        if (!is_array($identifiers) || empty($identifiers)) {
            throw new HttpFriendlyException('No identifiers were found in the request payload.', 422);
        }

        $identities = $this->get('as3_modlr.store')->findQuery('identity', ['id' => ['$in' => $identifiers]]);


        $attributes = [
            'primaryEmail'  => 'Email Address',
            'firstName'     => 'First Name',
            'lastName'      => 'Last Name',
            'title'         => 'Job Title',
            'companyName'   => 'Company Name',
        ];
        $phoneAttrs = [
            'phoneType' => 'Phone Type',
            'number'    => 'Phone #',
        ];
        $addrAttrs  = [
            'companyName'   => 'Company Name (Address)',
            'street'        => 'Street',
            'extra'         => 'Extra Address',
            'city'          => 'City',
            'region'        => 'Region',
            'regionCode'    => 'Region Code',
            'country'       => 'Country',
            'countryCode'   => 'Country Code',
            'postalCode'    => 'Postal Code',
        ];

        $items = [];
        $labels = ['ID' => true];
        foreach ($identities as $identity) {
            $item = [];
            $item['ID'] = $identity->getId();

            foreach ($attributes as $key => $label) {
                $item[$label] = $identity->get($key);
                $labels[$label] = true;
            }

            $phone = (array) $identity->get('primaryPhone');
            foreach ($phoneAttrs as $key => $label) {
                $item[$label] = isset($phone[$key]) ? $phone[$key] : null;
                $labels[$label] = true;
            }

            $address = (array) $identity->get('primaryAddress');
            foreach ($addrAttrs as $key => $label) {
                $item[$label] = isset($address[$key]) ? $address[$key] : null;
                $labels[$label] = true;
            }

            $answers = QuestionAnswerFactory::humanizeAnswers($identity->get('answers'));
            foreach ($answers as $answer) {
                $item[$answer['name']] = $answer['value'];
                $labels[$answer['name']] = true;
            }
            var_dump($item);
            die();
            $items[] = $item;
        }
        $labels = array_keys($labels);

        $filename = sprintf('%s/export.%s.csv', sys_get_temp_dir(), time());
        $file = new \SplFileObject($filename, 'w');

        $file->fputcsv($labels);
        foreach ($items as $index => $item) {
            $ordered = [];
            foreach ($labels as $label) {
                $ordered[] = isset($item[$label]) ? $item[$label] : null;
            }
            $file->fputcsv($ordered);
        }
        return new BinaryFileResponse($file);
    }
}
