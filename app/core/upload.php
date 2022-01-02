<?php if ( ! defined('in_app')) die('Accès Interdit');
	
	
	/* 
	 * drgstr - upload
	 *
	 * @author      Xavier Egoneau
	 * @copyright   2016 Xavier Egoneau
	 * @version     5.0
	 *
	 */

use Intervention\Image\ImageManagerStatic as Image; 
	 
	  class upload extends Socle {
		
static function upload_media($file) {
			global $app;
			$errors = [];
			$permitted = array(
			/*txt*/
			'application/msword', 
			'application/pdf', 
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
			'text/plain', 
			'text/rtf', 
			/*img*/
			'image/gif', 
			'image/jpeg', 
			'image/jpg', 
			'image/pjpeg', 
			'image/png', 
			//'image/tiff', 
			//'image/psd',
			//'application/ps',
			//'application/ai',
			//'application/eps',
			/*compressed files*/
			'application/zip', 
			'application/zip', 
			'application/x-zip-compressed', 
			'multipart/x-zip', 
			'application/x-compressed', 
			'application/octet-stream',
			'application/x-rar-compressed',
			/*audio*/
			'audio/mpeg', 
			'audio/mpeg3', 
			'audio/x-mpeg-3', 
			/*video*/
			'video/mpeg', 
			'video/mp4', 
			'video/webm',
			'video/quicktime', 
			'video/ogg', 
			'video/x-ms-wmv'
			
			);
			//dd($file);
			if (
				isset($file["name"]) && 
				is_array($file["name"])
			) {
				$file["name"] = $file["name"][0];
				$file["type"] = $file["type"][0];
				$file["tmp_name"] = $file["tmp_name"][0];
				$file["error"] = $file["error"][0];
				$file["size"] = $file["size"][0];
			}
			
			//dd($file);
			
			
			if (
				isset($file["name"]) && 
				$file["name"]!="" 
			) {
			
			
				$file['type'] = strtolower($file['type']);
				if( in_array($file['type'], $permitted)	){

					$filename		=	$file["name"];
					$idUnik3  = md5(uniqid(mt_rand(), true));
					$extension 	= 	upload::getExtension($filename);
					$extension 	= 	strtolower($extension);
					$newname = $idUnik3.".".$extension;
					$uploadedfile 	 = $file['tmp_name'];
					$type_file 		= $file["type"];//upload::type($file['tmp_name']);
					$pathnew = $app["path"]["uploads"].$newname;
					$array_img = array(
						'image/gif', 
						'image/jpeg', 
						'image/jpg', 
						'image/pjpeg', 
						'image/png', 
						'image/tiff', 
						'image/psd'
					);
					
					if(
						in_array($file['type'], $array_img)
					){
						$type="img";
					}else {
						$type = "other";
					}
					
					if( !is_uploaded_file($uploadedfile) ){
							$errors[]="Erreur. Le fichier n'a pas été correctement téléchargé.";
						
					}else{
						
	
						if (isset($file["size"]) && $file["size"] > upload::parse_size(ini_get('post_max_size'))	) {
						   $errors[]="Fichier trop volumineux!";
	
						}
					
						if($type=="img"){
							
							if(  !getimagesize($file["tmp_name"]) ){
								$errors[]="Le fichier est endommagé.";
							}else{
								
								upload::resize_img($uploadedfile,$newname,400,"small");
								upload::resize_img($uploadedfile,$newname,800,"medium");
								upload::resize_img($uploadedfile,$newname,1600,"big");
								move_uploaded_file($file["tmp_name"], $app["path"]["uploads"]. $newname);
																
							}
						}else{
							move_uploaded_file($file["tmp_name"], $app["path"]["uploads"]. $newname);
						}
						
					}
					
					
					
				}else{
					$errors[]="Erreur. Ce type fichier n'est pas accepté.";
				}
				
				
				
			}
			
			if(sizeof($errors)==0 && isset($newname)){
				return array("result"=>true, "file"=>$newname, "error"=>$errors);
			}else{
				return array("result"=>false, "file"=>"", "error"=>$errors);
			}
				
		}





		
		
		
		static function parse_size($size) {
		  $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); /* Remove the non-unit characters from the size.*/
		  $size = preg_replace('/[^0-9\.]/', '', $size); /* Remove the non-numeric characters from the size.*/
		  if ($unit) {
		    /* Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.*/
		    return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
		  }
		  else {
		    return round($size);
		  }
		}




		static function getExtension($str) 
		{
		
		         $i = strrpos($str,".");
		         if (!$i) { return ""; } 
		         $l = strlen($str) - $i;
		         $ext = substr($str,$i+1,$l);
		         return $ext;
		}
		
		static function type($file) {
	
			//return mime_content_type($file);
		}
		
		
		
				
		
		static function resize_img($path,$name,$size,$size_name){
			global $app;
			Image::configure(array('driver' => 'gd'));	/* gd or imagick*/
			$img = Image::make($path);
			// resolution
			$x = $img->exif('XResolution');
				
			if($size_name!=""){
			 	list($width,$height)	=	getimagesize($path);
			 	$img->resize($size,null, function ($constraint) {
			 	    $constraint->aspectRatio();
			 	    $constraint->upsize();
			 	});
 			}else{
 				if($x!=""){
  					$resolution = strtr($x,["/1"=>""]);
  					if($resolution!=72){
  									
 
  									
  									$coef_reso = $resolution / 72;
  									
  									list($width,$height)	=	getimagesize($path);
  									$sizenew = $width*$coef_reso;
  									if($sizenew > 3000){
  										$sizenew = 3000;
  									}
  									$img->resize($sizenew,null, function ($constraint) {
  										    $constraint->aspectRatio();
  										    $constraint->upsize();
  									});
  									
  					}
  				}	
 							
 			}
 						
		 	if($size_name!=""){
		 		$pathnew = $app["path"]["uploads"].$size_name."_".$name;
		 	}else{
		 		$pathnew = $app["path"]["uploads"].$name;
		 	}
		 	$img->save($pathnew, 100);
		}
		
		
		
		
		static function crop_img($url_file,$newwidth,$size,$file,$format){/*$size = big small or medium*/
			global $app;
			$errors = array();
			Image::configure(array('driver' => 'gd'));	/* gd or imagick*/
			$extension 	= 	upload::getExtension($file);
			$extension 	= 	strtolower($extension);
			$idUnik3  = md5(uniqid(mt_rand(), true));
			$newname = $idUnik3.".".$extension;
			
	
			$img = Image::make($url_file);
			list($width,$height)	=	getimagesize($url_file);
			$img->crop( (int)$format['w'], (int)$format['h'], (int)$format['x'], (int)$format['y']);
			$pathnew = $app['root_path'].$app['public_folder']."/uploads/".$newname;
			$img->save($pathnew, 100);
			
			upload::resize_img($pathnew,$newname,400,"small");
			upload::resize_img($pathnew,$newname,800,"medium");
			upload::resize_img($pathnew,$newname,1600,"big");
	
			
			
			
			if(sizeof($errors)==0){
				return array("result"=>true, "file"=>$newname, "error"=>$errors);
			}else{
				return array("result"=>false, "file"=>$newname, "error"=>$errors);
			}
		}
		
		
		

		static function upload_and_min($type) {
			
			global $app;
		
			$file_to_upload = Upload::upload_media($type);
			if($file_to_upload["result"]){
				$newurl = $app['root_path'].$app['public_folder']."/uploads/medium_". $file_to_upload["file"];
			}
						
			if(sizeof($file_to_upload["error"])==0){
				return array("result"=>true, "file"=>$file_to_upload["file"], "error"=>$file_to_upload["error"]);
			}else{
				return array("result"=>false, "file"=>$file_to_upload["file"], "error"=>$file_to_upload["error"]);
			}
			
		}
		
		
		
		
}