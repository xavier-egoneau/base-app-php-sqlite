<?php if ( ! defined('in_app')) die('Accès Interdit');

//use \DrewM\MailChimp\MailChimp;
class ag  {
    
    
    public $datas;
     /* Need:
        singleton("ag")->init_ag(); in root -> init
        singleton("ag")->route(); in root-> route
        a twig "ag" in template folder
      */


     /* create ag session if click */
    public function ajaxag(){
      
        $session = new session2; 
        $session::init();
        $session::create("ag","true",160);
        $ag = $session::check("ag");

      
    }

    /* because framework work want a class init */
    public function init(){
          

    }

     /* check ag session and print ag twig if not good */
    public function agcheck(){
        global $datas;
          global $app;
          if(!is_array($datas)){$datas =  [];}

          

          $arraypages = [];
          $session = new session2; 
          $session::init();

          $ag = $session::check("ag");
          if($ag!="true"){
              
              echo "false";

          }else{
              echo "true";
          }
              
          die();
    }


    

   



     /* rooting */
    public function route(){
        	global $app;


        		/*
        		| -------------------------------------------
        		| site
        		| -------------------------------------------
        		*/
      
        			
        		if(filter_url("/ajax/",$app["curent_url"])):
        			//for ajax
					    singleton("route")->match('POST','/ajax/ag/', "ag@ajaxag");
              singleton("route")->match('POST','/ajax/agcheck/', "ag@agcheck");
        			
				    endif;
        		
        		
        		
       
        	
    }
        
        
        
    
   
    
   
}