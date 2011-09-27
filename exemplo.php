<?php
error_reporting(E_ALL);
ini_set("display_errors","On");
$bd_data=array
(
	'dsn'=>"mysql",
	'user'=>"root",
	'passwd'=>"123",
	'host'=>"localhost",
	'dbname'=>"bd_teste"
);
function pr($v)
{
	echo "<pre>".print_r($v,1)."</pre>";
}
require_once("cmdb.php");
/*
$cmdb=new cmdb(); Não irá funcionar
é necessario implementar uma classe extendida
da mesma forma que é necessario criar todos os métodos abstratos da cmdb
*/

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
	private $nome=false;
	private $sobrenome=false;
	/**
	 * atributos publicos que irão interagir com a cmdb,
	 * não são usados diretamente os nomes dos atributos porque:
	 * -melhor legibilidade(minha opinião)
	 * -caso extenda a classe ou acrescente métodos não irão influenciar na comunicação com a cmdb
	 */
	public $campos=array("codigo","nome","sobrenome");
    public $tabela="teste";
    
    /**
	 * @name __construct
     * @abstract Construtor: simplesmente seta valores em atributos
     */
    function __construct
	(
		$codigo=false,
		$nome=false,
		$sobrenome=false
	)
	{
			$this->__set('codigo',$codigo);
			$this->__set('nome',$nome);
			$this->__set('sobrenome',$sobrenome);
	}
	/**
	 * @name __set
	 * @abstract altera o valor de um atributo da classe teste
	 * caso a tabela tenha relacionamentos
	 * e queira implementar um tratamento quando for setar valores para os atributos
	 */
	public function __set($p,$v)
	{
		$this->$p=$v;
	}
	/**
	 * @name __get 
	 * @abstract retorna o valor de um atributo da classe
	 */
	public function __get($p)
	{
		return isset($this->$p)?$this->$p:false;
	}
	/**
	 * @name get_array
	 * @abstract retorna o resultado da consulta executada pelo metodo exec da classe cmdb em um array
	 */
	public function get_array($persistencia=false)
	{
		$tmp=array();
		foreach($this->result as $un)
		{
			$temp=$un;
			if($persistencia)
			{
				/*
				 * para implementar um tratamento quando for retornar valores relativos à relacionamento, como uma persistência
				 */
			}
			$tmp[]=$temp;
		}
		return $tmp;
	}
	/**
	 * @name get_object
	 * @abstract retorna o resultado da consulta executada pelo metodo exec da classe cmdb em um array de objetos
	 */
	public function get_object($persistencia=false)
	{
		$tmp=array();
		foreach($this->result as $un)
		{
			$temp=new teste();
			$temp->extract($un);
			if($persistencia)
			{
				/*
				 * para implementar um tratamento quando for retornar valores relativos à relacionamento, como uma persistência
				 */
			}
			$tmp[]=$temp;
		}
		return $tmp;
	}		
}
$teste= new teste();

//selecionando todos os dados da tabela
$teste->select();

//retornando o resultado do select em um array
$dados=$teste->get_array();
pr($dados);

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
$teste->codigo_gt(1)->select();

//retornando o resultado do select em um array
$dados=$teste->get_array();
pr($dados);
pr($teste);
?>
