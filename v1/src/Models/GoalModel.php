<?php

/**
 * Created by PhpStorm.
 * User: Daniel Bigelow
 * Date: 11/29/2016
 * Time: 9:41 AM
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