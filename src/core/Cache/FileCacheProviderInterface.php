<?php


namespace Semiorbit\Cache;


interface FileCacheProviderInterface extends CacheProviderInterface
{

    public function UseCachePath($path);

    public function CachePath();

    public function StoreVar($key, $value);

    public function ReadVar($key);

}