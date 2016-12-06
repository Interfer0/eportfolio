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
use Eportfolio\Models\TokenModel;
use Eportfolio\Utilities\DatabaseConnection;


class ClassController
{
    public function getClass($args)
    {
        return $this->getDBClass($args['USER']);
    }
    public function getClassByID($args)
    {
        return $this->getDBClassBy($args['USER'],"classid", $args['ARG2']);
    }
    public function getClassBySemester($args)
    {
        return $this->getDBClassBy($args['USER'],"semester", $this->adjuster("sem",$args['ARG2']));
    }
    public function getClassBySchool($args)
    {
        return $this->getDBClassBy($args['USER'],"school", $this->adjuster("sch",$args['ARG2']));
    }
    public function getClassByYear($args)
    {
        return $this->getDBClassBy($args['USER'],"year", $this->adjuster("y",$args['ARG2']));
    }
    public function createClass($args)
    {
        //check if user is authorized
        echo TokenModel::getUsernameFromToken();
        //post the class

    }
    public function editClass($args)
    {
        //check if user is authorized

        //patch the class
    }
    public function deleteClass($args)
    {

    }

    private function adjuster(String $arg1, String $arg2)
    {
        $arg2 = strtolower($arg2);
        $prefix = $arg1;
        if (substr($arg2, 0, strlen($prefix)) == $prefix) {
            $arg2 = substr($arg2, strlen($prefix));
        }
        return $arg2;
    }

    private function getDBClass(String $user)
    {
        try{
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }

        $stmtGetClasses = $dbh->prepare("SELECT * FROM Class C INNER JOIN User U on C.userid = U.userid WHERE username =:USER AND C.active = 1");
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
            die();
        }
        return $rtn;
    }
    private function getDBClassBy(String $user,String $arg1, String $arg2)
    {
        try{
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }
        $stmtGetClasses = $dbh->prepare("SELECT * FROM Class C INNER JOIN User U on C.userid = U.userid WHERE username =:USER AND {$arg1} = :ARG2 AND C.active = 1");
        $stmtGetClasses->bindValue(':USER', $user);
        $stmtGetClasses->bindValue(':ARG2', $arg2);
        $stmtGetClasses->execute();
        $rtn = array();
        while($now = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC))
        {
            $rtn[] = new ClassModel($now);
        }
        if(count($rtn) == 0)
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die();
        }
        return $rtn;
    }
}