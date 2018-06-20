<?php

use \Hcode\Model\User;



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








?>