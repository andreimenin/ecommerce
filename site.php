<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;


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

		//var_dump($cart->getValues());

		//exit;


		$page->setTpl("cart", [
			'cart'=>$cart->getValues(),
			'products'=>$cart->getProducts(),
			'error'=>Cart::getMsgError()
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


	

//////////////115

	//rota que vai receber a chamada do envio do formulário com CEP para calcular


	$app->post("/cart/freigth", function(){

		$cart = Cart::getFromSession();

		$cart->setFreight($_POST['zipcode']);

		header("Location: /cart");

		exit;

	});


////////116

	//rota que só poderá ser acessada se o usuário tiver feito o login com cadastro

	$app->get("/checkout", function(){

		User::verifyLogin(false);

		$cart = Cart::getFromSession();

		$address = new Address();

		$page = new Page();

		$page->setTpl("checkout",['cart'=>$cart->getValues(),'address'=>$address->getValues()
		]);

	});


	$app->get("/login", function(){		

		$page = new Page();

		$page->setTpl("login",[
						'error'=>User::getError()]);

	});



	$app->post("/login", function(){

		try{
			User::login($_POST['login'], $_POST['password']);
		}catch(Exception $e){
			User::setError($e->getMessage());
		}

		header("Location: /checkout");

		exit;

	});


	$app->get("/logout", function (){

		User::logout();

		header("Location: /login");

		exit;

	});




?>