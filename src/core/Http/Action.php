<?php
/*
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT                       				 					 semiorbit.com
 *------------------------------------------------------------------------------------------------
 */

namespace Semiorbit\Http;


use Semiorbit\Support\AltaArray;
use Semiorbit\Support\Str;

class Action extends AltaArray
{

    public $Method = '';

    public $Alias = '';

    public $View;

    public $CP;

    public $Pms;

    public $Box;

    public $Allow;

    public $Title;

    public $Options = array();

    public $onCall;

    public $Verb;

    /**
     * Action constructor.
     * @param $alias
     * @param array|AltaArray|null $props
     */
    function __construct($alias, $props)
    {
        parent::__construct(array());

        $this->Prepare($alias, $props);
    }


    /**
     * FullName of method in controller
     * eg: <b>Edit</b>, <b>ShowProfile</b>...
     *
     * @param String $value FullName of method in controller
     * @return $this
     */

    public function setMethod($value)
    {
        $this->Method = $value;

        return $this;
    }

    /**
     * Action segment value in URL (param-case)<br/>
     * eg: <b>edit</b>, <b>show-profile</b><br/>
     * http://my-domain.com/controller-name/action-alias/id
     *
     * @param $value
     * @return $this
     */

    public function setAlias($value)
    {
        $this->Alias = $value;

        return $this;
    }

    public function setView($value)
    {
        $this->View = $value;

        return $this;
    }

    public function setTitle($value)
    {
        $this->Title = $value;

        return $this;
    }

    public function setVerb($value)
    {
        $this->Verb = $value;

        return $this;
    }

    public function setOptions(array $value)
    {
        $this->Options = $value;

        return $this;
    }


    /**
     * Parameters pattern used to parse PATH_INFO starts after action segment <br/>
     * 'http://www.my-domain.com/lang/controller/action/<b>{id}</b>/<b>{some-thing}</b>'<br/>
     *  eg: ':id/:some-thing' or 'id/some-thing'
     *
     * @param $value
     * @return $this
     *
     */
    public function setPms($value)
    {
        $this->Pms = $value;

        return $this;
    }

    /**
     * Scaffolding show/hide users control panel.
     *
     * @param bool $value
     * @return $this
     */

    public function setCP($value = true)
    {
        $this->CP = $value;

        return $this;
    }

    /**
     * Scaffolding view default box.
     *
     * @param $value
     * @return $this
     */

    public function setBox($value = 'panel')
    {
        $this->Box = $value;

        return $this;
    }

    /**
     * Allowed roles|roles array.<br/><br/>
     * <ul>
     * <li><b>FALSE</b> to deny access for all users.</li>
     * <li><b>TRUE</b>|<b>NULL</b>|<b>''</b> to allow everyone (<u>ANONYMOUS</u>)</li>
     * <li><b>SUPER_ADMIN</b> | <b>ANY_AUTHENTICATED_USER</b></li>
     * <li>array(<b>role1</b>, <b>role2</b>, ...)</li>
     * </ul>
     *
     * @param $value
     * @return $this
     */

    public function setAllow($value)
    {
        $this->Allow = $value;

        return $this;
    }

    protected function Prepare($alias, $props)
    {

        $action_props = is_array($props) || $props instanceof AltaArray ? $props : array('method' => (is_string($props) ? $props : null));


        $this->setMethod( ! empty($action_props['method']) ? $action_props['method'] : Str::PascalCaseByHyphen($alias) );

        $this->setAlias( isset($action_props['alias']) ? $action_props['alias'] : $alias);

        $this->setAllow( isset($action_props['allow']) ? $action_props['allow'] : null );

        $this->setBox( isset($action_props['box']) ? $action_props['box'] : 'panel' );

        $this->setView( isset($action_props['view']) ? $action_props['view'] : null );

        $this->setTitle( isset($action_props['title']) ? $action_props['title'] : null );

        $this->setCP( isset($action_props['cp']) ? $action_props['cp'] : true );

        $this->setPms( isset($action_props['pms']) ? $action_props['pms'] : '');

        $this->setVerb( isset($action_props['verb']) ? $action_props['verb'] : '');

        $this->onCall( isset($action_props['oncall']) ? $action_props['oncall'] : null );


        return $this;

    }

    public function onCall($event)
    {
        $this->onCall = $event;
    }

    public static function Load($alias, $props)
    {
        $myAction = new Action($alias, $props);

        return $myAction;

    }

    public function IsVerbAccepted($verb)
    {
        return !$this->Verb || $verb == $this->Verb;
    }



}