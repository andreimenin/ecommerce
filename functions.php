<?php

use \Hcode\Model\User;
use \Hcode\Model\Cart;


function formatPrice($vlprice){
		//valor, casas decimais, separador de casa decimal e de casa milhar

	if(!$vlprice > 0){$vlprice = 0;}


	return number_format($vlprice, 2 , "," , ".");

}


/////////////////116
function checkLogin($inadmin = true){

	return User::checkLogin($inadmin);
}


/////////////////116
function getUserName(){

	$user = User::getFromSession();

	return $user->getdesperson();

}

///////////122
//função para mostrar quantos produtos tem e o valor do carrinho

function getCartNrQtd(){

	$cart = Cart::getFromSession();

	$totals = $cart->getProductsTotals();

	return $totals['nrqtd'];
}

function getCartVlSubtotal(){

	$cart = Cart::getFromSession();

	$totals = $cart->getProductsTotals();

	return formatPrice($totals['vlprice']);
}


////////124

function formatDate($date){

	return date('d/m/Y', strtotime($date));


}










?>