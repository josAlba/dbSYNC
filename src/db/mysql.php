<?php

namespace dbSYNC\driver;

class mysql{

    private $link;
	
	public function __construct(
		$user,
		$passwd,
		$host,
		$db,
		$port=3306){

		$this->link = new \mysqli($host, $user, $passwd, $db, $port);
		$this->link->set_charset("utf8");

	}
	
	public function _limpiar($texto){
		return $this->link->real_escape_string($texto);
	}

	public function __destruct(){
		$this->link->close();
	}
	
	public function _ultimo_id(){
		return $this->link->insert_id;
	}
	
	public function _db_consulta($sql){
		
		$result = $this->link->query($sql);
		if($result instanceof \mysqli_result)
		{
				$data = array();

				while ($row = $result->fetch_assoc()) {
					$data[] = $row;
				}
				
				$result1 = new \stdClass();
				$result1->num_rows = $result->num_rows;
				$result1->row = isset($data[0]) ? $data[0] : array();
				$result1->rows = $data;
				
		}
		else
		{  	
			return false;
		}
		return $result1;
	}

}