<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 12/11/2016
 * Time: 8:36 PM
 */

include_once 'myGlobals.php';
require_once 'FormWorks.php';
include_once 'scripts.html';
include_once 'MyFunctions.php';
echo "<html>";
echo "<head>";
echo "<title>Eportfolio demonstation</title>";
echo '<link href="'.$myCss.'" rel="stylesheet" type="text/css">';
echo '<link rel="shortcut icon" href="'.$myIcon.'">';
echo "</head>";

echo "<body>";
include 'Header.php'; //load header bar
echo '<div id="maincolumn">';
include 'APINav.php';


if($JSON = @file_get_contents("http://icarus.cs.weber.edu/~db88485/eportfolio/v1/user/Daniel/goal")) {
    if($JSON != FALSE) {

        $goals = json_decode($JSON);
        $goalrtn = array();
        foreach ($goals as $dec) {
            $goal = json_decode($dec);

            foreach ($goal as $key => $value) {
                $goalrtn[$key] = $value;
            }
            createGoalForm($goalrtn);

        }
    }

}

newGoal();
echo "</body>";
echo "</html>";
