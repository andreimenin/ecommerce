<?php
	
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;

///////////////////124

$app->get("/admin/orders/:idorder/status", function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$page = new PageAdmin();

	$page->setTpl("order-status",[
			'order'=>$order->getValues(),
			'status'=>OrderStatus::listAll(),
			'msgError'=>Order::getError(),
			'msgSuccess'=>Order::getSuccess()

		]);

});

//alterar status do pedido
$app->post("/admin/orders/:idorder/status", function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	//se o parâmetro idstatus não foi informado...
	if(!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0){

		Order::setError("Informe o status atual.");

		header("Location: /admin/orders/" . $idorder . "/status");

		exit;
	}

	$order->setidstatus((int)$_POST['idstatus']);

	$order->save();

	Order::setSuccess("Status atualizado.");

	header("Location: /admin/orders/" . $idorder . "/status");	

	exit;	

});



//excluir pedido
$app->get("/admin/orders/:idorder/delete", function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$order->delete();

	header("Location: /admin/orders");

	exit;

});


//exibindo dados do pedido
$app->get("/admin/orders/:idorder", function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	

	//pegando o carrinho deste pedido
	$cart = $order->getCart();

	$page = new PageAdmin();

	$page->setTpl("order",[
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()

	]);


});





//listando os pedidos
$app->get("/admin/orders",function(){

	User::verifyLogin();



	//126 - Adicionado códigos de paginação
		$search = (isset($_GET['search'])) ? $_GET['search'] : '';

		$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

		if($search != ''){
			$pagination = Order::getPageSearch($search, $page, 10);
		}
		else{
			//Trazendo as páginas sem filtro de busca (search)
			//define o número de usuários por página
			//$pagination = Order::getPage($page);
		$pagination = Order::getPage($page, 10);
		}

		$pages = [];

		for($x = 0; $x < $pagination['pages']; $x++){
			array_push($pages, ['href'=>'/admin/orders?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1]);
		}/////




	$page = new PageAdmin();

	$page->setTpl("orders", [
		"orders"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages

	]);

});






?>

