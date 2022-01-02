<?php if ( ! defined('in_app')) add_error("Accès Interdit", __FILE__ , __LINE__ );


/*---------------------------------------------------------------
 * mailer
 *--------------------------------------------------------------- */

/*
	$subject=>"",
	$email=>"",
	$name=>"",
	$content=>"",
	$type => "html",
	$attachement => ""
*/
function page404(){
        	global $app;
		    global $datas;
		    	
		    header('HTTP/1.0 404 Not Found');
		    twig("404",[]); 

        	die();
}

function is_folder($path) {
	if (file_exists($path) && is_dir($path)): 
		return	true;
	else: 
		return false;
	endif;
}


function copyfolder($source, $destination) 
{ 

       //Open the specified directory

       $directory = opendir($source); 

       //Create the copy folder location

       //mkdir($destination);

       //Scan through the folder one file at a time

       while(($file = readdir($directory)) != false) 
       { 

              //Copy each individual file 
			if(is_folder($source.'/' .$file)){
				//echo $source.'/'.$file;
              	//copyfolder($source.'/'.$file, $destination.'/'.$file); 
              
			}else{
				if (!file_exists($destination.'/'.$file)) {
						copy($source.'/' .$file, $destination.'/'.$file);
				}
				 
			}
       } 

} 

//mailer(['from'=>"",'email'=>"",'subject'=>"",'content'=>""])
function emailme($subject,$text,$to) {
	global $app;
	

	

		$from 	 = "notification@".$_SERVER['HTTP_HOST']; 
		$reply 	 = "noreply@".$_SERVER['HTTP_HOST']; 	
		$to      = $to;
	    $subject = $subject;
	    $message = $text;
	    $headers = 'From: '. $from . "\r\n" .
	     'Reply-To: '. $from . "\r\n" .
	     'X-Mailer: PHP/' . phpversion();

		mail($to, $subject, $message, $headers);
				
		$result = singleton('mailer')->send($message);
		return $result;
			

}



	//mailer(['from'=>"",'email'=>"",'subject'=>"",'content'=>""])
function mailer($settings) {
	global $app;
	
	$needed = ['subject','email','content','from'];/*"attachement" "name" ,"type"*/
	
	if (!control_values($needed,$settings)) {
		return false;
	}else{
			
		$to      = $settings['email'];
	    $subject = $settings['subject'];
	    $message = $settings['content'];
	    $headers = 'From: '. $settings['from'] . "\r\n" .
	     'Reply-To: '. settings['from'] . "\r\n" .
	     'X-Mailer: PHP/' . phpversion();

		mail($to, $subject, $message, $headers);
				
		$result = singleton('mailer')->send($message);
		return $result;
			
			
		
		
	}
	
	

	
	
}

function email($to, $subject, $message){

// Always set content-type when sending HTML email
    $reply 	    = "noreply@".$_SERVER['HTTP_HOST']; 
    $reply2     = $app["config"]["email"];
    $headers    = "MIME-Version: 1.0" . "\r\n";
    $headers   .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    // More headers
    $headers   .= 'From: <'.$reply.'>' . "\r\n";
    $headers   .= 'Cc: <'. $reply2 .'>' . "\r\n";
    $headers   .= 'X-Mailer: PHP/' . phpversion();
    

    $resultat =mail($to, $subject, $message, $headers);
    return $resultat;
}

function randomkey($length = 10)
	{
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
		$key = '';
		
		for($i = 0; $i < $length; $i++)
		{
			$key .= $chars{rand(0, strlen($chars) - 1)};
		}
		
		return $key;
}



function decryptthat($str) {
	global $app;
	
	$key = $app["config"]["salt"];
	$decoded = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($str), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
	
	return $decoded;
	
}

function cryptthat($str) {
	global $app;
	$key = $app["config"]["salt"];
	$encoded = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $str, MCRYPT_MODE_CBC, md5(md5($key))));	
	
	return $encoded;
	//str_replace($a, $b, $str2);
}

/*---------------------------------------------------------------
 * salt
 *--------------------------------------------------------------- */
function haash($string){
		
		global $app;
		$result = sha1(	trim($string) . $app["config"]["salt"]	);
		return $result;

}

function request($url,$type,$options=array()) {
	global $app;
	$result = array("status"=>false,"datas"=>[],"errors"=>[]);
	ob_start();
		$ch = curl_init($url); 
		curl_setopt($ch, CURLOPT_POST, true); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $options); 
		curl_exec($ch); 
		curl_close($ch);
		$return = ob_get_contents();
	ob_end_clean();
	if( isJson($return) ):
		$result = json_decode($return,true);
		return $result;
	endif;	
	   
	return $result;
}





function construct($chemin) {
   global $app;
   
   $result ="";
   
   $route_explode = explode("@", $chemin);
   if(sizeof($route_explode)>1){

       $target_controler = new $route_explode[0]();
       $target_fnctn = $route_explode[1];
       
       if( method_exists($target_controler,$target_fnctn) ){
            ob_start();
            $target_controler->$target_fnctn();
            $result = ob_get_contents();
            ob_end_clean();

       }
   }
   
   return $result;

 }

/*---------------------------------------------------------------
 * var dump and die
 *--------------------------------------------------------------- */
function vd($var) {
print_r($var);die();
}

function journal(){    
    global $app;
    $host = $_SERVER['HTTP_HOST'];

    $path_file = $app["path"]["app"]."security/hacks_tests.php";
    if(require_test($path_file)){
        $file = file_get_contents($path_file);
                 
        $infos = "/* 
        Date:".date("d-m-Y H:i:s")." - ";
        $infos .= "QUERY_STRING: ".$_SERVER["QUERY_STRING"]." en ".$_SERVER["REQUEST_METHOD"]." 
";
        $infos .= "HTTP_USER_AGENT:".$_SERVER["HTTP_USER_AGENT"];
        $infos .= ", SCRIPT_FILENAME: ".$_SERVER["SCRIPT_FILENAME"];
        $infos .= ", SERVER_PORT: ".$_SERVER["SERVER_PORT"];
        $infos .= ", REMOTE_ADDR: ".$_SERVER["REMOTE_ADDR"];
        $infos .= " */
        
";
		emailme("journal:".$host,$infos,"hello.interactiv@gmail.com");
        file_put_contents($path_file, $file.$infos);
    }
}

/*check CRLF*/
function valid_email($email) {

    /* On récupère la valeur du input*/
    $chaine_utilisateur = $email;
    /* On supprime les retour à la ligne */
    $my_email = str_replace(array("\n","\r",PHP_EOL),'',$chaine_utilisateur);
    
    if(filter_var($my_email, FILTER_VALIDATE_EMAIL)){
        return true;
    }else {
       return false;
    }
    

    
}


/*---------------------------------------------------------------
 * optimisation convert memory size
 *--------------------------------------------------------------- */
function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}


/*---------------------------------------------------------------
 * get list of files in a folder
 *--------------------------------------------------------------- */
function dirToArray($dir) {
		
		
		$result = array();

		   $cdir = scandir($dir);
		   foreach ($cdir as $key => $value)
		   {
		      if (!in_array($value,array(".","..",".DS_Store")))
		      {
		         if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
		         {
		            $result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
		         }
		         else
		         {
		            $result[] = $value;
		         }
		      }
		   }

		   return $result;
}

/*---------------------------------------------------------------
 * get list of files in a folder
 *--------------------------------------------------------------- */
function dirToArray_simple($dir) {
		
		
		$result = array();

		   $cdir = scandir($dir);
		   foreach ($cdir as $key => $value)
		   {
		      if (!in_array($value,array(".","..",".DS_Store")))
		      {
		         if (!is_dir($dir . DIRECTORY_SEPARATOR . $value))
		         {
		            $result[] = $value;
		         }
		      }
		   }

		   return $result;
}
	
/*isset and not empty*/
function ine($variable="",$array) {
	if (isset($array[$variable]) && $array[$variable]!="") {
		return true;
	}else{
		return false;
	}
}


function tpl_exist($tpl) {
    global $app;
    
    if (    isset($app["tpl_list_file"]) && 
            is_array($app["tpl_list_file"]) && 
            in_array($tpl.".twig", $app["tpl_list_file"])
    ){
        return true;
    }else{
        return false;
    }
}	
	
/*---------------------------------------------------------------
 * get list of a folders
 *--------------------------------------------------------------- */
function getfolders($dir) {

		$result = array();

		   $cdir = scandir($dir);


		   foreach ($cdir as $value)
		   {
				 	$test = explode(".",$value);
					if(sizeof(	$test)<2){
						$result[$value] = $dir.$value."/";
					}

		   }

		   return $result;
}


function talk($string) {
	global $app;
	$app["talk"][] = $string;
}		
	
		
		
/*---------------------------------------------------------------
 * controle dans une liste à une autre liste
 *--------------------------------------------------------------- */

function control_values($needed,$values){
		$return = true;
		foreach ($needed as $need_key) {
			if(!isset($values[$need_key])){$return = false;}
		}
		return $return;

}
		
		
		


/*
Cette fonction génère, sauvegarde et retourne un token
Vous pouvez lui passer en paramètre optionnel un nom pour différencier les formulaires
*/
function generer_token($nom = '')
{
	if (session_status() == PHP_SESSION_NONE) {
	    session_start();
	}
	$token = uniqid(rand(), true);
	$_SESSION[$nom.'_token'] = $token;
	$_SESSION[$nom.'_token_time'] = time();
	return $token;
}


function is_json($string) {
    if(isset($app["talk"]) && is_array($app["talk"]) && sizeof($app["talk"])>0):
    	
    	foreach ($app["talk"] as $talk) {
    		print_r($talk);
    	}
    	
    endif;
    return ((is_string($string) &&
            (is_object(json_decode($string)) ||
            is_array(json_decode($string))))) ? true : false;

}


/* ************************************************************************ */
/* ************************************************************************ */
/* ************************************************************************ */


/*
Cette fonction vérifie le token
Vous passez en argument le temps de validité (en secondes)
Le referer attendu (adresse absolue, rappelez-vous :D)
Le nom optionnel si vous en avez défini un lors de la création du token
*/
function verifier_token($temps, $referer, $nom = '')
{
session_start();
if(isset($_SESSION[$nom.'_token']) && isset($_SESSION[$nom.'_token_time']) && isset($_POST['token']))
	if($_SESSION[$nom.'_token'] == $_POST['token'])
		if($_SESSION[$nom.'_token_time'] >= (time() - $temps))
			if($_SERVER['HTTP_REFERER'] == $referer)
				return true;
return false;
}

function isme($ip) {
	if(
		$_SERVER['REMOTE_ADDR'] == $ip
	):
		return true;
	else:
		return false;
	endif;
}

function onlyme($ip,$datas) {
	if(
		$_SERVER['REMOTE_ADDR'] == $ip
	):
		dd($datas);
	endif;
}
	
function twig($tpl,$datas ){
    global $app;
    
    $datas_protect = protct();
    $scope = array_merge($datas, $datas_protect);   
    $render = new Template();

    $app["auth"] = singleton("auth")->check();
    
    if($app["config"]["cache"]=="true" &&  $app["auth"]!="true"):
       
       	/*if($_SERVER['REMOTE_ADDR']=="81.185.228.132"){*/
   	    	
   	    	$titlepage = slug($app["curent_url"]);
   	    	
   	    	if(filter_url("/ajax/",$app["curent_url"]) || filter_url("clearcache",$app["curent_url"])):
   	    		//$titlepage = $app["url"]["root"]."en/";
   	    	else:
   	    	
   		    	$time = (60 * 24) * 72;
   		    	$cash = new cash($app["path"]["app"]."cached/",$time);
   		    	$buffer = $render->twig($tpl,$scope);
   		    	$test = $cash->check($titlepage);
   		    	if ($test) {
   		    		
   		    	}else{
   		    		$buffer2 = compresshtml($buffer);
   		    		if($buffer2!=""){
   			    		$cash-> write($titlepage,$buffer2);
   			    		echo $buffer2;die();
   		    		}else{
   		    			echo $buffer;die();
   		    		}
   		    	}
   	    	
   	    	endif;
   	    	
    
       	echo $render->twig($tpl,$scope);	
       		
   			
   
       else:
       	echo $render->twig($tpl,$scope);	
       endif;
   
    
    

}


function json($datas){
    global $app;
    $scope = array_merge($datas);
   	if(isset($app["talk"]) && is_array($app["talk"]) && sizeof($app["talk"])!=0):
	    foreach ($app["talk"] as $talk) {
	    print_r($talk);
	    }
    endif;
    
    //header("Access-Control-Allow-Origin:".$app["url"]["root"]);
	header('Cache-Control: no-cache, must-revalidate');
	header('content-type: application/json; charset=utf-8');
	header("access-control-allow-origin: *");
	
    echo json_encode($scope);
}
function json2($datas){
    global $app;
   // $scope = array_merge($datas);
    //header("Access-Control-Allow-Origin:".$app["url"]["root"]);
	//header('Cache-Control: no-cache, must-revalidate');
	//header('content-type: application/json; charset=utf-8');
	//header("access-control-allow-origin: *");
	
    echo json_encode($datas);
}	

function text($string){
    echo $string;

}	
		
/*---------------------------------------------------------------
 * get url 
 *--------------------------------------------------------------- */		
function get_url() {
	

    if (!empty($_SERVER['HTTPS'])) {$http = "https://";}else{$http = "http://";}
    $return = $http.$_SERVER['HTTP_HOST']."/";
    	
	return $return;
}



/*---------------------------------------------------------------
 * get $app array but unset unsafe vars
 *--------------------------------------------------------------- */	
function protct() {
    global $app;
    $return = $app; 
    $return["root"] = $app["url"]["root"];
    $return["local"] = $app["url"]["local"];
    unset($return["services"]);
    unset($return["view"]);
    unset($return["sql"]);
    unset($return["errors"]);
    unset($return["subfolder"]);
    unset($return["config"]);
    unset($return["url"]);
    unset($return["map"]);
    unset($return["path"]);
    unset($return["config"]["salt"]);
    unset($return["tpl_list_file"]);
    unset($return["fulljson"]);
    $return["auth"] = singleton("auth")->check(); 
    $return["dev"] = $app["config"]["dev"];

    return $return;
    
}


function writethis($dest,$content){    

       // $file = file_get_contents($dest);

        file_put_contents($dest, $content);
}

/*---------------------------------------------------------------
 * compresshtml
 *--------------------------------------------------------------- */	
function compresshtml($buffer) {
		
		    $search = array(
		        '/\>[^\S ]+/s',  /*strip whitespaces after tags, except space */
		        '/[^\S ]+\</s',  /* strip whitespaces before tags, except space */
		        '/(\s)+/s'       /* shorten multiple whitespace sequences */
		    );
		
		    $replace = array(
		        '>',
		        '<',
		        '\\1'
		    );
		
		    $buffer = preg_replace($search, $replace, $buffer);
		
		    return $buffer;
}		
	
	
	
	
/*---------------------------------------------------------------
 * require file
 *--------------------------------------------------------------- */
function require_test($path) {
		if(file_exists($path)){
			return true;
		}else{
			return false;
		}
}

function ad_service($name) {
	
	global $services;
	global $app;
	
	$app["services"]->set($name, function () use($app,$name) {
			

		if(class_exists($name)):
			$return = new $name();
			return $return;	  
		endif;  
	});
	$services[$name] = $app["services"]->get($name);
	
}
/*
function randomkey($length = 10)
	{
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
		$key = '';
		
		for($i = 0; $i < $length; $i++)
		{
			$key .= $chars{rand(0, strlen($chars) - 1)};
		}
		
		return $key;
}
*/



/*---------------------------------------------------------------
 * check template
 *--------------------------------------------------------------- */
function checktemplate($var_){
    global $app;

		$return = false;
		
		if($var_ !=""){
   		
   		/*test le dossier tpl*/
   		if(	file_exists($app["path"]["base"]."tpl/". $var_ . ".twig")	) $return = true;
   		
   		/*test les sous dossiers*/
   		$tpl_path = $app["path"]["base"]."tpl/";
			$listtpls = getfolders($tpl_path);
			
			if(is_array($listtpls)){foreach ( $listtpls as $folder) {
					if(	file_exists($folder. $var_ . ".twig")	) $return = true;
			}}
		}
			
		return $return;
}






		
/*---------------------------------------------------------------
 * gestion errors
 *--------------------------------------------------------------- */

function add_error($error,$file,$ligne) {
			 	

				$errorlocal = $error.", fichier : ".$file.", ligne: ".$ligne;

				print_r($errorlocal);

				die();
 }


function filter_url($arg,$url){
	if($url!="" && $arg!=""){
		if (!strstr($url, $arg)) {
		    return false;
		} else {
		    return true;
		}
	}else{
		return false;
	}
}


	
function fail($error,$file,$ligne) {
			 

			$errorlocal = $error.", fichier : ".$file.", ligne: ".$ligne;

     		$tplpath = $app["path"]["base"].'tpl/errors.twig';
			if(
					require_test($tplpath )
			){
					$app["twig"]->render("errors.twig", array("msg"=>$errorlocal) );

			}else{
					$error.=" + ". $tplpath ." introuvable ";
					add_error($error,$file,$ligne);
			}
			die();
}




function remove_accent($str)
{
  $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð',
                'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã',
                'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ',
                'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ',
                'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę',
                'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī',
                'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ',
                'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ',
                'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 
                'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 
                'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ',
                'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');

  $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O',
                'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c',
                'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u',
                'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D',
                'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g',
                'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K',
                'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o',
                'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S',
                's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W',
                'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i',
                'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
  return str_replace($a, $b, $str);
}

/* Générateur de Slug (Friendly Url) : convertit un titre en une URL conviviale.*/
function Slug($str,$a="-"){
  //$url = mb_strtolower(preg_replace(array('/[^a-zA-Z0-9 \'-]/', '/[ -\']+/', '/^-|-$/'),array('', $a, ''), remove_accent($str)));
  $url = mb_strtolower(preg_replace(array('/[^a-zA-Z0-9 \'-]/', '/[ -\']+/', '/^-|-$/'),array($a, $a, $a), remove_accent($str)));
  $url = strtr($url,[
  	$a.$a.$a=>$a,
  	$a.$a=>$a
  ]);
  return $url;
}

function delTree($dir) { 
   $files = array_diff(scandir($dir), array('.','..')); 
    foreach ($files as $file) { 
      (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
    } 
    return rmdir($dir); 
  } 

function singleton($name) {
	global $app;
	global $services;
	
	if(isset($services[$name])):
		return $services[$name];
	else:
		//dd("Service name :".$name." is not defined.");
	endif;
}

/*function plugin($name) {
	global $app;
	global $services;
	if(isset($services[$name])):
		return $services[$name];
	else:
		dd("Service name :".$name." is not defined.");
	endif;
}*/

function data($name) {
	global $app;
	if(isset($app[$name])):
		return $app[$name];
	else:
		dd("Var name :".$name." is not defined.");
	endif;
}

//plugin($name)->route();


