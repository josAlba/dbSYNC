<?php

class dbSync{

    private $db1;
    private $db2;

    public function __construct($db1,$db2){

        $this->db1 = new db($db1['user'],$db1['passwd'],$db1['host'],$db1['db'],$db1['port']);
        $this->db2 = new db($db2['user'],$db2['passwd'],$db2['host'],$db2['db'],$db2['port']);

    }

    /**
     * Sincroniza los datos de la tabla ( si la tabla ya existe con la misma estructura )
     */
    public function sincronizar_tabla($tabla,$reset=false){

        if($reset){
            //Borrar datos de la tabla.
            $this->db2->_db_consulta('TRUNCATE TABLE '.$tabla);
        }

        $tablaMaestra = $this->db1->_db_consulta(
            "SELECT * FROM ".$tabla
        );

        foreach($tablaMaestra->rows as $fila){

            $exist=false;

            //Comprobar que no existe el registro.
            if(!$reset){

                $where =" ";
                $columnas = array_keys($fila);
                for($i=0;$i<count($columnas);$i++){

                    if($where !=" "){
                        $where .= " AND ";
                    }

                    //Añadimos el parametro.
                    $where .= $columnas[$i]."='".$fila[$columnas[$i]]."'";

                }
                
                $if = "SELECT * FROM ".$tabla." WHERE ".$where;
                if($this->db2->_db_consulta($if)->num_rows >0){
                    $exist=true;
                }

            }
            // ./Comprobar que no existe el registro.

            if($exist==false){
                //Insertamos la fila.
                $sql="INSERT INTO ".$tabla." (".implode(", ",array_keys($fila)).") VALUES ('".implode("', '",array_values($row))."')";
                //Lanzar peticion.
                $this->db2->_db_consulta($sql);
            }

        }

    }

    public function sincronizar_db($db){

    }


}

class db{

    private $link;
	
	private $V_principal = array();
	
	public function _limpiar($texto)
	{
		return $this->link->real_escape_string($texto);
	}
	
	public function __construct($user,$passwd,$host,$db,$port=3306)
	{
		$V_principal['_BD'] = array();
		//HOSTING BASE DE DATOS.
		$V_principal['_BD']['BD_HOST']      = $host;
		//USUARIO BASE DE DATOS.
		$V_principal['_BD']['BD_USUARIO']   = $user;
		//CONTRASENA BASE DE DATOS.
		$V_principal['_BD']['BD_PASSWORD']  = $passwd;
		//PUERTO BASE DE DATOS.
		$V_principal['_BD']['BD_PUERTO']    = $port;
		//BASE DE DATOS.
		$V_principal['_BD']['BD_BD']        = $db;
		$this->link = new \mysqli($V_principal['_BD']['BD_HOST'], $V_principal['_BD']['BD_USUARIO'], $V_principal['_BD']['BD_PASSWORD'], $V_principal['_BD']['BD_BD'], $V_principal['_BD']['BD_PUERTO']);
		$this->link->set_charset("utf8");
	}
	
	public function __destruct() 
	{
		$this->link->close();
	}
	
	public function _ultimo_id(){
		return $this->link->insert_id;
	}
	
	public function _db_consulta($sql)
	{
		
		
		$result = $this->link->query($sql);
		if($result instanceof mysqli_result)
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
		//$this->link->close();
		return $result1;
	}

}