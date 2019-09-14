<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT                                  			 				 semiorbit.com
*------------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Data;


use Semiorbit\Auth\AccessControlPolicy;
use Semiorbit\Auth\Auth;
use Semiorbit\Auth\GenericPolicy;
use Semiorbit\Auth\GenericUser;

trait AccessControlTrait
{

    protected $_AccessControlPolicy;

    /**
     * @return GenericPolicy
     */

    final public function Policy()
    {

        if (! $this->ActivePolicy())

            /** @var DataSet $this */

            $this->UsePolicy(GenericPolicy::With($this));

        return $this->ActivePolicy();

    }

    final public function UsePolicy(AccessControlPolicy $policy)
    {
        $this->_AccessControlPolicy = $policy;

        /**@var $this DataSet*/

        $this->_AccessControlPolicy->UseDataSet($this);

        return $this;
    }


    public function ActivePolicy()
    {
        return $this->_AccessControlPolicy;
    }

    /**
     * This event will be triggered before any authorization check. If returns true or false checking process will be
     * canceled returning the result. But if null returned, check process will be continued.
     *
     * @param GenericUser $user  If not defined, current authenticated user would be used.
     * @return bool|null
     */

    public function onBeforeAuthorize($user)
    {
        if ( $user->IsSuperAdmin() ) return true;

        return null;
    }



}