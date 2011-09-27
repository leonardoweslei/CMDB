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
	private $conection=false;
	function __construct()
	{
		$this->conect();
	}
	private function conect()
	{
		global $bd_data;
		$this->conection=self::getPDO();
	}
	public function getconection()
	{
		if(empty($this->conection))
		{
			$this->conect();
		}
		return $this->conection;
	}
	public function getPDO()
	{
		global $bd_data;
		return new PDO
		(
			$bd_data['dsn'].
			":host=".$bd_data['host'].
			(
				(isset($bd_data['dbname'])&&!empty($bd_data['dbname']))?
				";dbname=".$bd_data['dbname']:
				''
			),
			$bd_data['user'],
			$bd_data['passwd']
		);
	}
}
?>
