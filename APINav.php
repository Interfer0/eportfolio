<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 12/12/2016
 * Time: 2:21 PM
 */


include_once '../myGlobals.php';


echo "<div style='Background-color:gainsboro;  width: 100%;'>";
echo "<div style='color:black; text-align:center;width:400px;font-size:48px; font-weight:bold;'>";
echo "</div>";

echo "<div style=' bottom:0; height: 25px;'>";
//echo '<a href="http://icarus.cs.weber.edu/~db88485/eportfolio/Users.php" class="button gray">Users</a>';
echo '<a href="http://icarus.cs.weber.edu/~db88485/eportfolio/index.php" class="button gray">Classes</a>';
//echo '<a href="http://icarus.cs.weber.edu/~db88485/eportfolio/Users.php" class="button gray">Users</a>';
echo '<a href="http://icarus.cs.weber.edu/~db88485/eportfolio/Goals.php" class="button gray">Goals</a>';
echo '<form style="display:inline; float:right;">';
echo '<input style="display: inline;"id="loginButton" class= "button gray" type="button" onclick="saveToken()" value="test"/>';
echo '</form>';

//echo "<input type='button' name='login' onclick='login()'>Login</input>";
echo file_get_contents("scripts.html");

echo "</div>";
echo "</div>";


