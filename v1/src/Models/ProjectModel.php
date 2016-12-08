<?php

/**
 * Created by PhpStorm.
 * User: Daniel Bigelow
 * Date: 11/29/2016
 * Time: 9:41 AM
 */

namespace Eportfolio\Models;

class ProjectModel implements \JSONSerializable
{
    private $projectid;
    private $classid;
    private $projectname;
    private $projectdescription;
    private $projectlink;




    public function __construct($args)
    {
        $this->projectid = $args['projectid'];
        $this->classid = $args['classid'];
        $this->projectname = $args['projectname'];
        $this->projectdescription = $args['projectdescription'];
        $this->projectlink = $args['projectlink'];


    }

    function jsonSerialize()
    {
        $rtn = array('projectid' => $this->projectid,
            'classid' => $this->classid,
            'projectname' => $this->projectname,
            'projectdescription' => $this->projectdescription,
            'projectlink' => $this->projectlink
        );
        return $rtn;
    }
}