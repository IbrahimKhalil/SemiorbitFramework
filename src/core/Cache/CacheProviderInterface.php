<?php


namespace Semiorbit\Cache;


interface CacheProviderInterface
{

    public function Store($key, $value, $seconds = 0);

    public function Read($key);

    public function Clear($key = null);

    /**
     * @param $key
     * @return bool
     */
    public function Has($key);

}