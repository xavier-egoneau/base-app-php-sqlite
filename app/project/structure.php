<?php if ( ! defined('in_app')) add_error("Accès Interdit", __FILE__ , __LINE__ );


/* for migrate and db */
/*use Illuminate\Database\Capsule\Manager as Capsule;  
use Phinx\Migration\AbstractMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
*/
//use Illuminate\Database\Schema\Blueprint;
class Structure  
{

    /** @var \Illuminate\Database\Capsule\Manager $capsule */
    //public $capsule;
    /** @var \Illuminate\Database\Schema\Builder $capsule */
    //public $schema;
    
    
    

	
	
	public function check(){
	    $return = "up";
	    if(singleton("shema")->hasTable('parametres')){
	        $return = "down";
	    }
	    return $return;
	}
	
	/*
	| -------------
	| migration
	| -------------
	*/
	public function bind() {
	    global $app;
	    
	    $test = $this->check(); 
	    if($test=="up"){
	        $this->up();
	    }else{
	        $this->down();
	        $this->up();
	    }
	    
	    die("Migration réussie");
	    
	}
	
	public function init_db() {
		singleton("db")->bootEloquent();
		singleton("db")->setAsGlobal();
	}
	public function up()
		{
			
			
	        singleton("shema")->create('parametres', function(Illuminate\Database\Schema\Blueprint $table){
	                 
		
				$table->increments('id');
				$table->integer('ordre')->default(0);
				$table->integer('version')->default(0);
				$table->string('title')->default("Site exemple");
				$table->text('description')->default('');
				$table->text('keywords')->default('');
				$table->text('keywordsen')->default('');
				$table->string('logo')->default('');
				$table->string('branding')->default('');
				$table->string('shortcut_icon')->default('');
				$table->string('mailaddress')->default('');
				$table->string('analytics')->default('');
				$table->string('permissions_parametres')->default('false');
				$table->text('permissions_folders')->default('');
				$table->text('permissions_modules')->default('');
				$table->text('permissions_users')->default('');
				$table->string('production')->default("false");
				$table->text('rs')->default('');
				$table->string('apicode')->default('');
				$table->string('lang')->default("fr");
				$table->text('langues')->default('');
				$table->text('ipfilter')->default('');
				$table->string('htmlpublish')->default("false");
				//$table->text('valideconditions')->default(false);
				$table->timestamps();

				
				
			});
			singleton("shema")->create('users', function(Illuminate\Database\Schema\Blueprint $table){
				$table->increments('id');
				//$table->string('titre')->default('New');
				$table->integer('ordre')->default(0);
				$table->string('parametres')->default('');
				$table->timestamps();
				$table->string('email')->default('');
				$table->string('password')->default('');
				$table->string('type')->default("4");
				$table->string('nom')->default('');
				$table->string('prenom')->default('');
				$table->string('societe')->default('');
				$table->string('tel')->default('');
				$table->text('infos')->default('');
				$table->string('statut')->default("false");
				$table->string('remember_token')->default("");

			});
			
			singleton("shema")->create('modules', function(Illuminate\Database\Schema\Blueprint $table){
				$table->increments('id');
				$table->integer('ordre')->default(0);
				$table->string('parametres')->default('');
				$table->timestamps();
				$table->string('titre')->default('');
				$table->string('slug')->default('');
				$table->text('options')->default('');
				$table->string('commentaire')->default('');
				$table->string('type')->default("texte");
				$table->integer('cat')->default();
				$table->string('traductible')->default("false");
				//$table->string('require')->default("false");
				//$table->string('replicable')->default("false");
				
				//for regions html
				$table->text('html')->default('');
				$table->text('needed')->default('');
				
				
		
			});
			

	        	
	
			singleton("shema")->create('folders', function(Illuminate\Database\Schema\Blueprint $table){

				$table->increments('id');
				$table->timestamps();
				$table->integer('ordre')->default(0);
				$table->string('parametres')->default('');
				$table->string('template')->default('');
				$table->string('titre')->default('New');
				$table->text('modules')->default('');
				$table->string('byregion')->default('false');
				$table->string('page')->default('true');
				$table->string('orderbydate')->default('true');
				$table->text('modulesinlist')->default('');
				$table->string('forcemodules')->default('true');
				$table->string('showtags')->default('false');
				$table->string('sitemappriority')->default('0.5');
				$table->string('sitemapfrequence')->default('never');
				$table->text('perm2')->default('');
				$table->text('perm3')->default('');
	
			});
			
	
			singleton("shema")->create('articles', function(Illuminate\Database\Schema\Blueprint $table){
				$table->increments('id');
				//$table->string('date')->default(0);
				$table->integer('ordre')->default(0);
				$table->string('parametres')->default('');
				$table->string('titre')->default('New');
				$table->string('url')->default('');
				$table->string('folder')->default('');
				$table->string('lang')->default("fr");
				$table->string('title')->default('');
				$table->longText('description')->default('');
				$table->integer('bydate')->default('00000000');
				$table->longText('tags')->default('');
				
				$table->longText('tagsslug')->default('');
				$table->string('ref')->default('');
				$table->text('modules')->default('');
				$table->string('template')->default('');
				$table->string('statut')->default("false");
				$table->string('page')->default("false");
				//$table->string('langlist')->default('');
				$table->longText('creditsimgs')->default('');
				$table->string('sitemappriority')->default('0.5');
				$table->string('sitemapfrequence')->default('never');
				$table->timestamps();
			});
			
			
			singleton("shema")->create('metas', function(Illuminate\Database\Schema\Blueprint $table){
				$table->increments('id');
				$table->string('parametres')->default('');
				$table->string('alt')->default('');
				$table->string('ref')->default('');
				$table->timestamps();
			});
			

			
		}
	
		/**
		 * Reverse the migrations.
		 *
		 * @return void
		 */
		public function down()
		{
			singleton("shema")->dropIfExists('parametres');
			singleton("shema")->dropIfExists('articles');
			singleton("shema")->dropIfExists('folders');
			singleton("shema")->dropIfExists('modules');
			singleton("shema")->dropIfExists('regions');
			singleton("shema")->dropIfExists('users');
			singleton("shema")->dropIfExists('metas');
		}
		
		
		public function checkcolumn($slug){
			if( singleton("shema")->hasColumn('articles', $slug) ){  /*check whether users table has email column*/
			 	return true;
			}else{
				return false;
			}
			
		}
		public function  check_column_lang($datas,$languages) {
			$result = false;
			
			
				foreach ($languages as $langu) {
					 
					if(	
						 $this->checkcolumn($datas['slug'].$langu)
					){
						$result = true;	
					}else{
						$result = false;
					}
					 	
				}
			
			return $result;
		}
		
		public	function clean_structure($languages=[]) {
			$modules = Module::all();
			$params = Parametre::first()->get()->toArray()[0]; 
			
			if (!$modules->isEmpty()){
				$moduless = $modules->toArray();
				$tokill = [];
				$mod =['id','ordre','parametres','titre','url','folder','lang','title','description', 'tags','ref','modules','template','statut','page','created_at','updated_at','bydate'];
				$languages_brut = $params["langues"];
				$languages = explode(",", $languages_brut);
				$columns = singleton("shema")->getColumnListing('articles');

				foreach ($moduless as $module) {

					$slugg = $module["slug"];
					if($module["slug"]!=""){
						$mod[] = $slugg;
						if(	!in_array($slugg, $columns)): 	
							$this -> add_column2($module,$slugg);
						endif;
					}
					
					

					if(sizeof($languages)>0 && $module["traductible"]=="true"):
						foreach ($languages as $lang) {
							$slugg = $module["slug"].$lang;
							$mod[] = $slugg;
							if(	!in_array($slugg, $columns)):   	
								$this -> add_column2($module,$slugg);
							endif;
						}
					endif;
				}

				
				
				foreach ($columns as $column) {
					if (!in_array($column, $mod)) {
						//delete
						$tokill[] = $column;
						
					}
				}
				
				singleton("shema")->table('articles', function(Illuminate\Database\Schema\Blueprint $table) use($tokill){
				    $table->dropColumn($tokill);
				});

				
				
				

			}
		}
		
		
		
		public function rename($from,$to) {
			if(
				$this->checkcolumn($from) && !$this->checkcolumn($to) 
			){
				singleton("shema")->table('articles', function (Illuminate\Database\Schema\Blueprint $table) use($from,$to) {
				    $table->renameColumn($from, $to);
				});
			}else{
				//echo($from." no exist or ".$to." exist");
			}
		}
		
		public	function drop_column($datas,$languages) {
			global $app;
			//dd($datas);

			//if (isset($datas['slug']) && 	$this->checkcolumn($datas['slug'])	) :
			if( isset($datas['slug']) && singleton("shema")->hasColumn('articles', $datas['slug']) ):
				singleton("shema")->table('articles', function(Illuminate\Database\Schema\Blueprint $table) use($datas){
				    $slug = $datas['slug'];
				    $table->dropColumn($slug);
				});		
			endif;
			
		}
		
		
		public function add_column2($module,$slug) {
			
			$type = $module["type"];
			singleton("shema")->table('articles', function(Illuminate\Database\Schema\Blueprint $table) use($datas,$slug){
				global $app;
								if ($type=="") {
									$type = "texte";
								}
			
							    if(
							    	$type=="textarea" || 
							    	$type=="wysiwyg" || 
							    	$type=="tags" || 
							    	$type=="code" || 
							    	$type == "select_multiple" || 
							    	$type == "checkbox" || 
							    	$type == "articles" || 
							    	$type == "Choix multiples" || 
							    	$type == "module_articles" || 
							    	$type == "contentmultiple" || 
							    	$type=="gallery" || 
							    	$type=="yaml" || 
							    	$type=="images"
							    ){
							    	$table->text($slug)->default('');
	
							    }else if(
							    
							    	$type=="zip" || 
							    	$type=="pdf" || 
							    	$type=="image"  
							    	
							    ){
							    	$table->string($slug)->default('');
							    	
							    }else{
							    	
							    	$table->string($slug)->default('');
							    
							    }
							    
							    
							});
			
			
		}
		
		public function add_column($datas,$languages) {
			
			if(	!$this->checkcolumn($datas['slug'])){ 	
				$this -> add_column2($datas,$datas['slug']);
				if (	$this-> check_column_lang($datas,$languages)	) :

					if(
						isset($datas['traductible']) && 
						$datas['traductible']=="true" && 
						is_array($languages)		
					){
						
						foreach ($languages as $langu) {
							$slugg = $datas['slug'].$langu;
							if(	!$this->checkcolumn($slugg) ): 	
								$this -> add_column2($datas,$slugg);
							endif;
						}
					}
				endif;
			}
			
		}
		
		
		
		
				
	

}
