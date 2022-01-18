<?php if ( ! defined('in_app')) add_error("Accès Interdit", __FILE__ , __LINE__ );


//use DebugBar\StandardDebugBar;
use Illuminate\Database\Capsule\Manager as Capsule; 

/* for migrate and db */


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Phinx\Migration\AbstractMigration;


class util extends \utilphp\util { }

/*
| __construct
|        -> init
|        -> config()
|            -> load config
|            -> load helpers
|            -> path
|            -> url
|            -> init whoops

|        -> add_services()
|            -> redbean mongolite, asphalte  
|        -> run()    
|        -> template()              
|
*/

class Socle  {

		
		public $capsule;
		/** @var \Illuminate\Database\Schema\Builder $capsule */
		public $schema;
		
	
	
	
	
	    public function __construct($dir) {
			global $app;
			global $services;
			
			/*
			--------------------------------------------
			!! config serveur
			-------------------------------------------- */
			ini_set("zlib.output_compression", "On");
			ini_set("zlib.output_compression", 4096);
			ini_set("memory_limit","1024M");
			ini_set('upload_max_filesize', '40M');
			ini_set('post_max_size', '40M');
			/* Désactivation de la reconnaissance de l'identifiant de session dans l'URL */
			ini_set('session.use_trans_sid', "0"); 
			/* Interdiction d'ajouter l'identifiant de session dans le code html généré */
			ini_set("url_rewriter.tags",""); 
			//define('always_populate_raw_post_data', '-1');
			
			
			set_time_limit(200);
			
			gc_enable();
			//if (substr_count($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip")) ob_start("ob_gzhandler"); else ob_start();
			
			
	        $this->config($dir);
	        $this->add_services();
	        
	        $app["map"] = util::array_clean((array)singleton("route")->map($app["request"]));
	        
	        //$this->add_projects();
	        $this->get_project();
	        
	        gc_collect_cycles();
	        
	    }
	
	   

		/*---------------------------------------------------------------
		 * config
		 *--------------------------------------------------------------- */
		public function config($dir) {

				global $app;
					
					/*
					--------------------------------------------
					!! init
					-------------------------------------------- */
					util::utf8_headers();
					date_default_timezone_set('Europe/Berlin');
					$app["errors"] = array("status"=>false,"msgs"=>[]);
					
					
					
					/*
					--------------------------------------------
					!! calcul app paths
					-------------------------------------------- */
					//dd($dir);
					$base_path = strtr( __dir__,["app\core"=>""]);
					$base_path_index = strtr( $dir,[$base_path=>""]);
					$test_subfolders = explode("/", $base_path_index);
					$public_folder = $test_subfolders[0];
					$apppath = $base_path."app/";
					
					/*ini_set('display_errors', 1);
					ini_set('display_startup_errors', 1);
					define('display_startup_errors', 1);
					ini_set('log_errors', 1);
					
					*/
					//ini_set('display_errors', 1);
					//ini_set('error_reporting', E_ALL);
					ini_set('error_log', $base_path."logs/logs.txt");
					
					
					if (sizeof($test_subfolders)>1): 
						$subfolder = $test_subfolders[1];
						$path_tpl = $base_path."app/project/".$subfolder."/tpl/";
					else: 
						$subfolder = "";
						$path_tpl = $base_path."app/project/root/tpl/";
					endif;
					
					
					
					/*
					--------------------------------------------
					!! contruct app paths
					-------------------------------------------- */
					$app["subfolder"] = $subfolder;
					$app["path"]= [
					    "root"=> $base_path,
					    "app" => $apppath,
					    "storage"=> $base_path."storage/",
					    "public"=> $base_path.$public_folder."/",
					    "project"=> $apppath."project/",
					    "cache"=> $apppath."cache/",
					    "uploads"=> $base_path.$public_folder."/uploads/",
					    "tpl" => $path_tpl
					];
					
					/*
					--------------------------------------------
					!! load helpers
					-------------------------------------------- */
					if(file_exists($apppath."helpers/helpers.php")):
						require_once($apppath."helpers/helpers.php");
					else:
						echo ("Error : ".$apppath."helpers/helpers.php not found!" . __FILE__." - ".__LINE__);
						die();
					endif;
					
					
					
					/*
					--------------------------------------------
					!! require config
					-------------------------------------------- */
					if(file_exists($base_path."config.yaml")):
						$config = Spyc::YAMLLoad($base_path."config.yaml");
					else: 
						dd($base_path."config.yaml not found");
					endif;
					
					
					/*
					--------------------------------------------
					!! require config subfolders
					-------------------------------------------- */
					if($subfolder!=""){
						$path_config = $base_path."app/project/".$subfolder."/config.yaml";
						if(file_exists($path_config)):
							$config2 = Spyc::YAMLLoad($path_config);
							foreach ($config2 as $key => $value) {
								$config[$key] = $value;
							}
						endif;
					}
					
					$app["config"] = $config;
					
					/*---------------------------------------------------------------
					 * control values config
					 *--------------------------------------------------------------- */
					$needed = array("version","dev",'salt','cache');
						
					if(	!control_values($needed,$config )	):
						dd("Variable manquante dans config.yaml", __FILE__ , __LINE__ );
					endif;
					
					
					/*---------------------------------------------------------------
					 * dev module
					 *--------------------------------------------------------------- */
					if($config["dev"]){
					
						$whoops = new \Whoops\Run;
						$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
						$whoops->register();
					}
					
					
					
					/*---------------------------------------------------------------
					 * SQL params
					 *--------------------------------------------------------------- */
					if(isset($app["config"]['storage']) && $app["config"]['storage']=="sql"){
						
						if(isset($app["config"]) && filter_url("localhost",$_SERVER['HTTP_HOST'])):
							$sql_infos = $app["config"]["SQL_connect"];
						else:
							$sql_infos = $app["config"]["SQL_connect_prod"];
						endif;
						
						if(
						    isset($sql_infos["host"]) && $sql_infos["host"]!="" && 
						    isset($sql_infos["dbname"]) && $sql_infos["dbname"]!="" && 
						    isset($sql_infos["username"]) && $sql_infos["username"]!="" && 
						    isset($sql_infos["password"]) && $sql_infos["password"]!="" 
						){
                            if(isset($sql_infos["port"])):
                                $port = $sql_infos["port"];
                            else:
                                $port = '8889';
                            endif;
						
                            $app["sql"] = [
                                      'driver'    => 'mysql',
                                      'host'      => $sql_infos["host"],
                                      'port'      => $port,
                                      'database'  => $sql_infos["dbname"],

                                      'username'  => $sql_infos["username"],
                                      'password'  => $sql_infos["password"],
                                      'charset'   => 'utf8',
                                      /*'prefix'    => '',*/
                                      'collation' => 'utf8_general_ci'
                            ];
						
						}else{
							dd("Error: identifiants sql not found.");
						}
					}else{
					
						$app["sql"] = [
						  'driver'    => 'sqlite',
						  'database'  => $app["path"]["storage"]."database.sqlite",
						  'charset'   => 'utf8',
						  /*'prefix'    => '',*/
						  'collation' => 'utf8_general_ci'
						];
						
					}
					
					
						
						
					
					
					/*
					--------------------------------------------
					!! contruct app url
					-------------------------------------------- */
					$root_url = get_url();
					if($subfolder!=""): $local = $root_url.$subfolder."/";
					else:$local = $root_url;
					endif;
					$app["url"] = [
					     "root"=> $root_url,
					     "local"=> $local,
					     "assets"=> $local."assets/",
					     "uploads"=> $root_url."uploads/"
					];
					
					/*
					--------------------------------------------
					!! others vars needed for the front
					-------------------------------------------- */
					if(!isset($app["fulljson"])) : $app["fulljson"]=false; endif;
					$app["https"] = util::is_https();						
					$app["assets"] = $app["url"]["assets"];
					$app["uploads"] = $app["url"]["uploads"];
					
					
					/*	
					---------------------------------------------------------------
					!! map url
					--------------------------------------------------------------- */
					if(isset($_GET["request"])){$request = $_GET["request"];}else{$request = "";}
					if(isset($_SERVER["REQUEST_URI"])){$request = $_SERVER["REQUEST_URI"];}
					
					$app["request"] = $request;
					if($app["https"]){
						$app["curent_url"] = 'https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					}else{
						$app["curent_url"] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					}
					if($_SERVER['SERVER_NAME']=="localhost"):
						$app["curent_url"] = 'http://localhost:8888'.$_SERVER['REQUEST_URI'];
					endif;
					
					
					/* scan all folder project */
					
					$app["project_files"] = dirToArray($app["path"]["project"]);
				
					
		}
			





		/*---------------------------------------------------------------
		 * services/intégrations r sql
		 *--------------------------------------------------------------- */
		
		public function add_services(){
				global $app;
				global $services;

				$app["services"] = DI\ContainerBuilder::buildDevContainer();
	
				/*---------------------------------------------------------------
				 * seloquent sql/sqlite
				 *--------------------------------------------------------------- */      
					$app["services"]->set('db', function () use($app) {
				       // $testmigrate = new Db(date("Y-m-d-H-i-s"));
				        //return $testmigrate;
				        
				        $this->capsule = new Capsule;
				        $this->capsule->addConnection($app["sql"]);
				        $this->capsule->bootEloquent();
				        $this->capsule->setAsGlobal();
				        return $this->capsule;
				        //$this->schema = $this->capsule->schema();
				});
				$services["db"] = $app["services"]->get('db');
				
            
            
				/*---------------------------------------------------------------
				 * sessions for auth
				 *--------------------------------------------------------------- */
				$app["services"]->set('session', function () use($app) {
					
					
					$session = new Gears\Session();
					$session->dbConfig = $app["sql"];
					$session->install();
					$session->globalise();
	
				    return $session;
				    
				});
				$services["session"] = $app["services"]->get('session');

				
				
				/*---------------------------------------------------------------
				 * shema migrations
				 *--------------------------------------------------------------- */      
				$app["services"]->set('shema', function () use($app) {
				        $return = singleton("db")->schema();
				        return $return;
				});
				$services["shema"] = $app["services"]->get('shema');
				
				
				
				
				/*---------------------------------------------------------------
				 * auth class ( use gears session )
				 *--------------------------------------------------------------- */
				$app["services"]->set('auth', function () use($app) {

					$return = new Auth;
				    return $return;
				    
				});
				$services["auth"] = $app["services"]->get('auth');
				
				
				
				/*---------------------------------------------------------------
				 * mobiledetect
				 *--------------------------------------------------------------- */
				$app["services"]->set('mobiledetect', function () use($app) {
				
					$return = new Mobile_Detect;
					return $return;
								    
				});
				$services["mobiledetect"] = $app["services"]->get('mobiledetect');
				
				

				
				
				/*---------------------------------------------------------------
				 * routing (use asphalte class )
				 *--------------------------------------------------------------- */
				$app["services"]->set('route', function () use($app) {
				    $return = new Asphalte;
		            return $return;
		        });
		        $services["route"] = $app["services"]->get('route');
		        

		        
		        
		        
		       
    		    
    		   
    		   	
    		   	
    		   	
    		   	
    		   
    		    
    		    
    		    if (in_array("structure.php", $app["project_files"])) {
    		    	
    		    	require($app["path"]["project"]."structure.php");
    		    	/*---------------------------------------------------------------
    		    	 * structure shema & migrations
    		    	 *--------------------------------------------------------------- */      
    		    	$app["services"]->set('structure', function () use($app) {
    		    	        $return = new Structure;
    		    	        return $return;
    		    	});
    		    	$services["structure"] = $app["services"]->get('structure');
    		    	
    		    }
    		    
    		   	
    		    
    		   	
    		   	

		}
		
	    public function innerload_classes($path_folder,$file){
						
						$name_explod = explode(".", $file);
		        		$name = $name_explod[0];
		        		$ext = $name_explod[1];
		        		if($ext=="php"){
		        			
		        			$toload = $path_folder.$file;
		        			if(!class_exists($name)):	
		        			require($toload);
		        			endif;
		        			
		        			ad_service($name);	
		        		
		        		}
	    } 


        public	function load_classes($path_folder,$array_files) {
        	global $app;
        	
        	
        	//$listfilesClient = dirToArray_simple($path_folder);
        	if(is_array($array_files)){
	        	foreach ($array_files as $file) {
	        		if (!is_array($file)) {
		        		$this->innerload_classes($path_folder,$file);
	        		}
	        	}

        	}else{
        		$this->innerload_classes($path_folder,$array_files);
        	}
        	 
        	
        }
        /*---------------------------------------------------------------
         * Load relative files
         *--------------------------------------------------------------- */ 
        public	function get_project() {
        	global $app;
        	$app["project"]=[];

        	/* load other plugins in relative path */
        	if($app["subfolder"]==""):
        		$subfolder="root";
        		$app["subfolder"] = "root";
        	else:
        		$subfolder = $app["subfolder"];
        	endif;
        	$local_folder = $app["path"]["project"].$subfolder.'/';
        	
        	/*scan subfolder */
        	foreach ($app["project_files"] as $key => $value) {
        		if($key==$subfolder && is_array($value)){
        			$app["project_files_local"] = $value;
        		}
        	}
        	
        	$app["project"][] = $subfolder;
        	
        	/* load other classes in the folder */
        	if(isset($app["project_files_local"])):	
        	$this->load_classes($local_folder,$app["project_files_local"]);
        	endif;
        	/* lunch init function of the primary class */
        	if( method_exists($subfolder,"init") ):
        		singleton($subfolder)->init();
        	endif;
        	
        	
        	/*
        	--------------------------------------------
        	!! get languages if exist
        	-------------------------------------------- */
        	$yaml = $app["path"]["project"].$subfolder."/languages.yaml";
        	
        	if(file_exists($yaml) ):$app["languages"] = Spyc::YAMLLoad($yaml);endif;
        	
			$base_path_plugins = $app["path"]["project"].$subfolder.'/plugins/';
			if (
				isset($app["project_files_local"]["plugins"])
				&& is_array($app["project_files_local"]["plugins"])
			):
				
				foreach ($app["project_files_local"]["plugins"] as $folder) {
				
					$folder_name = strtr($folder[0] , [$base_path_plugins=>"","/"=>"",".php"=>""]);
					$app["project"][] = $folder_name;
					$fileroot = $base_path_plugins.$folder_name."/".$folder_name.".php";
					$pahfolder = $base_path_plugins.$folder_name."/";
					/*
					load
					*/
					$this->load_classes($pahfolder,$folder);
					
					ad_service($folder_name);	
					
					if( method_exists($folder_name,"init") ){
					
						singleton($folder_name)->init();
					}else{
						dd("singleton($folder_name)->init() not find");
					}
					
				}
				
			endif;
			
        	
        	
        	
        	
        }
        
        /*---------------------------------------------------------------
         * plugins
         Les plugins sont constitués d'une seule classe par convention
         
         ->__construct()
         ->up()
         ->down()
         ->inject()
         ->route()
         *--------------------------------------------------------------- */
        
      
        	
        
        
        
        
        
        
        /*---------------------------------------------------------------
         * route
         *--------------------------------------------------------------- */
        
        public function run(){
        		global $app;
        		global $services;	
        			
					
        			
				if (class_exists("Route")) :$routing = new Route;
        		else: dd("Route Class not found!");
        		endif;
        			
        		gc_collect_cycles();
        		
        		echo singleton("route")->dispatch();
        			
        }	
        
        
		
		
	
	
	


	
}

