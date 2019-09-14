<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT                                  			 				 semiorbit.com
*------------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Auth;


interface AccessControlPolicyInterface
{


    /**
     * Defines a policy to check if a user is allowed to insert new rows.
     *
     * @param GenericUser $user  If not defined, current authenticated user would be used.
     * @return bool
     */

    public function CreateRule($user);


    /**
     * Defines a policy to check  if a user is allowed to read/view current row.
     *
     * @param GenericUser $user  If not defined, current authenticated user would be used.
     * @return bool
     */

    public function ReadRule($user);


    /**
     * Defines a policy to check if a user is allowed to update current row.
     *
     * @param GenericUser $user  If not defined, current authenticated user would be used.
     * @return bool
     */

    public function UpdateRule($user);


    /**
     * Defines a policy to check if a user is allowed to delete current row.
     *
     * @param GenericUser $user  If not defined, current authenticated user would be used.
     * @return bool
     */

    public function DeleteRule($user);


    /**
     * This event will be triggered before any authorization check. If returns true or false checking process will be
     * canceled returning the result. But if null returned, check process will be continued.
     *
     * @param GenericUser $user  If not defined, current authenticated user would be used.
     * @return bool|null
     */

    public function onBeforeAuthorize($user);



}