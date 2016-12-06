<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 12/4/2016
 * Time: 2:40 PM
 */

namespace eportfolio\Controllers;


class AuthToken
{
    private $username = "";
    private $inputHash = "";
    private $Hash = "";
    private $userID = "";

    public function __construct($username = "", $inputHash = "")
    {
        $this->username = $username;
        $this->inputHash = &inputHash;

    }

}