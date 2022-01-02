<?php 



class Template extends Socle  {

		
	
	
	
	
	
	    public function __construct() {
			global $app;
			global $services;
			
			
			
			$app["tpl_list_file"] = [];
			
			
			    
    		$controllerpath =  $app["path"]["tpl"];
    			   
    		$listtpls = [];

    		if(is_folder($app["path"]["tpl"])){
    			   
	    			   $listtpls = getfolders($controllerpath);
	    			   $listtpls[] = $app["path"]["tpl"];	
	    			    
	    			 if( $listtpls!="" && is_array( $listtpls)){
	    			   	foreach ( $listtpls as $key => $pathfldr) {
	    			   	
	    			   	    $listfilesClient = dirToArray($pathfldr);
	    			   	    if(isset($listfilesClient) && $listfilesClient!="" && is_array($listfilesClient)){
	  
	    			   	    	foreach ($listfilesClient as $file) {
	    			   	    	    if(!is_array( $file)){    
	    			   	    	    
	    			   	    	        $testextention = explode(".", $file);
	    			   	    	    	if(sizeof($testextention)>2){
	    			   	    	    		add_error("Le fichier ". $controllerpath.$file ." comporte une erreur de naming. Il ne doit pas y avoir de '.' dans le nom des fichiers. ", __FILE__ , __LINE__ );
	    			   	    	    		
	    			   	    	    		
	    			   	    	    	}else{
	    			   	    	    	    $app["tpl_list_file"][] = $file;
	    			   	    	    	}
	    			   	    	    }	
	    			   	    	    
	    			   	        }
	    			   	    }    
	    			   	    
	    			   	}   	
    			  	}
    			  
    		}else{
    			  	
    			dd("folder of templates not found.");
    			  	
    		}
			   
			    
			
			
			 /*---------------------------------------------------------------
			  * services/intégrations twig moteur de templating php
		      *--------------------------------------------------------------- */
		        
		     $app["services"]->set('twig', function () use($app)  {
		        		   global $app;
						   $array_tpl = [];
		        		   Twig_Autoloader::register();
		        		    
		        		   /* list les dossier dans le folder principal */
		        		   if(!is_folder($app["path"]["tpl"])):
		        		   dd("folder of templates not found.");
		        		   endif;
		        		   
		        		   $listtpls = getfolders($app["path"]["tpl"]);
		        		   $listtpls[] = $app["path"]["tpl"];
		        		   					   
		        		   if(is_array($listtpls)):
			        		   foreach ( $listtpls as $folder) {
			        		       $array_tpl[] = $folder;
			        		   }
		        		   endif;
		        		  
		     
		        		   $loader = new Twig_Loader_Filesystem($array_tpl);
		        		   
		        		   
		        		   $debug =  $app["config"]["dev"];
		        		   
		        		   if($debug):
		        		       $obj_twig = new Twig_Environment($loader, array('cache' => false,'debug' =>true));
		        		   else:
		        		       
		        		       $obj_twig = new Twig_Environment($loader, array('cache' => false,'debug' => false));
		        		   endif;
		        		   
		        		   
		        		   if($app["config"]["dev"]):
		        		   		$obj_twig->addExtension(new Twig_Extension_Debug());		        		   		
		        		   endif;
		        		   
		        		   $twig = $obj_twig;
		        		   
		        		   




		         			/*---------------------------------------------------------------
						    * api
						   	*--------------------------------------------------------------- */
						   	$api = new  Twig_SimpleFunction('api', function ($url) use($app) {
						   	
						   		$opts = array('http' =>
						   		    array(
						   		        'method'  => 'get',
						   		        'header'  => 'Content-type: application/x-www-form-urlencoded'
						   		    )
						   		);
						   		
						   		$context  = stream_context_create($opts);
						   		
						   		$result = file_get_contents($url,false,$context);
						   		$return = json_decode($result,true);
						   		
						   		return $return["datas"];
						   	
						   	});
						   	
						   	$twig->addFunction($api);
						   	
						   	
						   	/*---------------------------------------------------------------
						   	 * is_mobile
						   	 *--------------------------------------------------------------- */
						   	$mobile = new  Twig_SimpleFunction('is_mobile', function () {
						   		
						   			
						   			if(
						   				singleton("mobiledetect")->isMobile() && 
						   				!singleton("mobiledetect")->isTablet() 
						   			){
						   				return true;
						   			}else{
						   				return false;
						   			}
						   			
						   			
						   		
						   	});
						   		
						   	$twig->addFunction($mobile);
						   	
						   	/*---------------------------------------------------------------
						   	 * is_tablet
						   	 *--------------------------------------------------------------- */
						   	$tablet = new  Twig_SimpleFunction('is_tablet', function () {
						   			
						   				
						   				if(
						   					singleton("mobiledetect")->isMobile() && 
						   					singleton("mobiledetect")->isTablet() 
						   				){
						   					return true;
						   				}else{
						   					return false;
						   				}
						   				
						   				
						   			
						   	});
						   			
						   	$twig->addFunction($tablet);
						   	
						   	
						   		
						   		
						   		
		                    /*---------------------------------------------------------------
		                	 * encode url or tag with _
		                	 *--------------------------------------------------------------- */
		                	$encode_ = new  Twig_SimpleFunction('encode_', function ($url) {
		                	
		                		$url = utf8_decode($url);
		                		
		                		$url = strtr( $url,["'"=>'"']);
		                		$url = strtolower(strtr($url, utf8_decode('ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ()[]\'"~$&%*@ç!?;,:/\^¨€{}<>|+.- @&é"(§è!çà)-_°^¨%ù`$*/.+?%'),  
		                		'aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn____________c_________e___________e___e_ca_______u_________'));
		                		
		                		
		                		$regles = [
		                			' '=>'_',
		                			'___' =>'_',
		                			'__' =>'_'
		                		];
		                		$url = strtr( $url,$regles);
		                		
		                		$url = trim($url,'_');
		                		return $url; 			 							 						 						
		                				 						 	
		                	});
		                	$twig->addFunction($encode_);
		                	
		                	
		                	
		                	
		                	/*---------------------------------------------------------------
		                	 * to string
		                	 *--------------------------------------------------------------- */
		                	$tostring = new  Twig_SimpleFunction('tostring', function ($array) {
		                	
		                		$return = "";
		                		foreach ($array as $item) {
		                			if($return==""):
		                				$return = $item;
		                			else:
		                				$return = $return .",". $item;
		                			endif;
		                		}
		                		return $return; 			 							 						 						
		                				 						 	
		                	});
		                	$twig->addFunction($tostring);
						   	
						   		
						   		
		
		                    /*---------------------------------------------------------------
					   		 * in array
					   		 *--------------------------------------------------------------- */
					   		$in_array = new  Twig_SimpleFunction('in_array', function ($word,$array) {
					   			if(!is_array($array)){
					   				$array2 = explode(",", $array);
					   				$array = $array2;
					   			}
					   			if (in_array($word, $array)) {
					   				return true;
					   			}else{return false;}
		
					   		});
					   		$twig->addFunction($in_array);
					   		
					   		
					   		
					   		
					   		/*---------------------------------------------------------------
					   		 * is array
					   		 *--------------------------------------------------------------- */
					   		$is_array = new  Twig_SimpleFunction('is_array', function ($array) {
					   			
					   			if(!is_array($array)){
					   				return false;
					   			}else{return true;}
					   				
					   		});
					   		$twig->addFunction($is_array);
					   		
					   		
					   		
					   		/*---------------------------------------------------------------
                             * string truncate
					         *--------------------------------------------------------------- */
					   			$trunc = new  Twig_SimpleFunction('trunc', function ($text, $chars = 150) {
					   				
					   					if(strlen($text) > $chars) {
					   					       $text = $text.' ';
					   					       $text = strip_tags($text);
					   					       $text = substr($text, 0, $chars);
					   					       $text = substr($text, 0, strrpos($text ,' '));
					   					       $text = $text.'...';
					   					}
					   					return $text; 			
					   							 						 	
					   			});
					   			$twig->addFunction($trunc);
					   			

					   		
					   		
					   		
					   		/*---------------------------------------------------------------
					   		 * au hasard
					   		 *--------------------------------------------------------------- */
					   		$rand = new  Twig_SimpleFunction('rand', function () {
					   			
					   			$return = rand(0, 10);
					   			return $return;	
					   		});
					   		$twig->addFunction($rand);
					   		
					   		
					   		/*---------------------------------------------------------------
					   		 * slug
					   		 *--------------------------------------------------------------- */
					   		$slug = new  Twig_SimpleFunction('slug', function ($string) {
					   				
					   				$return = slug($string);
					   				return $return;	
					   		});
					   		$twig->addFunction($slug);
					   		
					   		
					   		/*---------------------------------------------------------------
					   		 * jsondecode
					   		 *--------------------------------------------------------------- */
					   		$slug = new  Twig_SimpleFunction('jsondecode', function ($string) {
					   					
					   					$return = json_decode($string,true);
					   					return $return;	
					   		});
					   		$twig->addFunction($slug);
			   		



		        		   
		        		   
		        	       return $twig;
		    });
		        	
		    $services["twig"] = $app["services"]->get('twig');
	        
		
	    }
	
	   


        
        /*---------------------------------------------------------------
         * route - we have route & we have maybe init templating... so -> we print
         *--------------------------------------------------------------- */
        public function twig($tpl,$data=[]){
            
            global $app;
            global $services;
            
            if(    tpl_exist($tpl)    ):
            	
                
            
               
                
                $tpl = singleton("twig")->render($tpl.".twig",$data);
                
                return $tpl;
            else:
                return singleton("twig")->render("404.twig",$data);
            endif;
            
            
        }

		
		


	
}

