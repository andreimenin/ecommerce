<?php


use \Hcode\PageAdmin;
use \Hcode\Model\User;

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











?>