<?php
/*
*-----------------------------------------------------------------------------------------------
* HTTP REQUEST PATH - SEMIORBIT REQUEST_URI PATH HELPER  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Http;



use Semiorbit\Support\AltaArray;
use Semiorbit\Support\AltaArrayKeys;


class PathInfo extends AltaArray
{

	protected $_Path = '';

    protected $_Pattern;

    protected $_NumArr = array();


    /**
     * PathInfo constructor.
     * @param $path
     * @param mixed $path_pattern
     * @param int $key_case
     */
    public function __construct($path, $path_pattern, $key_case = AltaArrayKeys::CI_LOWER)
    {

        parent::__construct( array(), $key_case );

        if ( is_string($path) ) {

            $this->_Path = $path;

            $this->_Pattern = $path_pattern;

            $this->Parse();

        }

    }


    public function &__get ($key)
    {
        $value = (  $this->offsetExists($key) ) ? parent::__get($key) : '';

        return $value;
    }

    public function &offsetGet($key)
    {

        if ( is_int($key) && isset( $this->_NumArr[$key] ) ) return $this->_NumArr[$key];

        $value = (  $this->offsetExists($key) ) ? parent::offsetGet($key) : '';

        return $value;

    }

    public function Param($key)
    {
        return $this->offsetGet($key);
    }


    public function Parse()
    {
        //TRIM SLASHES

        $path = trim($this->_Path,"/");

        $pattern = trim($this->_Pattern, "/");

        //CONVERT PATHS TO ARRAYS

        $pms = explode("/", $path);

        $pattern_pms = explode("/", $pattern);

        //CONVERT PMS TO ASSOC ARRAY

        $this->_NumArr = array();

        $assoc_pm = array();

        $n = 0;

        foreach ($pms as $pm) {

            //TODO: Check named params in path pattern that starts with ":"

            isset( $pattern_pms[$n] ) ? $k = ltrim( $pattern_pms[$n], ':' ) : $k = $n;

            $clean_pm = filter_var( $pm, FILTER_SANITIZE_STRING );

            $assoc_pm[$k] = $clean_pm;

            $this->_NumArr[] = $clean_pm;

            $n++;

        }

        $this->UseArray($assoc_pm);

        return $assoc_pm;
    }

    /**
     * @param string $path
     * @param string $path_pattern
     * @return PathInfo
     */

    public function UsePath($path, $path_pattern)
    {
        $this->_Path = $path;

        $this->_Pattern = $path_pattern;

        $this->Parse();

        return $this;
    }

    public function UsePattern($path_pattern)
    {

        $this->_Pattern = $path_pattern;

        $this->Parse();

        return $this;
    }

    public function Path()
    {
        return $this->_Path;
    }

    public function Pattern()
    {
        return $this->_Pattern;
    }

    public function __toString()
    {
        return $this->_Path;
    }

    //TODO: Request path pattern compile -- ref. FuelPHP

    /**
     * Compiles a route. Replaces named params and regex shortcuts.
     *
     * @return  string  compiled route.
     */
    /*
    protected function compile()
    {
        if ($this->path === '_root_')
        {
            return '';
        }

        $search = str_replace(array(
            ':any',
            ':alnum',
            ':num',
            ':alpha',
            ':segment',
        ), array(
            '.+',
            '[[:alnum:]]+',
            '[[:digit:]]+',
            '[[:alpha:]]+',
            '[^/]*',
        ), $this->path);

        return preg_replace('#(?<!\[\[):([a-z\_]+)(?!:\]\])#uD', '(?P<$1>.+?)', $search);
    }
    */
}