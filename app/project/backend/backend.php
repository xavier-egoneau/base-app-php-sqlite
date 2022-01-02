<?php if ( ! defined('in_app')) die('Accès Interdit');
	
	
	//use \MyProject\Migration\Migration;
	
	
	class  backend  {
		
		/** @var \Illuminate\Database\Capsule\Manager $capsule */
		public $capsule;
		/** @var \Illuminate\Database\Schema\Builder $capsule */
		public $schema;
		
		
		
		public function lang_trad($languages) {
			global $app;
			
			$lang = $app["config"]["default_lang"];
			foreach ($languages as $key => $value) {
				$languages[$key] = $value[$lang];
			}
			return $languages;
		}


		
		
		public function init() {
			
			global $app;
			global $services;
			
			
				//dd("init Drgstr");				
				
				/*
				| -------------
				| check auth
				| -------------
				*/
				//singleton("session")->start();
				//singleton("session")->set_time(30);
				//$auth = singleton("auth")->check(11);
				$app["auth"] = singleton("auth")->check();
				
				
				require($app["path"]["project"]."api/crud.php");
				
	
				/*---------------------------------------------------------------
				 * init specific helpers
				 *--------------------------------------------------------------- */
				$app["services"]->set("crud", function () use($app) {
					if(class_exists("crud")):
						$return = new Crud();
						return $return;	  
					endif;  
				});
				$services["crud"] = $app["services"]->get("crud");
				
				
				
				
				
			
			
			
		}
		
		
		public function controldoublons($array,$reference) {
			$ids_ref = [];
			foreach ($reference as $ref) {
				$ids_ref[] =  $ref["id"];
			}
			
			foreach ($array as $key => $result) {
				if (in_array($result["id"],$ids_ref)) {
					unset($array[$key]);
				}
			}
			return $array;
		}
		public function search() {
						global $app;
						$datas = $this->initpage();	
						$datas["columns"] = singleton("shema")->getColumnListing('articles');
						//dd($datas["columns"]);
						$datas["empty"]="false";
						$datas["modules_list"]=Module::all();
						$resultats = [];
						
						if (isset($_POST["search"]) && $_POST["search"]!="") {
							
							$strng = $_POST["search"];
							$words = explode(" ", trim($strng));
							
							$datas["articles"] = Article::SearchByKeyword($strng)->get();								
							
							if (sizeof($datas["articles"])==0) {
								$datas["empty"]="true";
							}
						}
						
						
						$datas["return"] = "";
						$datas["pagetitre"] =$datas["languages"]["search"];
						twig("search",$datas);
		
				
		}
				
		
		/*
		va permettre d'injecter le plugin dans le code du projet
		*/
		public function inject(){
		
		}
		
		public function creditsimg(){

				global $app;
				$result = array("status"=>false,"datas"=>[],"errors"=>[]);
				$ref = $_POST["ref"];
				$img = $_POST["img"];
				$crdt = $_POST["credit"];
				
				$needed = ["ref","img","credit"];
				
				if(	singleton("auth")->check() && control_values($needed,$_POST)	){
					
					$art = Article::where(["id"=>$ref])->get();	
					//dd($art);
					/*ok*/
					if (!$art ->isEmpty()){
						//dd($art);
						$artArray = $art->toArray();
						//dd($artArray[0]);
						$old = $artArray[0]["creditsimgs"];
						if($old!=""){
							$art_creditsimgs = json_decode($old,1);
						}else{
							$art_creditsimgs = [];
						}
						$art_creditsimgs[$img]=$crdt;
						
						$art_creditsimgs_json = json_encode($art_creditsimgs);
						
						$artt = Article::find($ref);
						$artt->creditsimgs = $art_creditsimgs_json;
						$artt->save();
						//dd($artt);
						//Article::find($ref)->update([ "creditsimgs" => $art_creditsimgs_json ]);	
						$result["status"]="true";
					}	
					
				}
				json($result);
		}
		
		/*
		ajoute des routes au routing principal
		Exemple : pour une page config du plugin.
		*/
		public function route(){
			global $app;
	
			/*
					| -------------------------------------------
					| backend
					| -------------------------------------------
					*/
					$b = "/backend/";
					
					
					if(filter_url("/backend",$app["curent_url"]) ) :
					
						if($app["auth"] ): 

			singleton("route")->match('GET',$b, "backend@home");
			singleton("route")->match('get',$b."search/", "backend@search");
			singleton("route")->match('post',$b."search/", "backend@search");
			singleton("route")->match('post',$b."exit", "backend@getLogout");
			singleton("route")->match('post',$b."creditsimg/", "backend@creditsimg");
			
			singleton("route")->match('get',$b."parametres/", "backend@parametre");
			singleton("route")->match('get',$b."users/", "backend@users");
			singleton("route")->match('get',$b."users/:id/", "backend@user");
			
			singleton("route")->match('get',$b."regions/", "backend@regions");
			singleton("route")->match('get',$b."regions/:id/", "backend@region");
			
			singleton("route")->match('get',$b."folders/:id/", "backend@folder");
			
			singleton("route")->match('get',$b."articles/:folder/", "backend@articles");
			singleton("route")->match('get',$b."articles/:folder/:page", "backend@articles");
			singleton("route")->match('get',$b."articles/:folder/:id/", "backend@article");
			singleton("route")->match('get',$b."articles/:folder/:id/:lang", "backend@article");
			singleton("route")->match('get',$b."articles/:folder/:id/:lang/:ref", "backend@article");
			
			
			
			
			
			singleton("route")->match('post',$b."newapicode/", "backend@newapicode");
			singleton("route")->match('get',$b."modules/", "backend@modules");
			singleton("route")->match('get',$b."modules/:id/", "backend@module");
			singleton("route")->match('post',$b."encodeurl/", "backend@encodeurl");
			singleton("route")->match('get',$b."dump/", "backend@dumpall");
			singleton("route")->match('get',$b."backup/", "backend@backup");
			singleton("route")->match('post',$b."save/", "backend@export");
			singleton("route")->match('post',$b."loadbackup/:id/", "backend@loadbackup");
			singleton("route")->match('post',$b."deletebackup/:id/", "backend@deletebackup");
			singleton("route")->match('post',$b."publish/", "backend@publish");
			//singleton("route")->match('GET',$b."initialize", "backend@initialize");
			singleton("route")->match('GET',$b."reminder", "backend@reminder");
								
								/*
								| -------------------------------------------
								| 404
								| -------------------------------------------
								*/
								singleton("route")->check_404(function () { 
									global $app;
								   twig("404",[]);   
								   die();
								});
							
							
				
						/*
						| -------------------------------------------
						| nevermind
						| -------------------------------------------
						*/	
						else:
                
							singleton("route")->match('GET',$b, "backend@connect");
							singleton("route")->match('GET',$b."reminder", "backend@reminder");
							singleton("route")->match('post',$b."remindersubmit", "backend@remindersubmit");
							singleton("route")->match('get',$b."install", "backend@install");
							
							/*
							| -------------------------------------------
							| 404
							| -------------------------------------------
							*/
							singleton("route")->check_404(function () { 
								global $app;

							   twig("connection",[]);   
							   die();
							});
							
						endif;
						
						
						
						
					endif;
					
			
		}
		
		
		
		
		public function publish(){
			global $app;
			
			singleton("crud")->render();
			
		}
		
		
		
		public function newapicode(){
			global $app;

			$paramss = Parametre::first();
			$apicode = randomkey(12);
			$paramss->apicode = $apicode;
			$paramss->save();
			$result= ["code"=>$apicode];
			json($result);
		}
		
		
		
		public function install() {
			global $app;
			$result= ["status"=>false];
			$datas = [];
			
			singleton("structure")->check();
			if(singleton("structure")->check()=="up"):
				
				$datas["msgs"][] = $this->loadbdd("base");
				
				$paramss = Parametre::first();
				$apicode = randomkey(12);
				$paramss->apicode = $apicode;
				$paramss->save();
				
				$datas["msgs"][] =$app["config"];
				if(isset($app["config"]["email"])){
					$datas["msgs"][] = "config email ok";
					$email = $app["config"]["email"];
					$user_email = User::where(["email"=>"superadmin@example.com"])->get();	
					
					/*ok*/
					if (!$user_email ->isEmpty()){
						$datas = $user_email->first()->toArray();
						
						$id = $datas["id"];
						$newpass=randomkey(5);
						$newpass_encode = haash($newpass);
					
						$req = User::find($id);
						$req->password = $newpass_encode;
						$req->email = $email;
						//$req->titre = $email;
						$req->save();
						$datas["msgs"][] = "C'est prêt!! 😝 🤘";
                        
                        
					   /* ------------------- */
                        
                        $to      = $email;
                        $subject = "Drgstr - INSTALL.";
                        $message = "Bonjour,<br>La Base De Donnée à été correctement installée et un mot de passe a été généré.<br>Identifiant : ". $email ."<br>Nouveau mot de passe  : ".$newpass."<br>Rendez-vous sur la page  ". $app["url"]["local"] ." et saisissez alors les données ci-dessus pour vous connecter.<br>Cordialement<br>Drgstr";
                     
                        
                        $mailsend = email($to, $subject, $message);

                        if($mailsend):
                            $datas["msgs"][] = "Un email vous a été envoyé à l'adresse: ".$email;
                            $datas["msgs"][] = '<a href="'.$app["url"]["local"].'" class="uk-button uk-button-primary">Revenir au login</a>';
                        else:
                            $datas["msgs"][] = "Oups!! Nous n'avons pas pu envoyer l'email.";
                        endif;				

			
					
					}
	
                //no email in config
				}else{
				    $datas["msgs"][]="superadmin@example.com n'existe pas";
				}
			else:
				$datas["msgs"][] = "Base de donnée déjà installée.";
			endif;
			twig("msg",$datas);	
			
		}
		
		
		public function initialize() {
			global $app;
			singleton("structure")->down();
			singleton("structure")->up();
			
			/*
			Folder::truncate();
			Article::truncate();
			Module::truncate();
			*/
		}
			
		
		
		
		
		/* ---------------
		 * crontroller
		   --------------- */
		   
	    public function dumpall(){
	    	global $app;
	    	//dd($app["config"]["dev"]);
			if($app["config"]["dev"]){
	    		
	    		$user = singleton("auth")->get();
	    		$params = $user["parametres"];
				$return = [];
				$return["parametres"] = Parametre::find($params)->toArray();
				$return["folders"] = Folder::all()->toArray();
				$return["modules"] = Module::all()->toArray();
				$return["articles"] = Article::all()->toArray();
				$return["users"] = User::all()->toArray();
				
				print_r($return); 
				die();	
	    	}else{
	    	  twig("404");  
	    	}
	    	
	    	
	    }
	    
	    public function export(){
	      	global $app;
	      	$result = array("status"=>false,"datas"=>[],"errors"=>[]);
	    	if(	singleton("auth")->check()	){
	      		
	      		$user = singleton("auth")->get();
	      		$params = $user["parametres"];
	    			$return = [];
	    			$return["parametres"] = Parametre::find($params)->toArray();
	    			$return["folders"] = Folder::all()->toArray();
	    			$return["modules"] = Module::all()->toArray();
	    			$return["articles"] = Article::all()->toArray();
	    			$return["users"] = User::all()->toArray();
	    			  
	
	    			 $json = json_encode($return);			
	    			 if (is_json($json)) {
	    			  	
	    			  	$date_day =  date("Y-m-d-H-i-s");
	    			  	$name_file = $date_day.".json";
	    			  	
	    			  	$save_folder = $app["path"]["root"]."storage/sauveguardes/";
	    			  	file_put_contents($save_folder.$name_file, $json);
	    			  	
	    			  	$result["status"]="true";
	    			  	
	    			 } 		
	      		json($result);
	      	}
	      	
	      	
	      	
	    }
	    
	    
	    public function backup() {
	           global $app;
	           
	           $datas = [];
	           $datas = $this->initpage();
	           if($datas["user"]["type"]=="1" || $datas["user"]["type"]=="2"){
	           		
	           		$datas["return"] = "";
	           		$datas["pagetitre"] ="Backup";
	           		$datas["template"] ="backup";	
	           		$datas["backups"] = dirToArray_simple($app["path"]["root"]."storage/sauveguardes/");
	           		
	           		twig("backup",$datas);
	           		
	           }else{
	           	twig("404",$datas);
	           }

	    	   
	    	   
	           
	            	
	    }
	    
	    
	    
	    
	    public function loadbdd($file) {
	    	global $app;	

	    	
	    	singleton("structure")->down();
	    	singleton("structure")->up();
	    	$urljson = $app["path"]["root"]."storage/sauveguardes/".$file.".json";
	    	$json = file_get_contents($urljson);
			if (is_json($json)) {
	    		$obj = json_decode($json,true);
	
	    		
	    		$return["parametres"] = $obj["parametres"];
	    		$return["folders"] = $obj["folders"];
	    		$return["modules"] = $obj["modules"];
	    		$return["articles"] = $obj["articles"];
	    		$return["users"] = $obj["users"];
	  
	    		
	    		/*parametres*/
	    		$req = new Parametre;
	    		foreach ($return["parametres"] as $key => $value) {
	    		
	    			if( singleton("shema")->hasColumn('parametres', $key) ){  
	    			 	$req->$key = $value;
	    			}
	    			
	    		}
	    		$req -> save();
	    		$idparams = $req->id;
	    		
	    		$languages = singleton("crud")->localize($idparams);
	    		
	    		$folders = [];
	    		
	    		/*folder*/
	    		foreach ($return["folders"] as $folder) {
		    		$req = new Folder;
		    		$folders[] = $folder["id"];
		    		foreach ($folder as $key => $value) {
		    			if( singleton("shema")->hasColumn('folders', $key) ){  
		    			$req->$key = $value;
		    			
		    			}
		    		}
		    		$req -> save();
	    		}
	    		
	    		$list_moduleslug =[];
	    		/*modules*/
	    		foreach ($return["modules"] as $module) {
	    			$req = new Module;
	    			foreach ($module as $key => $value) {
	    				if ($value == NULL):		$value = "";		endif;
	    				
	    				if( singleton("shema")->hasColumn('modules', $key) ){  
	    				$req->$key = $value;
	    				}
	    			}
	    			$req -> save();
	    		}
	    		
	    		//singleton("db")->init_db();
	    		
	    		foreach ($return["modules"] as $module) {
	    			foreach ($module as $key => $value) {
	    				//if(!singleton("structure")->checkcolumn($key)):
	    				singleton("structure")->add_column($module,$languages);
	    				//endif;
	    			}
	    			$list_moduleslug[] = $key;
	    			
	    		}
	    		
	    		
	
	    		/*articles*/
	    		foreach ($return["articles"] as $article) {
	    			if (in_array($article["folder"],$folders)) {
		    			$req = new Article;
		    			foreach ($article as $key => $value) {
		    				if ($value == NULL):	$value = "";		endif;
		    				if( singleton("shema")->hasColumn('articles', $key) ):
		    				$req->$key = $value;
		    				endif;
		    			}
		    			$req -> save();
	    			}
	    		}
	    		
	    		
	    		
	    		/*users*/
	    		foreach ($return["users"] as $user) {
	    			$req = new User;
	    			foreach ($user as $key => $value) {
	    				if( singleton("shema")->hasColumn('users', $key) ):
	    				$req->$key = $value;
	    				endif;
	    			}
	    			$req -> save();
	    		}
	    		
	    		
	    		
	    		$result["status"] = "true";
	    			
	    		
	    		
	    	}else{
	    		$result["status"] = "true";
	    		$result["errors"][] = "Invalid json.";
	    	}
	    	return $result;

	    	//json($result);
	    }
	     
	    public function loadbackup($route) {
	    	global $app;	
	    	$file = $route->id;
	    	
	    	$result = $this->load_backup($file);
	    	json($result);die();
	    }
	    
		public function load_backup($file) {
		    	global $app;	
		    	$result = array("status"=>false,"datas"=>[],"errors"=>[]);
		    	
		    	
		    	//$this->export();
		    	singleton("structure")->down();
		    	singleton("structure")->up();
		    	
		    	$urljson = $app["path"]["root"]."storage/sauveguardes/".$file.".json";
		    	//$urljson = $app["path"]["root"]."base.json";
		    	$json = file_get_contents($urljson);
		    	
		    	if (is_json($json)) {
		    		$obj = json_decode($json,true);
		
		    		
		    		$return["parametres"] = $obj["parametres"];
		    		$return["folders"] = $obj["folders"];
		    		$return["modules"] = $obj["modules"];
		    		$return["articles"] = $obj["articles"];
		    		$return["users"] = $obj["users"];
		  
		    		
		    		/*parametres*/
		    		$req = new Parametre;
		    		foreach ($return["parametres"] as $key => $value) {
		    		
		    			if( singleton("shema")->hasColumn('parametres', $key) ){  
		    			 	$req->$key = $value;
		    			}
		    			
		    		}
		    		$req -> save();
		    		$idparams = $req->id;
		    		
		    		$languages = singleton("crud")->localize($idparams);
		    		
		    		$folders = [];
		    		
		    		/*folder*/
		    		foreach ($return["folders"] as $folder) {
			    		$req = new Folder;
			    		$folders[] = $folder["id"];
			    		foreach ($folder as $key => $value) {
			    			if( singleton("shema")->hasColumn('folders', $key) ){  
			    			$req->$key = $value;
			    			
			    			}
			    		}
			    		$req -> save();
		    		}
		    		
		    		$list_moduleslug =[];
		    		/*modules*/
		    		foreach ($return["modules"] as $module) {
		    			$req = new Module;
		    			foreach ($module as $key => $value) {
		    				if ($value == NULL):		$value = "";		endif;
		    				
		    				if( singleton("shema")->hasColumn('modules', $key) ){  
		    				$req->$key = $value;
		    				}
		    			}
		    			$req -> save();
		    		}
		    		
		    		//singleton("db")->init_db();
		    		
		    		foreach ($return["modules"] as $module) {
		    			foreach ($module as $key => $value) {
		    				//if(!singleton("structure")->checkcolumn($key)):
		    				singleton("structure")->add_column($module,$languages);
		    				//endif;
		    			}
		    			$list_moduleslug[] = $key;
		    			
		    		}
		    		
		    		
		
		    		/*articles*/
		    		foreach ($return["articles"] as $article) {
		    			if (in_array($article["folder"],$folders)) {
			    			$req = new Article;
			    			foreach ($article as $key => $value) {
			    				if ($value == NULL):	$value = "";		endif;
			    				if( singleton("shema")->hasColumn('articles', $key) ):
			    				$req->$key = $value;
			    				endif;
			    			}
			    			$req -> save();
		    			}
		    		}
		    		
		    		
		    		
		    		/*users*/
		    		foreach ($return["users"] as $user) {
		    			$req = new User;
		    			foreach ($user as $key => $value) {
		    				if( singleton("shema")->hasColumn('users', $key) ):
		    				$req->$key = $value;
		    				endif;
		    			}
		    			$req -> save();
		    		}
		    		
		    		
		    		
		    		$result["status"] = "true";
		    			
		    		
		    		
		    	}else{
		    		$result["status"] = "false";
		    		$result["errors"][] = "Invalid json.";
		    	}
		    	return $result;
		    }
	    
	    
	    public function deletebackup($route) {
	    	global $app;
	    	
	    	$result = array("status"=>false,"datas"=>[],"errors"=>[]);
	    	
	    	$file = $route->id;
	    	$path = $app["path"]["root"]."storage/sauveguardes/";
	    	if (file_exists($path.$file.".json")) {
	    		unlink($path.$file.".json");	
	    		$result["status"] = "true";
	    	}
	    	
	    	
	    	json($result);	
	    }
	    
	    
	    
	   
		
		
		public function home() {
		       global $app;
		       
		       $datas = [];
		       $datas = $this->initpage();
				
			   $datas["pagetitre"] = $datas["languages"]["categorie"];
			   $datas["template"] ="home";	
		       twig("home",$datas);
		       
		       
		       
		        	
		}
		
		public function users() {
		       global $app;
		       
		       $datas = [];
		       $datas = $this->initpage();
			   $users = singleton('crud')->read("users");	
			   if( 	$users["status"]=="true"){
			   	$app["users"] = $users["datas"];
			   }
			   
			   $datas["return"] = "";
			   $datas["pagetitre"] ="Utilisateurs";
			   $datas["template"] ="users";	
			   
		       twig("users",$datas);
		       
		        	
		}
		
		
		
		public function paginator($query, $pagination){
		        $limit = $pagination['limit'];
		        $offset = $pagination['page'] * $pagination['limit'];						
		        return $query->take($limit)->skip($offset)->get();
		}
		public	function getnumberpage($limit,$size,$page) {
			$result = ceil($size / $limit);
			return $result-1;
		}
		public function calcul_paginate($pagesize,$page) {
			$next = "Null";
			$prev="Null";
			//dd($pagesize);
			if ($page<=($pagesize-1)) {
				$next=$page+1;
			}
			if ($page>0) {
				$prev=$page-1;
			}
			return array($next, $prev);
		}
		
		public function articles($route) {
		       global $app;
		       
		       $datas = [];
		       $id = $route->folder;
		       $limit = 800;
		       Session::forget('ref');
		       Session::put('lang', strtolower($app["config"]["default_lang"]));
		       	//$page=0;
		       	if (isset($route->page) && $route->page!=""):	$page=$route->page;
		       	else: 											$page=0;
		       	endif;
		       	$datas["pagenumber"] = $page;
		       	
		       $datas = $this->initpage();
		       $datas["cat"] = $id;	
			   $folder = singleton('crud')->read("folders",$id);	
			   //dd($folder);
			   if( 	$folder["status"]=="true"){
			   		
			   		$datas["folder"] = $folder["datas"];
			   		
			   		if(
			   			$datas["user"]["type"]=="1" || 
			   			$datas["user"]["type"]=="2" && in_array("u",  $datas["folder"]["perm2"]) || 
			   			$datas["user"]["type"]=="3" && in_array("u",  $datas["folder"]["perm3"])
			   		):
			   			$default = strtolower($app["config"]["default_lang"]);
			   			if($datas["folder"]["orderbydate"]=="true"):
			   				$query = Article::where(["folder"=>$id,"ref"=>""])->orderBy('bydate', 'asc'); //"lang"=>$default
			   			else:
			   				$query = Article::where(["folder"=>$id,"ref"=>""])->orderBy('ordre', 'asc'); // "lang"=>$default
			   			endif;
			   				
			   			$test = $query->get();
			   			$size = $test->count();
			   			$result = $this->paginator($query, ["limit"=>$limit,"page"=>$page]);   
			   			if (!$result->isEmpty()){
			   					
			   				$datas["articles"] = $result->toArray();
			   				$datas["pagenumber"] = $this->getnumberpage($limit,$size,$page);
			   				$datas["pageactual"] = $page;
			   					
			   				}else{
			   					$datas["articles"] = [];
			   					$datas["pageactual"] = $page;
			   					$datas["pagenumber"] =0;
			   				}
			   			/* end */	
			   			
			   			list($datas["next"],$datas["prev"]) = $this->calcul_paginate($datas["pagenumber"],$page);
			   			//dd($datas);
			   			$datas["return"] = "";
			   			$datas["pagetitre"] =$datas["folder"]["titre"]."/";
			   			$datas["template"] ="articles";	
			   			talk($datas);
			   			twig("articles",$datas);
			   		
			   		else:
			   			twig("404");
			   		endif;	
			   		 
			   }else{
			   	twig("404");
			   }	
			     	
		}
		
		public function article($route) {
		       global $app;
		       
		       $datas = [];
		       $id = $route->id;
		       $cat = $route->folder;
		       
		       $datas = $this->initpage();
		      
		       $datas["template"] ="article";
			   $datas["pagetitre"] ="Article";
			   $categ = singleton('crud')->read("folders",$cat);
			   $modules = singleton("backend")->get(singleton('crud')->read("modules"));
			   $datas["modules"] = $modules;
			   $tpls = $app["project_files"]["root"]["tpl"];
			   foreach ($tpls as $key => $item):
			   	if(is_array($item)):unset($tpls[$key]);endif;
			   endforeach;
			   $app["tpls"] = $tpls;
			   
			  
			   
			   $datas["return"] = "articles/".$cat."/";
			   $datas["cat_id"] = $cat;
	
				if(
					$datas["user"]["type"]=="1" && $categ["status"]=="true" || 
					$datas["user"]["type"]=="2" && $categ["status"]=="true" && in_array("u",  $categ["datas"]["perm2"]) || 
					$datas["user"]["type"]=="3" && $categ["status"]=="true" && in_array("u",  $categ["datas"]["perm3"]) 
				):	
			   		$datas["folder"] = $categ["datas"];
			   		
			   		  if ($id!="new") {
			   				   		$datas["new"] = false;
			   				   		$article = singleton('crud')->read("article",$id);
			   				   		
			   				   		
			   					   
			   					   if( 	$article["status"]=="true" && $categ["status"]=="true"	){
			   					   		
			   					   		$datas["article"] = $article["datas"];
			   							$datas["pagetitre"] = $article["datas"]["titre"];
			   					   		$datas["cat"]= $categ["datas"];
			   					   		$datas["pagetitre"] =$datas["cat"]["titre"]."/".$datas["article"]["titre"];
			   					   		
			   					   		
			   					   		
			   					   		
			   					   		//talk($datas["refexist"]);
			   					   		
			   					   		
			   					   		
			   					   		
			   					   		
			   					   		if($datas["cat"]["forcemodules"]=="true"){
			   					   			
			   					   			$datas["article"]["modules"] = $datas["cat"]["modules"];
			   					   			$datas["modules_ids"] = $datas["cat"]["modules"];
			   					   			//dd($datas["modules_ids"]);		
			   					   		}else{
			   					   			
											if (
				   					   			$datas["article"]["modules"]=="" || 
				   					   			is_array($datas["article"]["modules"]) && empty($datas["article"]["modules"])
				   					   		) {
			
				   					   			$datas["article"]["modules"] = $datas["cat"]["modules"];
				   					   			$datas["modules_ids"] = $datas["cat"]["modules"];
				   					   		
				   					   		}else{
				   					   			$datas["modules_ids"] = $datas["article"]["modules"];
				   					   		}
			   					   			
			   					   		}
			   					   		
			   					   		
			 
			   					   			$modules_Art = [];
			   					   			foreach ($datas["modules"] as $key => $module) {
			   					   				if (is_array($datas["article"]["modules"]) && in_array($module["id"],$datas["article"]["modules"])	):
			   					   					//$mod = [];
			   					   					$mod = $module;
			   					   					//$modules_Art[$key] = $module;
			   					   					$slug = $module["slug"];
			   					   					
			   					   						if(
			   					   								$module["type"] == "module_articles" || 
			   					   								$module["type"] == "module_article"
			   					   						){
			   					   							
			   					   								$module_cat = $module["cat"];
			   					   								$art = singleton('crud')->read("articles",$module_cat);
			   					   								//dd($art);
			   					   								if(	$art["status"]=="true" ){
			   					   									$mod["options"] = [];
			   					   									foreach ($art["datas"] as $module_article) {
			   					   										$module_article_titre = $module_article["titre"];
			   					   										$module_article_id = $module_article["id"];
			   					   										$mod["options"][] = ["id"=>$module_article_id,"titre"=>$module_article_titre];
			   					   									}
			   					   								}else{
			   					   									$mod["options"] = [];
			   					   								}
			   					   								
			   					   						}
			   					   							
	
			   					   						if (isset($datas["article"][$slug])) {
			   					   							$mod["value"] = $datas["article"][$slug];	
			   					   							
			   					   							
			   					   						}else{
			   					   							$mod["value"] = "";
			   					   						}
			   					   								
	
			   					   					$modules_Art[] = $mod;
			   					   					
			   					   				endif;
			   					   			}
			   					   			$datas["article"]["modules"] = $modules_Art;
			   					   			
			   					   			//dd($datas["languages"]);		   				   		
			   					   			twig("article",$datas);
			   					   		
			   		
			   					   		
			   					   		
			   					   		
			   					   	}else{
			   					   		twig("404");
			   						}  
			   						
			   				   }else{ 
			   						
			   						$datas["new"] = true;
			   				   		$categ = $categ["datas"];
			   				   		$datas["pagetitre"] =$categ["titre"]."/New";
			   				   		
			   				   		$datas["cat"] = $categ;
			   				   		$datas["modules_ids"] = $categ["modules"];
			   				   		
			   				   		
			   				   		
			   				   		$modls = [];
			   				   		if (isset($categ["modules"]) && is_array($categ["modules"])	) {
			   				   			
			   				   		
			   					   		foreach ($categ["modules"] as $key => $value) {
			   					   			foreach ($modules as $module) {
			   					   				if ($module["id"]==$value) {
			   					   					
			   					   					
			   					   					$modls[$key] = $module;
			   					   					if(
			   					   						$module["type"] == "module_articles" || 
			   					   						$module["type"] == "module_article"
			   					   					){
			   					   								
			   					   									$module_cat = $module["cat"];
			   					   									$art = singleton('crud')->read("articles",$module_cat);
			   					   									//dd($art);
			   					   									if(	$art["status"]=="true" ){
			   					   										$modls[$key]["options"] = [];
			   					   										foreach ($art["datas"] as $module_article) {
			   					   											$module_article_titre = $module_article["titre"];
			   					   											$module_article_id = $module_article["id"];
			   					   											$modls[$key]["options"][] = ["id"=>$module_article_id,"titre"=>$module_article_titre];
			   					   										}
			   					   									}else{
			   					   										$modls[$key]["options"] = [];
			   					   									}
			   					   									
			   					   									
			   					   					}
			   					   					
			   					   					
			   					   					
			   					   					
			   					   				}
			   					   			}
			   					   		}
			   					   		//$datas["article"]["modules"] = $modules_Art;
			   					   		
			   					   		
			   				   		}
			   				   		
			   				   		
			   				   		
			   				   		if (isset($route->lang) && isset($route->ref)) {
			   				   			
			   				   			$lang = strtolower ( $route->lang);
			   				   			$ref = $route->ref;
			   				   			//Session::put('lang', $lang);
			   				   			//$ref = Session::get("ref");
			   				   		 	//talk("ref: ".Session::get('ref'));
			   				   		 	
			   				   		}else{
			   				   			//Session::forget('ref');
			   				   			//Session::put('lang', $app["config"]["default_lang"]);
			   				   			
			   				   			$lang = strtolower($app["config"]["default_lang"]);
			   				   			$ref = "";
			   				   		}
			   				   		$datas["article"] = [
			   				   	     "titre" => "New",
			   				   	     "url" => "",
			   				   	     "ordre"=>0,
			   				   	     "folder" => $cat,
			   				   	     "lang" => $lang,
			   				   	     "title" => "",
			   				   	     "description" => "",
			   				   	     "tags" => "",
			   				   	     "ref" => $ref,
			   				   	     "modules" => $modls,
			   				   	     "template" => "",
			   				   	     "statut" => "false"
			   				   	     ]; 
			   				   	     
			   				   	     
			   				   	     $datas["return"] = "articles/".$cat."/";
			   				   	     twig("article",$datas);
			   				  
			   				  
			   				   } 
			   		
			   	else:
			   		twig("404",$datas);
				endif;
				
				
				
				
			
			
					   					
			  	
		}
		
		
		
				
		
		public function modules(){
		
			global $app;
			
			$datas = [];
			$datas = $this->initpage();
			$modules = singleton('crud')->read("modules");
			if( $modules["status"]=="true" ){
				if(
					$datas["user"]["type"]=="1" || 
					$datas["user"]["type"]=="2" && in_array("u", $datas["parametres"]["permissions_modules"])
				){
					$datas["modules"] = $modules["datas"];
					$datas["return"] = "";
					$datas["pagetitre"] ="Content Types";
					$datas["template"] ="modules";
					twig("modules",$datas);
				}else{
					twig("404");
				}
			}
			
			
			
			
		}
		
		public function folder($route){
		
			global $app;
			$datas = $this->initpage();
			
			$id = $route->id;
			
			$datas["modules"] = singleton("backend")->get(singleton('crud')->read("modules"));
			
			if ($id!="new") {
				$folder = singleton('crud')->read("folders",$id);
				
				if( $folder["status"]=="true" ){
				
					$folder = $folder["datas"];
					
					if(
						$datas["user"]["type"]=="1" || 
						$datas["user"]["type"]=="2" && in_array("u", $datas["parametres"]["permissions_folders"])
					){
						$datas["folder"] = $folder;
					}
					
				}
				$datas["new"] = false;
			}else{
				$datas["new"] = true;
				$datas["folder"] = [
					"id" => "new",
					"ref" => "",
					"template" => "",
					"titre" => "",
					"orderbydate" => "false",
					"modules" => "",
					"page" => "true",
					"perm2" => array("u","c","d"),
					"perm3" => array( "c")
				];
			}
			
			$folder_tpl_site= $app["project_files"]["root"]["tpl"];
			
			
			foreach ($folder_tpl_site as $key => $item) {
				if(is_array($item)){
					unset($folder_tpl_site[$key]);
				}
			}
			
			$datas["tpls"] = $folder_tpl_site;
	
			if(empty($datas["folder"])):
				twig("404");
			else:
				$datas["return"] = "";
				$datas["pagetitre"] ="Folder";
				$datas["template"] ="folders";
				twig("folder",$datas);
			endif;
			
			
		}
		
		public function module($route){
		
			global $app;
			$datas = $this->initpage();
	
			
			$id = $route->id;
			$datas["id"] = $id;
			$datas["template"] ="module";
			
			if ($datas["id"]!="new") {
				$data = singleton('crud')->read("modules",$id);
				$datas["module"] = $data["datas"];
				$datas["new"] = false;
	
			
			}else{
			
				$datas["new"] = true;
				$datas["module"] = [
					"id"=>"new",
					"titre"=>"new",
					"slug"=>"",
					"options"=>"",
					"commentaire"=>"",
					"type"=>"texte",
					"cat"=>"",
					"traductible"=>"false"
				];
			}
			if(
				!empty($datas["module"]) && $datas["user"]["type"]=="1" || 
				!empty($datas["module"]) && $datas["user"]["type"]=="2" && in_array("u", $datas["parametres"]["permissions_modules"])
			):
				$datas["return"] = "modules/";
				$datas["pagetitre"] ="Module - ".$datas["module"]["titre"];
				twig("module",$datas);
				
			else:
				twig("404");
			endif;
			
			
		}
		
		
		public function parametre() {
			global $app;
			$datas = $this->initpage();	
			
			if(empty($datas["parametres"])):
				twig("404");
			else:
				$datas["return"] = "";
				$datas["pagetitre"] ="Paramètres";
				twig("parametres",$datas);
			endif;	
	
		}
		
		
		
		public function user($route) {
			global $app;
			$datas = $this->initpage();		
			$id = $route->id;
			
			if ($id!="new") {
				$userlocal = singleton('crud')->read("users",$id);
				$datas["userlocal"] = $userlocal["datas"];
				$datas["new"] = false;
	
			}else{
				$datas["new"] = true;
	
				$datas["userlocal"] = [
					"id" => "new",
					"titre" => "new",
					"email" => "",
					"password" => "",
					"type" => "3",
					"nom" => "",
					"prenom" => "",
					"societe" => "",
					"tel" => "",
					"infos" => "",
					"statut" => "false"
				];
				
					  
			}
			
			if(empty($datas["userlocal"])):
				twig("404",$datas);
			else:
				$datas["pagetitre"] =$datas["userlocal"]["email"];
				$datas["return"] = "users/";
				$datas["template"] ="user";
				twig("user",$datas);
			endif;
			
			
		}     
		   
		    
		    
	
	    
	
	    
	    public function connect($route) {
	       global $app;
	       $datas = [];
	       //dd(haash("pass"));
	       //dd($app["path"]["public"]."install/index.php");
	       twig("connection",$datas);
	
	        
	    }
	    
	
	    
	    public function reminder() {
	    	global $app;
	    	$datas = [];
	    	if(singleton("auth")->check()): 
	    		header('Location: '.$app["url"]["local"]); 
	    	endif;
	    	
	    	twig("mdpo",$datas);
	    }
	    
	    public function remindersubmit() {
	    	
	    	global $app;
	    	$result = array("status"=>false,"datas"=>[],"errors"=>[]);
	    	
	    	if (
	
	    		ine("email",$_POST) && 
	    		valid_email($_POST["email"])
	    	) {
	    		$email = $_POST["email"];
	    		$user_email = User::where(["email"=>$email])->get();	
	    		
	    		/*ok*/
	    		if (!$user_email ->isEmpty()){
	    			$datas = $user_email->first()->toArray();
	    			
	    			$id = $datas["id"];
	    			$newpass=randomkey(5);
	    			$newpass_encode = haash($newpass);
	    		
	    		$req = User::find($id);
	    		$req->password = $newpass_encode;
	    		$req->save();
	    			
	    			$useremail = $datas["email"];
	    			$usertitre = $datas["titre"];
	
	    			
	    			$setings =[
	    			"subject"=>"Drgstr2 - Mot de passe oublié."
	    			,"email"=>$useremail
	    			,"name_to"=>$usertitre
	    			,"name"=>"Drgstr CMS"
	    			,"type"=>"normal"
	    			,"content"=>"Bonjour,
	    			
	Un nouveau mot de passe à usage unique a été généré.
	
	Identifiant : ". $useremail ."
	Nouveau mot de passe  : ".$newpass."
	
	Rendez-vous sur la page  ". $app["url"]["local"] ." et saisissez alors les données ci-dessus pour vous connecter.
	
	Cordialement
	
	Drgstr"
	    			/*,"attachement"=>$app["uploads"]."big_1bff79f3956743876caf2a30209585b6.jpg"*/
	    			];
					
					mailer($setings);
					
	    			$result["status"]= true;
	
	    			
	    		/*Non trouvé*/	
	    		}else{
	    			$result["status"]= false;
	    			$result["errors"]= "Erreur e-mail.";
	    		}
	    		
	    	/*email invalide	 */	
	    	}else{
	    		$result["status"]= false;
	    		$result["errors"]= "Erreur e-mail.";
	    	}
	    	
	    	json($result); 
	    	
	    }
	    
		
	    
	    public function changelang(){
	       	global $app;
	    
	       	$result = "";
	       	if (isset($_POST["lang"])) {
	       		$lang = $_POST["lang"];
	       		Session::put('lang', $lang);
	       		json(array("result"=>true));
	       	}else{
	       		json(array("result"=>false));
	       	}
	    }
	       
	       
	       
	    /*public function encodeurl(){
	    	global $app;
	 
	    	$result = "";
	    	if (isset($_POST["chaine"])) {
	    		$chaine = $_POST["chaine"];
	    		$result = slug($chaine,"_");	
	    		$test_slug = Article::where(["url"=>$result])->get();
	    		if (!$test_slug->isEmpty()){
		    		$size = $test_slug->count();	
		    		if($size>1){
		    			$result = $result."_".$size;
		    		}
	    		}
	    	}
	    	
	    	json(array("result"=>$result));
	    	
	    }*/
	    
	    public function encodeurl(){
	       	global $app;
	    
	       	$result = "";
	       	
	       	if (isset($_POST["chaine"]) ) {
	       		

	       		$chaine = $_POST["chaine"];
	       		
	       		if(isset($_POST["id"])):
					$id = $_POST["id"];
					$result = singleton('crud')->encodeurl($_POST["chaine"],$_POST["id"]);
				else:
					$result = singleton('crud')->encodeurl($_POST["chaine"]);
				endif;
	       	}
	       		
	       			
	       		       
	       	json(array("result"=>$result));
	       	
	     }
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    /* ---------------
	     * helpers
	       --------------- */
		
		public function initpage(){
		    	global $app;
		    	$datas = [];
		    	
		    	$parametres = singleton('crud')->read("parametres");
		    	$folders = singleton('crud')->read("folders");
		
		    	$user = singleton("auth")->get();
		    	if( 	
		    		$parametres["status"]=="true" && 
		    		$folders["status"]=="true" 
		    	){
		    			$datas["parametres"] = $parametres["datas"];
		    			$datas["folders"] = $folders["datas"];
		    			$datas["user"] = $user;
		    	}
		    	//$datas["languages"] = $app["languages"];
		    	$datas["languages"] = $this->lang_trad($app["languages"]);
				$app["languages"] = $datas["languages"];
		    	$datas["lang"] = strtolower($app["config"]["default_lang"]);
		    	$datas["default_lang"] =  strtolower($app["config"]["default_lang"]);
		    	return $datas;
		}
		    
		    
		    
		    
		public function get($json) {
			
			if( 	$json["status"]=="true"):
				$return= $json["datas"];
			else:
				$return = [];
			endif;
			
			return $return;
		}
		
		public function get_array_files($path){
			global $app;
			
			
			if(is_dir($path)){
				
				if($dossier = opendir($path)){
					$my_array=array();
					
					while(false !== ($fichier = readdir($dossier))){
						if($fichier!=".DS_Store" && $fichier!="." && $fichier!=".."){
						$name_file=explode(".twig", $fichier);
						$my_array[]= $name_file[0];
						
						
						}
					}
					return $my_array;
				}
			}else{	
				dd("Le dossier est introuvable à l'adresse: ".$path);
	
			}
		}
		
		public function compresshtml($buffer) {
				
				    $search = array(
				        '/\>[^\S ]+/s',  /* strip whitespaces after tags, except space*/
				        '/[^\S ]+\</s',  /* strip whitespaces before tags, except space*/
				        '/(\s)+/s'       /* shorten multiple whitespace sequences*/
				    );
				
				    $replace = array(
				        '>',
				        '<',
				        '\\1'
				    );
				
				    $buffer = preg_replace($search, $replace, $buffer);
				
				    return $buffer;
		}
		
		
		
		/*________________ formater urlrewriting articles ______________*/
		
		public function format_name_module($string){
		    
		    $string = utf8_decode($string);
		    $string = str_replace("'",'"',$string);
		    $string = strtolower(strtr($string, utf8_decode('ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ()[]\'"~$&%*@ç!?;,:/\^¨€{}<>|+.- @&é"(§è!çà)-_°^¨%ù`$*/.+?%'), 'aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn____________c_________e___________e___e_ca_______u_________'));
		    $string = str_replace(' ', '_', $string);
		    $string = str_replace('___', '_', $string);
		    $string = str_replace('__', '_', $string);
		    $string = trim($string,'_');
		    
		    return $string;
		}
		
}