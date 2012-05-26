<?
class database_mysql extends PDO
{
	function database_info()
	{
		$stmt			= $this->prepare("SHOW TABLES");
		if($stmt->execute())
		{
			$data		= $stmt->fetchAll();
			$relations	= array();
			if(!empty($data))
			{
				foreach($data as $table)
				{
					$relations[$table[0]] = $this->table_info($table[0]);
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
		$stmt			= $this->prepare("SHOW COLUMNS FROM $table");
		if($stmt->execute())
		{
			$data		= $stmt->fetchAll(PDO::FETCH_ASSOC);
			$fields	= array();
			if(!empty($data))
			{
				foreach($data as $data_f)
				{
					$fname										= $data_f['Field'];
					$fields[$fname]['ai']						= (substr_count(strtolower($data_f['Extra']),"auto_increment")>0?true:false);
					$fields[$fname]['default']					= $data_f['Default'];
					$fields[$fname]['key']						= $data_f['Key'];
					$fields[$fname]['name']						= $data_f['Field'];
					$fields[$fname]['null']						= $data_f['Null']=="YES"?true:false;
					$data_type									= preg_split("/[\(\) ]/", $data_f['Type']);
					if( strtolower($data_type[0])=="set" || strtolower($data_type[0])=="enum" )
					{
						$fields[$fname]['option']				= preg_split("/[\",]/", preg_replace("/['\"]/","", $data_type[1]));
					}
					else
					{
						$fields[$fname]['option']				= false;
						$tam									= isset($data_type[1])?$data_type[1]:false;
					}
					if(strtolower($data_f['Key'])=="mul")
					{
						$fields[$fname]['relation']				= $this->field_relation($table,$fname);
					}
					else
					{
						$fields[$fname]['relation']				= false;
					}
					if($fields[$fname]['key']=="UNI")
					{
						$fields[$fname]['unique']				= true;
					}
					if(isset($data_type[1]))
					{
						if(!empty($fields[$fname]['option']))
						{
							$opt								= max($fields[$fname]['option']);
							$fields[$fname]['size']				= strlen($opt);
						}
						else
						{
							
							$fields[$fname]['size']				= $data_type[1];
						}
					}
					else
					{
						$fields[$fname]['size']					= false;
					}
					$fields[$fname]['type']						= strtolower($data_type[0]);
					$fields[$fname]['unsigned']					= (substr_count(strtolower($data_f['Type']),"unsigned")>0?true:false);
					$fields[$fname]['zerofill']					= (substr_count(strtolower($data_f['Type']),"zerofill")>0?true:false);
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
		$query				= "
				SELECT
					ref.TABLE_NAME table_local,
					ref.COLUMN_NAME field_local,
					ref.REFERENCED_TABLE_NAME table_remote,
					ref.REFERENCED_COLUMN_NAME field_remote
				FROM
					information_schema.KEY_COLUMN_USAGE as ref
				WHERE
					ref.TABLE_SCHEMA=DATABASE()
					AND
					ref.TABLE_NAME='{$table}'
					AND
					ref.COLUMN_NAME='{$field}'";
		$stmt				= $this->prepare($query);
		if($stmt->execute())
		{
			$data			= $stmt->fetchAll(PDO::FETCH_ASSOC);
			$relations		= array();
			if(!empty($data))
			{
				$relations	= $data[0];
			}
			return $relations;
		}
		else
		{
			return array();
		}
	}
	private function table_relations($table,$type=3)
	{
		$c=array();
		if($type==1)
		{
			array_push($c, "ref.TABLE_NAME='{$table}'");
		}
		elseif($type==2)
		{
			array_push($c, "ref.REFERENCED_TABLE_NAME='{$table}'");
		}
		elseif($type==3)
		{
			array_push($c, "ref.TABLE_NAME='{$table}'");
			array_push($c, "ref.REFERENCED_TABLE_NAME='{$table}'");
		}
		$query				= "
				SELECT
					ref.TABLE_NAME table_local,
					ref.COLUMN_NAME field_local,
					ref.REFERENCED_TABLE_NAME table_remote,
					ref.REFERENCED_COLUMN_NAME field_remote
				FROM
					information_schema.KEY_COLUMN_USAGE as ref
				WHERE
					ref.TABLE_SCHEMA=DATABASE()
					AND
					(
						ref.TABLE_NAME is not null
						AND
						ref.COLUMN_NAME is not null
						AND
						ref.REFERENCED_TABLE_NAME is not null
						AND
						ref.REFERENCED_COLUMN_NAME is not null
					)
					AND
					(
					".implode(" OR " ,$c)."
					)";
		$stmt				= $this->prepare($query);
		if($stmt->execute())
		{
			$data			= $stmt->fetchAll(PDO::FETCH_ASSOC);
			$relations		= array();
			if(!empty($data))
			{
				$relations	= $data;
			}
			return $relations;
		}
		else
		{
			return array();
		}
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
