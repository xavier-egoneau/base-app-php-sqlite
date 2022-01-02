<?php if ( ! defined('in_app')) die('Accès Interdit');
	
	class datas  {
		
		
		public function init(){}
		
		
		public	function  route() {
			global $app;
			session_start();

			if (isset($_GET) && sizeof($_GET)>0 && $this->check()  ) {
				
				json($this->read());	
		
			}elseif (
				isset($_POST) && 
				sizeof($_POST)>0 && 
				filter_url("/connect",$app["curent_url"])
			) {
				
				json($this->connect());
			
			}else{
			
				if(filter_url("/parametres",$app["curent_url"]) && $this->check()):
					json($this->parametres());
				elseif(filter_url("/modules",$app["curent_url"]) && $this->check()):
					json($this->modules());
				else:
					json($this->stop());die();
				endif;
			}
			
			
		}
		
		
		
		public function modules() {
			global $app;	
			$this->init_site();
			
			$modules = $app["modules"];
			
			
			return $modules;
			
		}
		
		
		
		public function parametres() {
			global $app;	
			$this->init_site($slug="");
			//dd($app["parametres"]);
			$paramssafe = ["id", "version", "title","description", "keywords", "logo","branding", "shortcut_icon", "analytics", "production", "lang", "langues", "ipfilter", "created_at", "updated_at","rs"];
			$params = $app["parametres"];
			
			foreach ($params as $key => $param) {
				if (!in_array($key,$paramssafe)) {
					unset($params[$key]);
				}
			}
			return $params;
			
		}
		
		
		
		
		public function read($arr=[]) {
			global $app;
			$this->init_site($slug="");
			$filtres=["statut"=>"true"];
			$limit=1;
			$page=0;
			$sortby='ordre';
			$sens='desc';
			
			//$datas = init_site();
			$setting = ["limit","page","sortby","sens"];
			
			if(sizeof($_GET)>0 && isset($_GET["limit"])){
				$arr = $_GET;
			}

			if (isset($arr["limit"]) && $arr["limit"]!="" ):$limit = $arr["limit"]; endif;
			if (isset($arr["page"]) && $arr["page"]!="" ):$page = $arr["page"]; endif;
			if (isset($arr["sortby"]) && $arr["sortby"]!="" ):$sortby = $arr["sortby"]; endif;
			if (isset($arr["sens"]) && $arr["sens"]!="" ):$sens = $arr["sens"]; endif;
			//if (isset($arr["limit"])): $limit = $arr["limit"]; endif;
			
			
			// test injection sql
			foreach ($setting as $set) {
				if (isset($array[$set])) {			
					if(!$this->injectionsqltest($array[$set]) && !$this->injectionsqltest($array[$set])){
							$this->stop();die();
					}
				}
			}
			$setting = [
				"limit"=>$limit,
				"page"=>$page,
				"sortby"=>$sortby,
				"sens"=>$sens
			];
			$settinglist = [
				"limit",
				"page",
				"sortby",
				"sens"
			];
			//print_r($setting)."<br>";
			//print_r($arr);
			foreach ($arr as $key => $value) {
				
				if( !in_array($key, $settinglist) ){
				//echo "toto1";
					if( singleton("shema")->hasColumn('articles', $key)	){  
					
				 		//$filtres[] = [$key => $value];	
				 		if(!$this->injectionsqltest($key) && !$this->injectionsqltest($value)){
				 			$this->stop();die();
				 		}
				 		 $filtres[$key] = $value;	
				 	//test injection sql	 
				 	}else{
				 		$this->stop();die();
				 	}
				}				
			}
			//dd($filtres);
			$req = $this->search($filtres,$setting);		
			
			if (isset($req)) {
				return $req;
			}else{
				$this->stop();die();
			}
			die();
			
		}
		
		
		public function stop() {
			return ["status"=>"false",
					"datas"=>[],
					"errors"=>"404 not found.",

			];
			
		}
		
		
		/*
		Check API session
		*/	   
		public function check() {
			//print_r($_SESSION);
			if (
				isset($_SESSION['connectapi']) && $_SESSION['connectapi']=="true"
			):
				//unset($_SESSION['connectapi']);
				return "true";	
			
			else: 
				return "false";
				
			endif;
			
			
		}	 
		
		
		public function injectionsqltest($id){
			
			
			/*if(preg_match('/\s/', $id)): 
			    return "false toto";
			endif;*/
			if(preg_match('/[\'"]/', $id)): 
			    return false;
			endif;
			if(preg_match('/[\/\\\\]/', $id)): 
			    return false;
			endif;
			if(preg_match('/(and|or|null|not)/i', $id)): 
			    return false;
			endif;
			if(preg_match('/(union|select|from|where)/i', $id)): 
			    return false;
			endif;
			if(preg_match('/(group|order|having|limit)/i', $id)): 
			    return false;
			endif;
			if(preg_match('/(into|file|case)/i', $id)): 
			    return false;
			endif;
			if(preg_match('/(--|#|\/\*)/', $id)): 
			    return false;
			endif;
			if(preg_match('/(=|&|\|)/', $id)): 
			    return false;
			endif;
			
			return true;
		}
		
		
		
		/*
		Connect API session
		*/
		public function connect() {
			
			global $app;
			$result = "false";

			if(isset($_POST["apicode"]) && $_POST["apicode"]!="" ){
				
				
				$params = Parametre::first()->toArray();
				$apikey = $_POST["apicode"];

				if($params["apicode"]==$apikey):
					//$date_of_expiry = time() + 60 * 60 * 24 * 1 ;
					//setcookie( "connectapi", "true", $date_of_expiry );
					$_SESSION['connectapi'] = "true";	
					$result = "true";
					
				endif;
				
			}
				
			return ["result"=>$result];
			
		}
		
		
    	public function delete() {
    		unset($_SESSION['connectapi']);
    	}
    	
    	public function paginator($query, $pagination){
    	        $limit = $pagination['limit'];
    	        $offset = $pagination['page'] * $pagination['limit'];						
    	        return $query->take($limit)->skip($offset)->get();
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
    		return array((string)$next, (string)$prev);
    	}
    	
    	public	function getnumberpage($limit,$size,$page) {
    		$result = ceil($size / $limit);
    		return ceil($result-1);
    	}
    	public function exerpt($text, $chars = 150){
    		   
    		if(strlen($text) > $chars) {
    			       $text = $text.' ';
    			       $text = strip_tags($text);
    			       $text = substr($text, 0, $chars);
    			       $text = substr($text, 0, strrpos($text ,' '));
    			       $text = $text.'...';
    		}
    		return $text; 	
    	} 
    	
    	
    	
    	public function extend_article($article){
    		global $app;
    		
    		$this->init_site();

    		$modules = $app["modules"];
    		foreach ($modules as $module) {
    			
    			$cat = $module["cat"];
    			$slug = $module["slug"];
    			
    			
    			if ($module["type"]=="module_article" ) {
    				
    				if(isset($article[$slug]) && $article[$slug]!=""):
    					$id = $article[$slug];
    					$article[$slug] = $this->search(["id"=>$id],1);	
    				endif;
                }else if($module["type"]=="yaml"){
                    
                    $article[$slug] = Spyc::YAMLLoad($article[$slug]);
                    
    			}else if ($module["type"]=="module_articles" ) {	
    				
    				
    				if(isset($article[$slug]) && $article[$slug]!=""):
    					
    					if (is_json($article[$slug])) {
    					
    						$values = json_decode($article[$slug]);		
    					}
    					if (is_array($article[$slug])) {
    						
    						$values = $article[$slug];
    						//dd($values);
    						
    					}
    					
    					
    					$article[$slug] = [];
    					foreach ($values as $id) {
    						$art = $this->search(["id"=>$id],1);
    						if($art["status"]=="true"){
    							
    							$article[$slug][] = util::array_first($art["datas"]);
    						}	
    					}
    				endif;
    			}else if (
    				$module["type"]=="gallery" || 
    				$module["type"]=="tags" ||
    				$module["type"]=="select_multiple" || 
    				//$module["type"]=="select" //|| 
    				$module["type"]=="module_articles"
    			) {	
    				
    				//if(!isset($article[$slug])):dd($article);endif;
    				if(is_string ( $article[$slug] )){
    					$article[$slug] = explode(",", $article[$slug]);
    				}
    			
    			//regions
    			}else if ($module["type"]=="region" ) {
    				$html = $module["html"];
    				$value = $article[$slug];
    				if (is_json($article[$slug])) {
    					$value = json_decode($value,true);	
    				}
    				$article[$slug] = $value;
    			}else{
    				if (is_json($article[$slug])) {
    					
    					$article[$slug] = json_decode($article[$slug],true);		
    				}
    			}
    		}
    		

    		
    		
    		
    		    		
    		return $article;
    	}
    	
    	
    	public function init_site($slug="") {
    			global $app;
    			
    			/* get parametres of website */
    			$app["parametres"] = Parametre::first()->toArray();
    			
    	
    			$app["modules"] = Module::all()->toArray();
    			
    			
    	
    			/* get page datas */
    			/*if($slug!=""):	
    				$datas["page"] = $this->get_page($slug);    				
    			endif;
    			
    			//lang session
    			if(Session::get('lang')!==null){
    				$app["lang"]=Session::get('lang');
    				
    			}
    			
    			if(Session::get('lang')==null && isset($datas["page"]["lang"])):
    				
    				$app["lang"] = $datas["page"]["lang"];
    				Session::put('lang', $datas["page"]["lang"]);

    			endif;
    			*/
    			
    			//return  $datas;	
    	}    	
    	
    	
	//┐
  	//│  ╔════════════════════════════════════════════════════════════════════════════╗
  	//│  ║                                                                            ║
  	//╠──╢ search  									  						          ║
  	//│  ║                                                                            ║
  	//│  ╚════════════════════════════════════════════════════════════════════════════╝
  	//┘  
	public function search($filtres,$settings) {
	       		
	       		$this->init_site();
	       		if(isset($settings["limit"])): $limit=$settings["limit"];else: $limit= 15; endif; //number
	       		if(isset($settings["page"])): $page=$settings["page"];else: $page=0;endif;//number
	       		if(isset($settings["sortby"])): $sortby = $settings["sortby"];else: $sortby="ordre";endif;//ordre for exemple
	       		if(isset($settings["sens"])): $sens=$settings["sens"];else: $sens="asc"; endif;//asc or desc
	       		if(isset($settings["tag"])): $tag = $settings["tag"];else:endif;// string 
	       			
	       		$datas =[];
	       		$errors = [];
	       		$status = "true";
	       		$linkspages = 0;	
	       		$size=0;
	
	       					if(	ine("id",$filtres)	):
	       						$id = $filtres["id"];
								$array_search = [
									"id"=>$id
								];
								
	       					else:
	       						
	       						$array_search = [];
	       						if(is_array($filtres) && sizeof($filtres)>0){
	       						foreach ($filtres as $key => $value) {
	       							if(	ine($key,$filtres)	){
	       								if(	singleton("structure")->checkcolumn($key)	){
	       									$array_search[$key] = $value;
	       								}
	       							}
	       						}
	       						}
	       						
	       					endif;
	       					
	       					
	       					if(isset($tag)):
	       						$query = Article::where($criteres)
	       								->where("tagsslug", "LIKE", "%$tag%")
	       								->orderBy('bydate', 'desc');
	       					else:
	       						$query = Article::where($array_search)->orderBy($sortby, $sens);
	       					endif;			
	       					$test = $query->get();
	       					$size = $test->count();
	       					
	
							$result = $this->paginator($query, ["limit"=>$limit,"page"=>$page]);
							
	       					if (!$result->isEmpty()){
	       						//die($limit);
	       						if($limit==1){
	       							
	       							$datas1 = $result->first()->toArray();
	       							$datas = $this->extend_article($datas1);
	       						
	       						}else{
	       							$datas = $result->toArray();
	       							/*foreach ($datas as $key => $value) {
	       								$datas[$key] = $this->extend_article($value);	
	       							}*/
	       							
	       						}
	       						
	       						$linkspages = $this->getnumberpage($limit,$size,$page);
	       						list($next,$prev) = $this->calcul_paginate($linkspages,$page);
	       					}else{
	       						$datas = [];
	       						$status ="false";
	       					}
	
							if (!isset($next)) {
								$next = "Null";
							}
							if (!isset($prev)) {
								$prev = "Null";
							}
	       				
	       				
	       					
	       		return array(
	       			"status"=>$status,
	       			"datas"=>$datas,
	       			"size"=>$size,
	       			"errors"=>$errors,
	       			"pagesnumber"=>floor($linkspages),
	       			"navigation"=>[
	       				"next"=>$next,
	       				"prev"=>$prev
	       				]
	       		); 
	        		
	    }
	    
	
	
	
			
		
}


