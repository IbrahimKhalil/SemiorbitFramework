<?php
/*
*------------------------------------------------------------------------------------------------
* LANG - SEMIORBIT LOCALIZATION TOOL				 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Translation;



use Semiorbit\Support\Str;
use Semiorbit\Config\CFG;
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
		
		if ( ! in_array($lang, CFG::$Lang )) $lang = CFG::DefaultLang();
		
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
				
		$key = array_search($lang, CFG::$Lang);
		
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
		
		if ( ! is_empty ( self::$_DictIndex[ $key ] ) )  return self::ProcessValue( self::$_DictIndex[ $key ], $pms, $count );
		
		$key_parts = self::ParseKey($key);
		
		$dict_key = self::DictHas($key_parts);
		
		
		if ( $dict_key !== null ) self::$_DictIndex[ $key ] = &$dict_key;
		
		if ( $dict_key !== null && ! is_empty( $dict_key ) ) return self::ProcessValue( $dict_key, $pms, $count );
		
		if ( $dict_key !== null && is_empty( $dict_key ) ) return self::ProcessEmptyValue( $key_parts['keyword'], $default );
			
		
		
		$dict_keys = self::Import($key_parts['file'], $key_parts['package']);
		
		
		
		if ( ! is_empty( $dict_keys['locale'][ $key_parts['keyword'] ] ) ) {
			
			self::$_DictIndex[ $key ] = &$dict_keys['locale'][ $key_parts['keyword'] ];
			
			return self::ProcessValue( $dict_keys['locale'][ $key_parts['keyword'] ], $pms, $count );
		}
		
		if ( ! is_empty( $dict_keys['language'][ $key_parts['keyword'] ] ) ) {
			
			self::$_DictIndex[ $key ] = &$dict_keys['locale'][ $key_parts['keyword'] ];
			
			return self::ProcessValue( $dict_keys['language'][ $key_parts['keyword'] ], $pms, $count );
		}
		
		return self::ProcessEmptyValue($key_parts['keyword'], $default);		
		
	}
	
	private static function DictHas($key_parts)
	{
		
		$lang = self::ActiveLang();
		
		$language = self::Language();
		
		if (  $language != $lang ) :
		
			if ( ! is_empty( self::$_Dictionary[ $lang ][ $key_parts['package'] ][ $key_parts['file'] ][ $key_parts['keyword'] ] ) )
		
			return self::$_Dictionary[ $lang ][ $key_parts['package'] ][ $key_parts['file'] ][ $key_parts['keyword'] ];
		
		endif;
		
				
		if ( isset( self::$_Dictionary[ $language ][ $key_parts['package'] ][ $key_parts['file'] ][ $key_parts['keyword'] ] ) )
		
			return self::$_Dictionary[ $language ][ $key_parts['package'] ][ $key_parts['file'] ][ $key_parts['keyword'] ];
		
		else 
			
			return null;

	}
	
	private static function ProcessValue($value, $pms = [], $count = 0)
	{
		//TODO:: Improve translation to use countable words and other parameters
        
        if (!$pms || $count) return $value; //CHECK COUNT
		
		return $value;
	}
	
	private static function ProcessEmptyValue($keyword, $default = ':key')
	{
		if ( $default == ':key' ) return $keyword;
			
		else return $default;
	}
	
	public static function setTrans($key, $value)
	{
		
	}
	
	public static function ParseKey($key)
	{
		
		$key_parts = array('package'=>null, 'file'=>null, 'keyword'=>null);
		
		if ( is_empty( $key ) ) return $key_parts;
		
		$package_key = explode("::", $key);
		
		$package_key_count = count( $package_key );
		
		$key_parts['package'] = $package_key_count > 1 ? trim($package_key[0]) : 0;
		
		$file_keyword = $package_key_count > 1 ? $package_key[1] : $package_key[0];
		
		$file_key = explode(".", $file_keyword);
		
		$file_key_count = count( $file_key );
		
		$key_parts['file'] = $file_key_count > 1 ? trim($file_key[0]) : trim(CFG::$AppClass);
			
		$key_parts['keyword'] = $file_key_count > 1 ? trim($file_key[1]) : trim($file_key[0]);
			
		return $key_parts;
		
	} 
	
	public static function Dictionary()
	{
		return self::$_Dictionary[ self::Language() ];
	}
	
	private static function ImportGlobals()
	{
		
		$lang = self::ActiveLang();
		
		$language = self::Language();
		
		$ext = CFG::$LangPathExt;


		/**SEMIORBIT**/
		
		$path = Finder::LookFor( array(
				  
								'semiorbit/' . $language . '/' . $language . $ext  ,
								'semiorbit/' . $language . $ext,
								'semiorbit/' . $language . '/' . $language . '.inc',
								'semiorbit/' . $language . '.inc',
				
								'semiorbit/en/en.inc',
								'semiorbit/en.inc'
				
						), Finder::Lang, true);
		
		if ( $path ) include_once "{$path['path']}";
		
		
		/**APP**/
		
		$app_locale_path = Finder::LookFor( array(  $language . '/' . $lang . $ext, $lang . $ext ), Finder::Lang, true, true);
		
		if ( $app_locale_path ) include_once "{$app_locale_path['path']}";
		
		
		$app_lang_path = Finder::LookFor( array(  $language . '/' . $language . $ext, $language . $ext ), Finder::Lang, true, true);
		
		if ( $app_lang_path ) include_once "{$app_lang_path['path']}";
		
		
		
		/*DEPRECATED*/
		
		$GLOBALS['lang'] = $lang;
		
		if ( ! $app_lang_path )
		{	
			$path = Finder::LookFor( CFG::$Controllers. '_'.$lang.'.inc', Finder::Lang, true);
			if ($path) /** @noinspection PhpIncludeInspection */
				include_once $path['path'];
		}
		
	}
	
	
	
	private static function Import($file, $package = null, $force_reload = false)
	{
		
		$lang = self::ActiveLang();
		
		$language = self::Language();
		
		$ext = CFG::$LangPathExt;
		
		$package_path = is_empty( $package ) ? '' : $package . '/';
		
		$package_id = is_empty( $package ) ? 0 : $package;


		$file = trim($file);
		
		$dict_keys = array('language'=>null, 'locale'=>null);
		
		if ( isset( self::$_Dictionary[ $language ][ $package_id ][ $file ]  ) 
		  
		  && isset( self::$_Dictionary[ $lang ][ $package_id ][ $file ] ) 
		  
		  && $force_reload == false ) {
			
			$dict_keys['language'] = &self::$_Dictionary[ $language ][ $package_id ][ $file ];
			
			$dict_keys['locale']   = &self::$_Dictionary[ $lang ][ $package_id ][ $file ];
			
			return   $dict_keys;
		}
		
		$path = Finder::LookFor(array(
								
								$package_path . $language . '/' . $file . '.' . $language . $ext,
								$package_path . $file . '.' . $language . $ext,

                                $package_path . $language . '/' . Str::ParamCase( $file ) . '.' . $language . $ext,
                                $package_path . Str::ParamCase( $file ) . '.' . $language . $ext
								
								), Finder::Lang, true, $package_id == 'semiorbit' ? false : true);
			
		if ( $path ) /** @noinspection PhpIncludeInspection */
			self::$_Dictionary[ $language ][ $package_id ][ $file ] = include "{$path['path']}";
				
				else self::$_Dictionary[ $language ][ $package_id ][ $file ] = array();

			
		
		if ( $lang != $language ) :
			
		$locale = Finder::LookFor(array(
				
								$package_path . $language . '/' . $file . '.' . $lang . $ext,
								$package_path . $file . '.' . $lang . $ext,

                                $package_path . $language . '/' . Str::ParamCase( $file ) . '.' . $lang . $ext,
								$package_path . Str::ParamCase( $file ) . '.' . $lang . $ext
		
								), Finder::Lang, true, $package_id == 'semiorbit' ? false : true);
		
		if ( $locale ) /** @noinspection PhpIncludeInspection */
			self::$_Dictionary[ $lang ][ $package_id ][ $file ] = include "{$locale['path']}";
		
				  else self::$_Dictionary[ $lang ][ $package_id ][ $file ] = array();

		endif;
		
			
		$dict_keys['language'] = &self::$_Dictionary[ $language ][ $package_id ][ $file ];
				  	
		$dict_keys['locale']   = &self::$_Dictionary[ $lang ][ $package_id ][ $file ];

				  	
		return   $dict_keys;
				  
	}

	
}