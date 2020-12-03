<?php

namespace dbSYNC\lib;

use dbSYNC\conf\db1;
use dbSYNC\driver\mysql;

class sincronizar{

    private $db1;
    private $db2;

    public function __construct($db1,$db2){

        $this->db1 = new mysql(db1::$user,db1::$passwd,db1::$host,db1::$db,db1::$port);
        $this->db2 = new mysql(db2::$user,db2::$passwd,db2::$host,db2::$db,db2::$port);

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
                $sql="INSERT INTO ".$tabla." (".implode(", ",array_keys($fila)).") VALUES ('".implode("', '",array_values($fila))."')";
                //Lanzar peticion.
                $this->db2->_db_consulta($sql);
            }

        }

    }

    public function sincronizar_db($db){
    }


}