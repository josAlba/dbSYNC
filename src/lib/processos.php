<?php

namespace dbSYNC\lib;

use dbSYNC\conf\db1;
use dbSYNC\driver\mysql;

class processos{

    private $p;
    private $mysql;
    private $rds;
    private $r;
    private $rr=2;

    public function __construct($p,$rds=true){

        $this->p        = $p;
        $this->mysql    = new mysql(db1::$user,db1::$passwd,db1::$host,db1::$db,db1::$port);
        $this->rds      = $rds;

        echo "\n [X] Conectado";
        if($rds==true){
            echo "\n [X] RDS";
        }else{
            echo "\n [X] RDS";
        }

        sleep(1);

    }

    public function commands($recursive=false){

        $this->r = $recursive;

        //No existe un comando.
        if(count($this->p) <= 1){

            $this->showHelp();
            return;
        }

        //Comandos.
        if($this->p[1]=="kill"){

            $this->command_conexiones($this->p);
            $this->recursivo();
            return;

        }else if($this->p[1]=="list"){

            $this->command_conexiones($this->p,true);
            $this->recursivo();
            return;

        }else{

            $this->showHelp();
            return;

        }

    }

    private function showHelp(){

        echo "\n";
        echo "\n-------------";
        echo "\n|  H E L P  |";
        echo "\n-------------";
        echo "\n() kill";
        echo "\n |__. Mata las conexiones de la db";
        echo "\n() litst";
        echo "\n |__. Listar conexiones";
        echo "\n() PARAMETROS";
        echo "\n |__.u :: Filtrar por usuario ( ejemple: u=admin )";
        echo "\n |__.c :: Filtrar por numero de conexiones ( ejemple: c=1 )";
        echo "\n |__.t :: Filtrar por tiempo de conexiones ( ejemple: t=1 )";
        echo "\n |__.t :: Recursividad del comando en segundos ( ejemple: t=1 )";
    }

    private function recursivo(){

        if($this->r==false){
            return;
        }

        //echo "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";
        echo "---:Esperando ".$this->rr." segundos";
        sleep($this->rr);
        system('clear');
        $this->commands(true);
        $this->recursivo();
    }

    private function command_conexiones($tp,$list=false){

        /**
         * u = Usuario.
         * c = Conexiones.
         * t = Tiempo de las conexiones.
         */
        $filtro=array(
            'u'    =>"",
            'c'    =>0,
            't'    =>0
        );
        //Nos saltamos el nombre del fichero y el primer parametro.
        for($i=2;$i<count($this->p);$i++){

            $c = $this->command_param($this->p[$i]);

            $filtro[$c['command']]=$c['param'];

        }

        if(isset($filtro['r'])){

            $this->r    =true;
            $this->rr   =$filtro['r'];

        }

        $sql="SELECT * FROM `information_schema`.`processlist` WHERE 1 ";
        if($filtro['u'] !=""){

            //Filtramos por usuario.
            $sql.=" AND `USER`='".$filtro['u']."'";

        }else if(intval($filtro['t']) > 0){

            //Filtramos por tiempo de conexion.
            $sql.=" AND `TIME` > ".$filtro['t'];

        }

        $r = $this->mysql->_db_consulta($sql);

        if(!isset($r->num_rows)){
            echo "\n No contiene filas.";
            return;
        }
        if(intval($filtro['c']) > $r->num_rows){

            //Si el numero de conexiones recuperado es menor que..., salimos.
            echo "\n No se alcanzado el maximo de conxiones.";
            return;

        }

        if($list==true){


            echo "\n ID \t\t USER \t\t COMMAND";
            foreach($r->rows as $conexion){

                $t="";$tt="";
                if(intval($conexion['ID']) < 999999){
                    $t="\t";
                }
                if(strlen($conexion['USER']) < 7){
                    $tt="\t";
                }

                echo "\n ".$conexion['ID']." \t$t ".$conexion['USER']." \t$tt ".$conexion['COMMAND'];

            }

            echo "\n_________________________";
            echo "\n Total conexiones ( ".$r->num_rows." )";

            return;

        }

        echo "\n Matando conexiones:";

        //Recoremos las conexiones.
        foreach($r->rows as $conexion){

            $pid = $conexion['ID'];
            //Mata la conexion del mysql.
            
            if($this->rds==true){
                
                //Matamos conexiones del rds
                $command = "CALL mysql.rds_kill(".$pid.")";
            
            }else{

                //Mata conexiones de mysql ( el usuario tiene que tener permiso para hacerlo )
                $command = "KILL ".$pid;
            
            }

            
            $this->mysql->_db_consulta($command);
            echo "\n[x] ".$pid." ";

        }

        

    }
    /**
     * Recupera el parametro y el valor.
     * @param string Comando ( c=100 )
     */
    private function command_param($command){

        $c="";
        $p=0;

        $pp = explode('=',$command);

        $c=$pp[0];
        if(count($pp) == 2){
            $p=$pp[1];
        }
        
        return array(
            'command'   =>$c,
            'param'     =>$p
        );

    }

}