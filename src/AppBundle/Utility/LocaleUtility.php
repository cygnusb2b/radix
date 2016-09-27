<?php

namespace AppBundle\Utility;

/**
 * Utility class for retrieving country/region data.
 *
 * @author Jacob Bare <jacob.bare@gamil.com>
 */
class LocaleUtility
{
    /**
     * Gets address data from Google's geo-code API for the provided address data.
     *
     * @param   string|array    $address
     * @return  array
     */
    public static function getAddressData($address)
    {
        if (is_array($address)) {
            $address = implode(' ', $address);
        }
        $address = urlencode($address);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('http://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=true', $address));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = @curl_exec($ch);
        curl_close($ch);

        if (empty($response)) {
            return [];
        }
        $response = @json_decode($response, true);
        if (!is_array($response) || empty($response)) {
            return [];
        }
        return $response;
    }

    /**
     * Gets locality data (city, region, country) for the provided postal code.
     *
     * @param   string  $postalCode
     * @return  array
     */
    public static function getLocalityDataFor($postalCode)
    {
        $crc  = [null, null, null];
        $data = self::getAddressData($postalCode);

        if (!isset($data['results'][0]['address_components'])) {
            return;
        }
        foreach ($data['results'][0]['address_components'] as $component) {
            if (isset($component['types'])) {
                if (in_array('locality', $component['types'])) {
                    // City
                    $crc[0] = $component['long_name'];
                } else if (in_array('administrative_area_level_1', $component['types'])) {
                    // State
                    $crc[1] = $component['short_name'];
                } else if (in_array('country', $component['types'])) {
                    // Country
                    $crc[2] = $component['short_name'];
                }
            }
        }
        return $crc;
    }

    /**
     * Gets a list of countries, keyed by ISO 3166-1 alpha-3 code.
     *
     * @return  array
     */
    public static function getCountries()
    {
        return [
            'USA' => 'United States',
            'CAN' => 'Canada',
            'AFG' => 'Afghanistan',
            'ALA' => 'Aland Islands',
            'ALB' => 'Albania',
            'DZA' => 'Algeria',
            'ASM' => 'American Samoa',
            'AND' => 'Andorra',
            'AGO' => 'Angola',
            'AIA' => 'Anguilla',
            'ATA' => 'Antarctica',
            'ATG' => 'Antigua and Barbuda',
            'ARG' => 'Argentina',
            'ARM' => 'Armenia',
            'ABW' => 'Aruba',
            'ASC' => 'Ascension Island',
            'AUS' => 'Australia',
            'AUT' => 'Austria',
            'AZE' => 'Azerbaijan',
            'BHS' => 'Bahamas',
            'BHR' => 'Bahrain',
            'BGD' => 'Bangladesh',
            'BRB' => 'Barbados',
            'BLR' => 'Belarus',
            'BEL' => 'Belgium',
            'BLZ' => 'Belize',
            'BEN' => 'Benin',
            'BMU' => 'Bermuda',
            'BTN' => 'Bhutan',
            'BOL' => 'Bolivia',
            'BIH' => 'Bosnia and Herzegovina',
            'BWA' => 'Botswana',
            'BVT' => 'Bouvet Island',
            'BRA' => 'Brazil',
            'IOT' => 'British Indian Ocean Territory',
            'BRN' => 'Brunei Darussalam',
            'BGR' => 'Bulgaria',
            'BFA' => 'Burkina Faso',
            'BDI' => 'Burundi',
            'CIV' => 'Cote dIvoire',
            'KHM' => 'Cambodia',
            'CMR' => 'Cameroon',
            'CAN' => 'Canada',
            'CPV' => 'Cape Verde',
            'CYM' => 'Cayman Islands',
            'CAF' => 'Central African Republic',
            'TCD' => 'Chad',
            'CHL' => 'Chile',
            'CHN' => 'China',
            'CXR' => 'Christmas Island',
            'CCK' => 'Cocos (Keeling) Islands',
            'COL' => 'Colombia',
            'COM' => 'Comoros',
            'COG' => 'Congo',
            'COD' => 'Congo, the Democratic Republic of the',
            'COK' => 'Cook Islands',
            'CRI' => 'Costa Rica',
            'HRV' => 'Croatia',
            'CUB' => 'Cuba',
            'CYP' => 'Cyprus',
            'CZE' => 'Czech Republic',
            'DNK' => 'Denmark',
            'DJI' => 'Djibouti',
            'DMA' => 'Dominica',
            'DOM' => 'Dominican Republic',
            'ECU' => 'Ecuador',
            'EGY' => 'Egypt',
            'SLV' => 'El Salvador',
            'GNQ' => 'Equatorial Guinea',
            'ERI' => 'Eritrea',
            'EST' => 'Estonia',
            'ETH' => 'Ethiopia',
            'FLK' => 'Falkland Islands (Malvinas)',
            'FRO' => 'Faroe Islands',
            'FJI' => 'Fiji',
            'FIN' => 'Finland',
            'FRA' => 'France',
            'FXX' => 'France, Metropolitan',
            'GUF' => 'French Guiana',
            'PYF' => 'French Polynesia',
            'ATF' => 'French Southern Territories',
            'GAB' => 'Gabon',
            'GMB' => 'Gambia',
            'GEO' => 'Georgia',
            'DEU' => 'Germany',
            'GHA' => 'Ghana',
            'GIB' => 'Gibraltar',
            'GRC' => 'Greece',
            'GRL' => 'Greenland',
            'GRD' => 'Grenada',
            'GLP' => 'Guadeloupe',
            'GUM' => 'Guam',
            'GTM' => 'Guatemala',
            'GGY' => 'Guernsey',
            'GIN' => 'Guinea',
            'GNB' => 'Guinea-Bissau',
            'GUY' => 'Guyana',
            'HTI' => 'Haiti',
            'HMD' => 'Heard Island and McDonald Islands',
            'VAT' => 'Holy See (Vatican City State)',
            'HND' => 'Honduras',
            'HKG' => 'Hong Kong',
            'HUN' => 'Hungary',
            'ISL' => 'Iceland',
            'IND' => 'India',
            'IDN' => 'Indonesia',
            'IRN' => 'Iran',
            'IRQ' => 'Iraq',
            'IRL' => 'Ireland',
            'IMN' => 'Isle of Man',
            'ISR' => 'Israel',
            'ITA' => 'Italy',
            'JAM' => 'Jamaica',
            'JPN' => 'Japan',
            'JEY' => 'Jersey',
            'JOR' => 'Jordan',
            'KAZ' => 'Kazakhstan',
            'KEN' => 'Kenya',
            'KIR' => 'Kiribati',
            'PRK' => 'Korea, Democratic Peoples Republic of',
            'KOR' => 'Korea, Republic of',
            'KWT' => 'Kuwait',
            'KGZ' => 'Kyrgyzstan',
            'LAO' => 'Lao Peoples Democratic Republic',
            'LVA' => 'Latvia',
            'LBN' => 'Lebanon',
            'LSO' => 'Lesotho',
            'LBR' => 'Liberia',
            'LBY' => 'Libyan Arab Jamahiriya',
            'LIE' => 'Liechtenstein',
            'LTU' => 'Lithuania',
            'LUX' => 'Luxembourg',
            'MAC' => 'Macao',
            'MKD' => 'Macedonia, the former Yugoslav Republic of',
            'MDG' => 'Madagascar',
            'MWI' => 'Malawi',
            'MYS' => 'Malaysia',
            'MDV' => 'Maldives',
            'MLI' => 'Mali',
            'MLT' => 'Malta',
            'MHL' => 'Marshall Islands',
            'MTQ' => 'Martinique',
            'MRT' => 'Mauritania',
            'MUS' => 'Mauritius',
            'MYT' => 'Mayotte',
            'MEX' => 'Mexico',
            'FSM' => 'Micronesia, Federated States of',
            'MDA' => 'Moldova, Republic of',
            'MCO' => 'Monaco',
            'MNG' => 'Mongolia',
            'MNE' => 'Montenegro',
            'MSR' => 'Montserrat',
            'MAR' => 'Morocco',
            'MOZ' => 'Mozambique',
            'MMR' => 'Myanmar',
            'NAM' => 'Namibia',
            'NRU' => 'Nauru',
            'NPL' => 'Nepal',
            'NLD' => 'Netherlands',
            'ANT' => 'Netherlands Antilles',
            'NCL' => 'New Caledonia',
            'NZL' => 'New Zealand',
            'NIC' => 'Nicaragua',
            'NER' => 'Niger',
            'NGA' => 'Nigeria',
            'NIU' => 'Niue',
            'NFK' => 'Norfolk Island',
            'MNP' => 'Northern Mariana Islands',
            'NOR' => 'Norway',
            'OMN' => 'Oman',
            'PAK' => 'Pakistan',
            'PLW' => 'Palau',
            'PSE' => 'Palestinian Territory, Occupied',
            'PAN' => 'Panama',
            'PNG' => 'Papua New Guinea',
            'PRY' => 'Paraguay',
            'PER' => 'Peru',
            'PHL' => 'Philippines',
            'PCN' => 'Pitcairn',
            'POL' => 'Poland',
            'PRT' => 'Portugal',
            'PRI' => 'Puerto Rico',
            'QAT' => 'Qatar',
            'REU' => 'Reunion',
            'ROU' => 'Romania',
            'RUS' => 'Russian Federation',
            'RWA' => 'Rwanda',
            'SHN' => 'Saint Helena',
            'KNA' => 'Saint Kitts and Nevis',
            'LCA' => 'Saint Lucia',
            'MAF' => 'Saint Martin',
            'SPM' => 'Saint Pierre and Miquelon',
            'VCT' => 'Saint Vincent and the Grenadines',
            'WSM' => 'Samoa',
            'SMR' => 'San Marino',
            'STP' => 'Sao Tome and Principe',
            'SAU' => 'Saudi Arabia',
            'SEN' => 'Senegal',
            'SRB' => 'Serbia',
            'SYC' => 'Seychelles',
            'SLE' => 'Sierra Leone',
            'SGP' => 'Singapore',
            'SVK' => 'Slovakia',
            'SVN' => 'Slovenia',
            'SLB' => 'Solomon Islands',
            'SOM' => 'Somalia',
            'ZAF' => 'South Africa',
            'SGS' => 'South Georgia and the South Sandwich Islands',
            'ESP' => 'Spain',
            'LKA' => 'Sri Lanka',
            'SDN' => 'Sudan',
            'SUR' => 'Suriname',
            'SJM' => 'Svalbard and Jan Mayen',
            'SWZ' => 'Swaziland',
            'SWE' => 'Sweden',
            'CHE' => 'Switzerland',
            'SYR' => 'Syrian Arab Republic',
            'TWN' => 'Taiwan',
            'TJK' => 'Tajikistan',
            'TZA' => 'Tanzania, United Republic of',
            'THA' => 'Thailand',
            'TLS' => 'Timor-Leste',
            'TGO' => 'Togo',
            'TKL' => 'Tokelau',
            'TON' => 'Tonga',
            'TTO' => 'Trinidad and Tobago',
            'TUN' => 'Tunisia',
            'TUR' => 'Turkey',
            'TKM' => 'Turkmenistan',
            'TCA' => 'Turks and Caicos Islands',
            'TUV' => 'Tuvalu',
            'UGA' => 'Uganda',
            'UKR' => 'Ukraine',
            'ARE' => 'United Arab Emirates',
            'GBR' => 'United Kingdom',
            'USA' => 'United States',
            'UMI' => 'United States Minor Outlying Islands',
            'URY' => 'Uruguay',
            'UZB' => 'Uzbekistan',
            'VUT' => 'Vanuatu',
            'VEN' => 'Venezuela',
            'VNM' => 'Viet Nam',
            'VGB' => 'Virgin Islands, British',
            'VIR' => 'Virgin Islands, U.S.',
            'WLF' => 'Wallis and Futuna',
            'ESH' => 'Western Sahara',
            'YEM' => 'Yemen',
            'ZMB' => 'Zambia',
            'ZWE' => 'Zimbabwe',
        ];
    }

    /**
     * Gets a list of all valid regions.
     *
     * @return  array
     */
    public static function getRegionsAll()
    {
        return array_merge(self::getRegionsCanada(), self::getRegionsMilitary(), self::getRegionsUnitedStates());
    }

    /**
     * Gets a list of Canadian regions.
     *
     * @return  array
     */
    public static function getRegionsCanada()
    {
        return [
            'AB' => 'Alberta',
            'BC' => 'British Columbia',
            'MB' => 'Manitoba',
            'NB' => 'New Brunswick',
            'NF' => 'Newfoundland',
            'NT' => 'Northwest Territories',
            'NS' => 'Nova Scotia',
            'NU' => 'Nunavut',
            'ON' => 'Ontario',
            'PE' => 'Prince Edward Island',
            'QC' => 'Quebec',
            'SK' => 'Saskatchewan',
            'YT' => 'Yukon Territory',
        ];
    }

    /**
     * Gets a list of US military regions.
     *
     * @return  array
     */
    public static function getRegionsMilitary()
    {
        return [
            'AA' => 'U.S. Military - America (AA)',
            'AE' => 'U.S. Military - Overseas Europe (AE)',
            'AP' => 'U.S. Military - Overseas Pacific (AP)',
        ];
    }

    /**
     * Gets a list of United States regions.
     *
     * @return  array
     */
    public static function getRegionsUnitedStates()
    {
        return [
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'DC' => 'District of Columbia',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'GU' => 'Guam',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'PR' => 'Puerto Rico',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VI' => 'Virgin Islands',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming',
        ];
    }
}
