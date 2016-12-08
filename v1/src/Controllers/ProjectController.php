<?

    /**
     * Created by PhpStorm.
     * User: Daniel Bigelow
     * Date: 11/29/2016
     * Time: 9:12 AM
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
            $rtn[] = new ProjectModel($now);
        }
        if(count($rtn) == 0)
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die();
        }
        return $rtn;
    }

    //Get Specific by
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
            $rtn[] = new ProjectModel($now);
        }
        if(count($rtn) == 0)
        {
            http_response_code(StatusCodes::BAD_REQUEST);
            die();
        }
        return $rtn;
    }

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
        } catch(PDOException $e)
        {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die("check your input JSON and try again");
        }
        http_response_code(StatusCodes::CREATED);
        return;
    }

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

