<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;


$app->get('/', function() {
    
	$products = Product::listAll();//linha adicionada aula 109

	$page = new Page();

	//linha alterada aula 109
	$page->setTpl("index", ['products'=>Product::checkList($products)
]);

});

////////////////////////////107
	$app->get("/categories/:idcategory", function($idcategory){		

		$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

		$category = new Category();

		$category->get((int) $idcategory);

		$pagination = $category->getProductsPage($page);

		$pagination = $category->getProductsPage();

		$pages = [];

		for ($i=1; $i < $pagination['pages'] ; $i++) { 
			array_push($pages, ['link'=>'/categories/'.$category->getidcategory().'?page='.$i,
				'page'=>$i]);
		}




		$page = new Page();	

		$page->setTpl("category", ['category'=>$category->getValues(),
								   'products'=>$pagination["data"],
								   'pages'=>$pages
					]);

	});


?>