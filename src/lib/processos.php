<?php

namespace dbSYNC\lib;

use dbSYNC\conf\db1;
use dbSYNC\conf\alert1;
use dbSYNC\driver\mysql;
use dbSYNC\mensajes\telegram;
use dbSYNC\conf\telegram1;

class processos{

    /**
     * Script lanzado.
     */
    private $p;
    /**
     * Objeto mysql.
     */
    private $mysql;
    /**
     * Modo RDS ( Amazon ).
     */
    private $rds;

    /**
     * Si es recursiva.
     */
    private $r;
    /**
     * Segundos de espera.
     */
    private $rr=2;

    /**
     * Parametros del script.
     */
    private $parametros;

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

        $this->ValidarParametros();
        

        sleep(1);

    }

    private function ValidarParametros(){

        $this->parametros=array(
            'u'    =>"",
            'c'    =>0,
            't'    =>0
        );
        //Nos saltamos el nombre del fichero y el primer parametro.
        for($i=2;$i<count($this->p);$i++){

            $c = $this->command_param($this->p[$i]);

            $this->parametros[$c['command']]=$c['param'];

        }
        if(isset($this->parametros['r'])){

            $this->r    =true;
            $this->rr   =$this->parametros['r'];

        }
        echo "\n [X] Parametros";

    }

    /**
     * Comprueba el comando y lo lanza.
     */
    public function commands(){

        //No existe un comando.
        if(count($this->p) <= 1){

            $this->showHelp();
            return;
        }

        //Comprueba si tiene que ser recursiva.
        if($this->r==true){
            if($this->vuelta==0){
                $this->recursivo();
                return;
            }
        }

        //Comandos.
        if($this->p[1]=="kill"){

            $this->command_conexiones();
            return;

        }else if($this->p[1]=="list"){

            $this->command_conexiones(true);
            return;

        }else{

            $this->showHelp();
            return;

        }

    }

    /**
     * Muestra la ayuda de comandos en la terminal.
     */
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

    private $vuelta=0;
    private function recursivo(){

        if($this->r==false){
            return;
        }
        $this->vuelta++;
        //Nos metemos en recursivo.
        while (true) {

            system('clear');
            $this->commands();
            echo "\n---:Esperando ".$this->rr." segundos";
            sleep($this->rr);

        }
    }

    private $ultimoAviso=0;
    private function alert($conexiones=0){

        if(alert1::$activo==false){
            return;
        }

        $time   = time();

        if( floatval($time) < floatval(floatval(alert1::$interval) + $this->ultimoAviso) ){
            return;
        }

        $this->ultimoAviso=$time;


        $alertas = alert1::$limit;

        for($i=0;$i<count($alertas);$i++){

            if($alertas[$i]<$conexiones){

                if(telegram1::$token!=""){ 

                    echo "\n---: Enviando aviso.";
                    telegram::sendMessage(
                        "DB Avisa - ".$conexiones." conexiones"
                    );

                }
                break;
            }

        }

    }

    /**
     * Funcion para controlar los procesos activos de la base de datos.
     * @param boolean $list Si tiene que mostrar la consulta.
     */
    private function command_conexiones($list=false){

        $sql="SELECT * FROM `information_schema`.`processlist` WHERE 1 ";
        if($this->parametros['u'] !=""){

            //Filtramos por usuario.
            $sql.=" AND `USER`='".$this->parametros['u']."'";

        }else if(intval($this->parametros['t']) > 0){

            //Filtramos por tiempo de conexion.
            $sql.=" AND `TIME` > ".$this->parametros['t'];

        }

        $r = $this->mysql->_db_consulta($sql);

        if(!isset($r->num_rows)){
            echo "\n No contiene filas.";
            return;
        }
        if(intval($this->parametros['c']) > $r->num_rows){

            //Si el numero de conexiones recuperado es menor que..., salimos.
            echo "\n No se alcanzado el maximo de conxiones.";
            return;

        }

        $this->alert($r->num_rows);

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

        if(telegram1::$token!=""){        
         
            if($r->num_rows > 0){
               
                telegram::sendMessage(
                    "DB Dice - $r->num_rows processos matados."
                );
                
            }        

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