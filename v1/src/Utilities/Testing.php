<?php
/**
 * Created by PhpStorm.
 * User: Joshua
 * Date: 10/27/2015
 * Time: 2:59 PM
 */

namespace Eportfolio\Utilities;

require_once __DIR__ . "/../../config.php";

use eportfolio\Http\Methods;

class Testing
{
    const JSON = 'application/json';
    const FORM = 'application/x-www-form-urlencoded';

    private static $lastResponseCode = 0;

    /**
     * @param $url Full URL to call
     * @param string Http Method to use. See constants in Http\Methods
     * @param string $body Appropriately formated body content
     * @param string $token A token if required
     * @param string $type JSON | FORM
     * @return mixed false on failure, string on success.
     */
    public static function callAPIOverHTTP($endpoint, $method = Methods::GET, $body = "", $token = "", $type = self::JSON)
    {
        global $baseURI;
        $url = "http://icarus.cs.weber.edu".$baseURI . $endpoint;
        $headers = array();
        array_push($headers, "Content-Length: " . strlen($body));
        array_push($headers, "Authorization: Bearer $token");
        array_push($headers, "Content-Type: $type");//application/json
        // 1. initialize
        $ch = curl_init();


        // 2. set the options, including the url
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

        // 3. execute and fetch the resulting HTML output
        $output = curl_exec($ch);

        self::$lastResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (self::$lastResponseCode == 0)
        {
            throw new \Exception (curl_error($ch));
        }
        curl_close($ch);

        return $output;
    }

    public static function getLastHTTPResponseCode() {
        return self::$lastResponseCode;
    }
}