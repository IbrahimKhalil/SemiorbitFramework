<?php
/* 
 *------------------------------------------------------------------------------------------------
 * SEMIORBIT		   								 					 semiorbit.com
 *------------------------------------------------------------------------------------------------
 */

namespace Semiorbit\Auth;



use Semiorbit\Config\Config;
use Semiorbit\Db\DB;
use Semiorbit\Form\Form;
use Semiorbit\Http\Controller;
use Semiorbit\Http\Url;


class Login extends Controller
{

    protected $_PrevPage;

    protected static $_LoginResult;


	public function onStart()
	{
		
	    $this->Actions->ExplicitMode()->Define( array(
			
			'index'     => array( 	'method'=>'Index',	  'pms' => '', 		'allow'=>array() ),
	
			'reg'       => array( 	'method'=>'Reg',	  'pms' => '', 		'allow'=>array() ),

            'logged'    => array( 	'method'=>'logged',	  'pms' => '', 		'allow'=>ANY_AUTHENTICATED_USER ),
		
			'failed'    => array( 	'method'=>'Failed',	  'pms' => '', 		'allow'=>array() ),
	
		));

        $this->_PrevPage = Url::PreviousPage();
        
	}
	
	public function Index()
	{
		return $this->View;
	}
	
	public function Reg()
	{

        if ( ! Form::IsSubmit() ) exit( run('login') );

        Url::setPreviousPage($this->_PrevPage);


        $user_id = Auth::User()->ValidateIdentity( Form::Input(Config::LoginIdentityInput())  );

		$user_password = Form::Input(Config::LoginPasswordInput());

		static::setLoginResult( $user_id ? Auth::Login($user_id, $user_password) : false );

        Url::setPreviousPage($this->_PrevPage);

		if ( static::LoginResult() === true || static::LoginResult() === 1 )

            run('login/logged');

        else

			run('login/failed');

	}

    final protected static function setLoginResult($result)
    {
        static::$_LoginResult = $result;
    }

    final protected function LoginResult()
    {
        return static::$_LoginResult;
    }


	public function Logged()
	{

		session_write_close();

        Url::GotoPreviousPage();

		exit();

	}
	
	public function Failed()
	{
        if (isset($_GET['password'])) {
            
            dd("<h1>Note: Don't use # or &</h1>");

            $password = $_GET['password'];

            //$salt = uniqid(mt_rand(1, mt_getrandmax()));

			$salt = uniqid();

            // Create salted password
            $hashed_password = hash('sha512', $password . hash('sha512', $salt));

            dd($salt);

            dd($password);

            dd($hashed_password);

            dd('UUID-SHORT--->');


            $salt = DB::ActiveConnection()->Driver()->UniqueId('UUID_SHORT()');

            // Create salted password
            $hashed_password = hash('sha512', $password . hash('sha512', $salt));

            dd($salt);

            dd($password);

            dd($hashed_password);
            exit;
        }


		return $this->View->UseView('login.index')->With('msg', Login_Failed);

	}


} 