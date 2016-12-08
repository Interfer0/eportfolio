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
use PDOException;


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
        if(TokenModel::getUsernameFromToken() != $args['USER'])
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }
        //post the class
        return $this->postDBClass($args);

    }
    public function editClass($args)
    {
        //check if user is authorized
        if(TokenModel::getUsernameFromToken() != $args['USER'])
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }
        //post the class
        return $this->patchDBClass($args);

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

    private function checkInput($input)
    {
        if(
            !isset($input["classname"]) ||
            !isset($input["classnumber"]) ||
            !isset($input["classdescription"]) ||
            !isset($input["semester"]) ||
            !isset($input["year"]) ||
            !isset($input["grade"]) ||
            !isset($input["school"]) ||
            !isset($input["goal"]) ||
            !isset($input["outcome"])
        )
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die("check your input JSON and try again");
        }
        return $input;
    }

    private function getUserID($args)
    {
        try{
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }

        $stmtGetClasses = $dbh->prepare("SELECT * FROM  User WHERE username =:USER");
        $stmtGetClasses->bindValue(':USER', $args['USER']);
        $stmtGetClasses->execute();

        $rtn = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC);
        return $rtn['userid'];
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

    private function postDBClass($args)
    {
        try{
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $input = $this->checkInput($input);

        try {
            $stmtPostClass = $dbh->prepare("INSERT INTO Class (`classname`,`classnumber`,`classdescription`, `semester`, `grade`, `year`,`school`, `goal`,`outcome`,`userid`,`active`) VALUES (:CLASSNAME, :CLASSNUMBER, :CLASSDESCRIPTION, :SEMESTER, :GRADE, :YEAR, :SCHOOL, :GOAL, :OUTCOME, :USERID, 1);");
            $stmtPostClass->bindValue(':CLASSNAME', strip_tags($input['classname']));
            $stmtPostClass->bindValue(':CLASSNUMBER', strip_tags($input['classnumber']));
            $stmtPostClass->bindValue(':CLASSDESCRIPTION', strip_tags($input['classdescription']));
            $stmtPostClass->bindValue(':SEMESTER', strip_tags($input['semester']));
            $stmtPostClass->bindValue(':GRADE', strip_tags($input['grade']));
            $stmtPostClass->bindValue(':YEAR', strip_tags($input['year']));
            $stmtPostClass->bindValue(':SCHOOL', strip_tags($input['school']));
            $stmtPostClass->bindValue(':GOAL', strip_tags($input['goal']));
            $stmtPostClass->bindValue(':OUTCOME', strip_tags($input['outcome']));
            $stmtPostClass->bindValue(':USERID', $this->getUserID($args));
            $stmtPostClass->execute();
        } catch(PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die("check your input JSON and try again");
        }
        http_response_code(StatusCodes::CREATED);
        return;
    }

    private function patchDBClass($args)
    {
        try{
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $input = $this->checkInput($input);

        $user = $this->getUserID($args);

        //check if the User owns the class and that the class even exists
        $stmtGetClasses = $dbh->prepare("SELECT * FROM Class C WHERE userid =:USER AND classid = :CLASSID");
        $stmtGetClasses->bindValue(':USER', $user);
        $stmtGetClasses->bindValue(':CLASSID', $args['ID']);
        $stmtGetClasses->execute();
        $rtn = array();
        $rtn = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC);
        if($rtn == false)
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }

        try{
        $stmtPatchClass = $dbh->prepare("UPDATE Class SET classname = :CLASSNAME, classnumber = :CLASSNUMBER,
                                        classdescription = :CLASSDESCRIPTION, semester = :SEMESTER,
                                        grade = :GRADE, school = :SCHOOL, year = :YEAR ,
                                        goal = :GOAL,outcome = :OUTCOME,
                                        userid = :USERID
                                        WHERE classid = :CLASSID;");
        $stmtPatchClass->bindValue(':CLASSNAME', strip_tags($input['classname']));
        $stmtPatchClass->bindValue(':CLASSNUMBER', strip_tags($input['classnumber']));
        $stmtPatchClass->bindValue(':CLASSDESCRIPTION', strip_tags($input['classdescription']));
        $stmtPatchClass->bindValue(':SEMESTER', strip_tags($input['semester']));
        $stmtPatchClass->bindValue(':YEAR', strip_tags($input['year']));
        $stmtPatchClass->bindValue(':GRADE', strip_tags($input['grade']));
        $stmtPatchClass->bindValue(':SCHOOL', strip_tags($input['school']));
        $stmtPatchClass->bindValue(':GOAL', strip_tags($input['goal']));
        $stmtPatchClass->bindValue(':OUTCOME', strip_tags($input['outcome']));
        $stmtPatchClass->bindValue(':USERID', $user);
        $stmtPatchClass->bindValue(':CLASSID', $args['ID']);
        $stmtPatchClass->execute();
        } catch(PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die("check your input JSON and try again");
        }
        http_response_code(StatusCodes::OK);
        return;
        /*
     {
    "classname":"French",
    "classnumber":"FR 2013",
    "classdescription":"Pardon my French",
    "semester":"fall",
    "year":"2016",
    "grade":"F-",
    "school":"Weber State",
    "goal":"Not swear!",
    "outcome":"FUCK"
    }
        */

    }
}