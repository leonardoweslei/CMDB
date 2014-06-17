<?php
class database_sqlite extends PDO
{
	function __construct($db)
	{
		parent::__construct('sqlite:'.$db);
	}
	function database_info()
	{
		$stmt			= $this->prepare("SELECT name FROM sqlite_master WHERE type='table'");
		if($stmt && $stmt->execute())
		{
			$data		= $stmt->fetchAll();
			$relations	= array();
			if(!empty($data))
			{
				foreach($data as $table)
				{
					if($table[0]!="sqlite_sequence")
					{
						$relations[$table[0]] = $this->table_info($table[0]);
					}
				}
			}
			return $relations;
		}
		else
		{
			return array();
		}
	}
	function table_info($table)
	{
		$stmt			= $this->prepare("PRAGMA table_info($table)");
		if($stmt && $stmt->execute())
		{
			$data		= $stmt->fetchAll(PDO::FETCH_ASSOC);
			$fields	= array();
			if(!empty($data))
			{
				foreach($data as $data_f)
				{
					$fname										= $data_f['name'];
					$fields[$fname]['ai']						= $this->field_ai($table, $fname)!==false && $data_f['pk']=="1"?true:false;
					$fields[$fname]['default']					= $data_f['dflt_value'];
					$fields[$fname]['key']						= $data_f['pk']=="1"?"PRI":"";
					$fields[$fname]['name']						= $data_f['name'];
					$fields[$fname]['null']						= $data_f['notnull']!=1 && $data_f['pk']!="1"?true:false;
					$data_type									= preg_split("/[\(\) ]/", $data_f['type']);
					if(isset($data_type[1]) && preg_replace("/[0-9]/","",$data_type[1])!="")
					{
						$t=false;
						for($x=1; $x<count($data_type);$x+=2)
						{
							$t2=$data_type[$x];
							$data_type[$x]=$t;
							$t=$data_type[$x+1];
							$data_type[$x+1]=$t2;
						}
					}
					$fields[$fname]['option']					= false;
					$tam										= isset($data_type[1])?$data_type[1]:false;
					$fields[$fname]['relation']					= $this->field_relation($table,$fname);
					$fields[$fname]['unique']					= $data_f['pk']=="1" || $this->field_unique($table, $fname);
					if(isset($data_type[1]))
					{
						$fields[$fname]['size']					= $data_type[1];
					}
					else
					{
						$fields[$fname]['size']					= false;
					}
					$fields[$fname]['type']						= strtolower(array_shift(explode(" ",$data_type[0])));
					$fields[$fname]['unsigned']					= substr_count(strtolower(implode("",$data_type)), "unsigned")>0?true:false;
					$fields[$fname]['zerofill']					= false;
				}
			}
			return $fields;
		}
		else
		{
			return array();
		}
	}
	function field_relation($table,$field)
	{
		$query				= "PRAGMA foreign_key_list({$table})";
		$stmt				= $this->prepare($query);
		if($stmt && $stmt->execute())
		{
			$data			= $stmt->fetchAll(PDO::FETCH_ASSOC);
			$relations		= array();
			if(!empty($data))
			{
				foreach ($data as $r)
				{
					if($r['from']==$field)
					{
						return array('table_local'=>$table,'field_local'=>$field,'table_remote'=>$r['table'],'field_remote'=>$r['to']);
					}
				}
			}
		}
		return false;
	}
	function field_unique($table,$field)
	{
		$stmt			= $this->prepare("PRAGMA index_list($table)");
		if($stmt && $stmt->execute())
		{
			$data			= $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($data as $index)
			{
				$stmt2			= $this->prepare("PRAGMA index_info({$index['name']})");
				$stmt2->execute();
				$data			= $stmt2->fetchAll(PDO::FETCH_ASSOC);
				foreach($data as $fdata)
				{
					if($field==$fdata['name'])
					{
						return $index['unique'];
					}
				}
			}
		}
		return false;
	}
	function field_ai($table,$field)
	{
		$stmt			= $this->prepare("select * from sqlite_sequence where name='$table';");
		if($stmt && $stmt->execute())
		{
			$data			= $stmt->fetch(PDO::FETCH_ASSOC);
			return $data['seq'];
		}
		return false;
	}
	private function table_relations($table,$type=3)
	{
		/**
		 * TODO implementar este metodo
		 */
		return array();
	}
	function table_relations_all($table)
	{
		return $this->table_relations($table,3);
	}
	function table_relations_to($table)
	{
		return $this->table_relations($table,2);
	}
	function table_relations_from($table)
	{
		return $this->table_relations($table,1);
	}
}
?>
