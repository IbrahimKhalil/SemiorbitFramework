<?php


namespace Semiorbit\Cache;


class Cache
{

    protected static $__CacheProvider;


    public static function UseCacheProvider(CacheProviderInterface $cache_provider)
    {
        static::$__CacheProvider = $cache_provider;

        return static::$__CacheProvider;
    }


    /**
     * @return CacheProviderInterface
     */
    public static function ActiveCacheProvider()
    {
        return static::$__CacheProvider;
    }

    public static function Read($key)
    {
        return static::ActiveCacheProvider()->Read($key);
    }


    public static function Store($key, $value, $seconds = 0)
    {
        static::ActiveCacheProvider()->Store($key, $value, $seconds);
    }

    public static function Clear($key = null)
    {
        return static::ActiveCacheProvider()->Clear($key);
    }

    /**
     * @param $key
     * @return bool
     */
    public static function Has($key)
    {
        return static::ActiveCacheProvider()->Has($key);
    }

}