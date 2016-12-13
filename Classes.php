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

if($JSON = @file_get_contents("http://icarus.cs.weber.edu/~db88485/eportfolio/v1/user/Daniel/class")) {
    if($JSON != FALSE) {
        $classes = json_decode($JSON);
        $rtn = array();
        foreach ($classes as $dec) {
            $class = json_decode($dec);

            foreach ($class as $key => $value) {
                $rtn[$key] = $value;
            }
            createClassForm($rtn);
            //get classes Projects
            echo "<div style='padding-left: 100px'>";
            if($JSON = @file_get_contents("http://icarus.cs.weber.edu/~db88485/eportfolio/v1/user/Daniel/class/".$rtn['classid']."/project")) {
                if($JSON != FALSE) {


                    $projects = json_decode($JSON);
                    $projectrtn = array();
                    foreach ($projects as $dec) {
                        $project = json_decode($dec);
                        foreach ($project as $key => $value) {
                            $projectrtn[$key] = $value;
                        }
                        createProjectForm($projectrtn);

                    }
                }

            }
            newProject($rtn);
            echo "</div>";
        }
    }
}
newClass();

echo "</body>";
echo "</html>";