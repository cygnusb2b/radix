<?php

namespace AppBundle\Utility;

/**
 * Utility class for handling IP addresses
 *
 * @author Jacob Bare <jacob.bare@gamil.com>
 */
class IpAddressUtility
{
    /**
     * Gets a ranges of IP addresses (as integers) that are considered reserved (private).
     * This list it not completely exhaustive.
     *
     * @return  array
     */
    public static function getReservedIps()
    {
        return [
            167772160  => 184549375,  //    10.0.0.0 -  10.255.255.255
            3232235520 => 3232301055, // 192.168.0.0 - 192.168.255.255
            2130706432 => 2147483647, //   127.0.0.0 - 127.255.255.255
            2851995648 => 2852061183, // 169.254.0.0 - 169.254.255.255
            2886729728 => 2887778303, //  172.16.0.0 -  172.31.255.255
            3758096384 => 4026531839, //   224.0.0.0 - 239.255.255.255
        ];
    }

    /**
     * Determines if the given IP address is reserved.
     *
     * @param   string  $ip
     * @return  bool
     */
    public static function isIpReserved($ip)
    {
        $ipLong = sprintf('%u', ip2long($ip));
        foreach (self::getReservedIps() as $start => $end) {
            if (($ipLong >= $start) && ($ipLong <= $end)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determines if the IP address is version 4.
     *
     * @param   string  $ip
     * @return  bool
     */
    public static function isIpVersion4($ip)
    {
        return false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * Determines if the IP address is version 6.
     *
     * @param   string  $ip
     * @return  bool
     */
    public static function isIpVersion6($ip)
    {
        return false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * Attempts to geo-code an IP using ipinfo.io.
     *
     * @param   string  $ip
     * @return  array
     */
    public static function geoCodeIp($ip)
    {
        $geoCode = [];
        if (empty($ip) || true === self::isIpVersion6($ip) || false === self::isIpVersion4($ip) || self::isIpReserved($ip)) {
            return $geoCode;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('http://ipinfo.io/%s/json', $ip));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = @curl_exec($ch);
        curl_close($ch);

        if (empty($response)) {
            return $geoCode;
        }
        $response = @json_decode($response, true);
        if (!is_array($response) || empty($response)) {
            return $geoCode;
        }
        if (isset($response['loc'])) {
            $response['loc'] = explode(',', $response['loc']);
        }
        return $response;
    }
}
