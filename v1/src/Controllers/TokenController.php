<?php
/*
 * Created by PhpStorm.
 * User: Daniel Bigelow
 * for: CS 3620
 * Date: 12/14/2016
 */

namespace Eportfolio\Controllers;

use Eportfolio\Models\TokenModel;
use Eportfolio\Http\StatusCodes;
use Eportfolio\Utilities\DatabaseConnection;

class TokenController
{
    /*
     * Builds a token from a username and password, either from POST or a JSON.
     * Verifys password with Database HASH for user.
     */
    public function buildToken(string $username, string $password)
    {
        try {
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e) {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }
        //lookup username to get hash bad request if username is not valid
        $stmtToken = $dbh->prepare("SELECT userhash, admin FROM User WHERE username =:USER AND active = 1");
        $stmtToken->bindValue(':USER', $username);
        $stmtToken->execute();

        if ($stmtToken->rowCount() == 0) //if there is no user
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }
        if ($stmtToken->rowCount() >= 2) //if somehow there are duplicate users
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }
        $user = $stmtToken->fetch(\PDO::FETCH_ASSOC);
        //echo password_hash($password, PASSWORD_DEFAULT);
        if (password_verify(strip_tags($password), $user['userhash']) != 1)
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }
        return (new TokenModel())->buildToken($username, $user['admin']);

    }

}
