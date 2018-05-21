<?php

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

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






///105 FORGET PASSWORD

	$app->get("/admin/forgot", function(){

		$page = new PageAdmin([

		"header"=>false,

		"footer"=>false
		]);

		$page->setTpl("forgot");


	});

	
	$app->post("/admin/forgot",function(){
		
		$user =	User::getForgot($_POST["email"]);

		header("Location: /admin/forgot/sent");

		exit;

	});


	$app->get("/admin/forgot/sent", function(){

		$page = new PageAdmin([

		"header"=>false,

		"footer"=>false
		]);

		$page->setTpl("forgot-sent");


	});



	$app->get("/admin/forgot/reset", function(){

		$user = User::validForgotDecrypt($_GET["code"]);


		$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
		]);
	

		$page->setTpl("forgot-reset", array(
			"name"=>$user["desperson"],
			"code"=>$_GET["code"]
		));

		});


	$app->post("/admin/forgot/reset", function(){
		$forgot = User::validForgotDecrypt($_POST["code"]);

		User::setForgotUsed($forgot["idrecovery"]);

		$user = new User();

		$user->get((int)$forgot["iduser"]);

		$password = User::getPasswordHash($_POST["password"]);


		$user->setPassword($password);


		$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
		]);

		

		$page->setTpl("forgot-reset-success");



	});



?>