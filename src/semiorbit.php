<?php
/* 
 *---------------------------------------------------------------
 * SEMIORBIT 8 									       22-09-2023
 *---------------------------------------------------------------
 *
 * Semiorbit is a friendly, no fat code with the same taste of advanced PHP framework.
 * Its main goal is to provide most useful new "web apps" development techniques
 * in an easy-to-use, straight forward and lightweight tools.
 * In addition to rapid development process, Semiorbit has a flexible coding rules
 * with mostly configurable naming and skeleton conventions, so "Feel at Home!".
 * 			  
 *
 * @package	   SEMIORBIT FRAMEWORK
 * @version    8
 * @author     IBRAHIM KHALIL KHALBOUS
 * @copyright  © 2006 - 2023 SEMIORBIT
 * @license    MIT License					   http://opensource.org/licenses/MIT 
 * @link       https://framework.semiorbit.com
 */


/* 
 *---------------------------------------------------------------
 * Semiorbit Framework Version
 *---------------------------------------------------------------
 */

const SEMIORBIT_VERSION = '8';

/*
 *---------------------------------------------------------------
 * Semiorbit Startup Time
 *---------------------------------------------------------------
 */

define('SEMIORBIT_START_TIME', microtime(true));

/*
 *---------------------------------------------------------------
 * Semiorbit Framework PathInfo
 *---------------------------------------------------------------
 */

define('FW', rtrim( str_replace( DIRECTORY_SEPARATOR, '/', __DIR__ ), '/' ) . '/' );


/* 
 *---------------------------------------------------------------
 * Common Authentication Constants
 *---------------------------------------------------------------
 */

const SUPER_ADMIN = 'role_super_admin';

const ANY_AUTHENTICATED_USER = 'role_any_auth_user';
