<?php
/*
 * Created by PhpStorm.
 * User: Daniel Bigelow
 * for: CS 3620
 * Date: 12/14/2016
 */

namespace Eportfolio\Models;


class UserModel implements \JSONSerializable
{
    private $userid;
    private $username;
    private $active;
    private $admin;

    public function __construct($args)
    {
        $this->userid = $args['userid'];
        $this->username = $args['username'];
        $this->active = $args['active'];
        $this->admin = $args['admin'];
    }

    function jsonSerialize()
    {
        $rtn = array('userid' => $this->userid,
            'username' => $this->username,
            'active' => $this->active,
            'admin'=> $this->admin

        );
        return $rtn;
    }
}