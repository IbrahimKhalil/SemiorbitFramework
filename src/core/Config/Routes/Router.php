<?php /** @noinspection PhpIncludeInspection */


namespace Semiorbit\Config\Routes;


use Semiorbit\Base\Application;
use Semiorbit\Cache\FrameworkCache;
use Semiorbit\Component\FinderResult;
use Semiorbit\Component\Package;
use Semiorbit\Component\Services;
use Semiorbit\Config\Config;
use Semiorbit\Http\Request;

class Router
{

    const DEFAULT_SCOPE = 0;

    const DEFAULT_REG_INDEX = 0;


    protected static $_Reg = [];

    protected static $_RegByName = [];

    protected static $_Callable = [];


    public static function RegisterController($scope, $index, $target)
    {
        static::$_Reg[$scope][$index][Route::TYPE_CONTROLLER] = $target;

        return new Route($scope, $index, Route::TYPE_CONTROLLER, null);
    }


    public static function RegisterAction($scope, $pattern, $verb, $target)
    {

        $pattern = trim($pattern, '/');

        $index = ($pos = strpos($pattern, '/')) ?

            trim(substr($pattern, 0, $pos)) : $pattern;

        if (! $index) $index = '/';

        elseif (fnmatch("{*}", $index))

            $index = self::DEFAULT_REG_INDEX;

        $constraints = [];

        [$reg_pattern, $params] = ActionRoute::CompileRegPattern($pattern, $constraints);

        $controller = $action = $callable = null;


        if (is_array($target)) {

            $controller = $target[0];

            $action = $target[1] ?? 'index';

        } elseif (is_callable($target)) {

            $callable_index = array_push(static::$_Callable, $target) - 1;

            $callable = static::$_Callable[$callable_index];

        } else

            Application::Abort(403, "Invalid Route: {$scope}.{$pattern}");


        static::$_Reg[$scope][$index][$verb][] =

            ActionRoute::Build($pattern,

                $controller, $action, $callable,

                $reg_pattern, $constraints, $params);



        return new ActionRoute($scope, $index, $verb, count(static::$_Reg[$scope][$index][$verb]) - 1);

    }


    public static function ValueOf($scope, $index, $verb = Route::TYPE_CONTROLLER, $order = null)
    {
        return ($order === null) ? (static::$_Reg[$scope][$index][$verb] ?? null) :

            (static::$_Reg[$scope][$index][$verb][$order] ?? null);
    }


    public static function ListByVerb($verb, $index, $scope = self::DEFAULT_SCOPE)
    {
        return static::$_Reg[$scope][$index][$verb];
    }



    public static function FindKeyByName($name)
    {
        return static::$_RegByName[$name] ?? [null, null, null, null];
    }


    /**
     * @return array
     */

    public static function &NameIndex()
    {
        return static::$_RegByName;
    }

    public static function Find($uri, $verb)
    {

        $controller = $action = $params = $callable = null;

        $path = Request::Path2Array($uri);

        $index = $path[0] ?? '/';


        if ($actions = (static::$_Reg[0][$index][$verb] ?? null) ) {

            foreach ($actions as $order => $val) {

                $actionRoute = new ActionRoute(0, $index, $verb, $order);

                if ($params = $actionRoute->Match($uri)) {

                    $controller = new FinderResult(['Class' => $actionRoute->ControllerName(), 'Selector' => $uri]);

                    $action = $actionRoute->Action();

                    $callable = $actionRoute->Callable();

                    if (isset($path[0])) unset($path[0]);

                    break;

                }

            }

        }


        if (!$controller && $class = (static::$_Reg[0][$index][Route::TYPE_CONTROLLER] ?? null)) {

            $controller = new FinderResult(['Class' => $class, 'Selector' => $index]);

            if (isset($path[0])) unset($path[0]);

        }


//
//        if (!$controller && $index == '/')
//
//            $controller = Config::MainPage();


        return [$controller, $action, implode('/', $path), $params, $callable];

    }

    public static function UpdateActionRoute(ActionRoute $route)
    {
        static::$_Reg[$route->Scope()][$route->Index()][$route->Verb()][$route->Order()] = $route->Value();
    }


    public static function ImportGroup($group)
    {

        if (strpos($group, '::')) {

            [$pkg, $grp] = explode($group, '::', 2);

            $path = Package::Select($pkg)->ConfigPath() . "routes/{$grp}.php";

        } else {

            $path = Application::Service()->ConfigPath("routes/{$group}.php");

        }

        require $path;

    }


    public static function LoadRoutes($reload = false)
    {

        $is_api = Config::ApiMode();

        $mode = $is_api ? 'api' : 'web';

        if (!$reload &&  $cache = FrameworkCache::ReadVar("{$mode}-routes")) {

            static::$_Reg = $cache['reg'];

            static::$_RegByName = $cache['reg-by-name'];

        } else {

            if (file_exists($fn = Application::Service()->ConfigPath("routes/{$mode}.php"))) require $fn;

            foreach (Services::List() as $service_id => $service) {

                if (isset($service[Package::PKG_CONFIG]) &&

                    file_exists($fn = $service[Package::PKG_CONFIG] . "routes/{$mode}.php")) require $fn;

            }


            if (empty(static::$_Callable)) {

                $cache = [];

                $cache['reg'] = static::$_Reg;

                $cache['reg-by-name'] = static::$_RegByName;

                FrameworkCache::StoreVar("{$mode}-routes", $cache);

            }

        }

    }

}