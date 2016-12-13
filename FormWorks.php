<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 12/12/2016
 */


function createClassForm($data)
{
    include_once 'scripts.html';
    echo "
    <br>
    <form>
    Class ID = ".$data['classid']." <br>
    Class Name = <input type='textfield' id='".$data['classid']."classname' value='".$data['classname']."'/><br>
    Class Number = <input type='textfield' id='".$data['classid']."classnumber' value='".$data['classnumber']."'/><br>
    Class Description = <input type='textfield' id='".$data['classid']."classdescription' size='128' value='".$data['classdescription']."'/><br>
    Semester = <input type='textfield' id='".$data['classid']."semester' value='".$data['semester']."'/><br>
    Grade = <input type='textfield' id='".$data['classid']."grade' value='".$data['grade']."'/><br>
    School = <input type='textfield' id='".$data['classid']."school' value='".$data['school']."'/><br>
    Year = <input type='textfield' id='".$data['classid']."year' value='".$data['year']."'/><br>
    Goal = <input type='textfield' id='".$data['classid']."goal' size='128' value='".$data['goal']."'/><br>
    Outcome = <input type='textfield' id='".$data['classid']."outcome' size='128' value='".$data['outcome']."'/><br>
    <input type='submit' value='Update' class= \"button gray\" onclick='saveClass(".json_encode($data).")'/>
    <input type='submit' value='Delete' class= \"button gray\" onclick='deleteClass(".json_encode($data).")'/>
    </form>
    <hr>
    ";
}

function createProjectForm($data)
{
    include_once 'scripts.html';
    echo "
    <br>
    <form>
    Project ID = ".$data['projectid']."<br>
    Project Name = <input type='textfield' id='".$data['projectid']."projectname' value='".$data['projectname']."'/><br>
    Project Description = <input type='textfield' id='".$data['projectid']."projectdescription'  size='128' value='".$data['projectdescription']."'/><br>
    Project Link = <input type='textfield' id='".$data['projectid']."projectlink' value='".$data['projectlink']."'/><br>
    <input type='submit' value='Update' class= \"button gray\" onclick='saveProject(".json_encode($data).")'/>
    <input type='submit' value='Delete' class= \"button gray\" onclick='deleteProject(".json_encode($data).")'/>
    </form>
    <hr>
    ";
}

function createGoalForm($data)
{
    include_once 'scripts.html';
    echo "
    <br>
    <form>
    Project ID = ".$data['goalid']."<br>
    Type of Goal = <input type='textfield' id='".$data['goalid']."goalid' value='".$data['longterm']."'/> *1 for Longterm or 0 for Shorterm.<br>
    Goal Name = <input type='textfield' id='".$data['goalid']."goalname'  size='128' value='".$data['goalname']."'/><br>
    Goal Description = <input type='textfield' id='".$data['goalid']."goaldescription' value='".$data['goaldescription']."'/><br>
    Target Date = <input type='textfield' id='".$data['goalid']."goaldescription' value='".$data['targetdate']."'/><br>
    Complete Date = <input type='textfield' id='".$data['goalid']."goaldescription' value='".$data['completedate']."'/><br>
    
    <input type='submit' value='Update' class= \"button gray\" onclick='saveGoal(".json_encode($data).")'/>
    <input type='submit' value='Delete' class= \"button gray\" onclick='deleteGoal(".json_encode($data).")'/>
    </form>
    <hr>
    ";
}


function newClass()
{
    echo "
    <br>
    <form>
    <input type='submit'  value='New Class' class= \"button gray\" onclick='newClass()'/>
    </form>
    <hr>
    ";
}

function newProject($data)
{
    echo "
    <br>
    <form>
    <input type='submit'  value='New Project' class= \"button gray\" onclick='newProject(".json_encode($data).")'/>
    </form>
    <hr>
    ";
}

function newGoal()
{
    echo "
    <br>
    <form>
    <input type='submit'  value='New Project' class= \"button gray\" onclick='newGoal()'/>
    </form>
    <hr>
    ";
}
