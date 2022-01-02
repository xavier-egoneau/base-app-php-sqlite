<?php if ( ! defined('in_app')) die('Accès Interdit');

use Carbon\Carbon;
class helpers {
	

	

	public function exerpt($text, $chars = 150){
		   
		if(strlen($text) > $chars) {
			       $text = $text.' ';
			       $text = str_replace("</p>", "</p> ", $text);
			       $text = str_replace("<br>", "<br> ", $text);
			       $text = str_replace("<br/>", "<br/> ", $text);
			       $text = strip_tags($text);
			       $text = substr($text, 0, $chars);
			       $text = substr($text, 0, strrpos($text ,' '));
			       $text = $this->check_space_all($text);
			       $text = $text.'...';
		}
		return $text; 	
	}   

public function page404($lang="fr") {
    global $app;
    global $datas;
    	
    header('HTTP/1.0 404 Not Found');
    singleton("root")->initpage();
    twig("404",$datas); 
    die(); 
}


public function gettoken(){
	$token = generer_token("form");
	$result = ["result"=>$token];
	json($result);
	die();
}	

	
	
	
	public function validation_datas($options) {
		/*
		
		https://openclassrooms.com/courses/les-filtres-en-php-pour-valider-les-donnees-utilisateur
		
		ex : 
		$options = array(
		    'prenom' => FILTER_SANITIZE_STRING, //Enlever les balises.
		    'email' => FILTER_VALIDATE_EMAIL, //Valider l'adresse de messagerie.
		    'age' => array(
		        'filter' => FILTER_VALIDATE_INT, //Valider l'entier.
		        'options' => array(
		            'min_range' => 0 //Minimum 0.
		        )
		    )
		);
		*/
		$resultat = filter_input_array(INPUT_POST, $options);
	
		return $resultat;
	}

		
		
		public function connect($apicode,$baseurl="") {
			global $app;
			$result = "false";
			if($baseurl==""):
				$baseurl = $app["url"]["root"];
			endif;
			
			if(isset($apicode) && $apicode!=""):
				$postdata = http_build_query(
				    array(
				       "apicode"=>$apicode
				    )
				);
				
				$opts = array('http' =>
				    array(
				        'method'  => 'POST',
				        'header'  => 'Content-type: application/x-www-form-urlencoded',
				        'content' => $postdata
				    )
				);
				
				$context  = stream_context_create($opts);
				
				$result = file_get_contents($baseurl."datas/connect", false, $context);
					
			endif;
			return json_decode($result,true);
			
		}
}