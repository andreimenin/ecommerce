<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

///106
//Model de usuários
class Cart extends Model{

	const SESSION = "Cart";

	//pegando dados da sessão
	public static function getFromSession(){

		$cart = new Cart();

		if(isset($_SESSION[Cart::SESSION]) && $_SESSION[Cart::SESSION]['idcart'] > 0){

			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);

		}
		else{

			$cart->getFromSessionID();

			//vefificar se o getFromSessionID não conseguiu carregar um carrinho
			if(!(int)$cart->getidcart() > 0){

				$data = [
					'dessesionid'=>session_id()
				];

				if(User::checkLogin(false)){

					$user = User::getFromSession();

					$data['iduser'] = $user->getiduser();

				}

				$cart->setData($data);

				$cart->save();

				$cart->setToSession();



			}

		}

		return $cart;


	}


	public function setToSession(){

		$_SESSION[Cart::SESSION] = $this->getValues();


	}



	public function get(int $idcart){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart",[
			':idcart'=>$idcart
		]);


		if(count($results) > 0){
			$this->setData($results[0]);
		}
	


	}

	//carregando um carrinho a partir de uma sessão
	public function getFromSessionID(){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessesionid = :dessesionid",[
			':dessesionid'=>session_id()
		]);

		if(count($results) > 0){

			$this->setData($results[0]);
			
		}
	}


	
	public function save(){

		$sql = new Sql();

		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)",
			[
				':idcart'=>$this->getidcart(),
				':dessesionid'=>$this->getdessesionid(),
				':iduser'=>$this->getiduser(),
				':deszipcode'=>$this->getdeszipcode(),
				':vlfreight'=>$this->getvlfreight(),
				':nrdays'=>$this->getnrdays()				
			]);

		if(count($results) > 0){

			$this->setData($results[0]);

		}

		




	}



	







}//fim da classe 

?>