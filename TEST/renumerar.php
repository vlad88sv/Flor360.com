<?php
exit (0); // Remover si llegara a ser necesario volver a utilizar

require_once ("../config.php");
require_once (__BASE__ARRANQUE."PHP/vital.php");

$c = 'SELECT `codigo_compra`, `fecha_entrega` FROM `flores_SSL_compra_contenedor` ORDER BY fecha_entrega ASC, fecha ASC ';
$r = db_consultar($c);

while ($f = mysqli_fetch_assoc($r))
{
    $x = db_consultar('SELECT (MAX(orden)+1) AS max_orden FROM flores_SSL_compra_contenedor WHERE `fecha_entrega` = "'.$f['fecha_entrega'] .'" GROUP BY `fecha_entrega`');
    $fx = mysqli_fetch_assoc($x);
    db_consultar('UPDATE `flores_SSL_compra_contenedor` SET `orden` = '. $fx['max_orden'] . ' WHERE codigo_compra='.$f['codigo_compra']);
    
    echo $f['codigo_compra']. ' - '. $f['fecha_entrega'] . ' - #' . $fx['max_orden'].'<br />';
    
}
?>