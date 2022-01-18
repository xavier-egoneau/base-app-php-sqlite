<?php

/*---------------------------------------------------------------
 * autoload
 *--------------------------------------------------------------- */
define('in_app', '');
require_once("../app/vendor/autoload.php");//load libs
//require_once("../app/project/datas/datas.php");//load libs
require_once("../app/core/cache.php");//load libs

//print_r(get_declared_classes());
//die();
$myapp = new Socle(__dir__);

/*---------------------------------------------------------------
 * datas
 *--------------------------------------------------------------- */
/*
$app["services"]->set('datas', function () use($app) {         
$return = new Datas; 
return  $return;   
});
$services["datas"] = $app["services"]->get('datas');
*/


/*---------------------------------------------------------------
 * init
 *--------------------------------------------------------------- */

$myapp->run();
?>