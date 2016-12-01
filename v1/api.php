<?php

/**
 * Created by PhpStorm.
 * User: Daniel Bigelow
 * Date: 11/29/2016
 * Time: 9:06 AM
 */


require_once 'config.php';
require_once 'vendor/autoload.php';

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) use ($baseURI) {

    //Eportfolio routes
    $handlePostToken = function ($args) {
        $tokenController = new \Eportfolio\Controllers\TokensController();
        //Is the data via a form?
        if (!empty($_POST['username'])) {
            $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
            $password = $_POST['password'] ?? "";
        } else {
            //Attempt to parse json input
            $json = (object) json_decode(file_get_contents('php://input'));
            if (count((array)$json) >= 2) {

                $username = filter_var($json->username, FILTER_SANITIZE_STRING);
                $password = $json->password;
            } else {
                http_response_code(\Eportfolio\Http\StatusCodes::BAD_REQUEST);
                exit();
            }
        }

        return $tokenController->buildToken($username, $password);
    };

    $handleGetClass = function($args){
        return (new Eportfolio\Controllers\ClassController)->getClass($args);
    };
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



    $handleGetGoal = function($args){
        return (new Eportfolio\Controllers\GoalController)->getGoal($args);
    };
    $handleGetGoalByID = function($args){
        return (new Eportfolio\Controllers\GoalController)->getGoalByID($args);
    };
    $handlePostGoal = function($args){
        return (new Eportfolio\Controllers\GoalController)->createGoal($args);
    };
    $handlePatchGoal = function($args){
        return (new Eportfolio\Controllers\GoalController)->editGoal($args);
    };
    $handleDeleteGoal = function($args){
        return (new Eportfolio\Controllers\GoalController)->deleteGoal($args);
    };


    $handleGetProject = function($args){
        return (new Eportfolio\Controllers\ProjectController)->getProject($args);
    };
    $handleGetProjectByID = function($args){
        return (new Eportfolio\Controllers\ProjectController)->getProjectByID($args);
    };
    $handlePostProject = function($args){
        return (new Eportfolio\Controllers\ProjectController)->createProject($args);
    };
    $handlePatchProject = function($args){
        return (new Eportfolio\Controllers\ProjectController)->editProject($args);
    };
    $handleDeleteProject = function($args){
        return (new Eportfolio\Controllers\ProjectController)->deleteProject($args);
    };

    $r->addRoute('POST', $baseURI . '/tokens', $handlePostToken);

    $r->addRoute('GET', $baseURI . '/class', $handleGetClass);
    $r->addRoute('GET', $baseURI . '/class/{ID: \d+}', $handleGetClassByID);
    $r->addRoute('POST', $baseURI . '/class/', $handlePostClass);
    $r->addRoute('PATCH', $baseURI . '/class/{ID: \d+}', $handlePatchClass);
    $r->addRoute('DELETE', $baseURI . '/class/{ID: \d+}', $handleDeleteClass);

    $r->addRoute('GET', $baseURI . '/goal', $handleGetGoal);
    $r->addRoute('GET', $baseURI . '/goal/{ID: \d+}', $handleGetGoalByID);
    $r->addRoute('POST', $baseURI . '/goal/', $handlePostGoal);
    $r->addRoute('PATCH', $baseURI . '/goal/{ID: \d+}', $handlePatchGoal);
    $r->addRoute('DELETE', $baseURI . '/goal/{ID: \d+}', $handleDeleteGoal);

    $r->addRoute('GET', $baseURI . '/project', $handleGetProject);
    $r->addRoute('GET', $baseURI . '/project/{ProjectID: \d+}', $handleGetProjectByID);
    $r->addRoute('POST', $baseURI . '/project/', $handlePostProject);
    $r->addRoute('PATCH', $baseURI . '/project/{ProjectID: \d+}', $handlePatchProject);
    $r->addRoute('DELETE', $baseURI . '/project/{ProjectID: \d+}', $handleDeleteProject);

});

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$pos = strpos($uri, '?');
if ($pos !== false) {
    $uri = substr($uri, 0, $pos);
}

$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($method, $uri);

switch($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(Eportfolio\Http\StatusCodes::NOT_FOUND);
        //Handle 404
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(Eportfolio\Http\StatusCodes::METHOD_NOT_ALLOWED);
        //Handle 403
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler  = $routeInfo[1];
        $vars = $routeInfo[2];

        $response = $handler($vars);
        echo json_encode($response);
        break;

}