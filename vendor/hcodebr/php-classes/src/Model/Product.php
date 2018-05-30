<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

///106
//Model de usuários
class Product extends Model{

	
	//MÉTODO READ
	public static function listAll(){
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
	}


	//109 
	//Método de listagem dos produtos
	public static function checkList($list){

		foreach ($list as &$row) {
			
			$p = new Product();

			$p->setData($row);

			$row = $p->getValues();

		}

		return $list;
	}


	


	
	//MÉTODO CREATE
	public function save(){
		$sql = new Sql();		

		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)",

			array(
				":idproduct"=>$this->getidproduct(),
				":desproduct"=>$this->getdesproduct(),
				":vlprice"=>$this->getvlprice(),
				":vlwidth"=>$this->getvlwidth(),
				":vlheight"=>$this->getvlheight(),
				":vllength"=>$this->getvllength(),
				":vlweight"=>$this->getvlweight(),
				":desurl"=>$this->getdesurl()
				 ));

			$this->setData($results[0]);

			}


	public function get($idproduct){


		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [':idproduct'=>$idproduct]);



		$this->setData($results[0]);

	}


public function delete(){

	$sql = new Sql();

	$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [':idproduct'=>$this->getidproduct()

		]);


	     $filename = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
     "resources" . DIRECTORY_SEPARATOR . 
     "site" . DIRECTORY_SEPARATOR . 
     "img" . DIRECTORY_SEPARATOR . 
     "products" . DIRECTORY_SEPARATOR . 
     $this->getidproduct() . ".jpg";
     $sql = new Sql();
     $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
       ':idproduct'=>$this->getidproduct()
     ]);
     if (file_exists($filename)) {
        unlink($filename);
     }

     Product::updateFile();

}




public function checkPhoto(){
	//vefifica se na pasta existe o arquivo da foto

	if(file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
					"resources" . DIRECTORY_SEPARATOR . 
					"site" . DIRECTORY_SEPARATOR . 
					"img" . DIRECTORY_SEPARATOR . 
					"products" . DIRECTORY_SEPARATOR . 
					$this->getidproduct() . ".jpg")){

		$url = "/resources/site/img/products/" . $this->getidproduct() . ".jpg";
	
	}
	else{
		$url = "/resources/site/img/product.jpg";
		
	}
	return $this->setdesphoto($url);
}





public function getValues(){

	$this->checkPhoto();

	$values = parent::getValues();

	return $values;
}


public function setPhoto($file){

	$extension = explode('.',$file['name']);

	$extension = end($extension);

	switch ($extension) {
		case "jpg":
		case "jpeg":
			$image = imagecreatefromjpeg($file["tmp_name"]);
			break;

		case "gif":
			$image = imagecreatefromgif($file["tmp_name"]);
			break;

		case "png":
			$image = imagecreatefrompng($file["tmp_name"]);
			break;	
		default:
			return ;
		break; 	
	}

	$dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
					"resources" . DIRECTORY_SEPARATOR . 
					"site" . DIRECTORY_SEPARATOR . 
					"img" . DIRECTORY_SEPARATOR . 
					"products" . DIRECTORY_SEPARATOR . 
					$this->getidproduct() . ".jpg";

	
	imagejpeg($image, $dist);

	imagedestroy($image);

	$this->checkPhoto();

}


////112 Método para pesquisar produto pela URL registrada e usar seus dados para exibição em product-detail.html
public function getFromURL($desurl){

	$sql = new Sql();

	$rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1", [
		':desurl'=>$desurl
	]);

	$this->setData($rows[0]);


}

/////112 - Método para listar as categorias que o produto pertence e mostrar em product-detail.html
public function getCategories(){


	$sql = new Sql();

	return $sql->select("SELECT * FROM tb_categories a INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory WHERE b.idproduct = :idproduct",[
		':idproduct'=>$this->getidproduct()
	]);
}





}//fim da classe 

?>