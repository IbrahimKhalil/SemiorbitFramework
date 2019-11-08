<?php


namespace Semiorbit\Component;


use Semiorbit\Base\Application;
use Semiorbit\Base\AppService;

abstract class PackageServiceProvider implements ServiceProviderInterface
{

    const ServiceID = null;

    /**
     * @var $App AppService
     */
    public $App;

    public $Package;

    final public function __construct(AppService $app)
    {

        $this->App = $app;

        $this->Package = new Package();

        $this->Package->LoadService($this->ServiceID());

    }

    abstract public function Register();

    abstract public function onStart();


    /**
     * @return Package
     */

    final public function Registry()
    {
        return $this->Package;
    }

    final public function ServiceID()
    {
        return static::ServiceID ?:

            Application::Abort(403, __CLASS__ . ": Service ID can not be empty");
    }

}