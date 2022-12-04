<?php
/*
*------------------------------------------------------------------------------------------------
* SEMIORBIT - FORM BUILDER    					 					 semiorbit.com
*------------------------------------------------------------------------------------------------
*/

namespace Semiorbit\Form;



use Semiorbit\Field\File;
use Semiorbit\Http\Url;
use Semiorbit\Support\ClipboardTrait;
use Semiorbit\Field\Field;
use Semiorbit\Config\Config;
use Semiorbit\Data\DataSet;
use Semiorbit\Support\Str;

class Form
{

	private static $_Counter = 1;
	
	private static $_IsOpen = false;
	
	private static $_FormInstance; 
	
	private static $_DataSet;
	
	private static $_FormOptions;
	
	private static $_FormTemplate;
	
	private static $_FrameSet;
	
	
	
	//<editor-fold desc="FORM ATTRS">
	
	private static $_ID = 'semiorbit-form1';

	private static $_Method = 'post';
	
	private static $_Action;
	
	private static $_CssClass;
	
	private static $_EncType;
	
	private static $_InputHashAlgo = 'md5';
	
	private static $_ExpireTime = 86400; // 1 Day
	
	private static $_HoneypotsMax;
	
	private static $_HoneypotsLabels;

    private static $_SubmitLabel;

    private static $_SubmitID;

    private static $_Verify;

    private static $_DefaultTemplate;

    private static $_ReadOnly;

    //</editor-fold>

    //<editor-fold desc="FormOptions">

    const ACTION = "action";

    const CSSCLASS = "cssclass";

    const ID = "id";

    const ENCTYPE = "enctype";

    const METHOD = "method";

    const INPUT_HASH_ALGO = "input_hash_algo";

    const EXPIRE_TIME = "expire_time";

    const HONEYPOTS_MAX = "honeypots_max";

    const HONEYPOTS_LABELS = "honeypots_labels";

    const VERIFY = "verify";

    const SUBMIT_LABEL = "submit_label";

    const SUBMIT_ID = "submit_id";

    const DEFAULT_TEMPLATE = "default_template";

    const READONLY = "read_only";

    //</editor-fold>

    //<editor-fold desc="ShowVerificationCode">

    const VERIFY_NONE = 0;

    const VERIFY_NEW_ONLY = 1;

    const VERIFY_EDIT_ONLY = 2;

    const VERIFY_ALWAYS = 3;

    //</editor-fold>


    use ClipboardTrait { Clipboard as protected; }

	/**
	 * @return static
	 */

	private static function FormInstance()
	{
		 
		if ( static::$_FormInstance  === null ) {

			static::$_FormInstance = new Form();
			
		} 
		
		return static::$_FormInstance;
		
	}
	
	public static function IsOpen()
	{
		return static::$_IsOpen;
	}
	

	public static function Open( $flush_output = true, $form_options = array() )
	{
		
		static::setFormOptions( $form_options );
		
		static::$_IsOpen = true;

        $html_output = static::ActiveTemplate()->OpenForm(static::ID(), $flush_output, static::FormOptions());
		
		$html_output .= static::CSRF( $flush_output );

        $html_output .= static::FormIdInput( $flush_output );
		
		return $html_output;
		
	}
	
	public static function Close( $flush_output = true )
	{
		
		$html_output = static::ActiveTemplate()->CloseForm( $flush_output, static::FormOptions() );
		
		$html_output .= static::LoadJs( $flush_output );
		
		static::Reset();
		
		return $html_output;
		
	}

    public static function Reset()
    {

        static::$_Counter ++;

        static::$_IsOpen = false;

        static::$_FormInstance = null;

        static::ResetFormOptions();

        static::$_DataSet = null;

        static::$_FormTemplate = null;

    }
	
	public static function CSRF( $flush_output = true )
	{
	
		$token = static::Token();
		
		$token_control_name = static::TokenControlName( false );
		
		$html_output = <<<HEREHTML

						<input type="hidden" name="{$token_control_name}" value="{$token}" />
HEREHTML;
		
		if ($flush_output) echo $html_output;
		
		return $html_output;
		
		
	}

    public static function FormIdInput( $flush_output = true )
    {

        $form_id = base64_encode( static::ID() );

        $form_id_input_name = static::FormIdInputName();

        $html_output = <<<HEREHTML

						<input type="hidden" name="{$form_id_input_name}" value="{$form_id}" />
HEREHTML;

        if ($flush_output) echo $html_output;

        return $html_output;


    }

    public static function IsSubmit($form = null, $method = null)
	{

	    if (static::ReadOnly()) return false;

		if ( ! $method ) $method = Form::Method();

		if ( ! $form ) $form = Form::FormIdInputValue();


        if ( static::Clipboard("{$form}.{$method}:is_submit") === true ) {

            //echo "<div style='height:20px;background:yellow;'>OK :)</div>";

            return true;

        }


        if ( $_SERVER['REQUEST_METHOD'] === strtoupper( $method ) ) {

            if ( ! static::CheckHoneypots() ) return false;

            //echo "<div style='height:20px;background:blue;color:#fff;'>{$_SERVER['REQUEST_METHOD']} =? " . $method . "</div>";

            $token_age = time() - static::InputTokenTime( $form );

            //echo "<div style='height:20px;background:lightblue;color:#fff;'>{$token_age} <-> " . static::ExpireTime() . "</div>";

            if ( $token_age > static::ExpireTime() ) {

                //echo "<div style='height:20px;background:orange;color:#fff;'>{$token_age} > " . static::ExpireTime() . "</div>";

                return false;

            }


			//dd('0>'.static::TokenControlName( true, $form ));

			//dd('1>'.static::InputByName( static::TokenControlName( true, $form ), false, $form ));

			//dd('2>'.static::InputToken( $form ) );




            //Check if the token value come from request (form post or get) is equal to token value stored in session

            if ( hash_equals( strval( static::InputByName( static::TokenControlName( true, $form ), false, $form ) ),

                                strval( static::InputToken( $form ) ))) {

                static::Clipboard("{$form}.{$method}:is_submit", true);

                //echo "<div style='height:20px;background:green;color:#fff;'>OK :)</div>";

                return true;
            }

        }

        //echo "<div style='height:20px;background:red;color:#fff;'>NOPE :( </div> ";

        return false;

		
	}
	
	public static function Token($form = null)
	{


		$form = static::ID( $form );

        if ( is_empty( static::Clipboard( $form . ':token' ) ) )
		{

            static::Clipboard( $form . ':input_token', isset ( $_SESSION[ $form . ':token' ] ) ? $_SESSION[ $form . ':token' ] : null );

            // if token already generated for session it will be used again
            // Ref:
			static::Clipboard( $form . ':token', isset( $_SESSION[ $form . ':token' ] ) ? $_SESSION[ $form . ':token' ]

                : hash_hmac( 'sha256',  $form . $_SERVER['HTTP_USER_AGENT'], static::GenerateRandomToken() ));

			if ( ! isset( $_SESSION[ $form . ':token' ] ) ) $_SESSION[ $form . ':token' ] = static::Clipboard( $form . ':token' );

		}

		return static::Clipboard( $form . ':token' );

	}

    /**
     * @return string
     * @link http://stackoverflow.com/questions/6287903/how-to-properly-add-csrf-token-using-php
     */

	public static function GenerateRandomToken()
    {
        return Str::GenerateRandomToken();
    }

	public static function InputToken($form = null)
	{

		$form = static::ID( $form );

		static::Token( $form );

		return static::Clipboard(  $form . ':input_token' );

	}

	public static function TokenTime($form = null)
	{

		$form = static::ID( $form );

		if ( is_empty( static::Clipboard( $form . ':token_time' ) )) {

			static::Clipboard( $form . ':input_token_time', $_SESSION[$form . ':token_time'] ?? time());

			static::Clipboard( $form . ':token_time',  time() );

            //TODO: Form per request time and token changing or managing

			$_SESSION[ $form . ':token_time' ] = static::Clipboard( $form . ':token_time' );

		}
		
		return static::Clipboard( $form . ':token_time' );
		
	}
	
	public static function InputTokenTime($form = null)
	{
		
		$form = static::ID( $form );
		
		static::TokenTime( $form );
		
		return static::Clipboard( $form . ':input_token_time' );
		
	}
	
	public static function TokenControlName($input_source = true, $form = null )
	{
		
		$form = static::ID( $form );

		
		if ( $input_source ) {

			$token_control_name = static::Clipboard(  $form . ':src_token_control_name' ) ?:
				
								static::Clipboard(  $form . ':src_token_control_name', hash( Form::InputHashAlgo(), $form . '_token' . static::InputToken( $form ) ));
			
		} else {
			
			$token_control_name = static::Clipboard(  $form . ':token_control_name' ) ?:
			
								static::Clipboard(  $form . ':token_control_name', hash( Form::InputHashAlgo(), $form . '_token' . static::Token( $form ) ));
			
		}


		return $token_control_name;
		
	}

    public static function FormIdInputName()
    {
        return static::Clipboard('form_id_input_name') ?:

            static::Clipboard('form_id_input_name', hash( Form::InputHashAlgo(), 'FormID' ) );
    }

    public static function FormIdInputValue($method = 'post')
    {

        $form_id_input_value = static::Clipboard( "{$method}:form_id_input_value" );

        if ( empty( $form_id_input_value ) ) {


            $input_name = static::FormIdInputName();


            $value = null;


            switch ($method) :

                case 'get':

                    if ( isset( $_GET[$input_name] ) ) $value = $_GET[$input_name];

                    break;

                case 'post':

                    if ( isset( $_POST[$input_name] ) ) $value = $_POST[$input_name];

                    break;

            endswitch;

            $form_id_input_value = static::Clipboard( "{$method}:form_id_input_value", base64_decode( $value ) );

        }

        return $form_id_input_value;

    }
	
	
	public static function Submit($submit_label = SAVE, $flush_output = true, $submit_id = null)
	{
	
		$pms['submit_label'] = $submit_label;
	
		$pms['submit_id'] = is_empty($submit_id) ? static::ID() . '_submit' : $submit_id;
		
		static::setFormOptions($pms);

        $html_output = '';

        if ( ! static::ReadOnly() ) {

            $html_output = static::ActiveTemplate()->RenderSubmit($flush_output, static::FormOptions());

        }
	
		return $html_output;
	
	}
	
	public static function ShowVerificationCode($verify = Form::VERIFY_NEW_ONLY, $flush_output = true)
	{
	
		if ( $verify == Form::VERIFY_NONE || static::ReadOnly() ) return "";
		
		$html_output = static::ActiveTemplate()->RenderVerification($flush_output, static::FormOptions());
	
		return $html_output;
	
	}
	
	public static function Render($flush_output = true, $form_options = array(), \Closure $on_submit = null)
	{
		if ( ! static::ActiveDataSet() || static::IsOpen() ) return false;
		
		$html_output = '';
	
		$myDataSet = static::ActiveDataSet();
		
		static::setCssClass('semiorbit-form-' . static::ActiveDataSet()->TableName());
		
		$myDataSet->onRenderStart();

        if ( ! isset( $form_options[ Form::HONEYPOTS_MAX ] ) )

            static::setHoneypotsMax( Config::HoneypotsMax() );


		$html_output .= static::Open($flush_output, $form_options);
		
		$html_output .= static::RenderControls($flush_output, static::FormOptions());
		
		$html_output .= static::ShowVerificationCode(static::Verify(), $flush_output);
		
		$html_output .= static::Submit(static::SubmitLabel(), $flush_output, static::SubmitID());
		
		$html_output .= static::Close($flush_output);
	
		$myDataSet->onRenderComplete();

        if ( static::IsSubmit() && $on_submit ) {

            $on_submit( $myDataSet, $html_output );

        }
		
		return $html_output;
		
	}
	
	
	public static function RenderControls($flush_output = true, $pms = array())
	{
		if ( ! static::ActiveDataSet() ) return false;
	
		$html_output = '';

		
		foreach (static::ActiveDataSet()->Groups() as $group)
		{
			$html_output .= static::ActiveTemplate()->OpenGroup($group, $flush_output);
			
			$html_output .= static::RenderGroupControls($group, $flush_output, $pms);

			$html_output .= static::ActiveTemplate()->CloseGroup($group, $flush_output);
					
		}
	
		return $html_output;
	
	}
	
	public static function RenderGroupControls($group, $flush_output = true, $pms = array())
	{
		if ( ! static::ActiveDataSet() || ! isset( $group['items'] ) ) return false;
	
		$html_output = '';

		foreach ($group['items'] as $k => $field)
		{

			$html_output .= static::RenderControl(static::ActiveDataSet()->Field( $k ), $flush_output, $pms);
	
		}
	
		return $html_output;
	
	}
	
	public static function RenderControl(Field $field, $flush_output = true, $pms = array())
	{

		if ( empty( $field ) ) return false;

		$html_output = $field->RenderControl( $flush_output, $pms );
		
		$html_output .= static::RenderHoneypot( $flush_output );
		
		return $html_output;
	
	}
	
	public static function RenderHoneypot($flush_output = true)
	{ 
		
		if ( ! Form::IsOpen() ) return false;
		
		if ( static::HoneypotsMax() == 0 ) return false;
		
		$nom = rand( 1, static::HoneypotsMax() );

		if ( isset( static::Clipboard()[ static::ID() . ':honeypot' ][ $nom ] ) ) return false;
		
		$honeypot_id  =  hash(static::InputHashAlgo(), uniqid( rand(), True ) . $nom ) ;

        static::Clipboard()[ static::ID() . ':honeypot_id' ][ $nom ] = $honeypot_id;

        $honeypot_input_name = hash(static::InputHashAlgo(), static::ID() . ':honeypot:' . $nom . static::Token() );

        static::Clipboard()[ static::ID() . ':honeypot' ][ $nom ] = $honeypot_input_name;

        $arr_h_pots =  static::HoneypotsLabels();

        shuffle( $arr_h_pots );

		$honeypot_label = array_shift( $arr_h_pots );
		
		$_SESSION[ '_honeypot_id' ][ $nom ] =  $honeypot_id; 

		$_SESSION[ static::ID() . ':honeypot' ] = static::Clipboard()[ static::ID() . ':honeypot' ];
		
		$html_output = <<<HEREHTML
		
						<div id="{$honeypot_id}" style="display: none;">
							{$honeypot_label}
							<input type="hidden" name="{$honeypot_input_name}" value="" />
						</div>
HEREHTML;
		
		if ($flush_output) echo $html_output;
		
		return $html_output;
		
	}
	
	public static function CheckHoneypots()
	{
		
		if ( static::HoneypotsMax() == 0 ) return true;
		
		if (  ! isset( $_SESSION[ static::ID() . ':honeypot' ] ) ) return false;
		
		foreach ( $_SESSION[ static::ID() . ':honeypot' ] as $nom => $honeypot_name ) {
			
			$honeypot_val = static::InputByName( $honeypot_name, false );
			
			if ( ! is_empty( $honeypot_val ) ) return false;
			
		}
		
		return true;
		
	}
	
	
	//===================================OPTIONS=================================================

	public static function setFormOptions($form_options)
	{
		
		if ( ! is_empty( $form_options[ Form::ID ] ) ) static::setID( $form_options[ Form::ID ] );
		
		unset( $form_options[ Form::ID ] );

		if ( ! is_empty( $form_options[ Form::ACTION ] ) ) static::setAction( $form_options[ Form::ACTION ] );
		
		unset( $form_options[ Form::ACTION ] );
		
		if ( ! is_empty( $form_options[ Form::METHOD ] ) )  static::setMethod( $form_options[ Form::METHOD ] );
		
		unset( $form_options[ Form::METHOD ] );
		
		if ( ! is_empty( $form_options[ Form::ENCTYPE ] ) ) static::setEncType( $form_options[ Form::ENCTYPE ] );
		
		unset( $form_options[ Form::ENCTYPE ] );
		
		if ( ! is_empty( $form_options[ Form::CSSCLASS ] ) ) static::setCssClass( $form_options[ Form::CSSCLASS ] );
		
		unset( $form_options[ Form::CSSCLASS ] );
		
		if ( ! is_empty( $form_options[ Form::EXPIRE_TIME ] ) ) static::setExpireTime( $form_options[ Form::EXPIRE_TIME ] );
		
		unset( $form_options[ Form::EXPIRE_TIME ] );
		
		if ( ! is_empty( $form_options[ Form::INPUT_HASH_ALGO ] ) ) static::setInputHashAlgo( $form_options[ Form::INPUT_HASH_ALGO ] );
		
		unset( $form_options[ Form::INPUT_HASH_ALGO ] );
		
		if ( ! is_empty( $form_options[ Form::HONEYPOTS_MAX ] ) ) static::setHoneypotsMax( $form_options[ Form::HONEYPOTS_MAX ] );
		
		unset( $form_options[ Form::HONEYPOTS_MAX ] );
		
		if ( ! is_empty( $form_options[ Form::HONEYPOTS_LABELS ] ) ) static::setHoneypotsLabels( $form_options[ Form::HONEYPOTS_LABELS ] );
		
		unset( $form_options[ Form::HONEYPOTS_LABELS ] );

        if ( ! is_empty( $form_options[ Form::VERIFY ] ) ) static::setVerify( $form_options[ Form::VERIFY ] );

        unset( $form_options[ Form::VERIFY ] );

        if ( ! is_empty( $form_options[ Form::SUBMIT_ID ] ) ) static::setSubmitID( $form_options[ Form::SUBMIT_ID ] );

        unset( $form_options[ Form::SUBMIT_ID ] );

        if ( ! is_empty( $form_options[ Form::SUBMIT_LABEL ] ) ) static::setSubmitLabel( $form_options[ Form::SUBMIT_LABEL ] );

        unset( $form_options[ Form::SUBMIT_LABEL ] );

        if ( ! is_empty( $form_options[ Form::DEFAULT_TEMPLATE ] ) ) static::setDefaultTemplate( $form_options[ Form::DEFAULT_TEMPLATE ] );

        unset( $form_options[ Form::DEFAULT_TEMPLATE ] );

        if ( ! is_empty( $form_options[ Form::READONLY ] ) ) static::setReadOnly( $form_options[ Form::READONLY ] );

        unset( $form_options[ Form::READONLY ] );
		
		static::$_FormOptions = array_merge( static::FormOptions(), $form_options );
		
		return static::FormInstance();
			
	}
	
	public static function FormOptions()
	{
		
		if ( is_empty( static::$_FormOptions ) ) static::$_FormOptions = [];
		
		static::$_FormOptions[ Form::ACTION ] = static::Action();
		
		static::$_FormOptions[ Form::CSSCLASS ] = static::CssClass();
		
		static::$_FormOptions[ Form::ID ] = static::ID();

		static::$_FormOptions[ Form::ENCTYPE ] = static::EncType();
		
		static::$_FormOptions[ Form::METHOD ] = static::Method();
		
		static::$_FormOptions[ Form::INPUT_HASH_ALGO ] = static::InputHashAlgo();
		
		static::$_FormOptions[ Form::EXPIRE_TIME ] = static::ExpireTime();

		static::$_FormOptions[ Form::HONEYPOTS_MAX ] = static::HoneypotsMax();
		
		static::$_FormOptions[ Form::HONEYPOTS_LABELS ] = static::HoneypotsLabels();

        static::$_FormOptions[ Form::VERIFY ] = static::Verify();

        static::$_FormOptions[ Form::SUBMIT_LABEL ] = static::SubmitLabel();

        static::$_FormOptions[ Form::SUBMIT_ID ] = static::SubmitID();

        static::$_FormOptions[ Form::DEFAULT_TEMPLATE ] = static::DefaultTemplate();

        static::$_FormOptions[ Form::READONLY ] = static::ReadOnly();

        return static::$_FormOptions;
		
		
	}
	
	public static function ResetFormOptions()
	{
		
		static::setID();

		static::setAction();
		
		static::setCssClass();
		
		static::setEncType();
		
		static::setMethod();
		
		static::InputHashAlgo();
		
		static::ExpireTime();
		
		static::HoneypotsMax();
		
		static::HoneypotsLabels();

        static::Verify();

        static::SubmitLabel();

        static::SubmitID();

        static::DefaultTemplate();

        static::setReadOnly();


        return static::FormInstance();
		
	}

    //-------FORM::ID-----------------------------------------------------------------------

    public static function setID($value = '') {

        if ( ! static::IsOpen() )

			static::$_ID = (  is_empty( $value ) || ! is_string( $value )  ) ? 'semiorbit-form' . static::$_Counter :  $value;

        return static::FormInstance();

    }

    public static function ID($form = null) {

        if ( ! is_empty( $form ) && is_string($form) ) return $form;

        if ( is_empty( static::$_ID ) ) static::setID(); return static::$_ID;

    }

	//-------FORM::METHOD-----------------------------------------------------------------------
	
	public static function setMethod($value = '') {
	
		if ( ! static::IsOpen() )
	
			static::$_Method = (  is_empty( $value ) || ! is_string( $value )  ) ? 'post' :  $value;
	
		return static::FormInstance();
	
	}
	
	public static function Method() { if ( is_empty( static::$_Method ) ) static::setMethod(); return static::$_Method; }
	
	//-------FORM::ENCTYPE-----------------------------------------------------------------------
	
	public static function setEncType($value = null) {
	
		if ( ! static::IsOpen() )
	
			static::$_EncType = (  $value === null || ! is_string( $value )  ) ? 'multipart/form-data' :  $value;
	
		return static::FormInstance();
	
	}
	
	public static function EncType() { if ( is_empty( static::$_EncType ) ) static::setEncType(); return static::$_EncType; }
	
	//-------FORM::ACTION-----------------------------------------------------------------------
	
	public static function setAction($value = null) {
	
		if ( ! static::IsOpen() )
	
			static::$_Action = (  is_empty( $value ) || ! is_string($value)  ) ? '?:pms' :  $value;
	
		return static::FormInstance();
	
	}
	
	public static function Action() { 
		
		if ( is_empty( static::$_Action ) ) static::setAction();
		
		$request_pms = Url::Params(array(File::REMOVE_FILE_KEY));
		
		$action = str_ireplace( is_empty($request_pms) ? '?:pms' : ':pms', $request_pms, static::$_Action);
		
		$action = rtrim($action, "&");
		

		return $action; 
	
	}
	
	//-------FORM::ACTION-----------------------------------------------------------------------
	
	public static function setCssClass($value = null) {
	
		if ( ! static::IsOpen() )
	
			static::$_CssClass = (  is_empty( $value ) || ! is_string($value)  ) ? static::ActiveTemplate()->Settings('form_css_class') :

				static::ActiveTemplate()->Settings('form_css_class') . ' ' . $value;
	
		return static::FormInstance();
	
	}
	
	public static function CssClass() { if ( is_empty( static::$_CssClass ) ) static::setCssClass(); return static::$_CssClass; }
	
	//-------FORM::INPUT HASH ALGORITHEM-----------------------------------------------------------------------
	
	public static function setInputHashAlgo($value = null) {
	
		if ( ! static::IsOpen() )
	
			static::$_InputHashAlgo = (  is_empty( $value ) || ! in_array( strtolower($value), hash_algos() ) ) ? 'md5' : strtolower( $value );
	
		return static::FormInstance();
	
	}
	
	public static function InputHashAlgo() { if ( is_empty( static::$_InputHashAlgo ) ) static::setInputHashAlgo(); return static::$_InputHashAlgo; }
	
	//-------FORM::Expire Time-----------------------------------------------------------------------
	
	public static function setExpireTime($value = null) {

        $int_value = intval($value);

		if ( ! static::IsOpen() )
	
			static::$_ExpireTime = (  is_empty( $value ) || is_empty( $int_value ) ) ? 86400 : $int_value;
	
		return static::FormInstance();
	
	}
	
	public static function ExpireTime() { if ( is_empty( static::$_InputHashAlgo ) ) static::setExpireTime(); return static::$_ExpireTime; }
	
	//-------FORM::Honeypots Max-----------------------------------------------------------------------
	
	public static function setHoneypotsMax($value = null) {
		
		if ( ! is_int( Config::HoneypotsMax() ) )  Config::setValueOf(Config::GROUP__FORMS, Config::FORMS__HONEYPOTS_MAX, 3);
	
		if ( ! static::IsOpen() )
			
			static::$_HoneypotsMax =  intval( $value );
	
		return static::FormInstance();
	
	}
	
	public static function HoneypotsMax() { if ( static::$_HoneypotsMax === null ) static::setHoneypotsMax(); return static::$_HoneypotsMax; }
	
	//-------FORM::Honeypots Labels-----------------------------------------------------------------------
	
	public static function setHoneypotsLabels($value = array()) {
		
		if ( ! is_array( Config::HoneypotsLabels() ) )  Config::setValueOf(Config::GROUP__FORMS, Config::FORMS__HONEYPOTS_LABELS, []);

		static::$_HoneypotsLabels = (  is_empty( $value ) || is_array( $value ) ) ? Config::HoneypotsLabels() : $value;
	
		return static::FormInstance();
	
	}
	
	public static function HoneypotsLabels() { if ( is_empty( static::$_HoneypotsLabels ) ) static::setHoneypotsLabels(); return static::$_HoneypotsLabels; }


    //-------FORM::Submit Label-----------------------------------------------------------------------

    public static function setSubmitLabel($value = SAVE) {

        static::$_SubmitLabel = (  is_empty( $value ) ) ? SAVE : $value;

        return static::FormInstance();

    }

    public static function SubmitLabel() { if ( is_empty( static::$_SubmitLabel ) ) static::setSubmitLabel(); return static::$_SubmitLabel; }

    //-------FORM::Submit ID-----------------------------------------------------------------------

    public static function setSubmitID($value = null) {

        static::$_SubmitID = (  is_empty( $value ) ) ? null : $value;

        return static::FormInstance();

    }

    public static function SubmitID() { if ( is_empty( static::$_SubmitID ) ) static::setSubmitID(); return static::$_SubmitID; }

    //-------FORM::Show Verification Code-----------------------------------------------------------------------

    public static function setVerify($value = Form::VERIFY_NONE) {

        static::$_Verify = (  is_empty( $value ) ) ? Form::VERIFY_NONE : $value;

        return static::FormInstance();

    }

    public static function Verify() { if ( is_empty( static::$_Verify ) ) static::setVerify(); return static::$_Verify; }

    //-------FORM::Default Template-----------------------------------------------------------------------

    public static function setDefaultTemplate($value = null) {

        static::$_DefaultTemplate = (  is_empty( $value ) ) ? Config::FormTemplate() : $value;

        return static::FormInstance();

    }

    public static function DefaultTemplate() { if ( is_empty( static::$_DefaultTemplate ) ) static::setDefaultTemplate(); return static::$_DefaultTemplate; }

    //-------FORM::READONLY-----------------------------------------------------------------------

    public static function setReadOnly($value = null) {

        if ( ! static::IsOpen() )

            static::$_ReadOnly = boolval($value);

        return static::FormInstance();

    }

    public static function ReadOnly() { if ( is_empty( static::$_ReadOnly ) ) static::setReadOnly(); return static::$_ReadOnly; }

    //=================================================================================================

    /**
     * @param DataSet $dataset
     * @return static
     */

    public static function UseDataSet(DataSet $dataset)
	{
		static::$_DataSet = $dataset;
	
		return static::FormInstance();
	}

    /**
     * @return DataSet
     */
    public static function ActiveDataSet()
	{
	
		if ( ! static::$_DataSet instanceof DataSet ) return null;
	
		return static::$_DataSet;
	
	}
	
	public static function UseTemplate($form_template = null)
	{
	
		if ( is_empty($form_template) ) $form_template = static::DefaultTemplate();
	
		if ( $form_template instanceof FormTemplate ) static::$_FormTemplate = $form_template;
		
		else static::$_FormTemplate = new FormTemplate($form_template);
	
		return static::FormInstance();
	
	}

    /**
     * @return FormTemplate
     */
    public static function ActiveTemplate()
	{
	
		if ( is_empty(static::$_FormTemplate) ) static::UseTemplate();
	
		return static::$_FormTemplate;
	
	}
	
	public static function ActiveFrameSet()
	{
	
		if ( is_empty(static::$_FrameSet) ) return false;
	
		return static::$_FrameSet;
	
	}
	
	//=================================================================================================

    public static function Input($key, $form = null, $method = null, $is_submit = false, $hash = true, $suffix = '')
    {
        return static::InputByName( static::InputName( $key, $form, $hash, $suffix ), $is_submit, $form, $method );
    }

	public static function InputByName($input_name, $is_submit = true, $form = null, $method = null)
	{
		
		//Log::JsConsole()->Trace(0)->Info('$_POST',$_POST);
		
		//Log::Inline()->Trace(0)->Debug($input_name, $_POST[$input_name]);

        if ( ! $method ) $method = Form::Method();
		
		$value = null;
		
		if ( $is_submit )  { if ( ! static::IsSubmit( $form ) ) return null; }

	
			switch ( $method ) :
			
			case 'get':
				
				if ( isset( $_GET[$input_name] ) )  $value = $_GET[$input_name];
				
				break;
			
			case 'post':
				
				if ( isset( $_POST[$input_name] ) )	$value = $_POST[$input_name];

                else if ( isset( $_FILES[$input_name] ) )	$value = $_FILES[$input_name];
				
				break;

			
			endswitch;
			
		
		return $value;
		
	}

    public static function InputName($key, $form = null, $hash = true, $suffix = '')
    {

        $input_name = static::Clipboard("{$form}.{$key}.{$hash}.{$suffix}:input_name");

        if ( ! $input_name ) {

            if (is_empty($key)) return '';

            if (!$form) $form = static::FormIdInputValue();

            $input_name = $form;

            $name_parts = explode(".", $key);

            if (is_array($name_parts))

                foreach ($name_parts as $part) $input_name .= '[' . $part . ']';

			if ( ! empty($suffix) ) $input_name .= $suffix;

            if ($hash)

                $input_name = hash( Form::InputHashAlgo(), $input_name );

            static::Clipboard("{$form}.{$key}.{$hash}.{$suffix}:input_name", $input_name);

        }

        return $input_name;

    }
	
	//=================================================================================================
	
	public static function LoadJs($flush_output = true)
	{
		//TODO:: CHECK SCRIPT
		/*
	
		$ap_form_js = BASE_URL . LANG ."/Run/js/ap_form_js.php?v=" . uniqid();
		
		$html_output = <<<HEREHTML
		<script type="text/javascript" src="{$ap_form_js}"></script>
HEREHTML;
		
		if ( $flush_output ) echo $html_output;
		
		return $html_output;
		*/

		return $flush_output;

	}


}