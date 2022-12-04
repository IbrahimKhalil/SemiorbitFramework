<?php
/*
*------------------------------------------------------------------------------------------------
* OUTPUT - SEMIORBIT OUTPUT HELPER				 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Output;



use Semiorbit\Base\AppManager;
use Semiorbit\Base\AppService;
use Semiorbit\Config\Config;
use Semiorbit\Support\Path;
use Semiorbit\Support\JavaScriptPacker;


class Output
{

    protected $_FilesList = array();

    protected $_OutputFile;

    protected $_OutputPath;

    protected $_Theme;

    protected $_App;


    /**
     * Combine multiple css or js files into one file. <br>
     * <b>This will automatically rebuild output if any of its content files has been changed.</b> <br><br>
     * <u>BUT  -to optimize perferormance in production-  it will NOT rebuild or check for changes when <b>debug mode</b> in environment (.env file) is FALSE unless output file was not found! </u><br>
     * To force rebuild when debug mode is false explicitly call ->Build() method. 
     * 
     * @param String $output Destination file name relative to selected theme.
     * @param array $files Array of files to be included in the pack.<br/> "<b>Add</b>" method could be used later too.
     * @param string|null $theme
     * @return Output
     */

    public static function Pack($output, $files = array(), $theme = null)
    {
        $package = new Output();

        $package->setOutputFile($output)->setTheme($theme);

        foreach ($files as $file) {

            $package->Add($file);

        }
        
        return $package;
    }

    /**
     * Add a css/js file from asstes to the pack.
     *
     * @param string $fn Css/js file path realative to selected theme folder
     * @param bool $compress Minify file then add to pack
     * @param bool $watch Check changes everytime to update pack
     * @param null $theme Theme folder name or NULL for default theme
     * @return static
     */

    public function AddAsset($fn, $compress = false, $watch = false, $theme = null)
    {
        $this->_FilesList[] = array("fn" => Path::Normalize($fn, false, false),

                                    "compress" => $compress, "watch" => $watch, "theme" => $theme, "path" => $this->App()->AssetPath($fn));

        return $this;
    }


    /**
     * Add a css/js file from anywhere to the pack.
     *
     * @param string $fn Css/js file <b>absolute path</b>
     * @param bool $watch Check changes everytime to update pack
     * @return static
     */

    public function Add($fn, $watch = false)
    {
        $this->_FilesList[] = array("fn" => $fn,

            "compress" => false, "watch" => $watch, "theme" => "", "path" => $fn);

        return $this;
    }


    protected function NeedsUpdate()
    {

        if (! file_exists($this->OutputPath())) return true;

        $output_mtime = filemtime($this->OutputPath());

        foreach ($this->_FilesList as $file) {

            if ($file['watch']) {

                $file_mtime = filemtime($file['path']);

                if ($file_mtime > $output_mtime) return true;

            }

        }

        return false;

    }

    public function Buid()
    {

        if (! $this->NeedsUpdate()) return true;

        $data = '';

        foreach ($this->_FilesList as $file) {

            $data .= ( $file['compress'] ? static::Minify($file['fn'], $file['theme'], false) :

                        file_get_contents($file['path']) );

        }

        $res = file_put_contents($this->OutputPath(), $data);

        return $res;

    }

    public function __toString()
    {

        if (Config::DebugMode() || ! file_exists($this->OutputPath())) 
            
            $this->Buid();

        return $this->OutputUrl();
        
    }

    public static function Minify($fp, $theme = null, $return_url = true)
	{

	    $app = AppManager::MainApp();

        $data = '';
	
		##MAINTAIN PATHS##
	
		$fp = ltrim($fp, '/');
	
	
		##GET FILE TYPE##
	
		$fp_fn_arr = explode(".", $fp);
	
		$fp_ext = array_pop( $fp_fn_arr );
	
		#### FP MIN ####
	
		$fp_fn = implode(".", $fp_fn_arr);
	
		$fp_min = $fp_fn . ".min." .$fp_ext;
	
	
		##CHECK IF MIN FILE EXISTS##
	
		$fp_exists = file_exists($app->AssetPath($fp, $theme));

        if (! $fp_exists) return '';

		$fp_min_exists = file_exists($app->AssetPath($fp_min, $theme));

	
		##YES >> COMPARE LAST MOD DATE WITH ORIGINAL FILE##
	
		$orig_is_newer = false;
	
		if ($fp_min_exists) {

		    $fp_lmod = filemtime($app->AssetPath($fp, $theme));

            $fp_min_lmod = filemtime($app->AssetPath($fp_min, $theme));

            if ( $fp_lmod > $fp_min_lmod ) $orig_is_newer = true;
	
		}
	
	
		##ORIG FILE IS NEWER || MIN FILE NOT FOUND ? RE/BUILD MIN FILE >> ##
	
	
		if ($orig_is_newer || $fp_min_exists == false) {

            switch (strtolower($fp_ext)) :

                ##CSS MINIFY >>##

                case "css":

                    $data = static::CompressCss(file_get_contents($app->AssetPath($fp, $theme)));

                    break;

                ##JS MINIFY >>##

                case "js":

                    $MyPacker = new JavaScriptPacker(file_get_contents($app->AssetPath($fp, $theme)), 'Normal', false, false);

                    $data = $MyPacker->pack();

                    break;

            endswitch;

            file_put_contents($app->AssetPath($fp_min, $theme), $data);

        } else if (! $return_url) {

            $data = file_get_contents($app->AssetPath($fp_min, $theme));

        }
	
		## << RETURN MIN FILE HTTP PATH##
	
		return $return_url ? $app->Asset($fp_min, $theme) : $data;
	
	}
	
	public static function CompressCss($buffer) 
	{

	    //TODO: Buffering big css files

		/* remove comments */
		$buffer = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", "", $buffer);

		/* remove tabs, spaces, newlines, etc. */
		$buffer = str_replace(array("\r\n","\r","\t","\n",'  ','    ','     '), '', $buffer);

		/* remove other spaces before/after ) */
		$buffer = preg_replace(array('(( )+\))','(\)( )+(^[-+*/]))'), ')', $buffer);
		
		return $buffer;
	}
	
	
	public static function Sanitize($buffer) 
	{
	
		$search = array(
			'/\>[^\S ]+/s',  // strip whitespaces after tags, except space
			'/[^\S ]+\</s',  // strip whitespaces before tags, except space
			'/(\s)+/s'       // shorten multiple whitespace sequences
		);
	
		$replace = array(
				'>',
				'<',
				'\\1'
				);
	
		$buffer = preg_replace($search, $replace, $buffer);
	
		return $buffer;
		
	}

    /**
     * @param mixed $OutputFile
     * @return static
     */
    public function setOutputFile($OutputFile)
    {

        $this->_App = AppManager::MainApp();

        $this->_OutputFile = $OutputFile;

        $this->_OutputPath = $this->App()->AssetPath($OutputFile);

        return $this;

    }

    /**
     * @return mixed
     */
    public function OutputFile()
    {
        return $this->_OutputFile;
    }

    /**
     * @param mixed $theme
     * @return static
     */
    public function setTheme($theme = null)
    {
        if ( is_empty( $theme ) )

            $this->_Theme = AppManager::MainApp()->ThemePath();

        $this->_Theme = $theme;

        return $this;
    }

    /**
     * @return mixed
     */
    public function Theme()
    {
        if (! $this->_Theme) $this->setTheme();

        return $this->_Theme;
    }

    /**
     * @return array
     */
    public function FilesList()
    {
        return $this->_FilesList;
    }

    public function OutputPath()
    {
        return $this->_OutputPath;
    }

    public function OutputUrl()
    {
        return $this->App()->Asset($this->OutputFile());
    }

    /**
     * @return AppService
     */

    public function App()
    {
        return $this->_App;
    }
}
