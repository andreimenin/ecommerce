<?php
namespace Hcode;

//Classe para automatizar os GETS e SETS para todas as classes


class Model{

	private $values = [];


	public function __call($name, $args){

		$method = substr($name, 0, 3);
		$fieldName = substr($name, 3, strlen($name));

		//var_dump($method, $fieldName);
		//exit;


		switch ($method) {
			case 'get':
				//se ainda não tiver dados no banco 
				return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULL;
				break;

			case 'set':
				$this->values[$fieldName] = $args[0];
				break;			
			default:
				# code...
				break;
		}
	}



	//Automatização dos SETS de cada atributo do objeto a ser inserido no banco de dados, tirando a necessidade de escrever tudo na mão.
	//Monta todos os GETS e SETS
	public function setData($data = array()){
		foreach ($data as $key => $value) {

			$this->{"set".$key}($value);

		}
	}


	//método final para retornar os atributos
	public function getValues(){
		return $this->values;
	}





}//fim da classe




?>