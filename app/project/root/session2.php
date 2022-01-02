<?php if ( ! defined('in_app')) die('Accès Interdit');


class session2  {
	
	static $time;
	
	
	/*
	| -------------------------------------------
	| init
	| -------------------------------------------
	*/
	static function init() {
		
		if (session_status() == PHP_SESSION_NONE) {
		    session_start();
		}
		
		
	}
	
	
	/*
	| -------------------------------------------
	| create
	| -------------------------------------------
	*/
	static function create($name,$value,$duration=30) { // en minutes
		//$_SESSION['LAST_ACTIVITY'] = $this::time;
		$duration = $duration * 60;
		$_SESSION['last'] = time();
		$_SESSION['duration'] = $duration;
		$_SESSION[$name] = $value;
	}
	
	
	/*
	| -------------------------------------------
	| delete
	| -------------------------------------------
	*/
	static function delete($name) {
		unset($_SESSION[$name]);
	}
	
	
	/*
	| -------------------------------------------
	| delete all
	| -------------------------------------------
	*/
	static function deleteall($name) {
		session_unset();     // unset $_SESSION variable for the run-time 
		session_destroy();
	}
	
	
	
	/*
	| -------------------------------------------
	| check
	| -------------------------------------------
	*/
	static function check($name) {
		
		$return = false;
		
		
		if (isset($_SESSION[$name]) && isset($_SESSION['last']) && isset($_SESSION['duration']) && (time() - $_SESSION['last'] > $_SESSION['duration'])) {
		    
		    unset($_SESSION[$name]);
		    
		}
		
		
		if (	isset($_SESSION[$name]) && $_SESSION[$name]!=""	) {
			$return = true;
		}
		
		
		
		
		
		return $return;
		
	}
	
	
	


}