<?php
const APPPATH = '';

const FW = '../../';

const PUBLICPATH = '';

const LANG = 'en';


/* Default Naming Conventions */
/* ==============================
 * 
 * 
 * namespace			PascalCase				Semiorbit\Field
 * 
 * class 				PascalCase				DataSet
 * 
 * class props			PascalCase				->TableName
 * 
 * class method			PascalCase | camelCase	->InsertRow() | ->insertRow() > see Note 3
 *
 * class private or
 *    protected props   _PascalCase				->$_FormInstance
 *
 *
 *
 * class props setters
 *        and getters   camelCase               ->setTitle($title); or ->$getTitle($title);
 *
 * events               camelCase              onEvent() > see Note 1
 *
 * object vars			camelCase				$newsComments or $myComments
 *
 *
 *
 * class const			UPPER_CASE				Form::CONST
 *
 *
 *
 * class method args	snake_case				->RenderWidget($flush_output)
 *  
 * global functions		snake_case				is_empty($var)
 * 
 * function args		snake_case				country_flag($country_code)
 * 
 * scalar vars			snake_case				$file_name
 *
 *  
 * 
 * * Note 1.
 * 	 -------
 *  Since prefixes (on, set, get) are serving a syntax role, so it is good to be in lower case to
 *  keep eye on meaningful words. Check the following
 *  examples:
 *
 *  Events: onEvent(); onSave(); onRead(); onInsertRow(); ... etc
 *
 * 
 * 
 * * Note 2.
 *   -------
 * * In Semiorbit\Field\Field and its extended classes (Text, Checkbox .. etc) keys/props	are	"Case Insensitive"
 * * 		 Title->Value = Title['value'] = Title['valuE']  :) don't care
 * 
 * *  by default, it has been used this way:
 * 
 * *		 'value'  for array keys  
 * *          ->Value for object props
 *  
 * * 		 $myNews->Title['value'] = $myNews->Title->Value
 * 
 * * Note 3.
 *   -------
 * * In PHP class methods and functions are "Case Insensitive" 
 * * so thanks PHP, we can use it the way we are accustomed to.
 * * to do that easily enable: "_semiorbit_ide_helper_camel_case"
 * 
 * 
 * 
 */