<?php if ( ! defined('in_app')) die('Accès Interdit');


class cash  {
	
	public $dirname; 
	public $duration;// in minutes
	public $buffer;
	
	
	/*
	| -------------------------------------------
	| init
	| -------------------------------------------
	*/
	public function __construct($dirname,$duration) {
		
		$this->dirname =  $dirname;
		$this->duration =  $duration; // in minutes
		
		
	}
	
	
	/*
	| -------------------------------------------
	| write
	| -------------------------------------------
	*/
	public function write($filename,$content) {
		
		return file_put_contents($this->dirname.$filename , $content);
	}
	/*
	| -------------------------------------------
	| check
	| -------------------------------------------
	*/
	public function check($filename) {

		$file = $this->dirname.$filename;
		
		if (!file_exists($file)) {
			return false;
		}
		$lifetime = ( time() - filemtime($file)) / 60;

		if ($lifetime > $this->duration) {
			return false;
		}
		
		$cont = file_get_contents($file);
		if($cont==""){
			return false;
		}
		return true;
	}
    
	/*
	| -------------------------------------------
	| read
	| -------------------------------------------
	*/
	public function read($filename) {
		$file = $this->dirname.$filename;
		return file_get_contents($file);
	}
	
	/*
	| -------------------------------------------
	| create
	| -------------------------------------------
	*/
	public function delete($filename) {
		$file = $this->dirname. "/" .$filename;
		if (file_exists($file)) {
			unlink($file);
			
		}
	}
	
	
	
	/*
	| -------------------------------------------
	| clear
	| -------------------------------------------
	*/
	public function clear($filename) {	
		$files = glob($this->dirname.'/*');
		foreach ($files as $file) {
			unlink($file);
		}
		
	}
	
	
	public function inc($file, $cachename = null) {
		if (!$cachename) {
			$cachename = basename($file);
			if($content = $this->read($cachename)){
				echo $content;
				return true;
			}
			ob_start();
			require $file;
			$content = ob_get_clean();
			$this -> write($cachename, $content);
			echo $content;
			return true;
		}
	}
	
	public function start($cachename){
		if ($content = $this->read($cachename)) {
			echo $content;
			$this->buffer = false;
			return true;
			
		}
		ob_start();
		$this->buffer = $cachename;
	
	}
	
	public function end() {
		if (!$this->buffer) {
			return false;
		}
		$content = ob_get_clean();
		echo $content;
		$this->write($this->buffer, $content);
	}


}