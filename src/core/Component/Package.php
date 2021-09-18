<?php


namespace Semiorbit\Component;


use Semiorbit\Support\Path;

class Package
{

    const PKG_MODELS_NS = 0;

    const PKG_CONTROLLERS_NS = 1;

    const PKG_CONFIG = 2;

    const PKG_LANG = 3;

    const PKG_PATH = 4;

    const PKG_VIEWS = 5;

    const PKG_ROUTES = 6;

    const PKG_MODELS = 7;

    const PKG_CONTROLLERS = 8;

    const PKG_API_CONTROLLERS_DIR = 9;


    private $_ServiceID;


    private static $_Instance;




    public function LoadService($service_id)
    {
        $this->_ServiceID = $service_id;

        Services::Init($service_id, []);

        return $this;
    }

    public function &Registry()
    {
        return Services::Read($this->_ServiceID);
    }


    public function ServiceItem($key)
    {
        return $this->Registry()[$key] ?? $this->UndefinedServiceException($key);
    }

    public static function Select($service_id)
    {
        return (static::$_Instance ?: (static::$_Instance = new static()))->LoadService($service_id);
    }


    public function setViewsPath($path)
    {
        $this->Registry()[self::PKG_VIEWS] = Path::Normalize($path);

        return $this;
    }

    public function setConfigPath($path)
    {
        $this->Registry()[self::PKG_CONFIG] = Path::Normalize($path);

        return $this;
    }

    public function setRoutesPath($path)
    {
        $this->Registry()[self::PKG_ROUTES] = Path::Normalize($path);

        return $this;
    }

    public function setLangPath($path)
    {
        $this->Registry()[self::PKG_LANG] = Path::Normalize($path);

        return $this;
    }

    public function setModelsPath($path)
    {
        $this->Registry()[self::PKG_MODELS] = Path::Normalize($path);

        return $this;
    }

    public function setControllersPath($path)
    {
        $this->Registry()[self::PKG_CONTROLLERS] = Path::Normalize($path);

        return $this;
    }

    public function setApiControllersDir($dir)
    {
        $this->Registry()[self::PKG_API_CONTROLLERS_DIR] = Path::Normalize($dir);

        return $this;
    }


    public function setPath($path)
    {
        $this->Registry()[self::PKG_PATH] = Path::Normalize($path);

        return $this;
    }

    public function setControllersNamespace($ns)
    {
        $this->Registry()[self::PKG_CONTROLLERS_NS] = trim($ns, '\\');

        Services::IndexControllerNs($ns, $this->_ServiceID);

        return $this;
    }

    public function setModelsNameSpace($ns)
    {
        $this->Registry()[self::PKG_MODELS_NS] = trim($ns, '\\');

        Services::IndexModelNs($ns, $this->_ServiceID);

        return $this;
    }


    public function Path()
    {
        return $this->ServiceItem(self::PKG_PATH);
    }

    public function ControllersNamespace()
    {
        return $this->ServiceItem(self::PKG_CONTROLLERS_NS);
    }

    public function ModelsNamespace()
    {
        return $this->ServiceItem(self::PKG_MODELS_NS);
    }

    public function LangPath()
    {
        return $this->ServiceItem(self::PKG_LANG);
    }


    public function ViewsPath()
    {
        return $this->ServiceItem(self::PKG_VIEWS);
    }

    public function ConfigPath()
    {
        return $this->ServiceItem(self::PKG_CONFIG);
    }

    public function RoutesPath()
    {
        return $this->ServiceItem(self::PKG_ROUTES);
    }

    public function ModelsPath()
    {
        return $this->ServiceItem(self::PKG_MODELS);
    }

    public function ControllersPath()
    {
        return $this->ServiceItem(self::PKG_CONTROLLERS);
    }

    public function ApiControllersDir()
    {
        return ($this->Registry()[self::PKG_API_CONTROLLERS_DIR]) ?? '';
    }



    public function UndefinedServiceException($key)
    {
        $constants = array_flip((new \ReflectionClass(__CLASS__))->getConstants());

        throw new \RuntimeException("Undefined Package Service: ({$this->_ServiceID}:{$constants[$key]})", 6601);
    }

}