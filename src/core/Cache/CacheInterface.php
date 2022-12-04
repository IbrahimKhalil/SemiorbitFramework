<?php


namespace Semiorbit\Cache;


interface CacheInterface
{


    public static function UseCacheProvider(CacheProviderInterface $cache_provider);


    /**
     * @return CacheProviderInterface
     */
    public static function ActiveCacheProvider();

    public static function Read($key);

    public static function Store($key, $value, $seconds = 0);

    public static function Clear($key = null);

    /**
     * @param $key
     * @return bool
     */
    public static function Has($key);

}