<?php


namespace Semiorbit\Component;


use Semiorbit\Base\Application;
use Semiorbit\Base\AppService;
use Semiorbit\Cache\FrameworkCache;
use Semiorbit\Config\Config;
use Semiorbit\Support\RegistryManagerInterface;

final class Services implements RegistryManagerInterface
{

    private static $_Reg = [];

    private static $_ModelsNsIndex = [];

    private static $_ControllersNsIndex = [];


    public static function Destroy()
    {
        self::$_Reg = [];
    }

    public static function Store($key, $value)
    {
        self::$_Reg[$key] = $value;
    }

    public static function &Read($key)
    {
        return self::$_Reg[$key];
    }

    public static function Clear($key)
    {
        unset(self::$_Reg[$key]);
    }

    public static function List()
    {
        return static::$_Reg;
    }

    /**
     * @param $key
     * @return bool
     */
    public static function Has($key)
    {
        return isset(self::$_Reg[$key]);
    }

    public static function Init($key, $value)
    {
        return self::$_Reg[$key] ?? self::$_Reg[$key] = $value;
    }

    public static function IndexModelNs($ns, $service_id)
    {
        static::$_ModelsNsIndex[$ns] = $service_id;
    }

    public static function IndexControllerNs($ns, $service_id)
    {
        static::$_ControllersNsIndex[$ns] = $service_id;
    }

    public static function FindPackageByModelNs($ns)
    {
        return static::$_ModelsNsIndex[$ns] ?? null;
    }


    public static function FindPackageByControllerNs($ns)
    {
        return static::$_ControllersNsIndex[$ns] ?? null;
    }

    public static function Register(AppService $appService)
    {

        static::Destroy();

        $packages = Config::Services();

        // TODO CACHE --------------

        foreach ($packages as $package) {
            
            
            if (class_exists($package)) {

                /** @var ServiceProviderInterface $pkg */

                $pkg = new $package($appService);

                $pkg->Register();

            } else

                Application::Abort(404, "Unable to register service ({$package})");


        }

    }

}