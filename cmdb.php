<?php
require_once("database.php");
require_once("gen_class.php");
   /**
	 * classe cmdb
	 * Classe manipuladora de banco de dados
	 * classe generica para manipular uma ou mais tabela de um banco de dados
	 *
	 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
	 * @since  08/02/2011 08:57:00
	 * @subpackage cmdb
	 * @version 1.0
	 * @name cmdb
	 */
	abstract class cmdb
	{
		/*** Atributos: ***/
		/**
		 * A variável $campos guarda a estrutura da tabela sendo que a as chaves do array são os nomes dos campos e os valores as estruturas dos campos
		 *
		 * @var public array $campos
		 */
		public $fields=array();
		/**
		 * A variável $values guarda os valores dos campos
		 *
		 * @var public array $values
		 */
		public $values=array();
		/**
		 * A variável $relation guarda as relações da tabela onde a posicao "to" guarda os dados de campos de outras tabelas que usam campos da tabela e a posição "from" guarda os dados dos campos de outras tabelas usadas por campos da tabela
		 *
		 * @var public array $struct
		 */
		public $relation=array();
		/**
		 * A variável $table guarda o nome da tabela/classe para criar objetos
		 *
		 * @var public array $table
		 */
		public $table=false;
		/**
		 * A variável $query dados das consultas a serem executadas e/ou condições para a execução no banco de dados
		 *
		 * @var private array $query
		 */
		public $query=array
		(
			'field'=>array(),
			'value'=>array(),
			'table'=>array(),
			'where'=>array(),
			'order'=>array(),
			'group'=>array(),
			'query'=>array(),
			'limit'=>""
		);
		/**
		 * A variável $result guarda os dados do resultado da última query executada
		 *
		 * @var private array $result
		 */
		protected $result=false;
		/**
		 * A variável $last_query guarda a última consulta executada
		 *
		 * @var private char $last_query
		 */
		public $last_query=false;
		/**
		 * A variável $error determina se houve erro na execução se o valor da variável for verdadeiro houve um erro
		 *
		 * @var public bool $error
		 */
		public $error=false;
		/**
		 * A variável $error_code guarda o codigo caso haja algum erro
		 *
		 * @var public bool $error_code
		 */
		public $error_code=false;
		/**
		 * A variável $error_desc guarda a descrição caso haja algum erro
		 *
		 * @var public bool $error_desc
		 */
		public $error_desc=false;
		/*** Metodos ***/
		/**
		 * @name __construct
		 * @abstract Construtor: simplesmente seta valores em atributos
		 */
		function __construct()
		{
				$args = func_get_args();
				while(list( , $arg) = each($args))
				{
					list(, $field)=each($this->fields);
					$this->values[$field['name']]=$arg;
				}
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
		abstract public function __set($p,$v);
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
		abstract public function __get($p);
		/**
		 * @name get
		 * @abstract retorna o valor de um atributo da classe
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $p
		 * @access public
		 */
		public function get($p)
		{
			return isset($this->values[$p])?$this->values[$p]:false;
		}
		/**
		 * @name set 
		 * @abstract altera o valor de um atributo da classe e retorna um clone do objeto
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $p
		 * @param $v
		 * @access public
		 */
		public function set($p,$v)
		{
				$this->values[$p]=$this->absolute_value($p,$v);
				return $this;
		}
		/**
		 * @name absolute_value
		 * @abstract altera o valor de um atributo da classe e retorna um clone do objeto
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $p
		 * @param $v
		 * @access public
		 */
		public function absolute_value($p,$v)
		{
			$attr_relation=false;
			if(isset($this->relation["from"]) && !empty($this->relation["from"]))
			{
				if(isset($this->relation["from"][$p]))
				{
					$attr_relation=$this->relation["from"][$p];
				}
				elseif(array_search($p,$this->relation["from"])!==false)
				{
					$k=array_search($p,$this->relation["from"]);
					$attr_relation=$k['local_field']==$p?$k:false;
				}
			}
			if(!empty($attr_relation))
			{
				if(is_object($v) && $attr_relation && is_a($v,$attr_relation['remote_table']))
				{
					$v=$v->$attr_relation['remote_field'];
				}
				elseif(is_array($v) && $attr_relation && isset($v[$attr_relation['remote_field']]))
				{
					$v=$v[$attr_relation['remote_field']];
				}
				elseif(is_array($v))
				{
					$v=array_shift($v);
				}
			}
			return $v;
		}
		/**
		 * @name persistence 
		 * @abstract busca dependencias de chaves estrangeiras de um atributo baseado no uso da CMDB
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  29/11/2011 10:01:00
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $value
		 * @param $class
		 * @param $fk
		 * @param $type
		 * @access public
		 */
		function persistence($value,$class,$fk,$type="array")
		{
			$obj=new $class;
			$method=$fk."_eq";
			$obj->$method($value)->select();
			if($type=="array")
			{
				return $obj->get_array(true);
			}
			else
			{
				return $obj->get_object(true);
			}
		}
		/**
		 * @name attr_fetch 
		 * @abstract pega um dado referente a um campo
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  20/12/2011 16:44:00
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $attr
		 * @param $key
		 * @access private
		 */
		private function attr_fetch($attr,$key="name")
		{
			foreach ($this->fields as $k=>$v)
			{
				if(is_array($v) && ($v['name']==$attr))
				{
					return $v[$key];
				}
			}
			return false;
		}
		/**
		 * @name __call 
		 * @abstract "emula" um método de acordo com os atributos da classe
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  27/09/2011 17:51:00
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $p
		 * @param $v
		 * @access public
		 */
		public function __call($method,$arguments)
		{
			if(method_exists($this,$method))
			{
				return call_user_func_array(array($this, $method), $arguments);
			}
			elseif($this->attr_fetch($method)==$method && count($arguments)==1)
			{
				array_unshift($arguments,$method);
				return call_user_func_array(array($this, "set"), $arguments);
			}
			elseif($this->attr_fetch($method)==$method && count($arguments)==0)
			{
				array_unshift($arguments,$method);
				return call_user_func_array(array($this, "get"), $arguments);
			}
			elseif(substr_count($method,"_")>0)
			{
				$method=explode("_",$method);
				$m=array_pop($method);
				$attr=implode("_", $method);
				$method=$m;
				switch($method)
				{
					case "eq":
						$value=(count($arguments)==0 || !$arguments[0])?$this->$attr:$arguments[0];
						return $this->__set_data_query($attr."='".$value."'","where",(isset($arguments[1])?$arguments[1]:" AND "));
						break;
					case "ne":
						$value=(count($arguments)==0 || !$arguments[0])?$this->$attr:$arguments[0];
						return $this->__set_data_query($attr."!='".$value."'","where",(isset($arguments[1])?$arguments[1]:" AND "));
						break;
					case "gt":
						$value=(count($arguments)==0 || !$arguments[0])?$this->$attr:$arguments[0];
						return $this->__set_data_query($attr.">'".$value."'","where",(isset($arguments[1])?$arguments[1]:" AND "));
						break;
					case "ge":
						$value=(count($arguments)==0 || !$arguments[0])?$this->$attr:$arguments[0];
						return $this->__set_data_query($attr.">='".$value."'","where",(isset($arguments[1])?$arguments[1]:" AND "));
						break;
					case "lt":
						$value=(count($arguments)==0 || !$arguments[0])?$this->$attr:$arguments[0];
						return $this->__set_data_query($attr."<'".$value."'","where",(isset($arguments[1])?$arguments[1]:" AND "));
						break;
					case "le":
						$value=(count($arguments)==0 || !$arguments[0])?$this->$attr:$arguments[0];
						return $this->__set_data_query($attr."<='".$value."'","where",(isset($arguments[1])?$arguments[1]:" AND "));
						break;
					case "between":
						$value1=$arguments[0];
						$value2=$arguments[1];
						return $this->__set_data_query($attr." BETWEEN '".$value1."' AND '".$value2."'","where",(isset($arguments[2])?$arguments[2]:" AND "));
						break;
					case "nbetween":
						$value1=$arguments[0];
						$value2=$arguments[1];
						return $this->__set_data_query($attr." NOT BETWEEN '".$value1."' AND '".$value2."'","where",(isset($arguments[2])?$arguments[2]:" AND "));
						break;
					case "in":
						$value=(count($arguments)==0 || !$arguments[0])?$this->$attr:$arguments[0];
						$value=is_array($value)?$value:array($value);
						return $this->__set_data_query($attr." IN('".implode("','",$value)."')","where",(isset($arguments[1])?$arguments[1]:" AND "));
						break;
					case "nin":
						$value=(count($arguments)==0 || !$arguments[0])?$this->$attr:$arguments[0];
						$value=is_array($value)?$value:array($value);
						return $this->__set_data_query($attr." NOT IN('".implode("','",$value)."')","where",(isset($arguments[1])?$arguments[1]:" AND "));
						break;
						break;
					case "null":
					case "isnull":
					case "is_null":
						return $this->__set_data_query($attr." IS NULL","where",(isset($arguments[0])?$arguments[0]:" AND "));
						break;
					case "nnull":
					case "isnotnull":
						return $this->__set_data_query($attr." IS NOT NULL","where",(isset($arguments[0])?$arguments[0]:" AND "));
						break;
					case "like":
						$value=(count($arguments)==0 || !$arguments[0])?$this->$attr:$arguments[0];
						return $this->__set_data_query($attr." like '".$value."'","where",(isset($arguments[1])?$arguments[1]:" AND "));
						break;
					case "nlike":
					case "notlike":
					case "not_like":
						$value=(count($arguments)==0 || !$arguments[0])?$this->$attr:$arguments[0];
						return $this->__set_data_query($attr." like '".$value."'","where",(isset($arguments[1])?$arguments[1]:" AND "));
						break;
				}
			}
			else
			{
				die("Fatal error: Call to undefined method ".$this->table."::".$method."()");
			}
		}
		/**
		 * @name get_values
		 * @abstract retorna os valores contidos nos atributos da classe em um array
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @access public
		 */
		public function get_values()
		{
			$tmp=array();
			foreach($this->fields as $field)
			{
				$tmp[$field['name']]=(isset($this->values[$field['name']])?$this->values[$field['name']]:false);
			}
			return $tmp;
		}
		/**
		 * @name get_fields
		 * @abstract retorna um array com o nome dos atributos da classe
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @access public
		 */
		public function get_fields()
		{
			$tmp=array();
			foreach($this->fields as $field)
			{
				$tmp[$field['name']]=$field['name'];
			}
			return $tmp;
		}
		/**
		 * @name get_values_query
		 * @abstract retorna os valores contidos na posição 'value' do atributo query da classe se NÃO existir relacao entre o dado e a posição 'field' ele tenta assumir o valor do atributo da classe, e se este for nulo o atributo assume valor nulo
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @access public
		 */
		public function get_values_query()
		{
			$tmp=array();
			foreach($this->query['value'] as $k=>$v)
			{
				$tmp[$k]=(empty($v)?(strlen($this->$k)==0?NULL:$this->$k):$v);
			}
			return $tmp;
		}
		/**
		 * @name get_fields_query
		 * @abstract retorna os valores contidos na posição 'field' do atributo query da classe
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @access public
		 */
		public function get_fields_query()
		{
			$tmp=array();
			foreach($this->query['field'] as $k=>$v)
			{
				$tmp[$k]=$v;
			}
			return $tmp;
		}
		/**
		 * @name __set_data_query
		 * @abstract altera as condições para execucoes de querys
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $value
		 * @param $act
		 * @param $join
		 * @param $p
		 * @access private
		 */
		protected function __set_data_query($value,$act,$join=false,$p=false)
		{
			if(is_array($value))
			{
				$value2=array();
				foreach($value as $k=>$v)
				{
					$value2[(gettype($k)=="integer"?$v:$k)]=$v;
				}
				if($act=="where")
				{
					$value=empty($p)?"(".implode(" ".$join." ",$value2).")":implode(" ".$join." ",$value2);
				}
				else
				{
					$value=$value2;
				}
			}
			if(!in_array($value,$this->query[$act]))
			{
				
				if($act=="value" OR $act=="field" OR $act=="table")
				{
					$this->query[$act]=array_merge($value,isset($this->query[$act])?$this->query[$act]:array());
				}
				else
				{
					if(!empty($this->query[$act]))
					{
						$this->query[$act][]=(empty($join)?'':" ".$join." ").$value;
					}
					else
					{
						$this->query[$act][]=$value;
					}
				}
			}
			return $this;
		}
		/**
		 * @name where
		 * @abstract altera as condições para execução de querys contidas na posição 'where' do atributo query, caso o valor recebido seja um array o dado é interpretado como uma única condição
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $value valor
		 * @param $sep separador de condição
		 * @access public
		 */
		public function where($value,$sep="AND")
		{
			return $this->__set_data_query($value,"where",$sep);
		}
		/**
		 * @name field
		 * @abstract  altera os campos a serem recuperados/alterados durante a execução de querys, os valores são armazenados na posição 'field' do atributo query, onde as chaves de cada posição mostram qual valor as mesmas referenciam dentro da classe por default a chave tera o mesmo valor da posição caso a chave NÃO seja passada
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $value
		 * @access public
		 */
		public function field($value="*")
		{
			return $this->__set_data_query($value,"field",", ",1);
		}
		/**
		 * @name tables
		 * @abstract seta junções da tabela contida no atrbuto $table com outras tabelas
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $value
		 * @acess public
		 */
		public function tables($value)
		{
			return $this->__set_data_query($value,"table","");
		}
		/**
		 * @name value
		 * @abstract altera os valores a serem recuperados/alterados durante a execução de querys, os valores são armazenados na posição 'value' do atributo query, onde as chaves de cada posição mostram qual valor as mesmas referenciam dentro da classe ou dentro da posição 'field' do atributo query
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $value
		 * @access public
		 */
		public function value($value)
		{
			return $this->__set_data_query($value,"value",false,1);
		}
		/**
		 * @name order
		 * @abstract adiciona condição de ordenação de resultados de querys este valor e armazenado na posição 'order' do atributo query
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $value
		 * @access public
		 */
		public function order($value)
		{
			return $this->__set_data_query($value,"order");
		}
		/**
		 * @name group
		 * @abstract adiciona condição de agrupamento de resultados de querys este valor e armazenado na posição 'group' do atributo query
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $value
		 * @access public
		 */
		public function group($value)
		{
			return $this->__set_data_query($value,"group");
		}
		/**
		 * @name limit
		 * @abstract  seta a quantidade de resultados de querys. este valor e armazenado na posição 'group' do atributo query
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $init
		 * @param $end
		 * @access public
		 */
		public function limit($init=0,$end=10)
		{
			$this->query["limit"]=$init.", ".$end;
			return $this;
		}
		/**
		 * @name select
		 * @abstract busca dados do banco de dados com uma query montada de acordo com os valores contidos no atributo query da classe
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $table_fields determina se o nome dos atributos padrões da classe vão ser usados ou o valor contido na posição 'value' do atributo query
		 * @access public
		 */
		public function select($table_fields=false,$d=false)
		{
			$fields=$this->get_fields_query();
			$fields=(!$table_fields || empty($fields)?$this->get_fields():$fields);
			$query='SELECT '.(empty($fields)?'*':implode(",",$fields)).' FROM '.(empty($this->query['table'])?$this->table:implode(" ",$this->query['table']));
			$query.=empty($this->query['where'])?'':' WHERE '.implode("",$this->query['where']);
			$query.=empty($this->query['group'])?'':' GROUP BY '.implode(", ",$this->query['group']);
			$query.=empty($this->query['order'])?'':' ORDER BY '.implode(", ",$this->query['order']);
			$query.=empty($this->query['limit'])?'':' LIMIT '.$this->query['limit'];
			return $this->exec($query,null,$d);
		}
		/**
		 * @name search
		 * @abstract busca dados do banco de dados com uma query montada de acordo com os valores dos atributos da classe
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @access public
		 */
		public function search($d=false)
		{
			$fields=$this->get_fields();
			$data=array();
			$data2=array();
			$values=array();
			foreach($fields as $k=>$v)
			{
				if(!empty($this->values[$k]))
				{
					$value=($this->values[$k]==NULL?NULL:$this->values[$k]);
					$values[$k]=$value;
					$data[":".$k]=$values[$k];
					$data2[]=$v."=:".$k;
				}
			}
			$query='SELECT '.(empty($fields)?'*':implode(",",$fields)).' FROM '.$this->table;
			$query.=empty($fields)?'':' WHERE '.implode(" AND ",$data2);
			return $this->exec($query,$data,$d);
		}
		/**
		 * @name update
		 * @abstract altera dados do banco de dados com uma query montada de acordo com os valores contidos no atributo query da classe
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $table_fields determina se o nome dos atributos padrões da classe vão ser usados ou o valor contido na posição 'value' do atributo query
		 * @access public
		 */
		public function update($table_fields=false,$d=false)
		{
			$fields=$this->get_fields_query();
			$fields=(!$table_fields || empty($fields)?$this->get_fields():$fields);
			$values=$this->get_values_query();
			$values=empty($table_fields) && !empty($values)?$values:$this->get_values();
			$data=array();
			$data2=array();
			foreach($fields as $k=>$v)
			{
				//$value=empty($values[$k])?(empty($this->$k)?"NULL":$this->$k):$values[$k];
				$value=empty($values[$k])?(empty($this->values[$k])?NULL:$this->values[$k]):$values[$k];
				//$values[$k]=($value=="NULL"?"NULL":$value);
				$values[$k]=($value==NULL?NULL:$value);
				$data[":".$k]=$values[$k];
				$data2[]=$v."=:".$k;
			}
			$query='UPDATE '.(empty($this->query['table'])?$this->table:implode(" ",$this->query['table'])).' SET '.implode(", ",$data2);
			$query.=empty($this->query['where'])?'':' WHERE '.implode("",$this->query['where']);
			$query.=empty($this->query['group'])?'':' GROUP BY '.implode(", ",$this->query['group']);
			$query.=empty($this->query['order'])?'':' ORDER BY '.implode(", ",$this->query['order']);
			$query.=empty($this->query['limit'])?'':' LIMIT '.$this->query['limit'];
			return $this->exec($query,$data,$d);
		}
		/**
		 * @name replace
		 * @abstract sobrescreve dados do banco de dados com uma query montada de acordo com os valores contidos no atributo query da classe
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $table_fields determina se o nome dos atributos padrões da classe vão ser usados ou o valor contido na posição 'value' do atributo query
		 * @access public
		 */
		public function replace($table_fields=false,$d=false)
		{
			$fields=$this->get_fields_query();
			$fields=(!$table_fields || empty($fields)?$this->get_fields():$fields);
			$values=$this->get_values_query();
			$values=empty($table_fields) && !empty($values)?$values:$this->get_values();
			$data=array();
			foreach($fields as $k=>$v)
			{
				$value=empty($values[$k])?(empty($this->values[$k])?NULL:$this->values[$k]):$values[$k];
				$values[$k]=($value==NULL?NULL:$value);
				$data[":".$k]=$values[$k];
			}
			$query='REPLACE INTO '.$this->table.'('.implode(", ",array_keys($fields)).') VALUES('.implode(", ",array_keys($data)).")";
			return $this->exec($query,$data,$d);
		}
		/**
		 * @name delete
		 * @abstract apaga dados do banco de dados com uma query montada de acordo com os valores contidos no atributo query da classe
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @access public
		 */
		public function delete($d=false)
		{
			$query='DELETE FROM '.(empty($this->query['table'])?$this->table:implode(" ",$this->query['table']));
			$query.=empty($this->query['where'])?'':' WHERE '.implode("",$this->query['where']);
			$query.=empty($this->query['group'])?'':' GROUP BY '.implode(", ",$this->query['group']);
			$query.=empty($this->query['order'])?'':' ORDER BY '.implode(", ",$this->query['order']);
			$query.=empty($this->query['limit'])?'':' LIMIT '.$this->query['limit'];
			return $this->exec($query,null,$d);
		}
		/**
		 * @name insert
		 * @abstract insere dados do banco de dados com uma query montada de acordo com os valores contidos no atributo query da classe
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $table_fields determina se o nome dos atributos padrões da classe vão ser usados ou o valor contido na posição 'value' do atributo query
		 * @access public
		 */
		public function insert($table_fields=false,$d=false)
		{
			$fields=$this->get_fields_query();
			$fields=(!$table_fields || empty($fields)?$this->get_fields():$fields);
			$values=$this->get_values_query();
			$values=empty($table_fields) && !empty($values)?$values:$this->get_values();
			$data=array();
			foreach($fields as $k=>$v)
			{
				$value=empty($values[$k])?(empty($this->values[$k])?NULL:$this->values[$k]):$values[$k];
				$values[$k]=($value==NULL?NULL:$value);
				$data[":".$k]=$values[$k];
			}
			$query='INSERT INTO '.$this->table.'('.implode(", ",array_keys($fields)).') VALUES('.implode(", ",array_keys($data)).")";
			//$query2='INSERT INTO '.$this->table.'('.implode(",",array_keys($fields)).') VALUES('.implode(",",$data).")";
			return $this->exec($query,$data,$d);
		}
		/**
		 * @name exec
		 * @abstract executa uma query no banco de dados e seta se a mesma foi executada com sucesso ou nao
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $query
		 * @param $data
		 * @param $debug
		 * @access private
		 */
		protected function exec($query,$data=null,$d=false)
		{
			$bd=new database();
			$bd=$bd->getconection();
			$stmt=$bd->prepare($query);
			$this->error=($stmt?$stmt->execute($data):false);
			$this->error=$this->error==false?true:false;
			if($this->error==false)
			{
				$stmt2=$stmt->fetchAll(PDO::FETCH_ASSOC);
				$this->result=$stmt2;
			}
			else
			{
				$this->result=array();
				$error=$stmt?$stmt->errorInfo():"Erro desconhecido";
				$this->error_code=$error[1];
				$this->error_desc=$error[2];
			}
			if($d)
			{
				if($this->error)
				{
					print_r($this->error_code.":".$this->error_desc);
				}
				print_r($query);
				print_r($stmt);
				print_r($data);
			}
			$this->last_query=$query;
			return $this->__set_data_query($query,"query");
		}
		/**
		 * @name get_array
		 * @abstract retorna o resultado da consulta executada pelo metodo exec em um array
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param bolleam $persistence
		 * @access public
		 */
		public function get_array($persistence=false)
		{
			$tmp=array();
			foreach($this->result as $un)
			{
				$temp=$un;
				if($persistence)
				{
					if(isset($this->relation["from"]) && !empty($this->relation["from"]))
					{
						foreach($this->relation["from"] as $data)
						{
							$temp[$data['local_field']]=$this->persistence($temp[$data['local_field']],$data['remote_table'],$data['remote_field'],"array");
						}
					}
				}
				$tmp[]=$temp;
			}
			return $tmp;
		}
		/**
		 * @name get_object
		 * @abstract retorna o resultado da consulta executada pelo metodo exec em um array de objetos
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param bolleam $persistence
		 * @access public
		 */
		public function get_object($persistence=false)
		{
			$tmp=array();
			foreach($this->result as $un)
			{
				$temp=new $this->table;
				$temp->extract($un);
				if($persistence)
				{
					if(isset($this->relation["from"]) && !empty($this->relation["from"]))
					{
						foreach($this->relation["from"] as $data)
						{
							$temp->$data['local_field']=$this->persistence($temp[$data['local_field']],$data['remote_table'],$data['remote_field'],"object");
						}
					}
				}
				$tmp[]=$temp;
			}
			return $tmp;
		}
		/**
		 * @name extract
		 * @abstract seta valores do objeto contidos em um array
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @param array $values
		 * @access public
		 */
		public function extract($values=array())
		{
			foreach($this->fields as $field)
			{
				$this->values[$field['name']]=(isset($values[$field['name']])?$values[$field['name']]:false);
			}
			return $this;
		}
		/**
		 * @name set_result
		 * @abstract seta valores do objeto a partir de um dos resultados da colsulta
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  29/11/2011 11:41:00
		 * @subpackage cmdb
		 * @version 1.0
		 * @param $num
		 * @access public
		 */
		public function set_result($num=0)
		{
			if(!empty($this->result))
			{
				$r=$num<count($this->result)?$this->result[$num]:$this->result[0];
				$this->extract($r);
			}
			return $this;
		}
		/**
		 * @name compact
		 * @abstract coloca os valores dos atributos do objeto relativos a tabela do banco de dados em um array
		 * 
		 * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
		 * @since  08/02/2011 08:57:00
		 * @final  09/03/2011 16:53:59
		 * @subpackage cmdb
		 * @version 1.0
		 * @access public
		 */
		public function compact()
		{
			return $this->get_values();
		}
	} // fim da classe cmdb
