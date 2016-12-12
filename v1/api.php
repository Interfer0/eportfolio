<?php

/**
 * Created by PhpStorm.
 * User: Daniel Bigelow
 * Date: 11/29/2016
 * Time: 9:06 AM
 */

use Eportfolio\Http\Methods;


require_once 'config.php';
require_once 'vendor/autoload.php';

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) use ($baseURI) {

    //Eportfolio routes
    $handlePostToken = function ($args) {

        $tokenController = new \Eportfolio\Controllers\TokenController();
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
        //get paramaters to pass into method for specific gets
        $uri = $_SERVER['REQUEST_URI'];
        $pos = strpos($uri, '?');
        if ($pos !== false) {
            $uri = substr($uri, $pos+1);
            $args['BY'] = $uri;
        }
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
        //get paramaters to pass into method for specific gets
        $uri = $_SERVER['REQUEST_URI'];
        $pos = strpos($uri, '?');
        if ($pos !== false) {
            $uri = substr($uri, $pos+1);
            $args['BY'] = $uri;
        }
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

    $handleGetUser  = function($args){
        return (new Eportfolio\Controllers\UserController)->getUser($args);
    };
    $handleGetUserByName  = function($args){
        return (new Eportfolio\Controllers\UserController)->getUserByName($args);
    };
    $handlePostUser = function($args){
        return (new Eportfolio\Controllers\UserController)->postUser($args);
    };
    $handlePatchUser  = function($args){
        return (new Eportfolio\Controllers\UserController)->patchUser($args);
    };

    $r->addRoute('POST', $baseURI . '/token', $handlePostToken);

    $r->addRoute('GET', $baseURI . '/user/{USER: [\w\d]+}/class', $handleGetClass);
    $r->addRoute('GET', $baseURI . '/user/{USER: [\w\d]+}/class/{ARG2: \d+}', $handleGetClassByID);
    $r->addRoute('POST', $baseURI . '/user/{USER: [\w\d]+}/class', $handlePostClass);
    $r->addRoute('PATCH', $baseURI . '/user/{USER: [\w\d]+}/class/{ID: \d+}', $handlePatchClass);
    $r->addRoute('DELETE', $baseURI . '/user/{USER: [\w\d]+}/class/{ID: \d+}', $handleDeleteClass);


    $r->addRoute('GET', $baseURI . '/user/{USER: [\w\d]+}/goal', $handleGetGoal);
    $r->addRoute('GET', $baseURI . '/user/{USER: [\w\d]+}/goal/{ARG2: \d+}', $handleGetGoalByID);
    $r->addRoute('POST', $baseURI . '/user/{USER: [\w\d]+}/goal', $handlePostGoal);
    $r->addRoute('PATCH', $baseURI . '/user/{USER: [\w\d]+}/goal/{ID: \d+}', $handlePatchGoal);
    $r->addRoute('DELETE', $baseURI . '/user/{USER: [\w\d]+}/goal/{ID: \d+}', $handleDeleteGoal);

    $r->addRoute('GET', $baseURI . '/user/{USER: [\w\d]+}/class/{CLASS: \d+}/project', $handleGetProject);
    $r->addRoute('GET', $baseURI . '/user/{USER: [\w\d]+}/project/{PROJECTID: \d+}', $handleGetProjectByID);
    $r->addRoute('POST', $baseURI . '/user/{USER: [\w\d]+}/class/{CLASS: \d+}/project', $handlePostProject);
    $r->addRoute('PATCH', $baseURI . '/user/{USER: [\w\d]+}/project/{PROJECTID: \d+}', $handlePatchProject);
    $r->addRoute('DELETE', $baseURI . '/user/{USER: [\w\d]+}/project/{PROJECTID: \d+}', $handleDeleteProject);

    $r->addRoute('GET', $baseURI . '/user', $handleGetUser);
    $r->addRoute('GET', $baseURI . '/user/{USER: [\w\d]+}', $handleGetUserByName);
    $r->addRoute('POST', $baseURI . '/user', $handlePostUser);
    $r->addRoute('PATCH', $baseURI . '/user/{USER: [\w\d]+}', $handlePatchUser);


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