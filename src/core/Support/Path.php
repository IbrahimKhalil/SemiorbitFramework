<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - STRING HELPER    					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Support;



class Path
{

    use ClipboardTrait {
        Clipboard as protected;
    }

    /**
     * Convert all backslashes to slashes and add a trailing slash by default.<br/>
     * eg. path\to\folder will be path/to/folder/
     *
     * @param string $path PathInfo string to clean
     * @param bool|null $leading_slash  TRUE = force add, FALSE = force remove, NULL = leave as is
     * @param bool|null $trailing_slash TRUE = force add, FALSE = force remove, NULL = leave as is
     * @return string cleaned path will be in this Format<br/> path/to/folder/
     */

    public static function Normalize($path, $leading_slash = null, $trailing_slash = true)
    {

        $path = str_replace( DIRECTORY_SEPARATOR, '/', $path );

        if ($leading_slash === null && $trailing_slash !== null ) $path = rtrim( $path, '/' );

        else if ($leading_slash !== null && $trailing_slash === null ) $path = ltrim( $path, '/' );

        else if ($leading_slash !== null && $trailing_slash !== null ) $path = trim( $path, '/' );

        $path = ( $leading_slash ? '/' : '' ) . $path . ( $trailing_slash ? '/' : '' );

        return $path;

    }

    /**
     * Convert all slashes to backslashes. Then trimming both leading and trailing backslashes by default <br/>
     * eg. path/to/class will be path\to\class
     *
     * @param string $path PathInfo string to clean
     * @param bool|null $leading_backslash  TRUE = force add, FALSE = force remove, NULL = leave as is
     * @param bool|null $trailing_backslash TRUE = force add, FALSE = force remove, NULL = leave as is
     * @return string cleaned path will be in this Format<br/> path/to/folder/
     */

    public static function NormalizeNamespace($path, $leading_backslash = false, $trailing_backslash = false)
    {

        $path = str_replace( '/', '\\', $path );

        if ($leading_backslash === null && $trailing_backslash !== null ) $path = rtrim( $path, '\\' );

        else if ($leading_backslash !== null && $trailing_backslash === null ) $path = ltrim( $path, '\\' );

        else if ($leading_backslash !== null && $trailing_backslash !== null ) $path = trim( $path, '\\' );

        $path = ( $leading_backslash ? '\\' : '' ) . $path . ( $trailing_backslash ? '\\' : '' );

        return $path;

    }

    /**
     * Check if path string refers to relative or absolute path
     *
     * @param $path
     * @return bool
     *
     * @see http://stackoverflow.com/questions/23570262/how-to-determine-if-a-file-path-is-absolute
     */

    public static function IsAbsolute($path)
    {

        $check = static::Clipboard('is_abs@' . $path);

        if ($check === null) {

            if ($path === null || $path === '') return false;

            $check = static::Clipboard('is_abs@' . $path, $path[0] === DIRECTORY_SEPARATOR || preg_match('~\A[A-Z]:(?![^/\\\\])~i', $path) > 0);
        }

        return $check;

    }

    /**
     * Find the relative path between two paths
     *
     * @param string $from From path
     * @param string $to To path
     * @return string
     *
     * @see http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php
     */

    public static function RelativePath($from, $to)
    {

        // some compatibility fixes for Windows paths

        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;

        $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;


        $from = str_replace('\\', '/', $from);

        $to   = str_replace('\\', '/', $to);


        $from     = explode('/', $from);

        $to       = explode('/', $to);

        $relPath  = $to;


        foreach( $from as $depth => $dir ) {

            // Find first non-matching dir

            if( $dir === $to[$depth] ) {

                // Ignore this directory

                array_shift($relPath);

            } else {

                // Get number of remaining dirs to $from

                $remaining = count($from) - $depth;

                if($remaining > 1) {

                    // Add traversals up to first matching dir

                    $padLength = ( count($relPath) + $remaining - 1 ) * -1;

                    $relPath = array_pad( $relPath, $padLength, '..' );

                    break;

                } else {

                    $relPath[0] = './' . $relPath[0];

                }

            }

        }

        return implode( '/', $relPath );

    }

    /**
     * Find relative path between a public path and current working directory path
     *
     * @param string $public_path
     * @return string
     */

    public static function PublicDirectory($public_path)
    {

        $public_path = Path::Normalize($public_path);

        $cwd = Path::Normalize( getcwd() ?: realpath('') );

        if ( substr($public_path, 0, strlen($cwd) ) == $cwd) {

            $public_dir = substr( $public_path, strlen($cwd) );

        } else {

            $public_dir = Path::RelativePath($public_path, $cwd);

        }

        return $public_dir;

    }

    /**
     * Get array item value by key path
     *
     * @param $data_array
     * @param $path
     * @param string $separator
     * @return mixed
     */

    public static function Value(&$data_array, $path, $separator = '.')
    {

        $keys = explode($separator, $path);

        foreach ($keys as $key) {

            if ( ! isset( $data_array[$key] ) ) return null;

            $data_array = &$data_array[$key];

        }

        return $data_array;

    }

    /**
     * Assign a value to an array item by key path
     *
     * @param array $data_array
     * @param string $path
     * @param mixed $value
     * @param string $separator
     */

    public static function AssignValue(&$data_array, $path, $value, $separator = '')
    {

        $keys = explode($separator, $path);

        foreach ($keys as $key) {

            $data_array = &$data_array[$key];

        }

        $data_array = $value;

    }

    /**
     * Returns class short name
     *
     * @param  string $argument Class fully qualified name or an object
     * @return string
     */

    public static function ClassShortName($argument)
    {
        if ( is_string($argument) )

            return static::Clipboard('csn@' . $argument) ?: static::Clipboard('csn@' . $argument,

                (new \ReflectionClass($argument))->getShortName() );

        else return (new \ReflectionClass($argument))->getShortName();
    }

    /**
     * Returns class file path & name
     *
     * @param  string $argument Class fully qualified name or an object
     * @return string
     */

    public static function ClassFileName($argument)
    {
        if ( is_string($argument) )

            return static::Clipboard('cfn@' . $argument) ?: static::Clipboard('cfn@' . $argument,

                (new \ReflectionClass($argument))->getFileName() );

        else return (new \ReflectionClass($argument))->getFileName();
    }

    /**
     * Remove directory with all files or sub-directories in it.
     *
     * @param $dir
     * @return bool
     */

    public static function RemoveDirectory($dir)
    {

        if (! $dir) return false;

        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);

        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach($files as $file) {

            if ($file->isDir()) {

                rmdir($file->getRealPath());

            } else {

                unlink($file->getRealPath());

            }

        }

        return rmdir($dir);

    }


}