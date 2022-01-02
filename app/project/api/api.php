<?php if ( ! defined('in_app')) die('Accès Interdit');
	
	class api  {
		
		
		
		public	function  route() {
			global $app;
			
			//singleton("session")->start();
			//singleton("session")->set_time(30);
			$app["auth"] = singleton("auth")->check();
			
			
			//dd($app["auth"]);
			/*
			| -------------------------------------------
			| api
			| -------------------------------------------
			*/
			$b = "api/v1/";
			//dd(filter_url($b.'login',$app["curent_url"]));
			if(filter_url("/api/",$app["curent_url"]) ) :
				
				$app["fulljson"]=true;
				
				singleton("route")->match('GET',$b.'read/:type', "api@read");
				singleton("route")->match('GET',$b.'read/:type/:id/', "api@read");
				singleton("route")->match('get',$b.'imagesjson/', "api@imagesjson");
				singleton("route")->match('get',$b.'imagesjson/:id', "api@imagesjson");//because ?_=1505858043016

				//get ??? really?		
				if($app["auth"]): 
					singleton("route")->match('post',$b.'exit', "api@logout");
					
					singleton("route")->match('post',$b.'create/:type/', "api@create");
					singleton("route")->match('post',$b.'update/:type/:id', "api@update");
					singleton("route")->match('post',$b.'delete/:type/:id', "api@delete");
					singleton("route")->match('post',$b.'ordre', "api@ordre");
					
					singleton("route")->match('post',$b.'upload', "api@upload");
					singleton("route")->match('post',$b.'uploads', "api@uploads");
					singleton("route")->match('post',$b.'uploadwysiwyg', "api@uploadwysiwyg");
					
					
					singleton("route")->match('post',$b.'uploadsbuilder', "api@uploadsbuilder");
                    
                    singleton("route")->match('post',$b.'uploads2builder', "api@uploads2builder");

					singleton("route")->match('post',$b.'deleteUploadbuilder', "api@deleteUploadbuilder");
                    singleton("route")->match('post',$b.'updatebuilder', "api@updatebuilder");

                    
					
					
					
					//singleton("route")->get($b."test",function($route){});
				//else: 
					
				endif;
				
				
				//singleton("route")->match('GET',$b.'read/:type', "api@read");
				//singleton("route")->match('GET',$b.'read/:type/:id/', "api@read");
				
				singleton("route")->match('post',$b.'login', "api@connect");
				singleton("route")->match('post',$b.'check', "api@checkconnect");
				
				
				/*
				| -------------------------------------------
				| 404
				| -------------------------------------------
				*/
				singleton("route")->check_404(function () { 
				   global $app;
				   json(array("status"=>false,"datas"=>[],"errors"=>["404"])); 
				   die();
				});
				
				
			endif;
		}
		public function init(){}
		
		
/*		
    	|--------------------------------------------------------------------------
    	| connect
    	|--------------------------------------------------------------------------
    	|
    	*/
    	public function connect(){
    		global $app;
    		

    		$result = array("status"=>false,"datas"=>[],"errors"=>[]);
    		
    		
    		if(isset($_POST) && control_values(["log","pass"],$_POST)){
    			if(valid_email($_POST["log"])){
    				singleton("auth")->connect($_POST["log"],$_POST["pass"]);
    				
    			}
    		}
    		if(singleton("auth")->check()): 
    			if (file_exists($app["path"]["public"]."install/index.php")) {
    				delTree($app["path"]["public"]."install/");
    			}
    			$result["status"] = true;
    		endif;
    		
    		json($result); 
    		
    	}
    	
    	/*public function checkinstallfolder() {
    		global	$app;
    		if (file_exists($app["path"]["public"]."install/index.php")) {
    			if (is_dir($app["path"]["public"]."install/")) {
    			    rmdir($app["path"]["public"]."install/");
    			}
    		}
    		
    	}*/
    	
    	
    	
    	public function checkconnect() {
    		global $app;
    		
    		$result = array("status"=>"false","datas"=>[],"errors"=>[]);
    		
    		if(singleton("auth")->check()): 
    			$result["status"] = "true";
    		endif;
    		
    		json($result); 
    	}
    	
    	
    	
    	
    	
    	
    	public function logout(){
    		
    		global $app;

    		
    		singleton("auth")->delete();
    		json(array("status"=>true,"datas"=>[],"errors"=>[])); 
    		
    	}
    	
    	public function create($route)
    	{
    		global $app;
    		
    		$type = $route->type;
    		if (isset($route->id)) {
    			$id= $route->id;
    		}
    		$return = array("status"=>false,"datas"=>[],"errors"=>[]);
    		
    		$allowed = array( "articles","users","folders","modules","parametres");

    		$datas = $_POST;
    		
    		/*check auth*/
    		if (singleton("auth")->check() && in_array($type, $allowed)){
    			
    			/*ok go !*/
    			$return = singleton('crud')->create($type,$datas);
                //dd($return1["datas"]["id"]);
                

                //$return= singleton('crud')->update($type,$id_new,$datas);
    				
    		}
    		
       
    		/*return */
    		json($return);
    		
    	}
    	
    	
    	
    	
    	public function read($route)
    	{
    		
    		global $app;
    		if(isset($route->type)) : $type = $route->type;endif;
    		if(isset($route->id)) : $id = $route->id;endif;
    		$return = array("status"=>false,"datas"=>[],"errors"=>[]);
    		
    		$allowed = array( "articles","article","users","folders","modules","parametres");
    		
    		
    		/*check auth*/
    		if (in_array($type, $allowed)){
    		
    				/*ok go !*/
    				if(isset($route->id)) :
    				$return = singleton('crud')->read($type,$id);
    				else:
    					$return = singleton('crud')->read($type);
    				endif;
    			
    		}		
    
    		
    		/*return*/ 
    		json($return); 
    		
    	}
    	
    	
    	public function update($route){
    		
    		$return = array("status"=>false,"datas"=>[],"errors"=>[]);
    		
    		if(isset($route->type)) : $type = $route->type;endif;
    		if(isset($route->id)) : $id = $route->id;endif;
    		
    		//special builder
    		
    		
    		$allowed = array( "articles","users","folders","modules","parametres");
    		$datas = $_POST;
			
			if(isset($_POST["region_slug"]) && $_POST["region_slug"]!=""):
				$slug = $_POST["region_slug"];
				$datas[$slug] = $_POST["content"];
				unset($datas["region_slug"]);
				if($slug!="content"):unset($datas["content"]);endif;
				//print_r($datas);
			endif;
    		
    		/*check auth*/
    		if (in_array($type, $allowed)){
    			
    			/*ok go !*/
    			$return = singleton('crud')->update($type,$id,$datas);
    			
    			
    				
    		}
    
    		/*return*/ 
    		json($return);
    	}
    	
    	public function ordre(){
    	    		
    	    		$return = array("status"=>false,"datas"=>[],"errors"=>[]);
    	    		
    	    		if(isset($route->type)) : $type = $route->type;endif;
    	    		if(isset($route->id)) : $id = $route->id;endif;
    	    		
    	    		$allowed = array( "articles","users","folders","modules","parametres");
    	    		$datas = $_POST;
    				
    				
    	    		if(
    	    			isset($datas["table"]) && $datas["table"]!="" && 
    	    			isset($datas["pages"]) && $datas["pages"]!=""
    	    		){
    	    			$type = $datas["table"];
    	    			$ids = $datas["pages"];
    	    			$ref = "";
    	    			if(isset($datas["ref"]) && $datas["ref"]!="") : $ref = $datas["ref"];endif;
    	    			/*ok go !*/
    	    			$return = singleton('crud')->ordre($type,$ids);
    	    		}
    	    		json($return);
    	    	}
    	
    	
    	
    	public function delete($route)
    	{
    		$return = array("status"=>false,"datas"=>[],"errors"=>[]);
    		$allowed = array( "articles","users","folders", "modules","parametres");
    		
    		
    		
    		
    		/*check auth*/
    		if (
    			isset($route->type) && 
    			isset($route->id) && 
    			singleton("auth")->check() 
    		){
    			
    			$type = $route->type;
    			$user = singleton("auth")->get();
    			$id = $route->id;

    			if(in_array($type, $allowed)){
    			    			
	    			if($type=="parametres" && $user["type"]!=1){
	    			
	    			}else{
	    				/* ok go !*/
	    				$return = singleton('crud')->delete($type,$id);
	    				$return["status"] = "true";
	    			}
	    			
    			}else{
    				$return["errors"] = "Action not allowed.";
    				$return["status"] = "false";
    			}
    				
    		}else{
    			$return["status"] = "false";
    		}
    		
    
    		json($return);
    		
    	}
    	
    	
    	
    	
    	
    	public function upload(){
    		global $app;
    		
    		if (isset($_FILES) && sizeof($_FILES)>0) {
    			if (isset($_FILES["image"])): 
    				$file = $_FILES["image"];
    				$media = upload::upload_media($file);
    				$type = upload::type($file['tmp_name']);
    			else:
    				$file = $_FILES[0];
    				$media = upload::upload_media($file);
    				$type = upload::type($file['tmp_name']);
    			endif;
    		}
    		


    		$datas = [
    			"id"=>$media["file"],
    			"title"=>"",
    			"description"=>"",
    			"datetime"=>"",
    			"name_original"=>$file['name'],
    			"link"=>$app["url"]["uploads"]."big_".$media["file"],
    			"type"=>$type
    			
    		];
    		$reponse = [
    			"data"=>$datas,
    			"status"=>"200",
    			"success"=>$media["result"]
    		];

    		json($reponse);
    		
    	} 
    	
    	
    	public function uploads(){
    		global $app;

    		$datas = [];
    		
    		if (isset($_FILES) && sizeof($_FILES)>0) {
    			foreach ($_FILES as $key => $value) {
    				$media = upload::upload_media($_FILES[$key]);
    				$type = upload::type($_FILES["files"]['tmp_name']);
    				$datas[] = [
    					"id"=>$media["file"],
    					"name_original"=>$_FILES["files"]['name'],
    					"link"=>$app["url"]["uploads"]."big_".$media["file"],
    					"type"=>$type
    					
    				];
    			}
    		}

    		$reponse = [
    			"data"=>$datas,
    			"status"=>"true"
    		];
    		json($reponse);
    		
    	}
    	
    	
    	
    	public function imagesjson() {
    		global $app;
    		$listuploads = dirToArray_simple($app["path"]["uploads"]);
    		$result=[];
    		foreach ($listuploads as $file) {
    			if(filter_url("medium_",$file) || filter_url("small_",$file) ) :
    				//nada
    			else:
	    			if(filter_url("big_",$file) ) :
	    				$thumb = str_replace("big_", "small_", $file);
	    			else:
	    				$thumb = $file;
	    			endif;
	    			$result[] = [
	    				"thumb"=> $app["url"]["uploads"].$thumb, 
	    				"url"=> $app["url"]["uploads"].$file, 
	    				"title"=> $file, 
	    				"id"=> $file
	    				];
    			endif;
    		}
    		
    		json($result);
    		
    	}
    	public function uploadwysiwyg(){
			global $app;
    		
    		if (isset($_FILES) && sizeof($_FILES)>0) {
    			if (isset($_FILES["file"])): 
    				$file = $_FILES["file"];
    				$media = upload::upload_media($file);
    				$type = upload::type($file['tmp_name']);
    			endif;
    		}
    		


    		$datas["file-0"] = [
    			"id"=>$media["file"],
    			"url"=>$app["url"]["uploads"].$media["file"]
    		];
    		
    		//$reponse = $datas;

    		json($datas);

    	} 
    

   	public function updatebuilder(){
            global $app;

            $reponse = ["status"=>"false"];
            
            //dd($_FILES["file"]["name"]);
            if (singleton("auth")->check()) {
                $id = $_POST["idArt"];
                $val = $_POST["valeur"];
                //$val = str_replace('<div data-redactor-tag="br">',"<p>",$val);
                //$val = str_replace('</div>',"</p>",$val);
                $slug = $_POST["slug"];

                $reponse = singleton('crud')->update("articles",$id,[$slug=>$val]);
                //$reponse = ["status"=>"true"];
               
            }               
            
            
            json($reponse);
            
    }

    public function uploads2builder(){
            global $app;

            $datas = [];
            
            //dd($_FILES["files"]);
            if (singleton("auth")->check() && isset($_FILES) && sizeof($_FILES)>0) {
                foreach ($_FILES as $key => $value) {

                    //$value= $value;
                    $media = upload::upload_media($value);
                    
                    $file =  $app["path"]["uploads"]."big_".$value['tmp_name'][0];
                    //$size = filesize($file);
                    $type = upload::type($file);
                    
                    $datas[] = [
                        "deleteType"=>"POST",
                        "deleteUrl"=>$app["url"]["root"]."api/v1/deleteUploadbuilder",
                        "name"=>$media["file"],
                    //  "size"=>$size,
                        "thumbnailUrl"=>$app["url"]["uploads"]."medium_".$media["file"],
                        "type"=>$type,
                        "url"=>$app["url"]["uploads"]."medium_".$media["file"]
                    ];

                }
            }               
            $reponse = [
                "files"=>$datas
            ];
            
            json($reponse);
            
    }
/*
        public function uploads2builder(){
            global $app;

            $datas = [];
            
            if (app("auth")->check() && isset($_FILES) && sizeof($_FILES)>0) {
                foreach ($_FILES as $key => $value) {
                    //dd($value);
                    $media = upload::upload_media($value);
                    $type = upload::type($value['tmp_name'][0]);
                    
                    $datas[] = [
                        "deleteType"=>"POST",
                        "deleteUrl"=>$app["url"]["root"]."api/v1/deleteUploadbuilder",
                        "name"=>$media["file"],
                        "size"=>$value["size"][0],
                        "thumbnailUrl"=>$app["url"]["uploads"]."medium_".$media["file"],
                        "type"=>$type,
                        "url"=>$app["url"]["uploads"]."medium_".$media["file"]
                    ];
                }
            }               
            
            $reponse = [
                "files"=>$datas
            ];
            json($reponse);
            
        }
*/



	public function uploadsbuilder(){
    		global $app;

    		$datas = [];
    		//dd($_FILES["file"]["name"]);
			if (singleton("auth")->check() && isset($_FILES["file"])) {
				//foreach ($_FILES as $key => $value) {
					//dd($value);
                $value= $_FILES["file"];
					$media = upload::upload_media($value);
					
					$file =  $app["path"]["uploads"]."big_".$value["name"];
					//$size = filesize($file);
					$type = upload::type($file);
					
					$datas[] = [
						"deleteType"=>"POST",
						"deleteUrl"=>$app["url"]["root"]."api/v1/deleteUploadbuilder",
						"name"=>$media["file"],
					//	"size"=>$size,
						"thumbnailUrl"=>$app["url"]["uploads"]."medium_".$media["file"],
						"type"=>$type,
						"url"=>$app["url"]["uploads"]."medium_".$media["file"]
					];
				//}
			}	    		
			
    		$reponse = [
    			"files"=>$datas
    		];
    		json($reponse);
    		
    	}


    	public function deleteUploadbuilder(){
    		
    		
    		if(empty($_POST['file']))
    		{
    		  exit();
    		}
    		
    		if(singleton("auth")->check() && file_exists($_POST['file']))
    		{
    		  unlink($_POST['file']);
    		}
    		
    	}
    	
    	
    	
    	
		
		
}


