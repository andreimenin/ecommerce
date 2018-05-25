<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;


//rota para acessar o template de categorias

	$app->get("/admin/categories" ,function(){

		User::verifyLogin();

		$categories = Category::listAll();

		$page = new PageAdmin();		

		$page->setTpl("categories", ['categories'=>$categories]);

	});


	$app->get("/admin/categories/create" ,function(){

		User::verifyLogin();

		$page = new PageAdmin();		

		$page->setTpl("categories-create");

	});

	$app->post("/admin/categories/create" ,function(){

		User::verifyLogin();

		$category = new Category();

		$category->setData($_POST);

		$category->save();

		header('Location: /admin/categories');
		exit;

	});




	$app->get("/admin/categories/:idcategory/delete", function($idcategory){

		User::verifyLogin();

		$category = new Category();

		$category->get((int)$idcategory);

		$category->delete();


		header('Location: /admin/categories');
		exit;

	});


	$app->get("/admin/categories/:idcategory", function($idcategory){

		User::verifyLogin();

		$category = new Category();

		$category->get((int)$idcategory);
		
		$page = new PageAdmin();		

		$page->setTpl("categories-update", ['category'=>$category->getValues()]);
	});

	$app->post("/admin/categories/:idcategory", function($idcategory){

		User::verifyLogin();

		$category = new Category();

		$category->get((int)$idcategory);
		
		$category->setData($_POST);

		$category->save();

		header('Location: /admin/categories');
		exit;

	});

	

	$app->get("/admin/categories/:idcategory/products", function($idcategory){

		User::verifyLogin(); // verificação do login

		$category = new Category(); //instanciando a categoria

		$category->get((int) $idcategory); //carrega os dados da categoria

		$page = new PageAdmin(); //instancia o Page 	

		$page->setTpl("categories-products", ['category'=>$category->getValues(),'productsRelated'=>$category->getProducts(true),
		   'productsNotRelated'=>$category->getProducts(false)
	]);

}); //montagem do template


$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){

		User::verifyLogin(); // verificação do login

		$category = new Category(); //instanciando a categoria

		$category->get((int) $idcategory); //carrega os dados da categoria

		$product = new product();

		$product->get((int)$idproduct);

		$category->addProduct($product);

		header("Location /admin/categories/".$idcategory."/products");
		exit;

}); 


$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){

		User::verifyLogin(); // verificação do login

		$category = new Category(); //instanciando a categoria

		$category->get((int) $idcategory); //carrega os dados da categoria

		$product = new product();

		$product->get((int)$idproduct);

		$category->removeProduct($product);

		header("Location /admin/categories/".$idcategory."/products");
		exit;

}); 









?>