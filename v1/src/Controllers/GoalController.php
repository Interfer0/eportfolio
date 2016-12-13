<?php

/*
 * Created by PhpStorm.
 * User: Daniel Bigelow
 * for: CS 3620
 * Date: 12/14/2016
 */

namespace Eportfolio\Controllers;

use Eportfolio\Models\GoalModel;
use Eportfolio\Models\Token;
use Eportfolio\Http\StatusCodes;
use Eportfolio\Models\TokenModel;
use Eportfolio\Utilities\DatabaseConnection;
use PDOException;

class GoalController
{
    /*
     *  Gets all Goals/ A;sp allows for search by longterm.
     *  0 if short, 1 for long term goals
     */
    public function getGoal($args)
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
            if($column != "longterm") //longterm is 0 for short term goal, 1 for long term goal
            {
                http_response_code(StatusCodes::BAD_REQUEST);
                die("search not allowed!");
            }
            return $this->getDBGoalBy($args['USER'],$column, strip_tags($row));
        }
        return $this->getDBGoal($args['USER']);
    }

    /*
     * Gets Goal by ID
     */
    public function getGoalByID($args)
    {
        return $this->getDBGoalBy($args['USER'],"goalid", $args['ARG2']);
    }

    /*
     * Creates a new Goal
     *
     * Input JSON:
     * {
     *      "longterm":"0",
     *      "goalname":"Take Over World2",
     *      "goaldescription":"The same thing we do every night",
     *      "targetdate":"fall 2017",
     *      "completedate":"summer 2019"
     *  }
     */
    public function createGoal($args)
    {
        //check if user is authorized
        if(TokenModel::getUsernameFromToken() != $args['USER'])
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }
        //post the class
        return $this->postDBGoal($args);

    }

    /*
     * Edits a Goal
     *
     * Input JSON:
      {
           "longterm":"0",
           "goalname":"Take Over World2",
           "goaldescription":"The same thing we do every night",
           "targetdate":"fall 2017",
           "completedate":"summer 2019"
       }
     */
    public function editGoal($args)
    {
        //check if user is authorized
        if(TokenModel::getUsernameFromToken() != $args['USER'])
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }
        //post the class
        return $this->patchDBGoal($args);

    }

    /*
     * Marks a goal to be deleted
     */
    public function deleteGoal($args)
    {

        //check if user is authorized
        if(TokenModel::getUsernameFromToken() != $args['USER'])
        {

            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }
        //post the class
        return $this->deleteDBGoal($args);

    }

    /*
     * Checks the input Json to see if its all there
     */
    private function checkInput($input)
    {
        if(!isset($input["completedate"]))
        {
            $input['completedate'] = "";
        }
        if(
            !isset($input["longterm"]) ||
            !isset($input["goalname"]) ||
            !isset($input["goaldescription"]) ||
            !isset($input["targetdate"])
        )
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die("check your input JSON and try again");
        }
        if(strcmp($input['longterm'],'0') != 0 && strcmp($input['longterm'],'1') != 0 )
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die("longterm must be 0 for short term or 1 for long term");
        }
        return $input;
    }

    /*
     *  gets a users ID from a username
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

        $stmtGetClasses = $dbh->prepare("SELECT * FROM  User WHERE username =:USER");
        $stmtGetClasses->bindValue(':USER', $args['USER']);
        $stmtGetClasses->execute();

        $rtn = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC);
        return $rtn['userid'];
    }

    /*
     * Handles database work for getting all Goals
     */
    private function getDBGoal(String $user)
    {
        try{
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }

        $stmtGet = $dbh->prepare("SELECT * FROM Goal G INNER JOIN User U on G.userid = U.userid WHERE username =:USER AND G.active = 1");
        $stmtGet->bindValue(':USER', $user);
        $stmtGet->execute();
        $rtn = array();
        while($now = $stmtGet->fetch(\PDO::FETCH_ASSOC))
        {
            $rtn[] = json_encode(new GoalModel($now));
        }
        if(count($rtn) == 0)
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die();
        }
        return $rtn;
    }

    /*
     * Get a goal by a specific field
     */
    private function getDBGoalBy(String $user,String $arg1, String $arg2)
    {
        try{
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }
        $stmtGet = $dbh->prepare("SELECT * FROM Goal G INNER JOIN User U on G.userid = U.userid WHERE username =:USER AND {$arg1} = :ARG2 AND G.active = 1");
        $stmtGet->bindValue(':USER', $user);
        $stmtGet->bindValue(':ARG2', $arg2);
        $stmtGet->execute();
        $rtn = array();
        while($now = $stmtGet->fetch(\PDO::FETCH_ASSOC))
        {
            $rtn[] = json_encode(new GoalModel($now));
        }
        if(count($rtn) == 0)
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die();
        }
        return $rtn;
    }

    /*
     * Handles Database to Post a new Goal
     */
    private function postDBGoal($args)
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
            $stmtPostClass = $dbh->prepare("INSERT INTO Goal (`longterm`,`goalname`,`goaldescription`, `targetdate`, `completedate`, `userid`) VALUES (:LONGTERM, :GOALNAME, :GOALDESCRIPTION, :TARGETDATE, :COMPLETEDATE, :USERID);");
            $stmtPostClass->bindValue(':LONGTERM', strip_tags($input['longterm']));
            $stmtPostClass->bindValue(':GOALNAME', strip_tags($input['goalname']));
            $stmtPostClass->bindValue(':GOALDESCRIPTION', strip_tags($input['goaldescription']));
            $stmtPostClass->bindValue(':TARGETDATE', strip_tags($input['targetdate']));
            $stmtPostClass->bindValue(':COMPLETEDATE', strip_tags($input['completedate']));
            $stmtPostClass->bindValue(':USERID', $this->getUserID($args));
            $stmtPostClass->execute();
            $rtnid = $dbh->lastInsertId();
        } catch(PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die("check your input JSON and try again");
        }
        $stmtGetClasses = $dbh->prepare("SELECT * FROM Goal WHERE goalid =:GOALID");
        $stmtGetClasses->bindValue(':GOALID', $rtnid);
        $stmtGetClasses->execute();
        $rtn = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC);
        http_response_code(StatusCodes::CREATED);
        return new GoalModel($rtn);
    }

    /*
     * Handles a database to patch a goal
     */
    private function patchDBGoal($args)
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

        //check if the User owns the GOAL and that the GOAL even exists
        $stmtGetClasses = $dbh->prepare("SELECT * FROM Goal G WHERE userid =:USER AND goalid = :GOALID");
        $stmtGetClasses->bindValue(':USER', $user);
        $stmtGetClasses->bindValue(':GOALID', $args['ID']);
        $stmtGetClasses->execute();
        $rtn = array();
        $rtn = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC);
        if($rtn == false)
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }

        try{
            $stmtPatchGoal = $dbh->prepare("UPDATE Goal SET longterm = :LONGTERM, goalname = :GOALNAME,
                                        goaldescription = :GOALDESCRIPTION, targetdate = :TARGETDATE,
                                        completedate = :COMPLETEDATE
                                        WHERE goalid = :GOALID;");
            $stmtPatchGoal->bindValue(':LONGTERM', strip_tags($input['longterm']));
            $stmtPatchGoal->bindValue(':GOALNAME', strip_tags($input['goalname']));
            $stmtPatchGoal->bindValue(':GOALDESCRIPTION', strip_tags($input['goaldescription']));
            $stmtPatchGoal->bindValue(':TARGETDATE', strip_tags($input['targetdate']));
            $stmtPatchGoal->bindValue(':COMPLETEDATE', strip_tags($input['completedate']));
            $stmtPatchGoal->bindValue(':GOALID', strip_tags($args['ID']));
            $stmtPatchGoal->execute();
        } catch(PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die("check your input JSON and try again");
        }
        $stmtGetClasses = $dbh->prepare("SELECT * FROM Goal WHERE  goalid = :ARG2");
        $stmtGetClasses->bindValue(':ARG2', $args['ID']);
        $stmtGetClasses->execute();

        $rtn = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC);


        http_response_code(StatusCodes::OK);
        return new GoalModel($rtn);
    }

    /*
     * handles database to mark a goal as inactive
     */
    private function deleteDBGoal($args)
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
        $stmtGetClasses = $dbh->prepare("SELECT * FROM Goal WHERE userid =:USER AND goalid = :GOALID");
        $stmtGetClasses->bindValue(':USER', $user);
        $stmtGetClasses->bindValue(':GOALID', $args['ID']);
        $stmtGetClasses->execute();
        $rtn = array();
        $rtn = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC);
        if($rtn == false)
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }

        try {
            $stmtDeleteClass = $dbh->prepare("UPDATE Goal SET active = 0
                                        WHERE goalid = :GOALID;");
            $stmtDeleteClass->bindValue(':GOALID', $args['ID']);
            $stmtDeleteClass->execute();
        } catch(PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die("check your input JSON and try again");
        }
        $stmtGetClasses = $dbh->prepare("SELECT * FROM Goal WHERE  goalid = :ARG2");
        $stmtGetClasses->bindValue(':ARG2', $args['ID']);
        $stmtGetClasses->execute();

        $rtn = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC);


        http_response_code(StatusCodes::OK);
        return new GoalModel($rtn);
    }
}