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

	//verificando qual o tipo de usuário logado para redirecioná-lo corretamente
	public static function verifyLogin($inadmin = true){

		//se não foi definida
		if(!isset($_SESSION[User::SESSION])||!$_SESSION[User::SESSION]||!(int)$_SESSION[User::SESSION]["iduser"] > 0 ||(bool)$_SESSION[User::SESSION]["inadmin"] !==$inadmin){

			header("Location: /admin/login");
			exit;
		}

	}

	public static function logout(){

		$_SESSION[User::SESSION] = NULL;

	}




}//fim da classe 

?>