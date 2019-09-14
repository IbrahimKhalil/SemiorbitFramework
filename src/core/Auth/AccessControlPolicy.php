<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT                                  			 				    semiorbit.com
*------------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Auth;


use Semiorbit\Data\DataSet;

abstract class AccessControlPolicy implements AccessControlPolicyInterface
{

    private $_DateSet;

    private $_CreateRule;

    private $_UpdateRule;

    private $_DeleteRule;

    private $_ReadRule;

    private $_Rules = array();

    private $_onBeforeAuthorizeEvent;

    private $_DSImplementsActiveControlPolicyInterface = false;

    private $_User;


    /**
     * @return GenericUser
     */

    final public function User()
    {
        return $this->_User ?: Auth::User();
    }

    /**
     * @param GenericUser $user
     * @return static
     */

    final public function ForUser(GenericUser $user)
    {
        $this->_User = $user;

        return $this;
    }

    /**
     * @param DataSet $data_set
     * @return static
     */

    public function UseDataSet(DataSet $data_set)
    {
        $this->_DateSet = $data_set;

        if ($this->_DateSet instanceof AccessControlPolicyInterface)

            $this->_DSImplementsActiveControlPolicyInterface = true;

        else $this->_DSImplementsActiveControlPolicyInterface = false;

        return $this;
    }

    /**
     * @return DataSet|AccessControlPolicyInterface
     */

    public function ActiveDataSet()
    {
        return $this->_DateSet;
    }

    /**
     * @param DataSet $data_set
     * @return static
     */

    public static function With(DataSet $data_set)
    {
        $myPolicy = new static;

        $myPolicy->UseDataSet($data_set);

        return $myPolicy;
    }

    final public function IsDataSetImplementsAccessControlPolicyInterface()
    {
        return $this->_DSImplementsActiveControlPolicyInterface;
    }

    final public function FireOnBeforeAuthorize($for = null)
    {
        return $this->_onBeforeAuthorizeEvent ? call_user_func_array($this->_onBeforeAuthorizeEvent, array($for)) :

            $this->onBeforeAuthorize($for);
    }

    final public function setOnBeforeAuthorize(callable $callback)
    {
        $this->_onBeforeAuthorizeEvent = $callback;
    }

    /**
     * Checks if policy user is allowed to access current row according to a rule.
     *
     * @param mixed $rule Access rule
     * @return bool
     */

    final public function Allows($rule)
    {
        $for = $this->User();
        
        $filter_event = $this->FireOnBeforeAuthorize($for);

        if ( $filter_event === true || $filter_event === false ) return $filter_event;

        return isset($this->_Rules[$rule]) ? call_user_func_array($this->_Rules[$rule], array($rule, $for)) : true;
    }

    /**
     * Checks if policy user is allowed to insert new rows.
     *
     * @return bool
     */

    final public function AllowsCreate()
    {
        $for = $this->User();

        $filter_event = $this->FireOnBeforeAuthorize($for);

        if ( $filter_event === true || $filter_event === false ) return $filter_event;

        return $this->_CreateRule ? call_user_func_array($this->_CreateRule, array($for)) : $this->CreateRule($for);
    }

    /**
     * Checks if policy user is allowed to update current row.
     *
     * @return bool
     */

    final public function AllowsUpdate()
    {
        $for = $this->User();

        $filter_event = $this->FireOnBeforeAuthorize($for);

        if ( $filter_event === true || $filter_event === false ) return $filter_event;

        return $this->UpdateRule($for);
    }

    /**
     * Checks if policy user is allowed to read/view current row.
     *

     * @return bool
     */

    final public function AllowsRead()
    {
        $for = $this->User();
        
        $filter_event = $this->FireOnBeforeAuthorize($for);

        if ( $filter_event === true || $filter_event === false ) return $filter_event;

        return $this->ReadRule($for);
    }

    /**
     * Checks if policy user is allowed to delete current row.
     *
     * @return bool
     */

    public function AllowsDelete()
    {
        $for = $this->User();
        
        $filter_event = $this->FireOnBeforeAuthorize($for);

        if ( $filter_event === true || $filter_event === false ) return $filter_event;

        return $this->DeleteRule($for);
    }


    /**
     * Checks if policy user is not allowed to access current row according to a rule.
     *
     * @param mixed $rule Access rule
     * @return bool
     */

    final public function Denies($rule)
    {
        return ! static::Allows($rule);
    }

    /**
     * Checks if policy user is not allowed to insert new rows.
     *
     * @return bool
     */

    final public function DeniesCreate()
    {
        return ! static::AllowsCreate();
    }

    /**
     * Checks if policy user is not allowed to update current row.
     *

     * @return bool
     */

    final public function DeniesUpdate()
    {
        return ! static::AllowsUpdate();
    }

    /**
     * Checks if policy user is not allowed to read/view current row.
     *

     * @return bool
     */

    final public function DeniesRead()
    {
        return ! static::AllowsRead();
    }

    /**
     * Checks if policy user is not allowed to delete current row.
     *

     * @return bool
     */

    final public function DeniesDelete()
    {
        return ! static::AllowsDelete();
    }

    final public function DefineCreateRule(callable $callback)
    {
        $this->_CreateRule = $callback;
    }

    final public function DefineReadRule(callable $callback)
    {
        $this->_ReadRule = $callback;
    }

    final public function DefineUpdateRule(callable $callback)
    {
        $this->_UpdateRule = $callback;
    }

    final public function DefineDeleteRule(callable $callback)
    {
        $this->_DeleteRule = $callback;
    }

    final public function DefineRule($rule, callable $callback)
    {
        $this->_Rules[$rule] = $callback;
    }

    final public function HasRole($rule)
    {
        return isset($this->_Rules[$rule]);
    }

    final public function DefinedRules()
    {
        return $this->_Rules;
    }

}