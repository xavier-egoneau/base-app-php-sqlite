<?php

/*
|--------------------------------------------------------------------------
| CRUD functions
|--------------------------------------------------------------------------
|
| 
|
*/
    //use Illuminate\Database\Schema\Blueprint;
    //use Illuminate\Database\Migrations\Migration;
    
    class Crud {
        
        
       
        
        /*
        |--------------------------------------------------------------------------
        | Read
        |--------------------------------------------------------------------------
        |
        | 
        */
		
		
        public function search($filtres = []) {
       		$datas =[];
       		$errors = [];
       		$status = "true";
       		$type = "articles";
       		
       			
       				$model = singleton("crud")->findModel($type);
       					
       					if(	ine("id",$filtres)	):
       						$id = $filtres["id"];
							$array_search = [
								"id"=>$id
							];
							
       					else:
       						
       						$array_search = [];
       						if(is_array($filtres) && sizeof($filtres)>0){
       						foreach ($filtres as $key => $value) {
       							if(	ine($filtres[$key])	){
       								if(	singleton("structure")->checkcolumn($key)	){
       									$array_search[$key] = $value;
       								}
       							}
       						}
       						}
       						
       					endif;
       					
       					$datas1 = $model::where($array_search)->get();

       					if (!$datas1->isEmpty()){
       						$datas = $datas1->toArray();
       					}else{
       						$datas = [];
       					}

       				
       				if (sizeof($datas)==1) {
       					$datas = util::array_first( $datas );
       				}
       					
       		return array("status"=>$status,"datas"=>$datas,"errors"=>$errors); 
        		
        }
        
        
        
        
        
        /*
        |--------------------------------------------------------------------------
        | Read
        |--------------------------------------------------------------------------
        |
        | 
        */
        
        public function read($type,$id="") {
        	
       		$datas =[];
       		$errors = [];
       		$status = "true";
       		$articleseul = false;
       		/*get id params => the client*/
       		
       		if(singleton("auth")->check()):
       			$user = singleton("auth")->get();
       			$idparams = $user["parametres"];
       			//dd($user );
       		endif;
       		
       		
       		if($type=="article"){
       			$type=="articles";
       			$articleseul = true;
       		}
       		/*control*/
       		$control  = singleton("crud")->control("r",$type,$id);
       		
       		if($control["result"] || $control["result"]=="true"){
       			
       				$model = singleton("crud")->findModel($type);
       				
       				if($type=="parametres"){
       					
       					$datas1 = $model::where(["id"=>$idparams])->get();	
						if (!$datas1->isEmpty()){
							$datas = $datas1->first()->toArray();
						}else{
							$datas = [];
						}
						
       				}else if($type=="articles"){
       					if(singleton("auth")->check()):

       						$datas1 = $model::where(["folder"=>$id,"parametres"=>$idparams])->orderBy('ordre', 'ASC')->get();
		                
		                else:
		                	
		                	if($articleseul==true){
		                		$datas1 = $model::find($id);
		                	}else{
		                  		$datas1 = $model::where(["folder"=>$id])->orderBy('ordre', 'ASC')->get();
		                  	}
		                  	
		                endif;

       					if (!$datas1->isEmpty()){
       						$datas = $datas1->toArray();
       					}else{
       						$datas = [];
       					}
       					
       				
       				}else{
       					
       					if($id==""):
       							$datas1 = $model::where(["parametres"=>$idparams])->orderBy('ordre', 'ASC')->get();
       							if (!$datas1->isEmpty()){
       								$datas = $datas1->toArray();
       								
       								if($type=="folders"):
       									foreach ($datas as $key => $value) {
       										$datas[$key]["perm2"] = json_decode($value["perm2"],true);
       										$datas[$key]["perm3"] = json_decode($value["perm3"],true);		
       									}
       								endif;
       								
       								if($type=="modules"):
       									foreach ($datas as $key => $value) {
       										$datas[$key]["needed_array"] = Spyc::YAMLLoad($value["needed"]);
       									}
       								endif;
       								
       							}else{
       								$datas = [];
       							}
       							
       							
       					else:
       							$datas1 = $model::where(["id"=>$id,"parametres"=>$idparams])->orderBy('ordre', 'ASC')->get();
       							if (!$datas1->isEmpty()){
       								$datas = $datas1->first()->toArray();
       								if($type=="folders"):
       									$datas["perm2"] = json_decode($datas["perm2"],true);
       									$datas["perm3"] = json_decode($datas["perm3"],true);
       								endif;
       								
       								if($type=="modules"):
       										$datas["needed_array"] = Spyc::YAMLLoad($datas["needed"]);
       								endif;
       								
       							}else{
       								$datas = [];
       								$status = "false";
       							}
       							
       							
       					endif;
       				
       				}
       				if(isset($datas) && is_array($datas)){
	       				foreach ($datas as $key => $value) {
	       					if(
	       						is_json($value)
	       						
	       					){
	       						
	       						$datas[$key] = json_decode($value,1);
	       					}
	       				}
       				}
       				
       				
       					
       				
       		}else{
       			
       			$errors[] = '404 - Not found.';$status = "false";
       			
       		}	
  
       		return array("status"=>$status,"datas"=>$datas,"errors"=>$errors); 
        		
        }
        	
        	
        	
        	
        	
        	/*
        	|--------------------------------------------------------------------------
        	| Delete
        	|--------------------------------------------------------------------------
        	|
        	| 
        	*/
        	public function delete($type,$id="") {
        		
				$datas =[];
	      		$errors = [];
	      		$status = "false";
	      		
	      		if($id!=""){
		      		
		      		/*get id params => the client*/
		      		$user = singleton("auth")->get();
		      		$idparams = $user["parametres"];
		      		/*control */
		      		$control  = singleton("crud")->control("d",$type,$id);
					
		      		if($control["result"]){
		      			
		      			$model = singleton("crud")->findModel($type);

		      			if($type=="parametres" && $idparams==$id  ){
		      				
		      				$req = $model::find($idparams);
		      				
		      				if($req !== null){
		      					$req->delete();
		      					$status = "true";
		      				}
		      			}else{
		      				
		      				
		      				
		      				$req= $model::find($id);
							
							if ($type=="articles") {
								
								$req= $model::where(["id"=>$id])->orWhere(["ref"=>$id]);
								
							}
							
							if ($req !== null){
								
								if ($type=="modules") {
								
									$datas = $req->toArray();
									
								}
								
								$req->delete();
								$status = "true";
								
								if ($type=="modules") {
									$languages = $this->localize($idparams);
									singleton("structure")->clean_structure($languages);
								}
								
								
							}
							
		      				
		      				
		      			}
		      				
		      			
		      		}else{$errors[] = 'Access denied.';}
		      		
	      		}else{$errors[] = 'Access denied.';}	
	      		
	      		return array("status"=>$status,"datas"=>$datas,"errors"=>$errors);      		
        		
        	}
        	
        	
        	
        	
        	
        	/*
        	|--------------------------------------------------------------------------
        	| findmodel
        	|--------------------------------------------------------------------------
        	|
        	| 
        	*/
        	public function findModel($string) {
        		
        		if($string=="article"){
      				$string = "articles";
        		}
        		
        	    $models = ['parametres', 'articles', 'folders', 'modules', 'regions', 'users'];
        	
        	    if (in_array($string, $models)) {
        	      return substr(ucfirst($string), 0, -1);
        	    }
        		
        		
        	    return false;
        	}
        	
        	
        	/*
        	|--------------------------------------------------------------------------
        	| update
        	|--------------------------------------------------------------------------
        	|
        	| 
        	*/
        	public function ordre($type,$ids) {
        		global $app;
        		$errors = [];
        		$status = false;
        		$idz = explode(",", $ids);
        		//dd($idz);
        		$model = singleton("crud")->findModel($type);
        		foreach ($idz as $key=>$id) {
        		  
        			$article = $model::find($id);	
                    if (!$article->get()->isEmpty()){
                    $article->ordre = $key;
                    $article->save();
                    }
                    /*
        			if (!$article->get()->isEmpty()){
        				
        				singleton("crud")->update($type,$id,["ordre"=>$key]);
        			}*/
        			
        			
        			
        			
        			
        		}
        		return array("status"=>"true","datas"=>[],"errors"=>"");
           	}
           	
           	public function encodemodule($datas,$n=0){
           	   	global $app;
           	
	           	   //	$result = "";



	           	   		$result = slug($datas["slug"],"_");	
                    $id = $datas["id"];
	           	   		//echo $result;
                    $matchThese = ["slug"=>$result];  
                    $orThose = ['id', '!=' ,  $id];
                    //$test_slug = Module::where($matchThese,$orThose)->get();

	           	   		$test_slug = Module::where($matchThese)->get()->toArray();
                    //dd($test_slug);
	           	   		if (sizeof($test_slug)>1 ){
	           	   			//echo " no empty";
	           	   			//print_r($test_slug);
	     					      $slug = $result."_".$n;
	           		    	$n = $n+1;
	           		    	return $this->encodemodule($datas,$n);
	           		    		
	           	   		}
	           	   	
           	   		
           	   		return $result;
           	   	
           	 }

             
             public function encodeurl($chaine="",$id=""){
             	global $app;
                $result = "";
                if($chaine!="" ):  
                	if($id!=""){
						
						$art = Article::find($id);
				 		$lang = $art->lang;
				 		$result = slug($chaine,"-");	
				 		$test_slug = Article::where(["page"=>"true","lang"=>$lang,"url"=>$result])->get();
				 		//dd($test_slug);
			       	 	  if (!$test_slug->isEmpty()){
			       	 	    
			       	 	    	$size = $test_slug->count();	
								if($size>1){
			           	 	    	    		
			           	 	    		$result = $result."-".$size;
			           	 	    	
			           	 	   // }elseif($size==1 && $id==""){
			           	 	    
			           	 	    //	    $result = $result."-".$size;
			           	 	    	    		
			           	 	    }elseif($size==1 && $id!=""){
			
			           	 	    	    $art = $test_slug->first()->toArray();
			           	 	    	    //echo $art["id"]."==".$id;die();
			           	 	    	    if($art["id"]==$id){
			           	 	    	    	$result = $result;
			           	 	    	    }else{
			           	 	    	    	$result = $result."-".$size;
			           	 	    	    }
			           	 	    }
			       	 	    	    	
			       	 	    	
			       	 	  } 
					           	 	                         	 		
	              	}else{
	                	$result = slug($chaine,"-");	
	                }
                        	 	
                        	 	
                        	 	
                        	 	
             	endif;       	
                return $result;
                        	 	       	
             }
             /*
           	 public function encodeurl($chaine="",$id=""){
           	 	global $app;
           	 	$result = "";
           	 	
           	 	if($chaine!="" ):    
           	 		$result = slug($chaine,"-");	
           	 	    $test_slug = Article::where(["url"=>$result])->get();
           	 	    if (!$test_slug->isEmpty()){
           	 	    
           	 	    	$size = $test_slug->count();	
						if($size>1){
           	 	    	    		
           	 	    		$result = $result."-".$size;
           	 	    	
           	 	    	}elseif($size==1 && $id==""){
           	 	    	    $result = $result."-".$size;
           	 	    	    		
           	 	    	}elseif($size==1 && $id!=""){

           	 	    	    $art = $test_slug->first()->toArray();
           	 	    	    if($art["id"]==$id){
           	 	    	    	$result = $result;
           	 	    	    }else{
           	 	    	    	$result = $result."-".$size;
           	 	    	    }
           	 	    	}
           	 	    	    	
           	 	    	
           	 	  }
           	 	
           	 	endif;       	
           		return $result;
           	 	       	
           	}*/
           	
           	/*
           	|--------------------------------------------------------------------------
           	| update
           	|--------------------------------------------------------------------------
           	|
           	| 
           	*/
           	public function render() {
           		global $app;
           		
           		$preview = $app["path"]["public"]."preview/";
           		$public = $app["path"]["public"];
           		
           		$baseurl = $app["url"]["root"]."preview/";
           		$preview_url = $app["url"]["root"]."preview/";
           		$root_url = $app["url"]["root"];
           		$dest = $public;
           		
           		$params = Parametre::all();	
           		if (!$params->isEmpty()){
           			$parametres = $params->first();
           		}else{
           			$parametres = [];
           		}
           		$langg = explode(",", $parametres->langues);
           		foreach ($langg as $langgg) {
	           		if (!file_exists($dest.$langgg)) {
	           				    mkdir($dest.$langgg, 0705, true);
	           		}
	           	}
           		
           		$content = file_get_contents($preview_url."/toto");
           		writethis($dest."/404.html",$content);
           			
           		foreach ($langg as $langgg) {
	           		if(sizeof($langg)>1){
	           			//home
	           			$content = file_get_contents($preview_url.$langgg."/");
	           			writethis($dest.$langgg."/index.html",$content);
	           			
	           			//copyhtaccess
	           			//copy($public . ".htaccess", $dest.$langgg."/.htaccess");
	           			
	           			//copy 404
	           			$content = file_get_contents($preview_url.$langgg."/toto");
	           			writethis($dest.$langgg."/404.html",$content);
	           		
	           		}else{
	           			//home
	           				$content = file_get_contents($preview_url);
	           				writethis($dest."index.html",$content);
	           				
	           				//copyhtaccess
	           				//copy($public . ".htaccess", $dest.$langgg."/.htaccess");
	           				
	           				//copy 404
	           				$content = file_get_contents($preview_url."toto");
	           				writethis($dest."404.html",$content);
	           		}
           			
           		}
           		
           		$articles = Article::all();	
           		foreach ($articles as $article) {
           			if(sizeof($langg)>1){
		           		if ($article->page=='true' && $article->url!="") {
		           			$lang_locale = $article->lang;
		           			
		           			$url = $article->url;
		           			
		           			$content = file_get_contents($preview_url.$lang_locale."/".$url);
		           		
		           			writethis($dest.$lang_locale."/".$url.".html",$content);
		           			//echo $baseurl.$lang_locale."/".$article->url.".html
		           			//";
		           		}
	           		}else{
	           			if ($article->page=='true' && $article->url!="") {
	           					$lang_locale = $article->lang;
	           					
	           					$url = $article->url;
	           					
	           					$content = file_get_contents($preview_url.$url);
	           				
	           					writethis($dest.$url.".html",$content);
	           					//echo $baseurl.$lang_locale."/".$article->url.".html
	           					//";
	           				}
	           		}	
	           	}
	           	//copy folder uploads
	           	/*
	           	if (!file_exists($dest."uploads/")) {
	           		mkdir($dest."uploads/", 0705, true);
	           	}	
	           	copyfolder($public."uploads/", $dest."uploads/");
	           	
	           	
	           	
	           	*/
	           	
	           	//copy assets
	           	//exec("cp -r ". $preview ."assets/ ". $public ."assets/");
	           	
	           	//copy favicon
	           	//copy($public . "favicon.ico", $dest . "favicon.ico");
	           	
	           	
	           	//$result = ["status"=>false,"datas"=>[],"errors"=>[]];
	           	json(["status"=>"true"]);
	           	die();
           	}
           	 
           	 
           	
           	 
        	/*
        	|--------------------------------------------------------------------------
        	| update
        	|--------------------------------------------------------------------------
        	|
        	| 
        	*/
        	public function update($type,$id="",$datas) {
        	
        		global $app;
        		$errors = [];
        		$status = false;
        		
        		/*get id params => the client*/
        		$user = singleton("auth")->get();
        		$idparams = $user["parametres"];
        		if ($type=="parametres"): $id = $idparams;endif;
        		/*control*/
        		
        		$control  = singleton("crud")->control("u",$type,$id);
				//dd($control);
        		if($control["result"]){
        			        			
        			if(isset($datas["parametres"])): unset($datas["parametres"]); endif;
        			if(isset($datas["id"])): $id =$datas["id"]; unset($datas["id"]); endif;
        			
        			$model = singleton("crud")->findModel($type);
        			
        			if ($type=="modules" && isset($datas["slug"])) {        				
        				$datas["slug"] = $this->encodemodule($datas,0);
        				//dd($datas["slug"]." - ".$slugg);
        			}

              //$modules_req = singleton('crud')->read("modules");
        			
        			/* if column dont exist ???*/
        			$req = $model::find($id);

        			if($req !== null){
        				
	        			$cible = $req->toArray();
	        				
	        			if ($type=="users") {
	        					
	        					
	        					
	        					
	        					if(
	        						isset($datas["password"]) && $datas["password"]=="" || 
	        						isset($datas["password"]) && $datas["password"]== $cible["password"]
	        					):
	        						unset($datas["password"]);
	        						//dd("unset");
	        					endif;
	        					$message="
                                Bonjour,<br>
                                Votre nouveau compte Drgstr vient d'être créé.<br><br>
                                Accés au Backend:<br>
                                <a href='".$app["url"]["root"]."/backend/'>".$app["url"]["root"]."/backend/</a>
                                Votre identifiant est ".$datas["email"]."<br>Votre mot de passe est :<br>".$datas["password"];
                                email($datas["email"], "New user", $message);
                            
	        					//dd($datas["password"]." / ".$cible["password"]);
	        					if(	ine("password",$datas)	):
	        						$datas["password"] = haash($datas["password"]);
	        					endif;
                            
                                
	        					
	        					
	        				}



                        if ($type=="modules" && isset($datas["html"]) && $datas["html"]!="") {
                                  $dest =$app["path"]["app"]."project/root/tpl/componements/";
                                  writethis($dest.$datas["slug"].".twig",$datas["html"]);
                            }
                        
	        			if ($type=="parametres") {
	        					if (!isset($datas["permissions_folders"])) {
	        						$datas["permissions_folders"][] = "";
	        					}
	        					if (!isset($datas["permissions_modules"])) {
	        						$datas["permissions_modules"][] = "";
	        					}
	        					
	        					
	        				}
	        					
	        			if(isset($datas["titre"]) && $type=="articles" ){
	        					
	        					
	        					//dd($datas);
	        					if(!ine("url",$datas) && $cible["page"]=="true" && isset($id) && $cible["url"]==""){
	        						
	        						$datas["url"] = $this->encodeurl($datas["titre"],$id);//slug($datas["titre"]);
	        						
	        						
	        					}elseif(!ine("url",$datas) && $cible["page"]!="true"){
	        						$datas["url"] = "#";
	        					}
	        					
	        					
	        					if ( isset($datas["tags"]) ) {
                                    
                                    $tags = $this->tag_filter($datas["tags"]);

	        						//$tags = explode(", ", $datas["tags"]);
	        						$tagsslug="";
	        						$tagsstring="";	
	        						
	        						foreach ($tags as $key => $tag) {
	        							if($tag!="" && $tag!="undefined"){
		        							
		        							if($tagsslug != ""):
		        								$tagsslug .=", ";
		        							endif;
		        							
		        							if($tagsstring != ""):
		        								$tagsstring.=", ";
		        							endif;
		        							
		        							$tagsslug .= slug($tag);
		        							$tagsstring.=$tag;
	        							}else{
	        								unset($tags[$key]);
	        							}
	        						
	        						}
	        						//dd($tagsslug);
	        						$req->tagsslug= $tagsslug;
	        						$req->tags = $tagsstring;
	        						//dd($tagsstring);
	        					}
	        					
	        					
	        				}
	        					        				
	        				if (
	        					isset($datas["filenames"]) && $type=="parametres" || 
	        					isset($datas["filenames"]) && $type=="articles"
	        				) {
	        					
                    /*get modules*/
	        					if($type=="articles"){
		        					$modules_req = singleton('crud')->read("modules");
		        					$modules = $modules_req["datas"];
	        					}




		        				foreach ($datas["filenames"] as $file) {
		        					
		        					$one_file = json_decode($file,true);
		        					
		        					foreach ($datas as $key => $value) {
		        						
		        						if($value == $one_file["name_original"] && $key!="filenames"){
		        							
		        							if($type=="articles"){
			        							foreach ($modules as $keymodule => $valuemodule) {
			        								if ($key == $keymodule) {
			        									$moduleID = $modules[$keymodule]["id"];
			        									$moduletype = $modules[$keymodule]["type"];	
			        								}
			        							}
		        							}else{
		        								$moduletype =$key;
		        								$moduleID = "";
		        							}

		        						}/*endif value..*/
		        					}/*endforeach*/
		        				}/*endforeach*/


	        				}/*end if filename*/
	        				
	        			
	        				//dd($datas["date"]);
	        				if (	
	        					$type=="articles" && 
	        					isset($datas["date"])
	        				){
	        					
	        					$dateparse = explode("-", $datas["date"]);
	        					if(sizeof($dateparse)==3):
	        						list($y,$m,$d) = explode("-", $datas["date"]);
	        						$req->bydate = $y.$m.$d;
	        					else:
	        						$req->bydate = date("Y").date("m").date("d");
	        					endif;
	        					
	        				}else if (	
	        					$type=="articles" && 
	        					$cible["bydate"]==0 && 
	        					!isset($datas["date"])
	        				){
	        					//$datas["bydate"]
	        					$req->bydate = date("Y").date("m").date("d");
	        				}
	                
                  /* si pas de title */  
                  if( $type=="articles" ){
                    if (
                        !isset($datas["title"]) || 
                        isset($datas["title"]) && $datas["title"]==""
                      ){
                      $datas["title"] = $datas["titre"];
                      $datas["url"] = slug($datas["titre"],"-");
                    }
                  }

                  /* si pas de description */  
                  if( $type=="articles" ){
                    if (
                      !isset($datas["description"]) || 
                      isset($datas["description"]) && $datas["description"]==""
                    ) {
                      $datas["description"] = $datas["titre"];
                    }
                  }


foreach ($datas as $key => $value) {
	        					
    if (isset($cible[$key])	) {
	        						
        if (is_array($value)) {
            $datas[$key] = json_encode($value);
            $req->$key  =  $datas[$key];
        }else{
            $req->$key  =  $value;

        }       						
    }

}
	        				
	        				
	        				
	        				
	        				
	        				
	        				
	        				$test = $req->save();
	        				
	        				
	        				
	        				
	        				if ($type=="modules" && isset($datas["slug"])) {

                    $langg = explode(",", $parametres->langues);
                    singleton("structure")->clean_structure($langg);
                   

	        				}

	        				
	        				
	        				if ($type=="articles" && isset($tags) && is_array($tags) && sizeof($tags)>0 ) {
	        					
	        					$paraams = Parametre::find($idparams);
	        					$languu = $datas["lang"];

	        					  $tag_list = explode(", ", $paraams["keywords"]);
	        						$newkeywords = $paraams["keywords"];
	        					
	        					foreach ($tags as $tag) {
	        						if (!in_array($tag, $tag_list)) {
	        							if($newkeywords==""):
	        							
	        								$newkeywords = $tag;
	        								
	        							else:
	        								
	        								$newkeywords = $newkeywords.", ".$tag;
	        								
	        							endif;
	        						}
	        					}
	        					

	        			        $paraams->keywords = $newkeywords;
                                $paraams->save();	
	        					
	        				}
	        				
        			}
        			
        			
        			
        			if($req!==null){
        				
        				$status = true;
        				$datas = $req["id"];
        				
        			}else{
        			
        				$status = false;
        				$errors[] = '404 - Not found.';
        				$return = array("status"=>true,"datas"=>[],"errors"=>[]);
        				
        			}
        			
        			
        		}else{
        	
        			$errors[] = 'Access denied.';
        		}
        		
        		return array("status"=>$status,"datas"=>$datas,"errors"=>$errors);
        	}
        	
        	
        	public function tag_filter($tagstring){
              $tags = [];
              $tags = explode(", ", $tagstring);
                if(sizeof($tags)>1){
                      foreach ($tag as $tags) {
                        if (!in_array($tag,$tags) && $tag!="") {
                          $tags[] = $tag;
                        }
                      }
                }
              return $tags;
          }
        	
        	public function localize($idparams) {
        		
        		global $app;
        		
        		$return = [];
        		$params = Parametre::find($idparams)->get();	
        		if (!$params->isEmpty()){
        			$params1 = $params->first()->toArray();
        		}else{
        			$params1 = [];
        		}
        			
        		if (isset($params1["laguages"])) {
        			$languages = $params["langues"];
        			$languages2 = explode(",", $languages);
        			$return = $languages2;
        		}
        		return $return;
        	}
        	
        	/*
        	|--------------------------------------------------------------------------
        	| create
        	|--------------------------------------------------------------------------
        	|
        	| 
        	*/	
        	public function create($type,$datas=[]) {

  				global $app;
	      		$errors = [];
	      		$status = false;
	      		
	      		$user = singleton("auth")->get();
	      		//dd($user);
	      		$idparams = $user["parametres"];
	      		$languages = $this->localize($idparams);
	      		
	      		//control
	      		$control  = singleton("crud")->control("c",$type);
	      		
				$models = ['parametres', 'articles', 'folders',  'modules','users']; 
	      		if($control["result"]){
	      			        			
	      			
	      			
	      			$datas = array_merge($datas, array("parametres"=>$idparams));
	      			$model = singleton("crud")->findModel($type);
	      			

	      				
	      				
	      				if( in_array($type, $models)){
	      					
	      					$req = new $model;
	      					$req-> parametres = $idparams;
	      					$req-> ordre = 0;
                            
	      					if($type!="users"){	
	      					if(!isset($datas["titre"])) : $req-> titre = "New";endif;
                            }
	      					$testreq = $req -> save();
	      					$id_new = $req ->id;
                   
	      					$datas["ordre"] = $datas["id"] = $id_new;
                            
	      					$req2 = $this->update($type,$id_new,$datas);
                            
	      					
	      					if($type=="modules"){
                                
                                $langg = explode(",", $parametres->langues);
                                singleton("structure")->clean_structure($langg);

	      					}
	      						
	      				}
	      				
	      				
	      				
	      					
	      				
		      			if(isset($req) && $req !== null && $req2!==null){
		      				
		      				$status = true;
		      				$datas = array("id"=>$req["id"]);
		      				
		      			}else{
		      				
		      				$status = false;
		      				$errors[] = "Error  - Can't create that.";
		      				$return = array("status"=>true,"datas"=>[],"errors"=>[]);
		      				
		      			}
	      			
	      			
	      		}else{
	      	
	      			$errors[] = 'Access denied.';
	      		}
	      		
	      		return array("status"=>$status,"datas"=>$datas,"errors"=>$errors);
        			
        		
        	}
        
        public	function prepare($datas) {
        	foreach ($datas as $key => $value) {
        		
        		if ( is_json($value)	) {
        			$datas[$key] = json_decode($value,true);
        		}
        	}
        	return $datas;
        }

		/*
		|--------------------------------------------------------------------------
		| permissions
		|--------------------------------------------------------------------------
		| 1 superadmin
		| 2 admin
		| 3 editeur
		| 4 for connections in the website >> not backend
		*/		
			
		public function permissions($action,$type,$id=""){
			global $app;
			//echo $action." - ".$type." - ".$id;
			$result = false;
			$errors = "";
			$connect = singleton("auth")->check();
			//dd($connect);
			
			if(
			$action=="r" && $type=="articles" 
			){
			
				$result = true;	
				
			
			}else{
				
				if($connect && $action=="r") {
					
					$result = true;	
					
				}elseif($connect && $action !="r" ){

					$user = singleton("auth")->get();
	
					if(sizeof($user)==0): 
						$errors[] = "user not find";
						$result = false;
						return array("result"=>$result,"error"=>$errors);
					endif;

					$type_user = $user["type"];
					$paramsid = $user["parametres"];
					
					$params = Parametre::findorfail($paramsid);
					
					if(!$params){
						$errors[] = 'Paramètres not found.';
						return array("result"=>$result,"error"=>$errors);
					}
					
					/*permissions*/
					$perms =[];
					if($type=="article"){	$type = "articles";	}
					
					$perms["parametres"]=array("r","u");
					$perms["folders"] =  json_decode($params->permissions_folders,true);
					$perms["modules"] =  json_decode($params->permissions_modules,true);
					$perms["users"] =  json_decode($params->permissions_users,true);
					$perms["articles"] = array("c","r","u","d");
					
					if($type_user=="4"){
						$result = false;/*aucun pouvoirs*/
						return array("result"=>$result,"error"=>$errors);
					}else if($type_user=="1"){
						$result = true;/*tous pouvoirs*/
						return array("result"=>$result,"error"=>$errors);
					
					}else if($type_user=="2" || $type_user=="3"){ /*on check les pouvoirs*/
						
						
						if( 	$type=="articles" 	){
							
									
									if(isset($_POST["folder"]) && $_POST["folder"]!=""):
										$folderid = $_POST["folder"];
									else:
										$articll = Article::find($id);
										$folderid = $articll->folder;
									endif;
									
									$folder = Folder::find($folderid);
									if ($folder != null  ){
										
										$fold = $folder->toArray();
										$fold2 = $this->prepare($fold);
										if(in_array($action, $fold2["perm".$type_user])	){
											$result = true;
										}
										
									}else{
										$errors[] = 'Folder not found.';
										return array("result"=>$result,"error"=>$errors);
									}
									
								//}else{	$errors[] = 'Id not found.';return array("result"=>$result,"error"=>$errors);	}
								
						  	
						}else{ 
		
							if(	
							is_array($perms[$type]) && in_array($action, $perms[$type])	
							){
								
								
								$result = true;	
								
							}else{
								$result = false;	
								$errors[] = 'Access denied1.';
							}
						}
					
					/*user type != 1,2,3,4*/
					}else{
						$errors[] = 'Access denied2.';
					}
					
					
				}else{
					$errors[] = 'Access denied3.';
				}
					
				
			
			}
			
			
			
			
			//dd($action." ".$type." ".$id." ".$result);
			return array("result"=>$result,"error"=>$errors);
			
		}
		
		
		
		
		
		
		/*
		|--------------------------------------------------------------------------
		| control
		|--------------------------------------------------------------------------
		| lunch test permissions 
		| filters? (...soon... )
		| 
		*/
		public function control($action,$type,$id=""){
			
			
			return singleton("crud")->permissions($action,$type,$id);
			
			/* check values? compare structure? */
			
			
			
			
			
		}
		

		
		
		
		
		
		
        
        
    }