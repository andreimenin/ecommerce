<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;


//Model de usuários
class User extends Model{

	const SESSION = "User";	
	const SECRET = "HcodePhp7_Secret"; //chave para criptografar e para descriptografar
	const ERROR = "UserError";
	const SUCCESS = "UserSuccess";
	const ERROR_REGISTER = "UserErrorRegister";


	///113
	public static function getFromSession(){

		$user = new User();

		if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){			

			$user->setData($_SESSION[User::SESSION]);
		}

			return $user;
	}

	/////113
	public static function checkLogin($inadmin = true){

		//
		if(!isset($_SESSION[User::SESSION])||
			!$_SESSION[User::SESSION]||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 ){

			//NÃO ESTÁ LOGADO
			return false;
		}else{

			if($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true){

				return true;

			}else if($inadmin === false){

				return true;
			}
			else{
				return false;
			}

		}

	}





	//CONFERINDO NO BD SE O LOGIN E SENHA ESTÃO CORRETOS
	public static function login($login, $password){

		$sql = new Sql();

		//$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(":LOGIN"=>$login));

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :LOGIN", array(
    	":LOGIN"=>$login
    	)); 

		if(count($results) === 0){
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}

		$data = $results[0];

		if(password_verify($password, $data["despassword"]) === true){
			$user = new User();

			$data['desperson'] = utf8_encode($data['desperson']);

			$user->setData($data);

			//var_dump($user);
			//criando e inserindo valores na sessão
			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

			//exit;



		}else{
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}


	}

	//verificando qual o tipo de usuário logado para redirecioná-lo corretamente caso não atenda os requisitos do login
	public static function verifyLogin($inadmin = true){

		//validando elementos da SESSION
		if(!User::checkLogin($inadmin)){

			if($inadmin){
				header("Location: /admin/login");	
			}else{
			header("Location: /login");
			}	

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
				":desperson"=>utf8_decode($this->getdesperson()),
				":deslogin"=>$this->getdeslogin(),
				":despassword"=>User::getPasswordHash($this->getdespassword()),
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

		$data = $results[0];

		$data['desperson'] = utf8_encode($data['desperson']);

		$this->setData($data);


	}

	///104
	//MÉTODO UPDATE
	public function update(){
		$sql = new Sql();

		

		$results = $sql->select("CALL  sp_users_update_save( :iduser,:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",

			array(
				":iduser"=>$this->getiduser(),
				":desperson"=>utf8_decode($this->getdesperson()),
				":deslogin"=>$this->getdeslogin(),
				":despassword"=>/*User::getPasswordHash(*/$this->getdespassword(),
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

				//
				if ($inadmin === true) {
                 $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$result";
	             } else {
	                 $link = "http://www.hcodecommerce.com.br/forgot/reset?code=$result";
	             } 


				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir senha da Hcode Store", "forgot", array(

					"name"=>utf8_encode($data["desperson"]),
					"link"=>$link

				));

				$mailer->send();

				return $link;

			}

		}

	}
//105
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
					    DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();",array(
	":idrecovery"=>$idrecovery
));


	if(count($results) === 0){
		throw new \Exception("Não foi possível recuperar a senha.");
	}
	else{
		return $results[0];
	}
}


//105
public static function setForgotUsed($idrecovery){

	//carregar o banco de dados para alterar a senha

	$sql = new Sql();

	$sql->query("UPDATE tb_userpasswordrecoveries SET dtrecovery = NOW() WHERE idrecovery = :IDRECOVERY", array(":IDRECOVERY"=>$idrecovery));


}


//105
public function setPassword($password){

	$sql = new Sql();

	$sql->query("UPDATE tb_users SET despassword = :PASSWORD WHERE iduser = :IDUSER", array(
		":PASSWORD"=>$password,
		":IDUSER"=>$this->getiduser()

	));

}

//105
public static function getPasswordHash($password)
	{
		return password_hash($password, PASSWORD_DEFAULT, [
				'cost'=>12
			]);
	}





///////////////////////////TALVEZ 115


	//////115 

	//método para disparar mensagem de erro
	public static function setError($msg){

		$_SESSION[User::ERROR] = $msg;

	}

	//////115 

	//método para atualizar mensagem de erro
	public static function getError(){

		$msg = (isset($_SESSION[User::ERROR])) ? $_SESSION[User::ERROR] : "";

		User::clearError();

		return $msg;


	}

	//////115 

	//método para limpar msg de erro
	public static function clearError(){
		$_SESSION[User::ERROR] = NULL;
	}






///////////117

	//método para disparar mensagem de erro
	public static function setErrorRegister($msg){

		$_SESSION[User::ERROR_REGISTER] = $msg;

	}

	//////117

	//método para atualizar mensagem de erro
	public static function getErrorRegister(){

		$msg = (isset($_SESSION[User::ERROR_REGISTER])) ? $_SESSION[User::ERROR_REGISTER] : "";

		User::clearErrorRegister();

		return $msg;


	}

	//////117

	//método para limpar msg de erro
	public static function clearErrorRegister(){
		$_SESSION[User::ERROR_REGISTER] = NULL;
	}


	//método para checar se o login já existe no banco de dados
	public static function checkLoginExists($login){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin",[
			':deslogin'=>$login
		]);

		return (count($results) > 0);//retorna se a contagem de resultado é MAIOR que 0
										//significa que este usuário já existe

	}



///////////119


	//método para disparar mensagem de sucesso
	public static function setSuccess($msg){

		$_SESSION[User::SUCCESS] = $msg;

	}	

	//método para atualizar mensagem de sucesso
	public static function getSuccess(){

		$msg = (isset($_SESSION[User::SUCCESS])) ? $_SESSION[User::SUCCESS] : "";

		User::clearSuccess();

		return $msg;


	}


	//método para limpar msg de sucesso
	public static function clearSuccess(){
		$_SESSION[User::SUCCESS] = NULL;
	}




///////122
	public function getOrders(){

		$sql = new Sql();

		$results = $sql->select("
			SELECT * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus)
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser 
			INNER JOIN tb_addresses e USING(idaddress) 
			INNER JOIN tb_persons f ON f.idperson = d.idperson 
			WHERE a.iduser = :iduser
			", [
				':iduser'=>$this->getiduser()
		]);

		return $results;



	}

















}//fim da classe 

?>