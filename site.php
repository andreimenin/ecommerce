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


/////////////////116

	//rota que só poderá ser acessada se o usuário tiver feito o login com cadastro

	$app->get("/checkout", function(){

		User::verifyLogin(false);

		$cart = Cart::getFromSession();

		$address = new Address();

		$page = new Page();

		$page->setTpl("checkout",['cart'=>$cart->getValues(),'address'=>$address->getValues()
		]);

	});

	/////////////////116
	$app->get("/login", function(){		

		$page = new Page();

		$page->setTpl("login",[
						'error'=>User::getError(),
						'errorRegister'=>User::getErrorRegister(),
						'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']
					]);

	});


	/////////////////116
	$app->post("/login", function(){

		try{
			User::login($_POST['login'], $_POST['password']);
		}catch(Exception $e){
			User::setError($e->getMessage());
		}

		header("Location: /checkout");

		exit;

	});

	/////////////////116
	$app->get("/logout", function (){

		User::logout();

		header("Location: /login");

		exit;

	});



///////////117
	$app->post("/register", function(){


		$_SESSION['registerValues'] = $_POST;



		//camada adicional de validação, pois já existe uma camada no front-end
		if(!isset($_POST['name']) || $_POST['name'] ==''){

			User::setErrorRegister("Preencha o seu nome.");

			header("Location: /login");

			exit;
		}

		if(!isset($_POST['email']) || $_POST['email'] ==''){

			User::setErrorRegister("Preencha o seu e-mail.");

			header("Location: /login");

			exit;
		}

		if(!isset($_POST['password']) || $_POST['password'] ==''){

			User::setErrorRegister("Preencha a senha.");

			header("Location: /login");

			exit;
		}

		//impedindo que o usuário preencha o login identico à um correspondente do banco de dados
		if(User::checkLoginExists($_POST['email']) === true){

			User::setErrorRegister("Este endereço de e-mail já está sendo utilizado.");

			header("Location: /login");

			exit;

		}

		$user = new User();

		$user->setData(['inadmin'=>0,
						'deslogin'=>$_POST['email'],
						'desperson'=>$_POST['name'],
						'desemail'=>$_POST['email'],
						'despassword'=>$_POST['password'],
						'nrphone'=>$_POST['phone']]);


		$user->save();

		User::login($_POST['email'], $_POST['password']);//autenticação do usuário 

		header('Location: /checkout');

		exit;

	});



	/////////118

	$app->get("/forgot", function(){

		$page = new Page();

		$page->setTpl("forgot");


	});

	/////////118
	$app->post("/forgot",function(){
		
		$user =	User::getForgot($_POST["email"], false);

		header("Location: /forgot/sent");

		exit;

	});

	/////////118
	$app->get("/forgot/sent", function(){

		$page = new Page();

		$page->setTpl("forgot-sent");


	});


	/////////118
	$app->get("/forgot/reset", function(){

		$user = User::validForgotDecrypt($_GET["code"]);


		$page = new Page();
	

		$page->setTpl("forgot-reset", array(
			"name"=>utf8_encode($user["desperson"]),
			"code"=>$_GET["code"]
		));


		});

	/////////118
	$app->post("/forgot/reset", function(){
		$forgot = User::validForgotDecrypt($_POST["code"]);

		User::setForgotUsed($forgot["idrecovery"]);

		$user = new User();

		$user->get((int)$forgot["iduser"]);

		$password = User::getPasswordHash($_POST["password"]);

		$user->setPassword($password);

		$page = new Page();

		$page->setTpl("forgot-reset-success");

	});



/////////////119


	$app->get("/profile", function(){

		User::verifyLogin(false);

		$user = User::getFromSession();

		$page = new Page();

		$page->setTpl("profile", [
			'user'=>$user->getValues(),
			'profileMsg'=>User::getSuccess(),
			'profileError'=>User::getError()

		]);


	});


	$app->post("/profile", function(){

		User::verifyLogin(false);

		if(!isset($_POST['desperson']) || $_POST['desperson'] === ''){
			User::setError("Preencha o seu nome.");
			header('Location: /profile');
			exit;
		}
		if(!isset($_POST['desemail']) || $_POST['desemail'] === ''){
			User::setError("Preencha o seu e-mail.");
			header('Location: /profile');
			exit;
		}

		$user = User::getFromSession();

		if($_POST['desemail'] !== $user->getdesemail()) {

			if(User::checkLoginExists($_POST['desemail']) === true){

				User::setError("Este endereço de e-mail já está cadastrado.");
				header('Location: /profile');
				exit;

			}
		}


		$_POST['iduser'] = $user->getiduser();
		$_POST['inadmin'] = $user->getinadmin();
		$_POST['despassword'] = $user->getdespassword();
		$_POST['deslogin'] = $_POST['desemail'];

		$user->setData($_POST);

		$user->update();

		$_SESSION[User::SESSION] = $user->getValues(); 

		User::setSuccess("Dados alterados com sucesso !");

		header('Location: /profile');
		exit;









	});
















		



?>