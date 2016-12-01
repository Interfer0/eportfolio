<?php

/**
 * Created by PhpStorm.
 * User: Daniel Bigelow
 * Date: 11/29/2016
 * Time: 9:12 AM
 */

namespace Eportfolio\Controllers;

use Eportfolio\Models\TokenModel;
use Eportfolio\Http\StatusCodes;

class TokensController
{
    public function buildToken(string $username, string $password)
    {
        $serverConfig = array('host' => '137.190.19.17',
            'FQDN' => 'cs.weber.edu',
            'accountDomainNameShort' => 'apollo',
            'accountCanonicalForm' => '4',
            'baseDn' => 'dc=cs,dc=weber,dc=edu');
        $ldapConnectionString = "ldap://" . $serverConfig["host"];
        $ad = ldap_connect($ldapConnectionString);
        if (is_null($ad)) {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die("Unable to connect to LDAP server.");
        }

        ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);

        $bind = @ldap_bind($ad, "{$username}@{$serverConfig["FQDN"]}", $password);

        if (!$bind) {
            http_response_code(StatusCodes::UNAUTHORIZED);
            die("Your credentials were rejected by the server.");
        }

        //Check group membership
        $userdn = strtolower($this->getDN($ad, $username, $serverConfig['baseDn']));

        if ($userdn == '') {
            http_response_code(StatusCodes::INTERNAL_SERVER_ERROR);
            die("Failure to query LDAP");
        }

        $role = '';


        if (strpos($userdn, "ou=students")) {
            $role = Token::ROLE_STUDENT;
        }

        if (strpos($userdn, "ou=faculty")) {
            $role = Token::ROLE_FACULTY;
        }

        if ($role == '') {
            http_response_code(StatusCodes::FORBIDDEN);
            exit("Not authorized");
        }

        return (new Token())->buildToken($role, $username);
    }

    /**
     * This function searchs in LDAP tree entry specified by samaccountname and
     * returns its DN or epmty string on failure.
     *
     * @param resource $ad
     *          An LDAP link identifier, returned by ldap_connect().
     * @param string $samaccountname
     *          The sAMAccountName, logon name.
     * @param string $basedn
     *          The base DN for the directory.
     * @return string
     */
    private function getDN($ad, $samaccountname, $basedn)
    {
        $result = ldap_search($ad, $basedn, "(samaccountname={$samaccountname})", array(
            'dn'
        ));
        if (!$result) {
            return '';
        }

        $entries = ldap_get_entries($ad, $result);
        if ($entries['count'] > 0) {
            return $entries[0]['dn'];
        }

        return '';
    }
}