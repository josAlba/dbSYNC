# dbSync
Conjunto de scripts para control de bases de datos mysql.

## Indice
- [**Cargar libreria**](#item0)
- [**Control de procesos**](#item1)
- [**Sincronizar**](#item2)

<a name="item0"></a>
## Cargar libreria
```php
<?php
    include('src/_load.php');
```

<a name="item1"></a>
## Control de procesos
Controla los procesos de la base de datos mysql, pudiendo listarlos y matar procesos ( en mysql y rds )
### Cargar libreria

```php
<?php

    include(__DIR__.'/_load.php');
    use dbSYNC\lib\processos;

    echo " DB SYNC";
    echo "\n----------";

    $x = new processos($argv);
    echo "\n----------";

    $x->commands();

    echo "\n";
```
### Listar procesos
Param mostrar los procesos es necesario pasar los parametros por consola o un array.
```
php index.php list
```
### Matar procesos
Param mostrar los procesos es necesario pasar los parametros por consola o un array.
```bash
php index.php kill
```
### Paramteros
#### Usuario a filtrar
Parametro **u**, Filtra por un usuario especifico indicando a que usuario se le a de aplicar el resto de filtros.
```bash
php index.php <kill><list> u=admin
```
### Tiempo minimo
Parametro **t**, Filtra las consultas que lleven más de x tiempo. 
```bash
php index.php <kill><list> t=1000
```
### Conexiones abiertas
Parametro **c**, Filtra para que solo se aplique la accion si hay más de x cnexiones abiertas.
```bash
php index.php <kill><list> c=10
```
### Recursividad
Parametro **r**, Indica que la accion sera recursiva y cuantos segundos se tiene que esperar entre accion y accion.
```bash
php index.php <kill><list> r=1
```


<a name="item2"></a>
## Sincronizar mySql
Sincroniza dos bases de datos mySql. Sincronizando tablas o bases de datos enteras.
<br>
La sincronizacion de tablas se encuentra en **lib/table

### Sincronizar tablas
Sincroniza dos tablas de un host a otro.
```php
include(__DIR__.'/_load.php');
use dbSYNC\lib\sincronizar;

$sync = new sincronizar(
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