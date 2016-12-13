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
echo '<a href="http://icarus.cs.weber.edu/~db88485/eportfolio/index.php" class="button gray">Classes</a>';
echo '<a href="http://icarus.cs.weber.edu/~db88485/eportfolio/Goals.php" class="button gray">Goals</a>';
if(isset($_COOKIE['AUTHENTICATION']))
{
    if($_COOKIE['AUTHENTICATION'] == 'NULL')
    {
        echo '<form style="display:inline; float:right;">Username and password displayed for testing purposes only.  ';
        echo '<input type="text" id="username" value="Daniel"/>';
        echo '<input type="text" id="password" value="Password1"/>';
        echo '<input style="display: inline;" id="loginButton" class= "button gray" type="submit" onclick="saveToken()" value="Login"/>';
        echo '</form>';
    }
    else {
        echo '<form style="display:inline; float:right;">';
        echo '<input style="display: inline;"id="loginButton" class= "button gray" type="submit" onclick="deleteToken()" value="Logout"/>';
        echo '</form>';
    }

} else {
    echo '<form style="display:inline; float:right;">Username and password displayed for testing purposes only.  ';
    echo '<input type="text" id="username" value="Daniel"/>';
    echo '<input type="text" id="password" value="Password1"/>';
    echo '<input style="display: inline;" id="loginButton" class= "button gray" type="submit" onclick="saveToken()" value="Login"/>';
    echo '</form>';
}

//echo "<input type='button' name='login' onclick='login()'>Login</input>";
echo file_get_contents("scripts.html");
echo "</div>";
echo "</div>";


