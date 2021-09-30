<?php

namespace App\Packages\Integrations\DirectAdminCommand;

trait AccountCommands
{
    public function createAdminAccount($username, $email, $password, $notify = false)
    {
        return $this->parse($this->guzzle->post('/CMD_API_ACCOUNT_ADMIN', ['form_params' => [
            'action' => 'create',
            'username' => $username,
            'email' => $email,
            'passwd' => $password,
            'passwd2' => $password,
            'notify' => ($notify ? 'yes' : 'no')
        ]]));
    }

    public function createResellerAccount($username, $email, $password, $domain, $package, $ip = 'shared', $notify = false)
    {
        return $this->parse($this->guzzle->post('/CMD_API_ACCOUNT_RESELLER', ['form_params' => [
            'action' => 'create',
            'add' => 'Submit',
            'username' => $username,
            'email' => $email,
            'passwd' => $password,
            'passwd2' => $password,
            'domain' => $domain,
            'package' => $package,
            'ip' => $ip,
            'notify' => ($notify ? 'yes' : 'no')
        ]]));
    }

    public function createUserAccount($username, $email, $password, $domain, $package, $ip, $notify = false)
    {
        return $this->parse($this->guzzle->post('/CMD_API_ACCOUNT_USER', ['form_params' => [
            'action' => 'create',
            'add' => 'Submit',
            'username' => $username,
            'email' => $email,
            'passwd' => $password,
            'passwd2' => $password,
            'domain' => $domain,
            'package' => $package,
            'ip' => $ip,
            'notify' => ($notify ? 'yes' : 'no')
        ]]));
    }

    public function deleteAccount($users = [])
    {
        if (!is_array($users)) {
            $users = [$users];
        }

        $users = array_flip(array_map(function ($key) {
            return "select{$key}";
        }, array_flip($users)));

        return $this->parse($this->guzzle->post('/CMD_API_SELECT_USERS', ['form_params' => [
            'confirmed' => 'Confirm',
            'delete' => 'yes'
        ] + $users]));
    }

    public function suspendAccount($users = [])
    {
        if (!is_array($users)) {
            $users = [$users];
        }

        $users = array_flip(array_map(function ($key) {
            return "select{$key}";
        }, array_flip($users)));

        return $this->parse($this->guzzle->post('/CMD_API_SELECT_USERS', ['form_params' => [
            'location' => 'CMD_RESELLER_SHOW',
            'suspend' => 'Suspend'
        ] + $users]));
    }

    public function unsuspendAccount($users = [])
    {
        if (!is_array($users)) {
            $users = [$users];
        }

        $users = array_flip(array_map(function ($key) {
            return "select{$key}";
        }, array_flip($users)));

        return $this->parse($this->guzzle->post('/CMD_API_SELECT_USERS', ['form_params' => [
            'location' => 'CMD_RESELLER_SHOW',
            'suspend' => 'unsuspend'
        ] + $users]));
    }

    public function listUserAccounts($reseller = false)
    {
        return $this->parse($this->guzzle->post('/CMD_API_SHOW_USERS', ['form_params' => [
            'reseller' => $reseller
        ]]));
    }

    public function listResellerAccounts()
    {
        return $this->parse($this->guzzle->get('/CMD_API_SHOW_RESELLERS'));
    }

    public function listAdminAccounts()
    {
        return $this->parse($this->guzzle->get('/CMD_API_SHOW_ADMINS'));
    }

    public function listAllAccounts()
    {
        return $this->parse($this->guzzle->get('/CMD_API_SHOW_ALL_USERS'));
    }

    public function getUserConfig($username)
    {
        return $this->parse($this->guzzle->get('/CMD_API_SHOW_USER_CONFIG?user=' . $username . '&both=yes&redirect=yes'));
    }

    public function resetAccountPassword($username, $password)
    {
        return $this->parse($this->guzzle->post('/CMD_API_USER_PASSWD', ['form_params' => [
            'username' => $username,
            'passwd' => $password,
            'passwd2' => $password
        ]]));
    }
}
