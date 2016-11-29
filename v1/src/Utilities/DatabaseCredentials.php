<?php
/**
 * Created by PhpStorm.
 * User: Daniel Bigelow
 * Date: 11/29/2016
 * Time: 10:00 AM
 */

namespace Eportfolio\Utilities;


class DatabaseCredentials
{
    private static $USER = "scholarshipdev";
    private static $PASS = "WeberCS!";

    /**
     * @return string
     */
    public static function getUSER()
    {
        return self::$user;
    }

    /**
     * @return string
     */
    public static function getPASS()
    {
        return self::$user;
    }



}