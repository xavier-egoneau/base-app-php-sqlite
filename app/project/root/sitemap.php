<?php if ( ! defined('in_app')) die('Accès Interdit');

//use \DrewM\MailChimp\MailChimp;
class sitemap  {
    
    
    public $datas;
    public $app;
     /* Need:
        
        singleton("sitemap")->route(); in root-> route
        a twig "ag" in template folder
      */


    

    /* because framework work want a class init */
    public function init(){}

        /* sitemap */
    public function go() {
          global $app;
          global $datas;
          header('Content-Type: text/xml; charset=UTF-8');
          
          singleton("root")->initpage();

          $query = Article::all();
          $datas["pages"]=[];
          
          
          foreach ($query as $page) {
           
            $priority = $page->sitemappriority;
            $frequence = $page->sitemapfrequence;

            $lastupdate = explode(" ", $page->created_at);

            if($page->updated_at!=""){
              $lastupdate = explode(" ", $page->updated_at);
            }


            $params = singleton("datas")->parametres();
            $crtatgen = $params["created_at"];
            $updtatgen = $params["updated_at"];
            //dd($params);
            $lastupdate_gen = explode(" ", $crtatgen);
            if($updtatgen!=""){
              $lastupdate_gen = explode(" ", $updtatgen);
            }
            
            if ($page->page=="true" ) {
              
              $url = $page->url;
              if($url!=""){
                $newpage = [
                        "url"=> $app["url"]["root"].$url, 
                        "lastupdate"=>$lastupdate[0],
                        "frequence"=>$frequence,//never, monthly,yearly,daily,weekly    
                        "priority"=>$priority //entre 0 et 1 avec des ,
                        ];
                $datas["pages"][] = $newpage;
              }
              
            }

            
           }//endforeach 

          $newpage = [
                    "url"=> $app["url"]["root"], 
                    "lastupdate"=>$lastupdate_gen[0],
                    "frequence"=>"yearly",//never, monthly,yearly,daily,weekly    
                    "priority"=>"1"//entre 0 et 1 avec des ,
                    ];
          $datas["pages"][] = $newpage;

          twig("sitemap",$datas); 
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
      
        		singleton("route")->match('GET','/sitemap', "sitemap@go");
        		
        		
        		
       
        	
    }
        
        
        
    
   
    
   
}