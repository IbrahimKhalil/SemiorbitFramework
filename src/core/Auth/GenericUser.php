<?php
/* 
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT		   								 					 semiorbit.com
 *------------------------------------------------------------------------------------------------
 */


namespace Semiorbit\Auth;



use Semiorbit\Data\DataSet;
use Semiorbit\Field\Field;
use Semiorbit\Field\Password;
use Semiorbit\Field\Text;
use Semiorbit\Config\Config;


class GenericUser extends DataSet
{

    protected $_Identity;

    protected $_Password;

    protected $_Role;
    

    public function Is($roles = null)
    {

        if ( $roles === false ) return false;

        if ( $roles === null || $roles === '' || $roles === array() || $roles === true ) return true;

        if ( ! $this->IsLoggedIn() ) return false;

        if ( $roles === SUPER_ADMIN ) return $this->IsSuperAdmin();

        else if ( $roles === ANY_AUTHENTICATED_USER ) return $this->IsLoggedIn();

        else if ( ! is_array($roles) ) return $this->Role()->FilterValue() === $roles;

        else {

            if ( in_array( SUPER_ADMIN, $roles ) && $this->IsSuperAdmin() ) return true;

            if ( in_array( $this->Role()->FilterValue(), $roles ) ) return true;

            if ( in_array( ANY_AUTHENTICATED_USER, $roles ) ) return $this->IsLoggedIn();

        }

        return false;

    }


    public function Can($permissions)
    {
        //TODO:: Check permissions
        
        return $permissions ?: true;
    }

    public function IsSuperAdmin()
    {
        return $this->Role()->FilterValue() === Config::SuperAdminRoleId();
    }


    /**
     * Identity field ( which is a unique field that user can use to log in
     * like <b>user name, number, or email</b> )
     *
     * @return Text
     */

    public function Identity()
    {
        if ( ! $this->_Identity ) $this->MapIdentity();

        return $this->_Identity;
    }

    /**
     * Password Field
     *
     * @return Password
     */

    public function Password()
    {
        if ( ! $this->_Password ) $this->MapPassword();

        return $this->_Password;
    }

    /**
     * Role id field (role id may refer to a foreign id in roles table where roles can be defined )
     * <p>Roles can be, for example (<b>Admin, Member, Moderator, Guest, ... etc</b>)
     *
     * @return Field
     */

    public function Role()
    {
        if ( ! $this->_Role ) $this->MapRole();

        return $this->_Role;
    }

    /**
     * Map user generic fields to its representatives in application user model
     *
     * @param Field $identity
     * @param Field $password
     * @param Field $role
     * @return $this
     */

    public function Map(Field $identity, Field $password, Field $role)
    {
        $this->MapIdentity($identity)->MapPassword($password)->MapRole($role);

        return $this;
    }


    /**
     * Map generic identity field ( which is a unique field that user can use to log in
     * like <b>user name, number, or email</b> ) to its representative in application user model
     *
     * @param Field|null $identity
     * @return $this
     */

    public function MapIdentity(Field $identity = null)
    {

        if (  ! $identity ) $identity = $this->{Config::UsersModelFields('Identity')};

        $this->_Identity = $identity;

        return $this;

    }

    /**
     * Map generic password field to its representative in application user model
     *
     * @param Field|null $password
     * @return $this
     */

    public function MapPassword(Field $password = null)
    {

        if (  ! $password ) $password = $this->{Config::UsersModelFields('Password')};

        $this->_Password = $password;

        return $this;

    }

    /**
     * Map generic role id field (role id may refer to a foreign id in roles table where roles can be defined )
     * to its representative in application user model.
     * <p>Roles can be, for example (<b>Admin, Member, Moderator, Guest, ... etc</b>)
     *
     * @param Field|null $role
     * @return $this
     */

    public function MapRole(Field $role = null)
    {

        if (  ! $role ) $role = $this->{Config::UsersModelFields('Role')};

        $this->_Role = $role;

        return $this;

    }

    /**
     * Validate identity string and return sanitized identity or false if not valid
     *
     * @param $identity
     * @return string|false
     */

    public function ValidateIdentity($identity) {

        $identity = $this->Identity()->FilterValue($identity);

        return $identity;
    }


    public function Authenticate($identity, $password)
    {

        $identity = $this->ActiveConnection()->Escape($identity);

        $user = $this->ActiveConnection()->Row("SELECT {$this->ID->Name} AS user_id, {$this->Password()->Name} AS user_password FROM {$this->TableName()}

                WHERE {$this->Identity()->Name} = '{$identity}' ");

        $uid = $user['user_id'];

        $hashed_password  = hash('sha512', $password . hash('sha512', $uid));

        if ($user) if ( $user['user_password'] === $hashed_password ) return $uid;

        return false;

    }

    public function IsLoggedIn()
    {
        return ( ! empty( $this->ID()->Value ) && Auth::ID() == $this->ID()->Value  );
    }


    public function onBeforeLogin()
    {
        return true;
    }

    public function onLogin()
    {
        return true;
    }

    public function onLoginFailed($identity, $password)
    {
        return false;
    }

    public function onLogout($user_id)
    {

    }

    /**
     * @return static
     */

    public static function Auth()
    {
        return Auth::User();
    }



}