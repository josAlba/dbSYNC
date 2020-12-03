<?php

    namespace dbSYNC\conf;

    class db1{

        public static $user     ="";
        public static $passwd   ="";
        public static $host     ="";
        public static $port     ="";
        public static $db       ="";

    }
    
    class alert1{

        public static $activo  =false;

        public static $limit   =array(
            50
        );

        public static $interval =10;

    }

    class telegram1{

        public static $token    = "";
        public static $sala     = 0;
    }

    //Sincronizador
    class db2{

        public static $user     ="";
        public static $passwd   ="";
        public static $host     ="";
        public static $port     ="";
        public static $db       ="";

    }