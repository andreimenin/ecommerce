<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;


//Model de usuários
class User extends Model{

	const SESSION = "User";


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



	public static function listAll(){
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");


	}

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



	//MÉTODO UPDATE
	public function get($iduser){


		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING (idperson) WHERE a.iduser = :iduser", array(":iduser"=>$iduser));


		$this->setData($results[0]);


	}

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



	public function delete(){
		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(":iduser"=>$this->getiduser()
	));

	}


}//fim da classe 

?>