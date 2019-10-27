<?php


namespace Semiorbit\Cache;


use Semiorbit\Base\Application;
use Semiorbit\Support\Path;



class ManagedFileCache implements FileCacheProviderInterface
{

    const DEFAULT_CACHE_DIR = 'var/cache/';

    protected $_CachePath;


    public function __construct($default_cache_dir = self::DEFAULT_CACHE_DIR)
    {
        $this->UseCachePath(Application::BasePath() . $default_cache_dir);
    }

    public function UseCachePath($path)
    {

        if (!file_exists( $path = Path::Normalize($path) ))

            mkdir($path, 0777, true);

        $this->_CachePath =  $path;

        return $this;

    }

    public function CachePath()
    {
        return $this->_CachePath;
    }


    public function Path($key)
    {
        return $this->_CachePath . $key;
    }


    public function Store($key, $value, $seconds = 0)
    {
        file_put_contents($this->Path($key), $value);

        return $this;
    }

    public function Read($key)
    {
        return file_get_contents("{$this->Path($key)}");
    }

    public function Clear($key = null)
    {
        return $key ? (unlink($this->Path($key . ($this->Has($key . '.php') ? '.php' : '')))) :

            Path::RemoveDirectory($this->CachePath());
    }

    /**
     * @param $key
     * @return bool
     */
    public function Has($key)
    {
        return file_exists($this->Path($key));
    }


    public function StoreVar($key, $value)
    {
        return $this->Store($key . '.php', '<?php return ' . var_export($value, true) . ';');
    }


    public function ReadVar($key)
    {
        /** @noinspection PhpIncludeInspection */
        return @include $this->Path("{$key}.php");
    }

}