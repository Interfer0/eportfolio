<?php

/*
 * Created by PhpStorm.
 * User: Daniel Bigelow
 * for: CS 3620
 * Date: 12/14/2016
 */

namespace Eportfolio\Models;

class GoalModel implements \JSONSerializable
{
    private $goalid;
    private $longterm;
    private $goalname;
    private $goaldescription;
    private $targetdate;
    private $completedate;



    public function __construct($args)
    {
        $this->goalid = $args['goalid'];
        $this->longterm = $args['longterm'];
        $this->goalname = $args['goalname'];
        $this->goaldescription = $args['goaldescription'];
        $this->targetdate = $args['targetdate'];
        $this->completedate = $args['completedate'];

    }

    function jsonSerialize()
    {
        $rtn = array('goalid' => $this->goalid,
            'longterm' => $this->longterm,
            'goalname' => $this->goalname,
            'goaldescription' => $this->goaldescription,
            'targetdate' => $this->targetdate,
            'completedate' => $this->completedate
        );
        return $rtn;
    }
}