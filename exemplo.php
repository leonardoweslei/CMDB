<?php
ini_set("display_errors","On");
//error_reporting(E_ERROR AND E_WARNING);
error_reporting(E_ALL);
function pr($v)
{
	echo "<pre>".print_r($v,1)."</pre>";
}
require_once("cmdb.php");
$database_params=array
(
	'dsn'=>"mysql",
	'user'=>"root",
	'passwd'=>"123",
	'host'=>"localhost",
	'dbname'=>"bd_teste"
);
/**
 * classe teste
 * Classe para exemplificar o uso da cmdb
 *
 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
 * @since  27/09/2011 17:19:00
 * @version 1.0
 * @name teste
 */
class teste extends cmdb
{
	/* atributos privados que irão representar os campos da tabela */
	private $codigo=false;
	private $dependencia=false;
	private $nome=false;
	private $sobrenome=false;
	/**
	 * atributos publicos que irão interagir com a cmdb,
	 * não são usados diretamente os nomes dos atributos porque:
	 * -melhor legibilidade(minha opinião)
	 * -caso extenda a classe ou acrescente métodos não irão influenciar na comunicação com a cmdb
	 */
	public $fields=array(
		"codigo"=>array("name"=>"codigo"),
		"dependencia"=>array("name"=>"dependencia"),
		"nome"=>array("name"=>"nome"),
		"sobrenome"=>array("name"=>"sobrenome"),
	);
	public $relation=array("from"=>array(array("local_field"=>"dependencia","remote_table"=>"teste","remote_field"=>"codigo")));
	public $table="teste";
		/**
		 * @name __get
		 * @abstract retorna o valor de um atributo da classe
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $p
		 * @access public
		 */
		public function __get($p)
		{
			return isset($this->$p)?$this->$p:false;
		}
		/**
		 * @name __set 
		 * @abstract altera o valor de um atributo da classe
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $p
		 * @param $v
		 * @access public
		 */
		public function __set($p,$v)
	{
			$this->$p=$this->real_value($p,$v);
	}
}
$teste= new teste();

//selecionando todos os dados da tabela
$teste->select();

//retornando o resultado do select em um array
$dados=$teste->get_array(1);
//$dados=$teste->get_object(1);
pr($dados);

/*$raquel=new teste();
$raquel->codigo_eq(4)->select()->set_result();
$cesar=new teste();
$cesar->codigo_eq(2)->select()->set_result()->set("nome","Cesar")->set("sobrenome","Diniz")->codigo_eq(2)->update();
$leo=new teste();
$leo->codigo_eq(1)->select()->set_result()->set("dependencia",$raquel)->codigo_eq(1)->update();
$leo2=new teste();
$leo2->codigo_eq(5)->select()->set_result()->set("dependencia",$cesar)->codigo_eq(5)->update();*/
//pr($leo);

//retornando o resultado do select em um array de objetos "teste"
//$dados=$teste->get_object();

//setando atributos referentes aos campos da tabela
/*$teste=$teste
->codigo('2')
->nome('John')
->sobrenome('connor');*/

//inserindo no banco de dados
//$teste->insert();

//verificando se ouve erros
//pr($teste->error?"Ouve erro!":"Ok!");


//selecionando todos os dados da tabela com codigo igual a 1
$dados=$teste
->codigo_gt(1)
//executando consulta
->select()
//retornando o resultado do select em um array
->get_array();

pr($dados);
pr($teste);
?>
