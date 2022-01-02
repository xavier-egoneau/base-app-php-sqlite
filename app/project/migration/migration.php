<?php if ( ! defined('in_app')) die('Accès Interdit');

//use \DrewM\MailChimp\MailChimp;
//use Carbon\Carbon;

class migration  {
    
    
    public $datas;
    
    public function init() {
    	global $app;
    	
    	
    }
	
  public function replacelocalhost($content){
    global $app;
    
    $return = str_replace("http://localhost/",$app["url"]["root"],$content);
    $return = str_replace("http://lab4.hello-interactiv.com/",$app["url"]["root"],$return);
    return $return;

  }
  public function home(){
    global $app;
    $crud = new Crud;
      
    $parametres = Article::all()->toArray(); 

      dd("migration");
  }


	public function op() {
	}

	
    public function route(){
        	global $app;
        	
           singleton("route")->match('GET','/migration/', "migration@home"); 

          
		    
        		/*
        		| -------------------------------------------
        		| 404
        		| -------------------------------------------
        		*/
        		singleton("route")->check_404(function () { 
        			global $app;
        		   echo "404";
        		   
        		   die();
        		});
        		
        		
       
        	
    }
        
        
        
    
   
    
   
}