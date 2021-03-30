<?php


namespace Semiorbit\Auth;


interface AuthInterface
{

    public function User() : GenericUser;

    public function UpdateLoginToken($new_password);

    public function UseUser(GenericUser $user = null) : AuthInterface;

    public function Login($identity, $password);

    public function VerifyLoggedUser() : ?GenericUser;

    public function Logout($destroy_session = true);

    public function ID();

    public function Allow($roles, $permissions = null);


}