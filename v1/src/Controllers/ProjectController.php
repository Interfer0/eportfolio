<?

/*
 * Created by PhpStorm.
 * User: Daniel Bigelow
 * for: CS 3620
 * Date: 12/14/2016
 */

namespace Eportfolio\Controllers;

    use Eportfolio\Models\ProjectModel;
    use Eportfolio\Models\Token;
    use Eportfolio\Http\StatusCodes;
    use Eportfolio\Models\TokenModel;
    use Eportfolio\Utilities\DatabaseConnection;
    use PDOException;

class ProjectController
{
    //Get Goals
    public function getProject($args)
    {
        return $this->getDBProject($args['CLASS']);
    }
    public function getProjectByID($args)
    {
        return $this->getDBProjectBy($args['USER'],"projectid", $args['PROJECTID']);
    }

    /*
     * Creates a new Project belonging to a class
     * Input:
     *      {
     *          "projectname":"Pipeline Project",
     *          "projectdescription":"Calculate the most cost effective method for a pipeline",
     *          "projectlink":"www.exxon.com"
     *      }
     */
    public function createProject($args)
    {
        //check if user is authorized
        if(TokenModel::getUsernameFromToken() != $args['USER'])
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }
        //post the class
        return $this->postDBProject($args);

    }

    /*
     * Patches a project
     * Input:
           {
               "projectname":"Pipeline Project",
               "projectdescription":"Calculate the most cost effective method for a pipeline",
               "projectlink":"www.exxon.com"
           }
     */
    public function editProject($args)
    {
        //check if user is authorized
        if(TokenModel::getUsernameFromToken() != $args['USER'])
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }
        //post the class
        return $this->patchDBProject($args);

    }

    /*
     * Marks a project to inactive
     */
    public function deleteProject($args)
    {
        var_dump($args);
        //check if user is authorized
        if(TokenModel::getUsernameFromToken() != $args['USER'])
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }
        return $this->deleteDBProject($args);
    }

    /*
     *  checks in input JSON
     */
    private function checkInput($input)
    {
        if (!isset($input["projectlink"]))
        {
            $input['projectlink'] = "";
        }
        if(
            !isset($input["projectname"]) ||
            !isset($input["projectdescription"]) ||
            !isset($input["projectlink"])
        )
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die("check your input JSON and try again");
        }
        return $input;
    }

    /*
     *  Gets the users ID from their username
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
     * Handles database to get all projects for a class
     */
    private function getDBProject(String $class)
    {
        try{
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }

        $stmtGet = $dbh->prepare("SELECT * FROM Project P INNER JOIN Class C on P.classid = C.classid WHERE C.classid =:CLASS AND P.active = 1");
        $stmtGet->bindValue(':CLASS', $class);
        $stmtGet->execute();
        $rtn = array();
        while($now = $stmtGet->fetch(\PDO::FETCH_ASSOC))
        {
            $rtn[] = json_encode(new ProjectModel($now));
        }
        if(count($rtn) == 0)
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die();
        }
        return $rtn;
    }

    /*
     * Gets a project by its ID
     */
    private function getDBProjectBy(String $user,String $arg1, String $arg2)
    {
        try{
            $dbh = DatabaseConnection::getInstance();
        } catch (PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die();
        }
        $stmtGet = $dbh->prepare("SELECT * FROM Project P WHERE  {$arg1} = :ARG2 AND P.active = 1");
        $stmtGet->bindValue(':ARG2', $arg2);
        $stmtGet->execute();
        $rtn = array();
        while($now = $stmtGet->fetch(\PDO::FETCH_ASSOC))
        {
            $rtn[] = json_encode(new ProjectModel($now));
        }
        if(count($rtn) == 0)
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die();
        }
        return $rtn;
    }

    /*
     * Handles posting a project for a class
     */
    private function postDBProject($args)
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
            $stmtPostClass = $dbh->prepare("INSERT INTO Project (`classid`,`projectname`,`projectdescription`, `projectlink`) VALUES (:CLASSID, :PROJECTNAME, :PROJECTDESCRIPTION, :PROJECTLINK);");
            $stmtPostClass->bindValue(':CLASSID', $args['CLASS']);
            $stmtPostClass->bindValue(':PROJECTNAME', strip_tags($input['projectname']));
            $stmtPostClass->bindValue(':PROJECTDESCRIPTION', strip_tags($input['projectdescription']));
            $stmtPostClass->bindValue(':PROJECTLINK', strip_tags($input['projectlink']));
            $stmtPostClass->execute();
            $rtnid = $dbh->lastInsertId();
        } catch(PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die("check your input JSON and try again");
        }
        $stmtGetClasses = $dbh->prepare("SELECT * FROM Project WHERE projectid =:PROJECTID");
        $stmtGetClasses->bindValue(':PROJECTID', $rtnid);
        $stmtGetClasses->execute();
        $rtn = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC);
        http_response_code(StatusCodes::CREATED);
        return new ProjectModel($rtn);
    }

    /*
     * Handles patching a Project
     */
    private function patchDBProject($args)
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

        //check if the Class owns the GOAL and that the GOAL even exists
        $stmtGetClasses = $dbh->prepare("SELECT * FROM Project WHERE projectid =:PROJECT");
        $stmtGetClasses->bindValue(':PROJECT', $args['PROJECTID']);
        $stmtGetClasses->execute();
        $rtn = array();
        $rtn = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC);
        if($rtn == false)
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }

        try{
            $stmtPatchGoal = $dbh->prepare("UPDATE Project SET projectname = :PROJECTNAME, projectdescription = :PROJECTDESCRIPTION,
                                        projectlink = :PROJECTLINK 
                                        WHERE projectid = :PROJECTID;");
            $stmtPatchGoal->bindValue(':PROJECTNAME', strip_tags($input['projectname']));
            $stmtPatchGoal->bindValue(':PROJECTDESCRIPTION', strip_tags($input['projectdescription']));
            $stmtPatchGoal->bindValue(':PROJECTLINK', strip_tags($input['projectlink']));
            $stmtPatchGoal->bindValue(':PROJECTID', $args['PROJECTID']);
            $stmtPatchGoal->execute();
        } catch(PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die("check your input JSON and try again");
        }
        $stmtGetClasses = $dbh->prepare("SELECT * FROM Project WHERE  projectid = :ARG2");
        $stmtGetClasses->bindValue(':ARG2', $args['PROJECTID']);
        $stmtGetClasses->execute();

        $rtn = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC);


        http_response_code(StatusCodes::OK);
        return new ProjectModel($rtn);
    }

    /*
     * Handles database calls to mark class as inactive
     */
    private function deleteDBProject($args)
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
        $stmtVerify = $dbh->prepare("SELECT * FROM Project WHERE projectid =:PROJECT");
        $stmtVerify->bindValue(':PROJECT', $args['PROJECTID']);
        $stmtVerify->execute();
        $rtn = array();
        $rtn = $stmtVerify->fetch(\PDO::FETCH_ASSOC);
        if($rtn == false)
        {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die();
        }
        try {
            $stmtDeleteClass = $dbh->prepare("UPDATE Project SET active = 0
                                        WHERE projectid = :PROJECTID;");
            $stmtDeleteClass->bindValue(':PROJECTID', $args['PROJECTID']);
            $stmtDeleteClass->execute();
        } catch(PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die("check your input JSON and try again");
        }
        $stmtGetClasses = $dbh->prepare("SELECT * FROM Project WHERE  projectid = :ARG2");
        $stmtGetClasses->bindValue(':ARG2', $args['PROJECTID']);
        $stmtGetClasses->execute();

        $rtn = $stmtGetClasses->fetch(\PDO::FETCH_ASSOC);

        http_response_code(StatusCodes::OK);
        return new ProjectModel($rtn);
    }
}

