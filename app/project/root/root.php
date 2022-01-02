<?php if ( ! defined('in_app')) die('Accès Interdit');


// --> l'ID de la page d'accueil
$hid=10;

//use \DrewM\MailChimp\MailChimp;
class root  {
    
    
    public $datas;
    
    public $lang="fr";
    

    public function clearcache(){
      global $app;
      $cash = new cash($app["path"]["app"]."cached/",1000000);
      $cash->clear($filename);
      die("that's ok :)");
    }




  public function getpages($array){

    foreach ($array as $key => $value) {
        $id = $value["id"];
        if(isset($value["pages"])){
          $pages = json_decode($value["pages"]);
          $newarray = [];
          
          foreach ($pages as $key2 => $value2) {
            $newpage  = singleton("datas")->search(["id"=>$value2],["limit"=>1])["datas"];
            if ($newpage["statut"]=="true") {
              $newarray[$key2] = $newpage;
            }
            
          }

          $array[$id] = $newarray;
        }
    }

    return $array;

  }

    public function prevnext($cat){
    	global $datas;
    	$datas["prev"] ="";
    	$datas["next"] = "";
    	$lang = $datas["lang"];
    	
    	$allpages = singleton("datas")->search(["folder"=>$cat,"statut"=>"true"],["limit"=>100,"page"=>0,"sortby"=>"ordre","sens"=>"desc"]);
    		foreach ($allpages["datas"] as $key => $value) {
    				if ($value["id"]==$datas["page"]["id"]) {
    	   				if(isset($allpages["datas"][($key-1)])){
    	   					$datas["prev"] = $allpages["datas"][($key-1)];
    	   				}
    	   				if(isset($allpages["datas"][($key+1)])){
    	   						$datas["next"] = $allpages["datas"][($key+1)];
    	   				}
    	   			}
    	   			
    		}
    	
    }
    
    
    



  public function init(){
      
    // --> l'ID de la page d'accueil
    $hid= 10;
    $lang= "fr";
 
  }

  public function initpage() {
      	global $datas;
      	global $app;
      	
  		  $arraypages = [];
       

      
  		#menu
  		# ------------------------------------------
  		
  		//$datas["menuappel"] = singleton("datas")->search(["folder"=>4,"statut"=>"true"],["limit"=>30,"sortby"=>"ordre","sens"=>"asc"])["datas"];
        //$datas["menus"] = $this->getpages($datas["menuappel"]);
        //$datas["menu"] = $datas["menus"][4];
        //$datas["menubottom"] = $datas["menus"][9];
  		

  		#pages courantes
  		# ------------------------------------------
  		$datas["pages"] = singleton("datas")->search(["folder"=>1,"statut"=>"true"],["limit"=>30,"sortby"=>"ordre","sens"=>"asc"])["datas"];
  		
  			
  		foreach ($datas["pages"] as $key => $value) {
  			$id = $value["id"];

  			$datas["pages_id"][$id] = $value;
  		}
  		
  		#url actuelle
  		# ------------------------------------------
  		$datas["uri"] = $_SERVER['REQUEST_URI'];
      	
      	
      	#params
      	# ------------------------------------------
      	
      	$params = singleton("datas")->parametres();
      	
      	#reseau sociaux
      	# ------------------------------------------
      	
      	$params["rs"] = Spyc::YAMLLoad($params["rs"]);
      	$datas["parametres2"] = $params;
      	//dd($datas["parametres2"]);
      	#modules
      	# ------------------------------------------
      	
        $datas["modules"] = singleton("datas")->modules();
      	foreach ($datas["modules"] as $key => $value) {
          $id = $value["id"];
          $datas["module"][$id] = $value;
        }
  	
      	
  }
    

    
    
    
    public function pages($route) {
       global $datas;
       global $hid;

       $lang = $route->lang;
       $slug = $route->slug;
       //$hid = 5;
       $datas["ispage"] = "true";
      $page1 = singleton("datas")->search(["url"=>$slug],["limit"=>1]);
         $page = singleton("datas")->extend_article($page1);
        
      $datas["page"] = $page;
      $lang = $datas["lang"];


       if(isset($page["datas"]["folder"])){
          $cat = $page["datas"]["folder"];
          $folder = Folder::find($cat)->toArray();  
       }
     
    
      if($datas["page"]["id"]== 10 ){
       	$this->home($route);

       	exit();
       }

       	if(isset($page["status"]) && $page["status"]!="false"){
       		
       		$datas["page"] = $page["datas"];
       		
       		$datas["lang"] = $datas["page"]["lang"];

            
            if($page["datas"]["id"]==5){
                
                $datas["clients"] = singleton("datas")->search(["id"=>10],["limit"=>1])["datas"];

            }
       		
            
       		if(isset($datas["page"]["dossier"]) && $datas["page"]["dossier"]!=""){
       			
                
       			$idfolder = $datas["page"]["dossier"];
       			$datas["items"] = singleton("datas")->search(["folder"=>$idfolder,"statut"=>"true"],["limit"=>100,"sortby"=>"ordre","sens"=>"asc"])["datas"];
                
                
       		}
            
            if(isset($datas["page"]["activitetoprint"]) && $datas["page"]["activitetoprint"]!=""){
       			
               $datas["activites"] = $datas["page"]["activitetoprint"];

                
       		}

          
          
		
       			
       	}else{
       		singleton("helpers")->page404($lang); 
       	}
       

       $this->initpage();
       

       if(isset($page["datas"]["folder"]) && $folder["template"]!=""){
          $tpl = str_replace(".twig", "", $folder["template"]);
          twig( $tpl,$datas);
          die();
       
       }else{

         if(isset($page["datas"]["template"]) && $page["datas"]["template"]!=""){
         	$tpl = str_replace(".twig", "", $page["datas"]["template"]);
         	twig($tpl,$datas);
         	die();
         }else{
         		twig("pagesimple",$datas);
            die();
         }

      }
       
       
              
       
       
       		
       

    }
    
    



    public function home($route) {
       global $datas;
       
       		

            
        $datas["themes"] = singleton("datas")->search(["folder"=>5],["limit"=>100,"sortby"=>"ordre","sens"=>"asc"])["datas"];

        foreach ($datas["themes"] as $key => $value) {  
            
            $datas["themes"][$key] = singleton("datas")->extend_article($value);
        }
      
        $this->initpage();
        twig("index",$datas); 
        
    }
    
    

	





    public function route(){
        	global $app;
          global $datas;
        	/*
            | -------------
            | check auth
            | -------------
            */
            $auth = singleton("auth")->check();
            
            $app["auth"] = $auth;

            /*
            | -------------------------------------------
            | site under construction
            | -------------------------------------------
            */

             if( $app["auth"]!="true" && $app["parametres"]["production"]=="false"){
              $this->initpage();  
               $datas["tpl"] = "suc";
              twig("suc",$datas);
              die(); 
             }



            /*
            | -------------------------------------------
            | site
            | -------------------------------------------
            */
            
            if($app["config"]["cache"]=="true"){
              //
              if( $app["auth"]=="true" || filter_url("/ajax/",$app["curent_url"]) ||  filter_url("/clearcache",$app["curent_url"])):

              else: 

                $current = $app["curent_url"];
                $time = (60 * 24) * 72;
                $cash = new cash($app["path"]["app"]."cached/",$time);
                $test = $cash->check(slug($current));
                if ($test) {
                  echo $cash->read(slug($current));
                  die();
                }
              endif;  
              
            }

            //singleton("ag")->route();
            singleton("sitemap")->route();
        		/*
        		| -------------------------------------------
        		| site
        		| -------------------------------------------
        		*/
        		
        			
        		if(filter_url("/ajax/",$app["curent_url"])):
        			//for ajax
        			
  					  
					    //singleton("route")->match('POST','/ajax/getretails/', "root@getretails");
        			
        		else:

              singleton("route")->match('POST','/ajax/gettoken', "helpers@gettoken");
              singleton("route")->match('GET','/clearcache', "root@clearcache");
              //singleton("route")->match('GET','/sitemap', "root@sitemap");


                    if( !filter_url("/assets/",$app["curent_url"]) && !filter_url("/uploads/",$app["curent_url"]) ){
                    singleton("route")->match('GET','/:slug', "root@pages");
                    //singleton("route")->match('GET','/:slug/:page', "root@pages");
	                singleton("route")->match('GET','/:lang/:slug', "root@pages");
	               //singleton("route")->match('GET','/:lang/:slug/:page', "root@pages");



                	//home
        		      singleton("route")->match('GET','/', "root@home");
        				
        		  }		
		        		
		        		
		        		
	        		//endif;
	        		
				
				    endif;
        		/*
        		| -------------------------------------------
        		| 404
        		| -------------------------------------------
        		*/
        		singleton("route")->check_404(function () { 
        			global $app;
        		   //twig("404");  
        		   singleton("helpers")->page404("en"); 
        		   die();
        		});
        		
        		
       
        	
    }
        
        

        
    
   
    
   
}