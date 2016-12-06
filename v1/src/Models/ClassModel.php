<?php

/**
 * Created by PhpStorm.
 * User: Daniel Bigelow
 * Date: 11/29/2016
 * Time: 9:39 AM
 */
namespace Eportfolio\Models;

class ClassModel implements \JSONSerializable
{
    private $classid;
    private $classname;
    private $classnumber;
    private $classdescription;
    private $semester;
    private $year;
    private $grade;
    private $school;
    private $goal;
    private $outcome;

    /**
     * ClassModel constructor.
     * @param $classid
     * @param $classname
     * @param $classnumber
     * @param $classdescription
     * @param $semester
     * @param $year
     * @param $grade
     * @param $school
     * @param $goal
     * @param $outcome
     */
    public function __construct($args)
    {
        $this->classid = $args['classid'];
        $this->classname = $args['classname'];
        $this->classnumber = $args['classnumber'];
        $this->classdescription = $args['classdescription'];
        $this->semester = $args['semester'];
        $this->year = $args['year'];
        $this->grade = $args['grade'];
        $this->school = $args['school'];
        $this->goal = $args['goal'];
        $this->outcome = $args['outcome'];
    }


    function jsonSerialize()
    {
        $rtn = array('classid' => $this->classid,
            'classname' => $this->classname,
            'classnumber' => $this->classnumber,
            'classdescription' => $this->classdescription,
            'semester' => $this->semester,
            'year' => $this->year,
            'grade' => $this->grade,
            'school' => $this->school,
            'goal' => $this->goal,
            'outcome' => $this->outcome
        );
        return $rtn;
    }
}