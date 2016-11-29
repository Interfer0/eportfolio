<?php
/**
 * Created by PhpStorm.
 * User: iamcaptaincode
 */

namespace Eportfolio\Utilities;


use Eportfolio\Utilities\DatabaseCredentials;

require_once 'DatabaseCredentials.php';

class DatabaseConnection
{
    private static $instance = null;
    private static $host = "localhost";
    private static $dbname = "W01288485";
    //private static $user = $USER;
    //private static $pass = $PASS;

    private function __construct()
    {

    }

    public static function getInstance($user, $pass)
    {
        if (!static::$instance === null) {
            return static::$instance;
        } else {
            try {
                $connectionString = "mysql:host=".static::$host.";dbname=".static::$dbname;
                static::$instance = new \PDO($connectionString, $GLOBALS['user'], $GLOBALS['pass']);
                static::$instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                return static::$instance;
            } catch (PDOException $e) {
                echo "Unable to connect to the database: " . $e->getMessage();
                die();
            }
        }
    }
}