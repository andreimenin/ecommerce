<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;


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

		$address = new Address();

		$cart = Cart::getFromSession();

		if (!isset($_GET['zipcode'])){
			$_GET['zipcode'] = $cart->getdeszipcode();
		}


		if (isset($_GET['zipcode'])){

			$address->loadFromCEP($_GET['zipcode']);

			$cart->setdeszipcode($_GET['zipcode']);

			$cart->save();

			$cart->getCalculateTotal();
		}

		//definindo as chaves do address
		if (!$address->getdesaddress()) $address->setdesaddress('');
		if (!$address->getdescomplement()) $address->setdescomplement('');
		if (!$address->getdesdistrict()) $address->setdesdistrict('');
		if (!$address->getdescity()) $address->setdescity('');
		if (!$address->getdesstate()) $address->setdesstate('');
		if (!$address->getdescountry()) $address->setdescountry('');
		if (!$address->getdeszipcode()) $address->setdeszipcode('');

		

		$page = new Page();

		$page->setTpl("checkout",['cart'=>$cart->getValues(),
								  'address'=>$address->getValues(),
								  'products'=>$cart->getProducts(),
								  'error'=>Address::getMsgError()
		]);

	});


///////////120

	$app->post("/checkout", function(){

		User::verifyLogin(false);

		if(!isset($_POST['zipcode']) || $_POST['zipcode'] === ''){
			Address::setMsgError("Informe o CEP.");
			header('Location: /checkout');
			exit;
		}
		if(!isset($_POST['desaddress']) || $_POST['desaddress'] === ''){
			Address::setMsgError("Informe o endereço.");
			header('Location: /checkout');
			exit;
		}
		if(!isset($_POST['desdistrict']) || $_POST['desdistrict'] === ''){
			Address::setMsgError("Informe o bairro.");
			header('Location: /checkout');
			exit;
		}
		if(!isset($_POST['descity']) || $_POST['descity'] === ''){
			Address::setMsgError("Informe a cidade.");
			header('Location: /checkout');
			exit;
		}
		if(!isset($_POST['desstate']) || $_POST['desstate'] === ''){
			Address::setMsgError("Informe o Estado.");
			header('Location: /checkout');
			exit;
		}
		if(!isset($_POST['descountry']) || $_POST['descountry'] === ''){
			Address::setMsgError("Informe o país.");
			header('Location: /checkout');
			exit;
		}

		$user = User::getFromSession();

		$address = new Address();

		$_POST['deszipcode'] = $_POST['zipcode'];
		$_POST['idperson'] = $user->getidperson();

		$address->setData($_POST);

		$address->save();

		$cart = Cart::getFromSession();

		//121
		//criando a order

		//pegando o valor total do carrinho
		$totals = $cart->getCalculateTotal();

		$order = new Order();

		$order->setData([
			'idcart'=>$cart->getidcart(),
			'idaddress'=>$address->getidaddress(),
			'iduser'=>$user->getiduser(),
			'idstatus'=>OrderStatus::EM_ABERTO,
			'vltotal'=>$totals['vlprice'] + $cart->getvlfreight()
		]);


		$order->save();




		header("Location: /order/".$order->getidorder());
		exit;





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




/////////////////120


	$app->post("/checkoutupdatecep", function(){

		$cart = Cart::getFromSession();

		if(!isset($_POST['zipcode']) || $_POST['zipcode'] === ''){
			Address::setMsgError("Informe o CEP.");
			header('Location: /checkout');
			exit;
		}

		$cart->setFreight($_POST['zipcode']);

		header('Location: /checkout');
		
		exit;

	});




/////////////////121

	$app->get("/order/:idorder", function($idorder){

		User::verifyLogin(false);

		$order = new Order();

		$order->get((int)$idorder);

		$page = new Page();

		$page->setTpl("payment", [
			'order'=>$order->getValues()
		]);

	});


	$app->get("/boleto/:idorder", function($idorder){

		User::verifyLogin(false);

		$order = new Order();

		$order->get((int)$idorder);






	//VARIÁVEIS DE CONFIGURAÇÃO DO BOLETO
		// DADOS DO BOLETO PARA O SEU CLIENTE
	$dias_de_prazo_para_pagamento = 10;
	$taxa_boleto = 5.00;
	$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
	$valor_cobrado = formatPrice($order->getvltotal()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
	$valor_cobrado = str_replace(",", ".",$valor_cobrado);
	$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

	$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
	$dadosboleto["numero_documento"] = '0123';	// Num do pedido ou nosso numero
	$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
	$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
	$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
	$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

	// DADOS DO SEU CLIENTE
	$dadosboleto["sacado"] = $order->getdesperson();
	$dadosboleto["endereco1"] = $order->getdesaddress() . " " . $order->getdesdistrict() . " " . $order->getdescountry() ;
	$dadosboleto["endereco2"] = $order->getdescity() . " - " . $order->getdesstate() . " - CEP: " . $order->getdeszipcode();

	// INFORMACOES PARA O CLIENTE
	$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
	$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
	$dadosboleto["demonstrativo3"] = "";
	$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
	$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
	$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
	$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

	// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
	$dadosboleto["quantidade"] = "";
	$dadosboleto["valor_unitario"] = "";
	$dadosboleto["aceite"] = "";		
	$dadosboleto["especie"] = "R$";
	$dadosboleto["especie_doc"] = "";


	// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


	// DADOS DA SUA CONTA - ITAÚ
	$dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
	$dadosboleto["conta"] = "48781";	// Num da conta, sem digito
	$dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

	// DADOS PERSONALIZADOS - ITAÚ
	$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

	// SEUS DADOS
	$dadosboleto["identificacao"] = "Hcode Treinamentos";
	$dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
	$dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
	$dadosboleto["cidade_uf"] = "São Bernardo do Campo - SP";
	$dadosboleto["cedente"] = "HCODE TREINAMENTOS LTDA - ME";

	// NÃO ALTERAR!
	$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "boletophp" . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR;

	require_once($path . "funcoes_itau.php"); 
	require_once($path . "layout_itau.php"); 
	






	});



		



?>