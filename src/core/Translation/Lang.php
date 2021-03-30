<?php
/*
*------------------------------------------------------------------------------------------------
* LANG - SEMIORBIT LOCALIZATION TOOL				 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Translation;



use Semiorbit\Component\Package;
use Semiorbit\Support\Str;
use Semiorbit\Config\Config;
use Semiorbit\Component\Finder;

class Lang
{
	
	private static $_Lang;
	
	private static $_Locale;
	
	private static $_Language;
	
	private static $_Dictionary = array();
	
	private static $_DictIndex = array();
	
	private static $_LangInstance;
	
	
	private static function LangInstance()
	{
			
		if ( self::$_LangInstance  === null ) {
	
			self::$_LangInstance = new Lang();
				
		}
	
		return self::$_LangInstance;
	
	}
	
	
	public static function UseLang($lang)
	{
		
		if ( ! in_array($lang, Config::Languages() )) $lang = Config::DefaultLang();
		
		self::$_Lang = $lang;
		
		if ( ! defined('LANG') )	define( 'LANG', $lang );
		
		self::ImportGlobals();
		
		return self::LangInstance();
	
	}
	
	public static function ActiveLang()
	{
		return self::$_Lang;
	}
	
	public static function Locale($lang = null)
	{
		
		if ($lang === null && ! is_empty( self::$_Locale ) ) return self::$_Locale;
		
		if ( is_empty($lang) ) $lang = self::ActiveLang();
				
		$key = array_search($lang, Config::Languages());
		
		if ($key == 'default') $key = $lang;
		
		if ( ! $key ) return false;
		
		if ( ! is_string($key) || is_empty($key) ) return $lang; 

		if ( $lang === self::ActiveLang() ) self::$_Locale = $key;
		
		return $key;
		
	}

	
	public static function Language($lang = null)
	{
		
		if ($lang === null && ! is_empty( self::$_Language ) ) return self::$_Language;
				
		$locale = self::Locale( $lang );
		
		$locale = str_replace("-", "_", $locale);
		
		$locale = strtolower( explode("_", $locale)[0] );
		
		return self::$_Language = $locale;
		
	} 
	
	public static function Trans($key, $pms = [], $count = 0, $default = ':key')
	{


		if ( isset( self::$_DictIndex[ $key ] ) )

		    return self::ProcessValue( self::$_DictIndex[ $key ], $pms, $count );


		
		[$pkg, $file, $keyword] = self::ParseKey($key);
		
		$dict_key = self::DictHas($pkg, $file, $keyword);
		
		
		if ( $dict_key !== null ) {

		    self::$_DictIndex[ $key ] = &$dict_key;

            return ( is_empty( $dict_key ) ) ? self::ProcessEmptyValue($keyword, $default)

                : self::ProcessValue( $dict_key, $pms, $count );
        }
		

		
		$dict_keys = self::Import($file, $pkg);
		

		
		if ( ! is_empty( $dict_keys['locale'][ $keyword ] ) ) {
			
			self::$_DictIndex[ $key ] = &$dict_keys['locale'][ $keyword ];
			
			return self::ProcessValue( $dict_keys['locale'][ $keyword ], $pms, $count );
		}
		
		if ( ! is_empty( $dict_keys['language'][ $keyword ] ) ) {
			
			self::$_DictIndex[ $key ] = &$dict_keys['language'][ $keyword ];
			
			return self::ProcessValue( $dict_keys['language'][ $keyword ], $pms, $count );
		}
		
		return self::ProcessEmptyValue($keyword, $default);
		
	}
	
	private static function DictHas($pkg, $file, $keyword)
	{
		
		$lang = self::ActiveLang();
		
		$language = self::Language();
		

		$res = (  $language != $lang ) ?

            self::$_Dictionary[ $lang ][ $pkg ][ $file ][ $keyword ] ?? null : null;
		

		return $res ?: self::$_Dictionary[ $language ][ $pkg ][ $file ][ $keyword ] ?? null;

	}
	
	private static function ProcessValue($value, $pms = [], $count = 0)
	{
		//TODO:: Improve translation to use countable words and other parameters
        
        if (!$pms || $count) return $value; //CHECK COUNT
		
		return $value;
	}
	
	private static function ProcessEmptyValue($keyword, $default = ':key')
	{
		return ( $default == ':key' ) ? $keyword : $default;
	}
	
	public static function setTrans($key, $value)
	{
		
	}
	
	public static function ParseKey($key)
	{
		
		[$pkg, $path] = strpos($key, '::') ? explode("::", $key, 2) : [null, $key];
		
		[$file, $keyword] = strpos($path, '.') ? explode(".", $path) : [null, $path];
			
		return [$pkg, $file, $keyword];
		
	} 
	
	public static function Dictionary()
	{
		return self::$_Dictionary[ self::Language() ];
	}
	
	private static function ImportGlobals()
	{
		
		$lang = self::ActiveLang();
		
		$language = self::Language();
		
		$ext = Config::LangExt();


		/**SEMIORBIT**/
		
		$path = Finder::LookFor([
				  
		    'semiorbit/' . $language . '/' . $language . $ext,

            'semiorbit/' . $language . $ext,

            'semiorbit/' . $language . '/' . $language . '.inc',

            'semiorbit/' . $language . '.inc',
				
            'semiorbit/en/en.inc',

            'semiorbit/en.inc'
				
        ], Finder::Lang, true);
		
		if ( $path ) include_once "{$path['path']}";
		
		
		/**APP**/
		
		$app_locale_path = Finder::LookFor( array(  $language . '/' . $lang . $ext, $lang . $ext ), Finder::Lang, true, true);
		
		if ( $app_locale_path ) include_once "{$app_locale_path['path']}";
		
		
		$app_lang_path = Finder::LookFor( array(  $language . '/' . $language . $ext, $language . $ext ), Finder::Lang, true, true);
		
		if ( $app_lang_path ) include_once "{$app_lang_path['path']}";

		
	}
	
	
	
	private static function Import($file, $package = null, $force_reload = false)
	{
		
		$lang = self::ActiveLang();
		
		$language = self::Language();
		
		$ext = Config::LangExt();
		
		$package_path = $package ? $package . '::' : '';
		
		$package_id = $package ?: 0;

		
		$dict_keys = array('language'=>null, 'locale'=>null);
		
		if ( isset( self::$_Dictionary[ $language ][ $package_id ][ $file ]  ) 
		  
		  && isset( self::$_Dictionary[ $lang ][ $package_id ][ $file ] ) 
		  
		  && $force_reload == false ) {
			
			$dict_keys['language'] = &self::$_Dictionary[ $language ][ $package_id ][ $file ];
			
			$dict_keys['locale']   = &self::$_Dictionary[ $lang ][ $package_id ][ $file ];
			
			return   $dict_keys;
		}
		
		$path = Finder::LookFor([
								
		    $package_path . $language . '/' . $file . '.' . $language . $ext,

            $package_path . $file . '.' . $language . $ext,

            $package_path . $language . '/' . Str::ParamCase( $file ) . '.' . $language . $ext,

            $package_path . Str::ParamCase( $file ) . '.' . $language . $ext
								
        ], Finder::Lang, true, $package_id !== 'semiorbit');
			
		if ( $path )

		    /** @noinspection PhpIncludeInspection */

			self::$_Dictionary[ $language ][ $package_id ][ $file ] = include "{$path['path']}";
				
			else self::$_Dictionary[ $language ][ $package_id ][ $file ] = array();

			
		
		if ( $lang != $language ) {

            $locale = Finder::LookFor([

                $package_path . $language . '/' . $file . '.' . $lang . $ext,

                $package_path . $file . '.' . $lang . $ext,

                $package_path . $language . '/' . Str::ParamCase($file) . '.' . $lang . $ext,

                $package_path . Str::ParamCase($file) . '.' . $lang . $ext

            ], Finder::Lang, true, $package_id !== 'semiorbit');

            if ($locale)

                /** @noinspection PhpIncludeInspection */

                self::$_Dictionary[$lang][$package_id][$file] = include "{$locale['path']}";

                else self::$_Dictionary[$lang][$package_id][$file] = array();

        }
		
			
		$dict_keys['language'] = &self::$_Dictionary[ $language ][ $package_id ][ $file ];
				  	
		$dict_keys['locale']   = &self::$_Dictionary[ $lang ][ $package_id ][ $file ];

				  	
		return   $dict_keys;
				  
	}

	
}