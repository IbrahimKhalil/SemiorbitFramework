<?php
namespace Semiorbit\Component;



use Semiorbit\Base\AppService;

interface ServiceProviderInterface
{

    public function __construct(AppService $app);

    public function Register();

    public function onStart();

    public function Registry();

    public function ServiceID();

}