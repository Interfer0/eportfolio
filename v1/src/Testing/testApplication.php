<?php

namespace Scholarship\Testing;

use Scholarship\Controllers\ApplicationController;
use Scholarship\Models\Application;
use Scholarship\Http\Methods;
use Scholarship\Utilities\Testing;
use Scholarship\Controllers\TokensController;
use Scholarship\Http\StatusCodes;
use Scholarship\Models\Token;


class ApplicationTest extends \PHPUnit_Framework_TestCase {

    private $this;
    public function testPostApplication()
    {
        $token = $this->generateToken('generic', 'Hello357');
        $endpoint = "/scholarship/1/application";
        $body = '{
            "timeframe":"1",
            "questions":["4","7"],
            "responses":["response4","response7"]
        }';

        try {
            $output = Testing::callAPIOverHTTP($endpoint, Methods::POST, $body, $token, Testing::JSON);
        } catch (\Exception $err) {
            $this->assertEmpty($err->getMessage());
        }

        $this->assertNotFalse($output); //False on error, otherwise it's the raw results. You should be able to json_decode to read the response.
        $this->assertEquals(201, $output); // False on anything but the Correct return code from postApplication()
        $this->assertEquals(200, Testing::getLastHTTPResponseCode());
    }

    public function testPatchApplication()
    {
        $body = '{"questions": "[4]","responses": "[With brackets test]"}';
        $endpoint = "/~db88485/scholarship/v1/scholarship/2/application/23"; //endpoint with id
        $token = $token = $this->generateToken('genericfac', 'Hello896');

        try{
            $output = Testing::callAPIOverHTTP($endpoint, Methods::PATCH, $body, $token, Testing::JSON);
        } catch (\Exception $err){
            $this->assertEmpty($err->getMessage());
        }

        $this->assertNotFalse($output);
        $this->assertEquals(200, Testing::getLastHTTPResponseCode()); //checks to see if update when through.

    }

    public function testGetApplication()
    {
        //Test a faculty to get applications
        $token = $this->generateToken('genericfac', 'Hello896');
        $body = "";
        $endpoint = "/~db88485/scholarship/v1/scholarship/1/application";

        try {
            $output = Testing::callAPIOverHTTP($endpoint, Methods::GET, $body, $token, Testing::FORM);
        } catch (\Exception $err) {
            $this->assertEmpty($err->getMessage());
        }

        $this->assertNotFalse($output); //False on error, otherwise it's the raw results. You should be able to json_decode to read the response.
        $this->assertEquals(200, Testing::getLastHTTPResponseCode());
        //$this->assertJsonStringEqualsJsonString(""); //Not testing against expected JSON as this may change as database changes

    }

    public function testGetApplicationById()
    {
        //test a faculty to get an application with their ID
        $token = $this->generateToken('genericfac', 'Hello896');
        $body = "";
        $endpoint = "/~db88485/scholarship/v1/scholarship/1/application/1";

        try {
            $output = Testing::callAPIOverHTTP($endpoint, Methods::GET, $body, $token, Testing::FORM);
        } catch (\Exception $err) {
            $this->assertEmpty($err->getMessage());
        }

        $this->assertNotFalse($output); //False on error, otherwise it's the raw results. You should be able to json_decode to read the response.
        $this->assertEquals(200, Testing::getLastHTTPResponseCode());
        $this->assertJsonStringEqualsJsonString($output,
            "{\"applicationID\":\"1\",\"timeframe\":\"1\",\"wNumber\":\"w01111111\",\"scholarshipID\":\"1\",\"questions\":[],\"responses\":[]}");

        //Test a user to get their application by ID
        $token = $this->generateToken('generic', 'Hello357');
        $body = "";
        $endpoint = "/~db88485/scholarship/v1/scholarship/1/application/34";

        try {
            $output = Testing::callAPIOverHTTP($endpoint, Methods::GET, $body, $token, Testing::FORM);
        } catch (\Exception $err) {
            $this->assertEmpty($err->getMessage());
        }

        $this->assertNotFalse($output); //False on error, otherwise it's the raw results. You should be able to json_decode to read the response.
        $this->assertEquals(200, Testing::getLastHTTPResponseCode());
        $this->assertJsonStringEqualsJsonString($output,
            "{\"applicationID\":\"34\",\"timeframe\":\"1\",\"wNumber\":\"generic\",\"scholarshipID\":\"1\",\"questions\" ".
            " :[\"What is a question?\",\"Does Ian REALLY have a rich dad?.\"],\"responses\":[\"response4\",\"response7\"]}");

    }

    private function generateToken($username, $password)
    {
        $tokenController = new TokensController();
        return $tokenController->buildToken($username, $password);
    }
}