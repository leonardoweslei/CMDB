<?php
require_once("database.php");
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
         * A variável $campos guarda todos os campos da  da tabela do banco de dados a ser trabalhada
         *
         * @var public array $campos
         */
        public $campos=array();
        /**
         * A variável $tabela guarda o nome da tabela/classe para criar objetos
         *
         * @var public array $tabela
         */
        public $tabela=false;
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
        protected $last_query=false;
        /**
         * A variável $error determina se houve erro na execução se o valor da variável for verdadeiro houve um erro
         *
         * @var public bool $error
         */
        public $error=false;
        /*** Metodos ***/
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
            $this->__set($p,$v);
            return $this->factory();
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
        public function __call($metodo,$argumentos)
        {
            /*pr($metodo);
            pr($argumentos);*/
			if(method_exists($this,$metodo))
			{
				return call_user_func_array(array($this, $metodo), $argumentos);
			}
			elseif(substr_count($metodo,"_")>0)
            {
				$metodo=explode("_",$metodo);
				$attr=$metodo[0];
				$metodo=$metodo[1];
				//array_unshift($argumentos,$attr);
				switch($metodo)
				{
					case "eq":
						$value=(count($argumentos)==0 || !$argumentos[0])?$this->$attr:$argumentos[0];
						return $this->__set_data_query($attr."='".$value."'","where",(isset($argumentos[1])?$argumentos[1]:false));
						break;
					case "gt":
						$value=(count($argumentos)==0 || !$argumentos[0])?$this->$attr:$argumentos[0];
						return $this->__set_data_query($attr.">'".$value."'","where",(isset($argumentos[1])?$argumentos[1]:false));
						break;
				}
			}
			elseif(in_array($metodo,$this->campos))
			{
				array_unshift($argumentos,$metodo);
				return call_user_func_array(array($this, "set"), $argumentos);
			}
			else
			{
				return $this->factory();
			}
        }
        /**
         * @name factory
         * @abstract retorna um objeto da classe clonado ou NÃO dependendo do parametro passado
         * 
         * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
         * @since  08/02/2011 08:57:00
         * @final  09/03/2011 16:53:59
         * @subpackage cmdb
         * @version 1.0
         * @param $clear determina se o objeto vai ser vazio ou nao
         * @access public
         */
        public function factory($clear=false)
        {
            //$tmp=new $this->tabela();
            if(empty($clear))
            {
                /*$tmp->query=$this->query;
                $tmp->last_query=$this->last_query;
                $tmp->result=$this->result;
                $tmp->error=$this->error;
                foreach($this->campos as $c)
                {
                    $tmp->$c=$this->$c;
                }
				return $tmp;*/
				return $this;
            }
            else if($clear==2)
            {
                /*foreach($this->campos as $c)
                {
                    $tmp->$c=$this->$c;
                }*/
                foreach(get_class_vars(__CLASS__) as $c)
                {
                    $this->$c=in_array($c,$this->campos)?$this->$c:false;
                }
				//return $tmp;
				return $this;
            }
            //return $tmp;
            return $this;
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
            foreach($this->campos as $c)
            {
				$tmp[$c]=$this->$c;
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
            foreach($this->campos as $c)
            {
                $tmp[$c]=$c;
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
            return $this->factory();
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
         * @abstract seta junções da tabela contida no atrbuto $tabela com outras tabelas
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
            return $this->factory();
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
            $fields=empty($table_fields)?$fields:$this->get_fields();
            $query='SELECT '.(empty($fields)?'*':implode(",",$fields)).' FROM '.(empty($this->query['table'])?$this->tabela:implode(" ",$this->query['table']));
			$query.=empty($this->query['where'])?'':' WHERE '.implode("",$this->query['where']);
			$query.=empty($this->query['group'])?'':' GROUP BY '.implode(", ",$this->query['group']);
			$query.=empty($this->query['order'])?'':' ORDER BY '.implode(", ",$this->query['order']);
			$query.=empty($this->query['limit'])?'':' LIMIT '.$this->query['limit'];
            return $this->exec($query,null,$d);
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
            $fields=(empty($table_fields)&& !empty($fields)?$fields:$this->get_fields());
            $values=$this->get_values_query();
            $values=empty($table_fields) && !empty($values)?$values:$this->get_values();
            $data=array();
            $data2=array();
            foreach($fields as $k=>$v)
            {
                //$value=empty($values[$k])?(empty($this->$k)?"NULL":$this->$k):$values[$k];
                $value=empty($values[$k])?(empty($this->$k)?NULL:$this->$k):$values[$k];
                //$values[$k]=($value=="NULL"?"NULL":$value);
                $values[$k]=($value==NULL?NULL:$value);
                $data[":".$k]=$values[$k];
                $data2[]=$v."=:".$k;
            }
            $query='UPDATE '.(empty($this->query['table'])?$this->tabela:implode(" ",$this->query['table'])).' SET '.implode(", ",$data2);
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
        public function replace($table_fields=false)
        {
            $fields=$this->get_fields_query();
            $fields=(empty($table_fields)&& !empty($fields)?$fields:$this->get_fields());
            $values=$this->get_values_query();
            $values=empty($table_fields) && !empty($values)?$values:$this->get_values();
            $data=array();
            foreach($fields as $k=>$v)
            {
                $value=empty($values[$k])?(empty($this->$k)?NULL:$this->$k):$values[$k];
                $values[$k]=($value==NULL?NULL:$value);
                $data[":".$k]=$values[$k];
            }
            $query='REPLACE INTO '.$this->tabela.'('.implode(", ",array_keys($fields)).') VALUES('.implode(", ",array_keys($data)).")";
            return $this->exec($query,$data);
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
        public function delete()
        {
            $query='DELETE FROM '.(empty($this->query['table'])?$this->tabela:implode(" ",$this->query['table']));
            $query.=empty($this->query['where'])?'':' WHERE '.implode("",$this->query['where']);
            $query.=empty($this->query['group'])?'':' GROUP BY '.implode(", ",$this->query['group']);
            $query.=empty($this->query['order'])?'':' ORDER BY '.implode(", ",$this->query['order']);
            $query.=empty($this->query['limit'])?'':' LIMIT '.$this->query['limit'];
            return $this->exec($query);
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
            $fields=(empty($table_fields)&& !empty($fields)?$fields:$this->get_fields());
            $values=$this->get_values_query();
            $values=empty($table_fields) && !empty($values)?$values:$this->get_values();
            $data=array();
            foreach($fields as $k=>$v)
            {
                $value=empty($values[$k])?(empty($this->$k)?NULL:$this->$k):$values[$k];
                $values[$k]=($value==NULL?NULL:$value);
                $data[":".$k]=$values[$k];
            }
            $query='INSERT INTO '.$this->tabela.'('.implode(", ",array_keys($fields)).') VALUES('.implode(", ",array_keys($data)).")";
            //$query2='INSERT INTO '.$this->tabela.'('.implode(",",array_keys($fields)).') VALUES('.implode(",",$data).")";
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
         * @access private
         */
        protected function exec($query,$data=null,$d=false)
        {
            
            $bd=database::getPDO();
            $stmt=$bd->prepare($query);
            $this->error=$stmt->execute($data);
            //var_dump($query);
            $this->error=$this->error==false?true:false;
            //var_dump($this->error);
            if($this->error==false)
            {
                $stmt2=$stmt->fetchAll(PDO::FETCH_ASSOC);
                $this->result=$stmt2;
            }
            else
            {
                $this->result=false;
            }
            if($d)
            {
            	pr($stmt->errorInfo());
				print_r($query);
				print_r($stmt);
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
         * @param bolleam $persistencia
         * @access public
         */
        abstract public function get_array($persistencia=false);
        /**
         * @name get_object
         * @abstract retorna o resultado da consulta executada pelo metodo exec em um array de objetos
         * 
         * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
         * @since  08/02/2011 08:57:00
         * @final  09/03/2011 16:53:59
         * @subpackage cmdb
         * @version 1.0
         * @param bolleam $persistencia
         * @access public
         */
        abstract public function get_object($persistencia=false);
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
            foreach($this->campos as $attr)
            {
                $this->__set($attr,(isset($values[$attr])?$values[$attr]:false));
            }
            return $this->factory();
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
            $tmp=array();
            foreach($this->campos as $attr)
            {
                $tmp[$attr]=$this->$attr;
            }
            return $tmp;
        }
        
        public function eq($attr,$value,$sep="AND")
        {
			$value=empty($value)?$this->$attr:$value;
            return $this->__set_data_query($attr."='".$value."'","where",$sep);
        }
	} // fim da classe cmdb
