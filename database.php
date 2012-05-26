<?php
   /**
     * classe database
     * Classe clase retorna instancia do PDO de acordo com as configurações definidas em um array global
     *
     * @author Leonardo Weslei Diniz <leonardoweslei@gmail.com>
     * @since  27/09/2011 14:57:00
     * @subpackage database
     * @version 1.0
     * @name database
     */
class database
{
	private $dsn;
	private $user;
	private $passwd;
	private $host;
	private $dbname;
	private $conection=false;
	// $dsn="mysql", $host="localhost", $user="root", $passwd=false, $dbname=false 
	function database()
	{
		$params=array();
		if(func_num_args()==0)
		{
			global $database_params;
			$params=$database_params;
		}
		else
		{
			$args=func_get_args();
			$params=array(
				'dsn'=>isset($args[0])?$args[0]:"mysql",
				'host'=>isset($args[1])?$args[1]:"localhost",
				'user'=>isset($args[2])?$args[2]:"root",
				'passwd'=>isset($args[3])?$args[3]:"",
				'dbname'=>isset($args[4])?$args[4]:false
			);
		}
		$this->set_attr($params);
	}
	private function set_attr($values)
	{
		foreach ($values as $k=>$v)
		{
			$this->$k=$v;
		}
	}
	public function getconection()
	{
		if(empty($this->conection))
		{
			$this->conection=$this->getPDO();
		}
		return $this->conection;
	}
	public function getPDO()
	{
		if(is_file(dirname(__FILE__)."/dsn/{$this->dsn}.php"))
		{
			require_once(dirname(__FILE__)."/dsn/{$this->dsn}.php");
			$dsn="database_{$this->dsn}";
			return new $dsn
			(
			$this->dsn.
			":host=".$this->host.
			(
			(isset($this->dbname)&&!empty($this->dbname))?
			";dbname=".$this->dbname:
								''
			),
			$this->user,
			$this->passwd
			);
		}
		else
		{
			return new PDO
			(
				$this->dsn.
				":host=".$this->host.
				(
					(isset($this->dbname)&&!empty($this->dbname))?
					";dbname=".$this->dbname:
					''
				),
				$this->user,
				$this->passwd
			);
		}
	}
}
?>
