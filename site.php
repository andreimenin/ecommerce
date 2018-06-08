<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;


$app->get('/', function() {
    
	$products = Product::listAll();//linha adicionada aula 109

	$page = new Page();

	//linha alterada aula 109
	$page->setTpl("index", ['products'=>Product::checkList($products)
]);

});

////////////////////////////107
	$app->get("/categories/:idcategory", function($idcategory){		

		//111
		$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

		$category = new Category();

		$category->get((int) $idcategory);

		//111
		$pagination = $category->getProductsPage($page);

		//111
		$pagination = $category->getProductsPage();

		//111
		$pages = [];

		///111
		for ($i=1; $i < $pagination['pages'] ; $i++) { 
			array_push($pages, ['link'=>'/categories/'.$category->getidcategory().'?page='.$i,
				'page'=>$i]);
		}


		$page = new Page();	

		$page->setTpl("category", ['category'=>$category->getValues(),
								   'products'=>$pagination["data"],
								   'pages'=>$pages
					]);// adicionada a paginação e a rows, sendo chamada do método getProductsPage

	});


///////112
	$app->get("/products/:desurl", function($desurl){

		$product = new Product();

		$product->getFromURL($desurl);

		$page = new Page();

		$page->setTpl("product-detail", ['product'=>$product->getValues(),
										 'categories'=>$product->getCategories()
										]);
								});

/////113

	$app->get("/cart", function(){

		$cart = Cart::getFromSession();

		$page = new Page();

		$page->setTpl("cart", [
			'cart'=>$cart->getValues(),
			'products'=>$cart->getProducts()
		]);

	});

/////114

	//rota para adicionar um produto
	$app->get("/cart/:idproduct/add", function($idproduct){

		$product = new Product();

		$product->get((int)$idproduct);//identifica o produto

		$cart = Cart::getFromSession();//chama método que carrega o carrinho de uma sessão ou cria um novo carrinho

		$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

		for($i = 0; $i < $qtd; $i++){
			$cart->addProduct($product);//adiciona 1 produto ao carrinho
		}

		header("Location: /cart");

		exit;

	});


	//rota para remover uma (1) quantidade de produto
	$app->get("/cart/:idproduct/minus", function($idproduct){

		$product = new Product();

		$product->get((int)$idproduct);//identifica o produto

		$cart = Cart::getFromSession();//chama método que carrega o carrinho de uma sessão ou cria um novo carrinho

		$cart->removeProduct($product);//remove 1 produto do carrinho

		header("Location: /cart");

		exit;

	});

	//rota para remover um produto (limpar do carrinho)
	$app->get("/cart/:idproduct/remove", function($idproduct){

		$product = new Product();

		$product->get((int)$idproduct);//identifica o produto

		$cart = Cart::getFromSession();//chama método que carrega o carrinho de uma sessão ou cria um novo carrinho

		$cart->removeProduct($product, true);//remove todos os produtos usando o parâmetro TRUE

		header("Location: /cart");

		exit;

	});


	












?>