<?php

/**
 * Created by PhpStorm.
 * User: Daniel Bigelow
 * Date: 11/29/2016
 * Time: 9:12 AM
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
    //Get Goals
    public function getGoal($args)
    {
        return $this->getDBGoal($args['USER']);
    }
    public function getGoalByID($args)
    {
        return $this->getDBGoalBy($args['USER'],"goalid", $args['ARG2']);
    }
    public function getGoalByTerm($args)
    {
        return $this->getDBGoalBy($args['USER'],"longterm", $this->adjuster("t",$args['ARG2']));
    }

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
            $rtn[] = new GoalModel($now);
        }
        if(count($rtn) == 0)
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die();
        }
        return $rtn;
    }

    //Get Specific by
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
            $rtn[] = new GoalModel($now);
        }
        if(count($rtn) == 0)
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die();
        }
        return $rtn;
    }

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
            $stmtPostClass->bindValue(':TARGETDATE', strip_tags(['targetdate']));
            $stmtPostClass->bindValue(':COMPLETEDATE', strip_tags($input['completedate']));
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
        http_response_code(StatusCodes::OK);
        return;
        /*
{

    "longterm":"0",
    "goalname":"Take Over World2",
    "goaldescription":"The same thing we do every night",
    "targetdate":"fall 2017",
    "completedate":"summer 2019"

}
        */

    }


    //delete a goal
}