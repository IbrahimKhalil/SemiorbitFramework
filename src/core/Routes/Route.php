<?php


namespace Semiorbit\Routes;


use Semiorbit\Base\Application;
use Semiorbit\Component\FinderResult;
use Semiorbit\Http\Request;

class Route
{


    const TYPE_CONTROLLER = '__CTRL';

    protected $_Scope;

    protected $_Index;

    protected $_Verb;

    protected $_Order;

    protected $_Value;

    protected $_Name;



    public function __construct($scope, $index, $verb = self::TYPE_CONTROLLER, $order = null)
    {

        $this->_Scope = $scope;

        $this->_Index = $index;

        $this->_Verb = $verb;

        $this->_Order = $order;


        $this->_Value = Router::ValueOf($scope, $index, $verb, $order)

            ?: Application::Abort(404, "Invalid Route: ({$this->Key()})");

    }


    public function Key()
    {
        return implode('.', $this->_Order === null ?

            [$this->_Scope, $this->_Index, $this->_Verb] :

            [$this->_Scope, $this->_Index, $this->_Verb, $this->_Order]);
    }

    public function NameAs($name)
    {

        $this->_Name = $name;

        Router::NameIndex()[$name] = [$this->Scope(), $this->Index(), $this->Verb(), $this->Order()];

        return $this;

    }


    /**
     * Select route by name
     *
     * @param $name
     * @return static
     */

    public static function Select($name)
    {
        [$scope, $index, $verb, $order] = Router::FindKeyByName($name);

        if ($index === null)

            Application::Abort(404, "No route with name ({$name}) found!");

        return new static($scope, $index, $verb, $order);
    }

    /**
     * Select rout by full key
     *
     * @param $scope
     * @param $index
     * @param string $verb
     * @param null $order
     * @return static
     */

    public static function At($scope, $index, $verb = self::TYPE_CONTROLLER, $order = null)
    {
        return new static($scope, $index, $verb, $order);
    }


    public static function Controller($index, $controller)
    {
        Router::RegisterController(ScopeProvider::ActiveScope(), $index, $controller);

        return new Route(ScopeProvider::ActiveScope(), $index);
    }

    /**
     * @param $verb
     * @param $pattern
     * @param $target array|callable [Controller, Action] or callabel
     * @return ActionRoute
     */
    
    public static function Add($verb, $pattern, $target)
    {
        return Router::RegisterAction(ScopeProvider::ActiveScope(), $pattern, $verb, $target);
    }

    /**
     * @param $pattern
     * @param $target array|callable [Controller, Action] or callabel
     * @return ActionRoute
     */
    
    public static function AddGet($pattern, $target)
    {
        return static::Add(Request::VERB_GET, $pattern, $target);
    }

    
    /**
     * @param $pattern
     * @param $target array|callable [Controller, Action] or callabel
     * @return ActionRoute
     */
    
    public static function AddPost($pattern, $target)
    {
        return static::Add(Request::VERB_POST, $pattern, $target);
    }
    

    /**
     * @param $pattern
     * @param $target array|callable [Controller, Action] or callabel
     */
    
    public static function AddPut($pattern, $target)
    {
        static::Add(Request::VERB_PUT, $pattern, $target);
    }
    

    /**
     * @param $pattern
     * @param $target array|callable [Controller, Action] or callabel
     * @return ActionRoute
     */
    
    public static function AddPatch($pattern, $target)
    {
        return static::Add(Request::VERB_PATCH, $pattern, $target);
    }

    
    /**
     * @param $pattern
     * @param $target array|callable [Controller, Action] or callabel
     * @return ActionRoute
     */
    
    public static function AddDelete($pattern, $target)
    {
        return static::Add(Request::VERB_DELETE, $pattern, $target);
    }


    #region "PROPS"

    /**
     * @return string
     */
    public function Scope()
    {
        return $this->_Scope;
    }

    /**
     * @return string
     */
    public function Name()
    {
        return $this->_Name ?: $this->_Name = array_search(

            [$this->Scope(), $this->Index(), $this->Verb(), $this->Order()],

            Router::NameIndex());
    }

    /**
     * @return string
     */
    public function Verb()
    {
        return $this->_Verb;
    }

    /**
     * @return mixed
     */
    public function Value()
    {
        return $this->_Value;
    }


    /**
     * @return mixed
     */
    public function Order()
    {
        return $this->_Order;
    }

    /**
     * @return mixed
     */
    public function Index()
    {
        return $this->_Index;
    }

    #endregion

}