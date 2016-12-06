<?php

/**
 * Created by PhpStorm.
 * User: Daniel Bigelow
 * Date: 11/29/2016
 * Time: 9:11 AM
 */

namespace Eportfolio\Controllers;
use Eportfolio\Models\ClassModel;
use Eportfolio\Models\Token;
use Eportfolio\Http\StatusCodes;
use Eportfolio\Utilities\DatabaseConnection;


class ClassController
{
    public function getClass($args)
    {
        return $this->getDBClass($args['USER']);
    }

    public function getClassByID($args)
    {
        return $this->getDBClassByID($args['USER']);
    }

    public function editClass($args)
    {

    }

    public function deleteClass($args)
    {

    }

    private function getDBClass(String $user)
    {
        try{
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            return StatusCodes::INTERNAL_SERVER_ERROR;
        }

        $stmtGetClasses = $dbh->prepare("SELECT * FROM Class C INNER JOIN User U on C.userid = U.userid WHERE username =:USER");
        $stmtGetClasses->bindValue(':USER', $user);
        $stmtGetClasses->execute();
        $rtn = array();
        while($now = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC))
        {
            $rtn[] = new ClassModel($now);
        }
        if(count($rtn) == 0)
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            return StatusCodes::BAD_REQUEST;
        }
        return $rtn;
    }

    /*
$handleGetClassByID = function($args){
    return (new Eportfolio\Controllers\ClassController)->getClassByID($args);
};
$handlePostClass = function($args){
    return (new Eportfolio\Controllers\ClassController)->createClass($args);
};
$handlePatchClass = function($args){
    return (new Eportfolio\Controllers\ClassController)->editClass($args);
};
$handleDeleteClass = function($args){
    return (new Eportfolio\Controllers\ClassController)->deleteClass($args);
};
    */


}