<?php
/**
 * Created by PhpStorm.
 * User: Daniel Bigelow
 * Date: 9/26/2016
 * Time: 7:16 PM
@lang text
 */
//block at top
include_once '../myGlobals.php';

echo "<div style='Background-color:gainsboro;  width: 100%;'>";
    echo "<div style='color:black; text-align:center; margin-left:25%; margin-right: auto; width:400px; font-size:48px; font-weight:bold;'>";
        echo $pageName;
    echo "</div>";

    echo "<div style=' bottom:0; height: 25px;'>";
        echo '<a href="http://www.DanielBigelow.com" class="button orange">www.DanielBigelow.com</a>';
        echo '<a href="http://icarus.cs.weber.edu/~db88485/MadLib" class="button orange">Homework 1</a>';
        echo '<a href="http://icarus.cs.weber.edu/~db88485/FormGenerator" class="button orange">Homework 2</a>';
        echo '<a href="http://icarus.cs.weber.edu/~db88485/XYZSatisfaction" class="button orange">Homework 3</a>';
        echo '<a href="http://icarus.cs.weber.edu/~db88485/eportfolio/index.php" class="button orange">Final</a>';
    echo "</div>";
echo "</div>";
