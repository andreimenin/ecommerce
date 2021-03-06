<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;


/////////////////116
class Address extends Model{

	const SESSION_ERROR = "AddressError";




	//consumindo dados do webservice dos correios usando curl
	public static function getCEP($nrcep){

		$nrcep = str_replace("-", "", $nrcep);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "http://viacep.com.br/ws/$nrcep/json/");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$data = json_decode(curl_exec($ch), true);

		curl_close($ch);

		return $data;

	}


	public function loadFromCEP($nrcep){

		$data = Address::getCEP($nrcep);

		if(isset($data['logradouro']) && $data['logradouro']){


			$this->setdesaddress($data['logradouro']);
			$this->setdescomplement($data['complemento']);
			$this->setdesdistrict($data['bairro']);
			$this->setdescity($data['localidade']);
			$this->setdesstate($data['uf']);
			$this->setdescountry('Brasil');
			$this->setdeszipcode($nrcep);


		}




	}


//////120

	public function save(){

		$sql = new Sql();

		$results = $sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :descomplement, :descity, :desstate, :descountry, :deszipcode, :desdistrict)",[
			':idaddress'=>$this->getidaddress(),
			':idperson'=>$this->getidperson(),
			':desaddress'=>utf8_decode($this->getdesaddress()),
			':descomplement'=>utf8_decode($this->getdescomplement()),
			':descity'=>utf8_decode($this->getdescity()),
			':desstate'=>utf8_decode($this->getdesstate()),
			':descountry'=>utf8_decode($this->getdescountry()),
			':deszipcode'=>$this->getdeszipcode(),
			':desdistrict'=>utf8_decode($this->getdesdistrict())
		]);


		if(count($results) > 0) {

			$this->setData($results[0]);


		}
	}




	//////120

	//método para disparar mensagem de erro
	public static function setMsgError($msg){

		$_SESSION[Address::SESSION_ERROR] = $msg;

	}

	//////120

	//método para atualizar mensagem de erro
	public static function getMsgError(){

		$msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";

		Address::clearMsgError();

		return $msg;


	}

	//////120

	//método para limpar msg de erro
	public static function clearMsgError(){
		$_SESSION[Address::SESSION_ERROR] = NULL;
	}











}//fim da classe 

?>