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

    public static function Select($service_id)
    {
        return (static::$_Instance ?: new static())->LoadService($service_id);
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

    public function setLangPath($path)
    {
        $this->Registry()[self::PKG_LANG] = Path::Normalize($path);

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
        return $this->Registry()[self::PKG_PATH];
    }

    public function ControllerNamespace()
    {
        return $this->Registry()[self::PKG_CONTROLLERS_NS];
    }

    public function ModelNamespace()
    {
        return $this->Registry()[self::PKG_MODELS_NS];
    }

    public function LangPath()
    {
        return $this->Registry()[self::PKG_LANG];
    }


    public function ViewsPath()
    {
        return $this->Registry()[self::PKG_VIEWS];
    }

    public function ConfigPath()
    {
        return $this->Registry()[self::PKG_CONFIG];
    }


}