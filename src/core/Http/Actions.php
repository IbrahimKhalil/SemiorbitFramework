<?php
/*
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT                       				 					 semiorbit.com
 *------------------------------------------------------------------------------------------------
 */

namespace Semiorbit\Http;


use Semiorbit\Base\Application;
use Semiorbit\Config\CFG;
use Semiorbit\Support\AltaArray;
use Semiorbit\Support\ClipboardTrait;
use Semiorbit\Support\Str;

class Actions extends AltaArray
{
    const AUTO = 'AUTO';

    const EXPLICIT = 'EXPLICIT';



    protected $_Mode = self::AUTO;

    protected $_Denied;

    protected $_DisabledMethods;

    protected $_CallEvent;


    protected $_Controller;

    use ClipboardTrait {
        Clipboard as protected;
    }


    public function __construct(Controller $controller, $array)
    {
        $this->UseController($controller);

        parent::__construct($array);
    }

    public function UseController(Controller $controller)
    {
        $this->_Controller = $controller;

        return $this;
    }

    /**
     * @return Controller
     */

    public function ActiveController()
    {
        return $this->_Controller;
    }


    public function UseArray($array)
    {
        $array = static::PrepareActions($array);

        return parent::UseArray($array);

    }

    public function Define($array)
    {
        return $this->UseArray($array);
    }

    final public function All()
    {

        // Prepare a list of all allowed actions for this controller.

        // List actions from actions array [in ACTIONS mode] and (+) from public methods [in AUTO mode]


        $tmp_actions = array();


        ####NOT ACTIONS###################################################################################
        $never_allow = array('on-Start', 'onstart');
        ##################################################################################################


        // Prepare actions array, by setting all properties.

        $this->Define($this->ToArray());



        // List of methods defined in actions, maybe one method is assigned for multiple actions

        // eg. actions(edit, create) => 'Edit'

        $methods_actions = array();

        foreach ($this->ToArray() as $action_key => $action_info) {

            $methods_actions[strtolower($action_info['method'])][$action_key] = $action_info;

        }



        // Retrieve methods from controller class (Only PUBLIC methods)

        $myReflection = new \ReflectionClass($this->ActiveController());

        $all_public_methods = $myReflection->getMethods(\ReflectionMethod::IS_PUBLIC);


        //Loop through public methods

        foreach ($all_public_methods as $myMethod) {

            // Public non static methods only

            if ($myMethod->isStatic()) continue;


            $method_name = $myMethod->getName();

            $method = strtolower($method_name);

            $action_alias = static::NormalizeAlias($method_name);

            if ($this->_DisabledMethods && in_array($method, $this->_DisabledMethods)) continue;


            // Check for allowed public methods.

            if (starts_with($method, '_')) continue;

            if (in_array($method, $never_allow)) continue;


            ////ADD PERMISSION FILTER CONTINUE;


            switch ($this->_Mode) :

                /** @noinspection PhpMissingBreakStatementInspection
                 *  Because it is meant to use next options alongside with this one.
                 */

                case self::AUTO:

                    if ( ! isset($methods_actions[$method]) ) {

                        if ($this->_Denied && in_array($action_alias,  $this->_Denied)) continue 2;

                        // Add action for this method, if it is not explicitly defined in actions array

                        $tmp_actions[$action_alias] = Action::Load(Str::ParamCase($method_name), $method_name);

                    } else {

                        // Add action for this method, if it is explicitly assigned to actions

                        // with different aliases than method name in actions array.

                        // In this case action info will be derived from the first action that is

                        // pointing to this method

                        if ( ! isset($methods_actions[$method][$action_alias])) {


                            if ( ! ($this->_Denied && in_array($action_alias,  $this->_Denied) ) ) {

                                $tmp_actions[$action_alias] = reset($methods_actions[$method]);

                            }

                            // ElseIf method is denied, check explicit action [>> goto explicit case]

                        }

                    }


                case self::EXPLICIT:

                    if ( isset($methods_actions[$method]) ) {

                        // Add actions that are explicitly defined for this method in actions array

                        foreach ($methods_actions[$method] as $action_key => $action_info) {

                            $norm_alias = static::NormalizeAlias($action_info['alias']);

                            if ($this->_Denied && in_array($norm_alias,  $this->_Denied)) continue;

                            $tmp_actions[$norm_alias] = $action_info;

                        }

                    }


            endswitch;

        }

        return $tmp_actions;

    }

    public static function NormalizeAlias($alis)
    {
        $key = 'A:' . $alis;

        if (!is_empty(self::Clipboard($key))) return self::Clipboard($key);

        $str = strtolower(str_replace('-', '', $alis));

        return self::Clipboard($key, $str);
    }


    /**
     * @param string $mode AUTO|EXPLICIT
     * @return $this
     */

    public function setMode($mode = self::AUTO)
    {
        $mode = strtoupper($mode);

        if (! in_array($mode, array(self::AUTO, self::EXPLICIT)) )

            $mode = self::AUTO;

        $this->_Mode = $mode;

        return $this;
    }

    public function AutoMode()
    {
        $this->setMode(self::AUTO);

        return $this;
    }

    public function ExplicitMode()
    {
        $this->setMode(self::EXPLICIT);

        return $this;
    }

    public function Mode()
    {
        return $this->_Mode;
    }

    public function Deny($array)
    {

        if (! is_array($array)) $array = array();

        $array = array_map(array('static', 'NormalizeAlias'), $array);

        $this->_Denied = $array;

        return $this;

    }

    public function Denied()
    {
        return $this->_Denied;
    }

    public function DisableMethods($array)
    {
        if (! is_array($array)) $array = array();

        $array = array_map('strtolower', $array);

        $this->_DisabledMethods = $array;

        return $this;

    }

    public function DisabledMethods()
    {
        $this->_DisabledMethods;
    }

    public function DefineAction($alias, $props)
    {
        $this->offsetSet($alias, new Action($alias, $props));

        return $this;
    }


    /**
     * @param String $action Action alias
     * @return Action
     */

    public function Action($action)
    {

       if ( ! $this->offsetExists($action) )

           $this->DefineAction($action, null);

       return $this->offsetGet($action);

    }

    /**
     * @return Action
     */

    public function Edit()
    {
        return $this->Action('edit');
    }

    /**
     * @return Action
     */

    public function Create()
    {
        return $this->Action('create');
    }

    /**
     * @return Action
     */

    public function Delete()
    {
        return $this->Action('delete');
    }


    /**
     * @return Action
     */

    public function Show()
    {
        return $this->Action('show');
    }

    /**
     * @return Action
     */

    public function Index()
    {
        return $this->Action(CFG::$IndexAction ?: 'index');
    }

    /**
     * @return Action
     */

    public function ListView()
    {
        return $this->Action('list');
    }

    /**
     * @return Action
     */

    public function TableView()
    {
        return $this->Action('table');
    }


    public function onCall(callable $event)
    {
        $this->_CallEvent = $event;

        return $this;
    }

    public function CallEvent()
    {
        return $this->_CallEvent;
    }

    /**
     * @param Action $action
     * @param array $params
     * @return mixed
     * @throws \Exception
     */

    public function Call($action, $params = array())
    {

        if ( ! $action instanceof Action) $action = Action::Load(is_array($action) ? $action['alias'] : $action, $action);

        $call_event_result = true;

        if (is_callable($this->CallEvent()))

            $call_event_result = call_user_func_array($this->CallEvent(), array($action));

        if ($call_event_result !== false && is_callable($action->onCall)) $call_event_result = call_user_func($action->onCall);

        if ($call_event_result !== false) {

            if ( ! method_exists($this->ActiveController(), $action->Method) ) Application::Abort(404);

            return call_user_func_array(array($this->ActiveController(), $action->Method), $params );

        }

        return Application::Abort(401);

    }


    public static function PrepareActions($actions)
    {

        $hash = md5(json_encode($actions));

        if (self::Clipboard($hash)) return self::Clipboard($hash);


        $prepared_actions = array();

        if (is_array($actions) || $actions instanceof \Traversable) {

            foreach ($actions as $action_key => $action_info) {

                $prepared_actions[$action_key] = Action::Load($action_key, $action_info);

            }

        }

        self::Clipboard($hash, $prepared_actions);

        return $prepared_actions;

    }


    public function __toString()
    {

        $array = array();

        foreach (parent::ToArray() as $alias => $action) {

            /** @var $action Action */

            $array[$alias] = $action->ToArray();

        }

        return strval(print_r($array, true));

    }


}