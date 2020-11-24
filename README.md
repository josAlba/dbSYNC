# Sincronizar mySql
Sincroniza dos bases de datos mySql. Sincronizando tablas o bases de datos enteras.

## Sincronizar tablas
Sincroniza dos tablas de un host a otro.
```php

include(__DIR__.'/dbSYNC.php');

$sync = new dbSync(
    array(
        'user'  =>'',
        'passwd'=>'',
        'host'  =>'',
        'db'    =>'',
        'port'  =>''
    ),
    array(
        'user'  =>'',
        'passwd'=>'',
        'host'  =>'',
        'db'    =>'',
        'port'  =>''
    )
);

$sync->sincronizar_tabla('TABLA');


```