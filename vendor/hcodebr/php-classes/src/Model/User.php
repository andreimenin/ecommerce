<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;


//Model de usuários
class User extends Model{

	const SESSION = "User";

	//chave para criptografar e para descriptografar
	const SECRET = "HcodePhp7_Secret";


	//CONFERINDO NO BD SE O LOGIN E SENHA ESTÃO CORRETOS
	public static function login($login, $password){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(":LOGIN"=>$login));

		if(count($results) === 0){
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}

		$data = $results[0];

		if(password_verify($password, $data["despassword"]) === true){
			$user = new User();

			$user->setData($data);

			//var_dump($user);
			//criando e inserindo valores na sessão
			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

			exit;



		}else{
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}


	}

	//verificando qual o tipo de usuário logado para redirecioná-lo corretamente caso não atenda os requisitos do login
	public static function verifyLogin($inadmin = true){

		//validando elementos da SESSION
		if(!isset($_SESSION[User::SESSION])||!$_SESSION[User::SESSION]||!(int)$_SESSION[User::SESSION]["iduser"] > 0 ||(bool)$_SESSION[User::SESSION]["inadmin"] !==$inadmin){

			header("Location: /admin/login");
			exit;
		}

	}

	public static function logout(){

		$_SESSION[User::SESSION] = NULL;

	}


	///104
	//MÉTODO READ
	public static function listAll(){
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");


	}
	///104
	//MÉTODO CREATE
	public function save(){
		$sql = new Sql();

		

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",

			array(
				":desperson"=>$this->getdesperson(),
				":deslogin"=>$this->getdeslogin(),
				":despassword"=>$this->getdespassword(),
				":desemail"=>$this->getdesemail(),
				":nrphone"=>$this->getnrphone(),
				":inadmin"=>$this->getinadmin()
				
			));

			$this->setData($results[0]);

	
	}


	///104
	//MÉTODO GET (BUSCAR POR CÓDIGO)
	public function get($iduser){


		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING (idperson) WHERE a.iduser = :iduser", array(":iduser"=>$iduser));


		$this->setData($results[0]);


	}

	///104
	//MÉTODO UPDATE
	public function update(){
		$sql = new Sql();

		

		$results = $sql->select("CALL  sp_users_update_save( :iduser,:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",

			array(
				":iduser"=>$this->getiduser(),
				":desperson"=>$this->getdesperson(),
				":deslogin"=>$this->getdeslogin(),
				":despassword"=>$this->getdespassword(),
				":desemail"=>$this->getdesemail(),
				":nrphone"=>$this->getnrphone(),
				":inadmin"=>$this->getinadmin()
				
			));

			$this->setData($results[0]);
	}

	///104
	//MÉTODO DELETE
	public function delete(){
		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(":iduser"=>$this->getiduser()
	));

	}


	//105
	public static function getForgot($email, $inadmin = true){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_persons a
			INNER JOIN tb_users b USING(idperson)
			WHERE a.desemail = :EMAIL;", array(":EMAIL"=>$email));

		if(count($results) === 0){
			throw new \Exception("Não foi possível recuperar a senha. ");
		}
		else{

			$data = $results[0];

			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(":iduser"=>$data["iduser"],
			":desip"=>$_SERVER["REMOTE_ADDR"]));


			if(count($results2) === 0){
				throw new \Exception("Não foi possível recuperar a senha. ");
			}else{
				$dataRecovery = $results2[0];
				//CRIPTOGRAFIA

				$iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));

				$code = openssl_encrypt($dataRecovery['idrecovery'], 'aes-256-cbc', User::SECRET, 0, $iv);

				$result = base64_encode($iv.$code);

				if ($inadmin === true) {
                 $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$result";
	             } else {
	                 $link = "http://www.hcodecommerce.com.br/forgot/reset?code=$result";
	             } 


				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir senha da Hcode Store", "forgot", array(

					"name"=>$data["desperson"],
					"link"=>$link

				));

				$mailer->send();

				return $link;

			}

		}

	}

public static function validForgotDecrypt($result){

	$result = base64_decode($result);

	$code = mb_substr($result, openssl_cipher_iv_length('aes-256-cbc'), null, '8bit');

	$iv = mb_substr($result, 0, openssl_cipher_iv_length('aes-256-cbc'), '8bit');

	$idrecovery = openssl_decrypt($code, 'aes-256-cbc', User::SECRET, 0, $iv);

	$sql = new Sql();

	$results = $sql->select("SELECT * FROM tb_userspasswordsrecoveries a
					INNER JOIN tb_users b USING(iduser)
					INNER JOIN tb_persons c USING(idperson)
					WHERE 
						a.idrecovery = :idrecovery
					    AND
					    a.dtrecovery IS NULL
					    AND
					    DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();

;",array(

	":idrecovery"=>$idrecovery

));


	if(count($results) === 0){
		throw new \Exception("Não foi possível recuperar a senha.");
	}
	else{
		return $results[0];
	}
}



public static function setForgotUsed($idrecovery){

	//carregar o banco de dados para alterar a senha

	$sql = new Sql();

	$sql->query("UPDATE tb_userpasswordrecoveries SET dtrecovery = NOW() WHERE idrecovery = :IDRECOVERY", array(":IDRECOVERY"=>$idrecovery));


}



public function setPassword($password){

	$sql = new Sql();

	$sql->query("UPDATE tb_users SET despassword = :PASSWORD WHERE iduser = :IDUSER", array(
		":PASSWORD"=>$password,
		":IDUSER"=>$this->getiduser()

	));

}


	public static function getPasswordHash($password)
		{
			return password_hash($password, PASSWORD_DEFAULT, [
				'cost'=>12
			]);
		}




}//fim da classe 

?>