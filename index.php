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


//aula 102 - ROTA PARA A PÁGINA PRINCIPAL DA ADMINISTRAÇÃO E SUAS CONFIGURAÇÕES
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


//aula 103 - VALIDAÇÃO DO TIPO DE USUÁRIO E LOGIN 

$app->post('/admin/login', function(){

	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");

	exit;

});

//aula 103 - LOGOUT
$app->get('/admin/logout', function(){

	User::logout();

	header("Location: /admin/login");

	exit;

});


//104 - CRUD DE USUÁRIOS

$app->get("/admin/users", function(){

	//verificando se o usuário é administrador para poder executar seus privilégios
	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl("users", array("users"=>$users));

});



$app->get("/admin/users/create", function(){

	//verificando se o usuário é administrador para poder executar seus privilégios
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");

});


//IMPORTANTE DEIXAR ESTE MÉTODO PRIMEIRO, POIS ESTA ROTA TEM PRIORIDADE PELO /delete
$app->get("/admin/users/:iduser/delete", function($iduser){

	//verificando se o usuário é administrador para poder executar seus privilégios
	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");

	exit;

});


$app->get("/admin/users/:iduser", function($iduser){

	//verificando se o usuário é administrador para poder executar seus privilégios
	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array("user"=>$user->getValues()));

});

$app->post("/admin/users/create", function(){

	//verificando se o usuário é administrador para poder executar seus privilégios
	User::verifyLogin();
	
	//passando os dados para o objeto $user
	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->setData($_POST);
	$user->save();

	header("Location: /admin/users");



});



$app->post("/admin/users/:iduser", function($iduser){

	//verificando se o usuário é administrador para poder executar seus privilégios
	User::verifyLogin();	

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");

	exit;

});







$app->run();



 ?>