<?php

/*---------------------------------------------------------------
 * autoload
 *--------------------------------------------------------------- */
define('in_app', '...for security dude!');
require_once("../app/vendor/autoload.php");//load libs
require_once("../app/project/datas/datas.php");//load libs
require_once("../app/core/cache.php");//load libs


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