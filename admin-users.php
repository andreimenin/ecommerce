<?php


use \Hcode\PageAdmin;
use \Hcode\Model\User;

//104 - CRUD DE USUÁRIOS

//129 - Mudança de senha de usuário na página administrativa
$app->get("/admin/users/:iduser/password",function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-password",[
		"user"=>$user->getValues(),
		"msgError"=>$user->getError(),
		"msgSuccess"=>$user->getSuccess()

	]);

});


$app->post("/admin/users/:iduser/password",function($iduser){

	User::verifyLogin();

	if(!isset($_POST['despassword']) || $_POST['despassword']===''){

		User::setError("Preencha a nova senha.");

		header("Location: /admin/users/$iduser/password");

		exit;

	}

	if(!isset($_POST['despassword-confirm']) || $_POST['despassword-confirm']===''){

		User::setError("Preencha a confirmação da senha.");

		header("Location: /admin/users/$iduser/password");

		exit;

	}

	if($_POST['despassword'] != $_POST['despassword-confirm']){

		User::setError("Confirme corretamente as senhas.");

		header("Location: /admin/users/$iduser/password");

		exit;
	}



	$user = new User();

	$user->get((int)$iduser);

	$user->setPassword(User::getPasswordHash($_POST['despassword']));

	User::setSuccess("Senha alterada com sucesso.");

	header("Location: /admin/users/$iduser/password");

	exit;


});



$app->get("/admin/users", function(){

	//verificando se o usuário é administrador para poder executar seus privilégios
	User::verifyLogin();


	//125 - Adicionado códigos de paginação
	$search = (isset($_GET['search'])) ? $_GET['search'] : '';

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if($search != ''){
		$pagination = User::getPageSearch($search, $page, 2);
	}
	else{
		//Trazendo as páginas sem filtro de busca (search)
		//define o número de usuários por página
		//$pagination = User::getPage($page);
	$pagination = User::getPage($page, 2);
	}

	$pages = [];

	for($x = 0; $x < $pagination['pages']; $x++){
		array_push($pages, ['href'=>'/admin/users?'.http_build_query([
			'page'=>$x+1,
			'search'=>$search
		]),
		'text'=>$x+1]);
	}





	$page = new PageAdmin();

	$page->setTpl("users", array("users"=>$pagination['data'], 
								 "search"=>$search,
								 "pages"=>$pages));

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