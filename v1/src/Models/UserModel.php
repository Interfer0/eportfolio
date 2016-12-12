<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 12/11/2016
 * Time: 9:36 AM
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