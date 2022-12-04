<?php


namespace Semiorbit\Cache;


use Semiorbit\Base\Application;
use Semiorbit\Support\Path;



class FileCacheProvider implements FileCacheProviderInterface
{

    const DEFAULT_CACHE_DIR = 'var/cache/';

    protected $_CachePath;

    protected $_Mode;


    const MODE_SERIALIZE = 0;

    const MODE_VAR_EXPORT = 1;


    public function __construct($mode = self::MODE_SERIALIZE, $default_cache_dir = self::DEFAULT_CACHE_DIR)
    {
        $this->_Mode = $mode;

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
        if ($this->_Mode == self::MODE_VAR_EXPORT) return $this->StoreVar($key, $value);

        file_put_contents($this->Path($key), serialize($value));

        return $this;
    }

    public function Read($key)
    {
        if ($this->_Mode == self::MODE_VAR_EXPORT) return $this->ReadVar($key);

        return unserialize(file_get_contents("{$this->Path($key)}"));
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
        return file_put_contents($this->Path("{$key}.php"), '<?php return ' . var_export($value, true) . ';');
    }


    public function ReadVar($key)
    {
        /** @noinspection PhpIncludeInspection */
        return @include $this->Path("{$key}.php");
    }

}