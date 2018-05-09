<?php

namespace Hcode;

class PageAdmin extends Page{

	public function __construct($opts = array(), $tpl_dir = "/views/admin/"){

		//invocando o método construtor da classe pai, sobrescrevendo o parâmetro $tpl_dir
		parent::__construct($opts, $tpl_dir);

	}


}

?>