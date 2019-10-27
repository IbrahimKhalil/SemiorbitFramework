<?php


namespace Semiorbit\Cache;


class FrameworkCache extends Cache
{

    const FWK_CACHE_DIR = 'var/cache/fwk/';



    public static function ActiveCacheProvider()
    {
        return static::$__CacheProvider ?:

            static::$__CacheProvider = static::UseCacheProvider(

                new ManagedFileCache(self::FWK_CACHE_DIR)

            );
    }

    public static function StoreVar($key, $array)
    {

        return (static::ActiveCacheProvider() instanceof FileCacheProviderInterface) ?

            static::ActiveCacheProvider()->StoreVar($key, $array) :

            static::ActiveCacheProvider()->Store($key, $array);

    }

    public static function ReadVar($key)
    {

        return (static::ActiveCacheProvider() instanceof FileCacheProviderInterface) ?

            static::ActiveCacheProvider()->ReadVar($key) :

            static::ActiveCacheProvider()->Read($key);

    }


}