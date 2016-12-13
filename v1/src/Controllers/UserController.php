<?php
/*
 * Created by PhpStorm.
 * User: Daniel Bigelow
 * for: CS 3620
 * Date: 12/14/2016
 */

namespace Eportfolio\Controllers;

use Eportfolio\Models\UserModel;
use Eportfolio\Models\Token;
use Eportfolio\Http\StatusCodes;
use Eportfolio\Models\TokenModel;
use Eportfolio\Utilities\DatabaseConnection;
use PDOException;

class UserController
{
    /*
     * Allows an admin to get a list of users
     */
    public function getUser($args)
    {
        if(TokenModel::getAdminFromToken() !== '1')
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die("Unauthorized user");
        }
        return $this->getDBUser($args);
    }

    /*
     * allows an admin to get a user by their name
     */
    public function getUserByName($args)
    {
        if(TokenModel::getAdminFromToken() !== '1')
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die("Unauthorized user");
        }
        return $this->getDBUserByName($args);
    }

    /*
     * Posts a new user. If not admins exist, user is created as an admin.
     * Input JSON:
     *   {
     *       "username": "Labron",
     *       "password": "Wilson"
     *   }
     */
    public function postUser($args)   //we only want admins to create accounts, this is a design feature. However the first account created should be allowed.
    {
        try{
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }
        $stmtGet = $dbh->prepare("SELECT * FROM User WHERE admin = 1 AND active = 1");
        $stmtGet->execute();
        $rtn = $stmtGet->fetch(\PDO::FETCH_ASSOC);
        if(count($rtn) == 0)
        {
            return $this->postDBUser($args,1);  //create the user as an admin if one does not exist
        } else {

            if (TokenModel::getAdminFromToken() !== '1') {
                http_response_code(StatusCodes::UNAUTHORIZED);
                die("Unauthorized Administrator 1");
            }
            return $this->postDBUser($args);
        }

    }

    /*
     * Allows admin and users to change passwords and allows admin to set accounts to admin
     * Input JSON:
     *   {
     *       "username": "Labron",
     *       "password": "Wilson",
     *       "admin":"0"
     *   }
     *               OR
     *   {
     *       "username": "Labron",
     *       "password": "Wilson"
     *   }
     */
    public function patchUser($args)
    {
        return $this->patchDBUser($args);
    }

    /*
     * Checks a JSON for username and password
     */
    private function checkInput($input)
    {
        if(
            !isset($input["username"]) ||
            !isset($input["password"])
        )
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die("check your input JSON and try again");
        }
        return $input;
    }

    /*
     * Handles database to get all users
     */
    private function getDBUser($args)
    {
        try{
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }

        $stmtGetClasses = $dbh->prepare("SELECT * FROM User");
        $stmtGetClasses->execute();
        $rtn = array();
        while($now = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC))
        {
            $rtn[] = json_encode(new UserModel($now));
        }
        if(count($rtn) == 0)
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die();
        }
        return $rtn;
    }

    /*
     * Handles database to get user by name
     */
    private function getDBUserByName($args)
    {
        try{
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }

        $stmtGetClasses = $dbh->prepare("SELECT * FROM User WHERE username = :USERNAME");
        $stmtGetClasses->bindValue(':USERNAME',$args['USER']);
        $stmtGetClasses->execute();
        $rtn = array();
        while($now = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC))
        {
            $rtn[] = json_encode(new UserModel($now));
        }
        if(count($rtn) == 0)
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die();
        }
        return $rtn;
    }

    /*
     * Handles database to post a new user. Only admin can do this.
     */
    private function postDBUser($args, Int $admin = 0)
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

        //check if username exists
        $stmtCheckUser = $dbh->prepare("SELECT * FROM  User WHERE username =:USER");
        $stmtCheckUser->bindValue(':USER', strip_tags($input['username']));
        $stmtCheckUser->execute();
        $rtn = $stmtCheckUser->fetch(\PDO::FETCH_ASSOC);
        if($rtn != false)
        {
            http_response_code(StatusCodes::CONFLICT);
            die("username already exists");
        }

        //hash the password
        $password = password_hash(strip_tags($input['password']), PASSWORD_DEFAULT);

        try {
            $stmtPostClass = $dbh->prepare("INSERT INTO User (`username`,`userhash`,`admin`) VALUES (:USERNAME, :USERHASH, :ADMIN);");
            $stmtPostClass->bindValue(':USERNAME', strip_tags($input['username']));
            $stmtPostClass->bindValue(':USERHASH', $password);
            $stmtPostClass->bindValue(':ADMIN', $admin);
            $stmtPostClass->execute();
            $rtnid = $dbh->lastInsertId();
        } catch(PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die("check your input JSON and try again");
        }
        $stmtCheckUser = $dbh->prepare("SELECT * FROM  User WHERE userid =:USERID");
        $stmtCheckUser->bindValue(':USERID', $rtnid);
        $stmtCheckUser->execute();
        $rtn = $stmtCheckUser->fetch(\PDO::FETCH_ASSOC);
        http_response_code(StatusCodes::CREATED);
        return new userModel($rtn);
    }

    /*
     * Handles patching a user. Uses old admin level if new one is not specified. Only lets admin set admin level
     */
    private  function patchDBUser($args)
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

        //get old password and verify with old password to make sure it is the user or an admin changing password
        $stmtGetClasses = $dbh->prepare("SELECT * FROM User WHERE username =:USER");
        $stmtGetClasses->bindValue(':USER', $args['USER']);
        $stmtGetClasses->execute();
        $rtn = array();
        $rtn = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC);
        //if the user doesn't exist
        if($rtn == false)
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }
        //if not the same user or an admin
        if(strcasecmp($rtn['username'],TokenModel::getUsernameFromToken()) != '0' &&
            TokenModel::getAdminFromToken() != '1')
        {
           http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }
        //if username is in token then override, otherwise get the one from the account from return of last call

            if (!isSet($input['admin'])) {
                $input['admin'] = $rtn['admin'];

            } else    // ensure admin in JSON is either one or zero
            {
                if(TokenModel::getAdminFromToken() == '1') { //allow
                    if ($input['admin'] != '0' && $input['admin'] != '1')
                    {
                        http_response_code(StatusCodes::BAD_REQUEST);
                        die("check your input JSON and try again");
                    }
                }
            }
        $password = password_hash(strip_tags($input['password']), PASSWORD_DEFAULT);
        try{
            $stmtPatchGoal = $dbh->prepare("UPDATE User SET userhash = :USERHASH, admin = :ADMIN
                                        WHERE username = :USERNAME;");
            $stmtPatchGoal->bindValue(':USERHASH', $password);
            $stmtPatchGoal->bindValue(':ADMIN', strip_tags($input['admin']));
            $stmtPatchGoal->bindValue(':USERNAME', $args['USER']);
            $stmtPatchGoal->execute();
        } catch(PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die("check your input JSON and try again");
        }
        $stmtGetClasses = $dbh->prepare("SELECT * FROM User WHERE  username = :USERNAME");
        $stmtGetClasses->bindValue(':USERNAME', $args['USER']);
        $stmtGetClasses->execute();

        $rtn = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC);


        http_response_code(StatusCodes::OK);
        return new UserModel($rtn);
    }
}