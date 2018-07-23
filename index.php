<?php 

//Arquivo que inicia a sessão e chama os outros arquivos de ROTAS do Rain Tpl

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;


$app = new Slim();

$app->config('debug', true);


require_once("site.php");
require_once("functions.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");
require_once("admin-orders.php");



$app->run();



 ?>