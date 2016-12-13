<?php


/*
 * Created by PhpStorm.
 * User: Daniel Bigelow
 * for: CS 3620
 * Date: 12/14/2016
 */

namespace Eportfolio\Controllers;
use Eportfolio\Models\ClassModel;
use Eportfolio\Http\StatusCodes;
use Eportfolio\Models\TokenModel;
use Eportfolio\Utilities\DatabaseConnection;
use PDOException;


class ClassController
{
    /*
     * Gets all classes handles incoming search parameter
     */
    public function getClass($args)
    {
        //if paramaters were passed in the search
        if(isset($args['BY']))
        {
            $pos = strpos($args['BY'], '=');
            if ($pos !== false)
            {
                $column = strtolower(substr($args['BY'],0, $pos));
                $row = strtolower(substr($args['BY'],$pos+1));
            }
            if($column != "semester" &&
                $column != "year"&&
                $column != "school")
            {
                http_response_code(StatusCodes::BAD_REQUEST);
                die("search not allowed!");
            }
            return $this->getDBClassBy($args['USER'],$column, strip_tags($row));
        }
        return $this->getDBClass($args['USER']);
    }

    /*
     *  Gets Classes by ID
     */
    public function getClassByID($args)
    {
        return $this->getDBClassBy($args['USER'],"classid", $args['ARG2']);
    }

    /*
     * Creates a new Class for the User
     * JSON Format:
     *      {
     *          "classname":"French",
     *          "classnumber":"FR 2013",
     *          "classdescription":"Pardon my French",
     *          "semester":"fall",
     *          "year":"2016",
     *          "grade":"F-",
     *          "school":"Weber State",
     *          "goal":"Not swear!",
     *          "outcome":"Golden"
     *      }
     */
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

    /*
     * Patch a class for a user
     * JSON Format:
           {
               "classname":"French",
               "classnumber":"FR 2013",
               "classdescription":"Pardon my French",
               "semester":"fall",
               "year":"2016",
               "grade":"F-",
               "school":"Weber State",
               "goal":"Not swear!",
               "outcome":"Golden"
           }
     */
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

    /*
     * Deletes a Class for a user. Well... it marks it as inactive rather than deleting it.
     */
    public function deleteClass($args)
    {
        //check if user is authorized
        if(TokenModel::getUsernameFromToken() != $args['USER'])
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }
        //post the class
        return $this->deleteDBClass($args);

    }

    /*
     * Check the Input Json to ensure all fields exist
     */
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
            die("check your input JSON and try again ");
        }
        return $input;
    }

    /*
     * Gets a user ID from their username
     */
    private function getUserID($args)
    {
        try{
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }

        $stmtGetClass = $dbh->prepare("SELECT * FROM  User WHERE username =:USER");
        $stmtGetClass->bindValue(':USER', $args['USER']);
        $stmtGetClass->execute();

        $rtn = $stmtGetClass->fetch(\PDO::FETCH_ASSOC);
        return $rtn['userid'];
    }

    /*
     * Database work for getting all classes
     * Paramaters: $user = username
     */
    private function getDBClass(String $user)
    {
        try{
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }

        $stmtGetClass = $dbh->prepare("SELECT * FROM Class C INNER JOIN User U on C.userid = U.userid WHERE username =:USER AND C.active = 1");
        $stmtGetClass->bindValue(':USER', $user);
        $stmtGetClass->execute();
        $rtn = array();
        while($now = $stmtGetClass->fetch(\PDO::FETCH_ASSOC))
        {
            $rtn[] = json_encode(new ClassModel($now));
        }
        if(count($rtn) == 0)
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die();
        }
        return $rtn;
    }

    /*
     * Handles database work for getting class by an argument
     * Paramaters:
     *  $user = username
     *  $arg1 = field to examine
     *  $arg2 = string to match
     *
     */
    private function getDBClassBy(String $user,String $arg1, String $arg2)
    {
        try{
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }
        $stmtGetClass = $dbh->prepare("SELECT * FROM Class C INNER JOIN User U on C.userid = U.userid WHERE username =:USER AND {$arg1} = :ARG2 AND C.active = 1");
        $stmtGetClass->bindValue(':USER', $user);
        $stmtGetClass->bindValue(':ARG2', $arg2);
        $stmtGetClass->execute();
        $rtn = array();
        while($now = $stmtGetClass->fetch(\PDO::FETCH_ASSOC))
        {
            $rtn[] = json_encode(new ClassModel($now));
        }
        if(count($rtn) == 0)
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die();
        }
        return $rtn;
    }

    /*
     * Creates a new Class in the Database return the new class
     */
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
            $rtnid = $dbh->lastInsertId();
        } catch(PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die("check your input JSON and try again");
        }

        $stmtGetPost = $dbh->prepare("SELECT * FROM Class C WHERE classid =:CLASSID");
        $stmtGetPost->bindValue(':CLASSID', $rtnid);
        $stmtGetPost->execute();
        $rtn = $stmtGetPost->fetch(\PDO::FETCH_ASSOC);
        http_response_code(StatusCodes::CREATED);
        return new ClassModel($rtn);

    }

    /*
     * Updates a database row with the new class information
     * returns newly updated row
     */
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
        $stmtGetClass = $dbh->prepare("SELECT * FROM Class C WHERE userid =:USER AND classid = :CLASSID");
        $stmtGetClass->bindValue(':USER', $user);
        $stmtGetClass->bindValue(':CLASSID', $args['ID']);
        $stmtGetClass->execute();
        $rtn = array();
        $rtn = $stmtGetClass->fetch(\PDO::FETCH_ASSOC);
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


        $stmtGetClass = $dbh->prepare("SELECT * FROM Class C WHERE  classid = :ARG2");
        $stmtGetClass->bindValue(':ARG2', $args['ID']);
        $stmtGetClass->execute();

        $rtn = $stmtGetClass->fetch(\PDO::FETCH_ASSOC);


        http_response_code(StatusCodes::OK);
        return new ClassModel($rtn);

    }

    /*
     * Marks a Class as inactive
     * returns the deleted item
     */
    private function deleteDBClass($args)
    {
        try{
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }

        $user = $this->getUserID($args);

        //check if the User owns the class and that the class even exists
        $stmtGetClass = $dbh->prepare("SELECT * FROM Class C WHERE userid =:USER AND classid = :CLASSID");
        $stmtGetClass->bindValue(':USER', $user);
        $stmtGetClass->bindValue(':CLASSID', $args['ID']);
        $stmtGetClass->execute();
        $rtn = array();
        $rtn = $stmtGetClass->fetch(\PDO::FETCH_ASSOC);
        if($rtn == false)
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }

        try {
            $stmtDeleteClass = $dbh->prepare("UPDATE Class SET active = 0
                                        WHERE classid = :CLASSID;");
            $stmtDeleteClass->bindValue(':CLASSID', $args['ID']);
            $test = $stmtDeleteClass->execute();
        } catch(PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die("check your input JSON and try again");
        }

        $stmtGetClass = $dbh->prepare("SELECT * FROM Class C WHERE  classid = :ARG2");
        $stmtGetClass->bindValue(':ARG2', $args['ID']);
        $stmtGetClass->execute();

        $rtn = $stmtGetClass->fetch(\PDO::FETCH_ASSOC);


        http_response_code(StatusCodes::OK);
        return new ClassModel($rtn);
    }
}