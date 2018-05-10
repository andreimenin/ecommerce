<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");	

});


//aula 102 - ROTA PARA ADMINISTRAÇÃO
$app->get('/admin/', function() {
    
    //invocando o método de verificação
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");	

});

$app->get('/admin/login', function(){

	$page = new PageAdmin([

		"header"=>false,

		"footer"=>false
		
	]);

	$page->setTpl("login");
});


$app->post('/admin/login', function(){

	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");

	exit;

});

$app->get('/admin/logout', function(){

	User::logout();

	header("Location: /admin/login");

	exit;

});







$app->run();



 ?>