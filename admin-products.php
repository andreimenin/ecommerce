<?php

use Hcode\PageAdmin;
use Hcode\Model\User;
use Hcode\Model\Product;

//LISTAGEM DE PRODUTOS
$app->get("/admin/products", function(){

	User::verifyLogin();

	//$products = Product::listAll();


	//127 - Adicionado códigos de paginação
		$search = (isset($_GET['search'])) ? $_GET['search'] : '';

		$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

		if($search != ''){
			$pagination = Product::getPageSearch($search, $page, 2);
		}
		else{
			//Trazendo as páginas sem filtro de busca (search)
			//define o número de produtos por página
			//$pagination = Category::getPage($page);
		$pagination = Product::getPage($page, 2);
		}

		$pages = [];

		for($x = 0; $x < $pagination['pages']; $x++){
			array_push($pages, ['href'=>'/admin/products?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1]);
		}/////







	$page = new PageAdmin();

	$page->setTpl("products", ["products"=>$pagination['data'],
							   "search"=>$search,
							   "pages"=>$pages]);

});



//NOVO PRODUTO
$app->get("/admin/products/create", function(){

	User::verifyLogin();	

	$page = new PageAdmin();

	$page->setTpl("products-create");

});

$app->post("/admin/products/create", function(){

	User::verifyLogin();	

	$product = new Product();

	$product->setData($_POST);

	$product->save();

	header("Location: /admin/products");

	exit;

});

//UPDATE PRODUTO
$app->get("/admin/products/:idproduct", function($idproduct){

	User::verifyLogin();	

	$product = new Product();

	$product->get((int)$idproduct);

	$page = new PageAdmin();

	$page->setTpl("products-update", ['product'=>$product->getValues()]);

});


$app->post("/admin/products/:idproduct", function($idproduct){

	User::verifyLogin();	

	$product = new Product();

	$product->get((int)$idproduct);

	$product->setData($_POST);

	$product->save();

	$product->setPhoto($_FILES["file"]);

	header('Location: /admin/products');

	exit;

});

//DELETE
$app->get("/admin/products/:idproduct/delete", function($idproduct){

	User::verifyLogin();	

	$product = new Product();

	$product->get((int)$idproduct);

	$product->delete();
	
	header('Location: /admin/products');

	exit;

});






?>