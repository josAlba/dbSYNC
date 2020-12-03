<?php

    include(__DIR__.'/_load.php');


    echo " DB SYNC";
    echo "\n----------";

    $x = new dbSYNC\lib\processos($argv);
    echo "\n----------";

    $x->commands();

    echo "\n";
    