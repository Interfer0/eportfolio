<?php

/**
 * Created by PhpStorm.
 * User: Jonathan
 * Date: 11/28/2016
 * Time: 5:57 PM
 */

namespace Eportfolio\Testing;

use Scholarship\Controllers\AwardController;
use Scholarship\Models\Award;
use Scholarship\Http\Methods;
use Scholarship\Utilities\Testing;
use Scholarship\Controllers\TokensController;
use Scholarship\Http\StatusCodes;
use Scholarship\Models\Token;

class testAward extends \PHPUnit_Framework_TestCase {

    public function testgetAllAwards()
    {
        $token = $this->generateToken('genericfac', 'Hello896');
        $body = "";
        $endpoint = "/scholarship/v1/award";

        try {
            $output = Testing::callAPIOverHTTP($endpoint, Methods::GET, $body, $token, Testing::FORM);
        } catch (\Exception $err) {
            $this->assertEmpty($err->getMessage());
        }

        $this->assertNotFalse($output);
        $this->assertEquals(200, Testing::getLastHTTPResponseCode());
    }

    public function testgetAwardById()
    {
    $token = $this->generateToken('genericfac', 'Hello896');
    $body = "";
    $id = 1;
    $endpoint = "/scholarship/v1/award/" . $id;

    try {
        $output = Testing::callAPIOverHTTP($endpoint, Methods::GET, $body, $token, Testing::FORM);
    } catch(\Exception $err) {
        $this->assertEmpty($err->getMessage());
    }

    $this->assertNotFalse($output);
    $this->assertEquals(200, Testing::getLastHTTPResponseCode());
    }

    public function testmodifyAward()
    {
        $token = $this->generateToken('genericfac', 'Hello896');
        $body = '"awardId" : "1",
                  "amount"  : "3000",
                  "donor" : "Jim",
                  "description" : "For Cool People"';
        $endpoint = "/scholarship/v1/award/create";

        try {
            $output = Testing::callAPIOverHTTP($endpoint, Methods::PUT, $body, $token, Testing::FORM);
        } catch(\Exception $err) {
            $this->assertEmpty($err->getMessage());
        }

        $this->assertNotFalse($output);
        $this->assertEquals(200, Testing::getLastHTTPResponseCode());
    }

    public function testdeleteAward()
    {
        $token = $this->generateToken('genericfac', 'Hello896');
        $body = '"awardId" : "1"';
        $endpoint = "/scholarship/v1/award/delete";

        try {
            $output = Testing::callAPIOverHTTP($endpoint, Methods::DELETE, $body, $token, Testing::FORM);
        } catch(\Exception $err) {
            $this->assertEmpty($err->getMessage());
        }

        $this->assertNotFalse($output);
        $this->assertEquals(200, Testing::getLastHTTPResponseCode());
    }

    private function generateToken($username, $password)
    {
        $tokenController = new TokensController();
        return $tokenController-> buildToken($username, $password);
    }

}