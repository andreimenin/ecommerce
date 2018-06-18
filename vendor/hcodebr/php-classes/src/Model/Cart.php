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
	const SESSION_ERROR = "CartError";

	//pegando dados da sessão
	public static function getFromSession(){

		$cart = new Cart();

		if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0){

			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);

		}
		else{

			$cart->getFromSessionID();

			//vefificar se o getFromSessionID não conseguiu carregar um carrinho
			if(!(int)$cart->getidcart() > 0){

				$data = [
					'dessessionid'=>session_id()
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

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
			':idcart'=>$idcart
		]);


		if(count($results) > 0){
			$this->setData($results[0]);
		}
	


	}

	//carregando um carrinho a partir de uma sessão
	public function getFromSessionID(){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid",[
			':dessessionid'=>session_id()
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
				':dessessionid'=>$this->getdessessionid(),
				':iduser'=>$this->getiduser(),
				':deszipcode'=>$this->getdeszipcode(),
				':vlfreight'=>$this->getvlfreight(),
				':nrdays'=>$this->getnrdays()				
			]);

		if(count($results) > 0){

			$this->setData($results[0]);

		}



}

///////114
//adicionar o produto
	
public function addProduct(Product $product){


	$sql = new Sql();

	$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)",[
		':idcart'=>$this->getidcart(),
		':idproduct'=>$product->getidproduct()
	]);

	$this->getCalculateTotal();//recalcular frete ao adicionar produto
}


public function removeProduct(Product $product, $all = false){

	//remover TODOS os produtos (quantidade)



	$sql = new Sql();

	if($all){
		$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL",[

			':idcart'=>$this->getidcart(),
			':idproduct'=>$product->getidproduct()
		]);
	}
	else{
		$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1",[

			':idcart'=>$this->getidcart(),
			':idproduct'=>$product->getidproduct()
		]);
	}

	$this->getCalculateTotal();//recalcular frete ao adicionar produto

}

	//identificando todos os produtos que estão no carrinho
	public function getProducts(){

		$sql = new Sql();

		$rows = $sql->select("
			SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
			FROM tb_cartsproducts a 
			INNER JOIN tb_products b ON a.idproduct = b.idproduct 
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
			GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
			ORDER BY b.desproduct",[
				':idcart'=>$this->getidcart()
			]);

		return Product::checkList($rows);

	}






//115

	//Método para somar os valores dos produtos 
	public function getProductsTotals(){

		$sql = new Sql();

		$results = $sql->select("
			SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
			FROM tb_products a 
			INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct 
			WHERE b.idcart = :idcart AND dtremoved IS NULL;",[
				':idcart'=>$this->getidcart()
			]);


		if(count($results) > 0){

			return $results[0];


		}else{
			return [];
		}




	}



//////115 

//Método que calcula o valor do frete
public function setFreight($nrzipcode){

		$zipcode = str_replace('-','', $nrzipcode);

		//var_dump($zipcode);

		$totals = $this->getProductsTotals();

		if($totals['nrqtd'] > 0){

			if($totals['vlheight'] < 2) $totals['vlheight'] = 2;
			if($totals['vllength'] < 16) $totals['vllength'] = 16;

			$qs = http_build_query([
					'nCdEmpresa'=>'',
					'sDsSenha'=>'',
					'nCdServico'=>'40010',
					'sCepOrigem'=>'09853120',
					'sCepDestino'=>$zipcode,
					'nVlPeso'=>$totals['vlweight'],
					'nCdFormato'=>'1',
					'nVlComprimento'=>$totals['vllength'],
					'nVlAltura'=>$totals['vlheight'],
					'nVlLargura'=>$totals['vlwidth'],
					'nVlDiametro'=>'0',
					'sCdMaoPropria'=>'S',
					'nVlValorDeclarado'=>$totals['vlprice'],
					'sCdAvisoRecebimento'=>'S'
				]);

	///////////consumindo webservice dos correios
			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);

			$result = $xml->Servicos->cServico;

			if($result->MsgErro != ''){
				Cart::setMsgError($result->MsgErro);
			}
			else{
				Cart::clearMsgError();
			}


			$this->setnrdays($result->PrazoEntrega);

			$this->setvlfreight(Cart::formatValueToDecimal($result->Valor));

			$this->setdeszipcode($zipcode);

			$this->save();

			return $result;

			/*
			echo json_encode($xml);

			exit; */

		}
		else{




		}

	}

	//método que formata valores 
	public static function formatValueToDecimal($value):float{

		$value = str_replace('.', '', $value);

		return str_replace(',', '.', $value);

	}

	//////115 

	//método para disparar mensagem de erro
	public static function setMsgError($msg){

		$_SESSION[Cart::SESSION_ERROR] = $msg;

	}

	//////115 

	//método para atualizar mensagem de erro
	public static function getMsgError(){

		$msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";

		Cart::clearMsgError();

		return $msg;


	}

	//////115 

	//método para limpar msg de erro
	public static function clearMsgError(){
		$_SESSION[Cart::SESSION_ERROR] = NULL;
	}

	//////115 

	//método para atualizar o valor do frete de acordo com os produtos que estão sendo adicionados no carrinho
	public function updateFreight(){

		if($this->getdeszipcode() != ''){
			$this->setFreight($this->getdeszipcode());
		}

	}


	//////115 

	//incluindo os valores no objeto para mostrar no template (sessão)
	public function getValues(){

		$this->getCalculateTotal();

		return parent::getValues();


	}

//////115 

	//informações e valores totais do carrinho
	public function getCalculateTotal(){

		$this->updateFreight();

		$totals = $this->getProductsTotals();

		$this->setvlsubtotal($totals['vlprice']);
		$this->setvltotal($totals['vlprice'] + (float)$this->getvlfreight());
	}








}//fim da classe 

?>