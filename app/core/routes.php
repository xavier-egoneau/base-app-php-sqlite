<?php if ( ! defined('in_app')) die('Accès Interdit');

class Route extends Socle  {

	
	


	public function __construct() {
	
		global $app;
		


		foreach ($app["project"] as $plugin) {

			singleton($plugin)->route();
		}

		/*
        | -------------------------------------------
        | 404
        | -------------------------------------------
        */
        singleton("route")->check_404(function () { 
        	page404();
        });

		
		/*if (class_exists("Route_client")) :$routing = new Route_client;
		else: dd("Route_client Class not found!");
		endif;*/

	}
    
/*
	public function hacktest(){

		if(!filter_url("localhost:",$app["curent_url"]) && !filter_url("?page=",$app["curent_url"])  ) :
			singleton("route")->check("?", function () { 
				global $app;
			    journal();
			    if($app["fulljson"]) : json(array("status"=>false,"datas"=>[],"errors"=>["404"])); 
			    else : echo twig("tentative");  
			    endif;  
			    die();
			   
			});
		endif;
		
	}



*/



}