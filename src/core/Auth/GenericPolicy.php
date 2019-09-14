<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT                                  			 				 semiorbit.com
*------------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Auth;



class GenericPolicy extends AccessControlPolicy
{


    /**
     * Defines a policy to check if a user or a group (defined in $for parameter) is allowed to insert new rows.
     *
     * @param mixed $for User or Group - If not defined, current authenticated user should be used.
     * @return bool
     */

    public function CreateRule($for = null)
    {
        if ($this->IsDataSetImplementsAccessControlPolicyInterface())

            return $this->ActiveDataSet()->CreateRule($for);

        else return true;
    }

    /**
     * Defines a policy to check  if a user or a group (defined in $for parameter) is allowed to read/view current row.
     *
     * @param mixed $for User or Group - If not defined, current authenticated user should be used.
     * @return bool
     */

    public function ReadRule($for = null)
    {
        if ($this->IsDataSetImplementsAccessControlPolicyInterface())

            return $this->ActiveDataSet()->ReadRule($for);

        else return true;
    }

    /**
     * Defines a policy to check if a user or a group (defined in $for parameter) is allowed to update current row.
     *
     * @param mixed $for User or Group - If not defined, current authenticated user should be used.
     * @return bool
     */

    public function UpdateRule($for = null)
    {
        if ($this->IsDataSetImplementsAccessControlPolicyInterface())

            return $this->ActiveDataSet()->UpdateRule($for);

        else return true;
    }

    /**
     * Defines a policy to check if a user or a group (defined in $for parameter) is allowed to delete current row.
     *
     * @param mixed $for User or Group - If not defined, current authenticated user should be used.
     * @return bool
     */

    public function DeleteRule($for = null)
    {
        if ($this->IsDataSetImplementsAccessControlPolicyInterface())

            return $this->ActiveDataSet()->DeleteRule($for);

        else return true;

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
        if ($this->ActiveDataSet()) return $this->ActiveDataSet()->onBeforeAuthorize($user);

        else return null;
    }


}