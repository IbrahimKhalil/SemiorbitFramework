<?php

namespace Semiorbit\Controllers;

 
class CPController extends \Semiorbit\Http\Controller
{


	public function Index()
	{
		$this->View->setPageTitle(Tools)->Render();
	}

} 