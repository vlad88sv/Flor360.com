<?php
exit (0); // Remover si llegara a ser necesario volver a utilizar

require_once ("../config.php");
require_once (__BASE__ARRANQUE."PHP/vital.php");

$c = 'SELECT codigo_compra, (cantidad*precio_grabado)+cargo_adicional+precio_envio AS monto, codigo_usuario, fecha, metodo_pago FROM flores_SSL_compra_contenedor WHERE metodo_pago IN ("kiosko_efectivo","kiosko_credito")';
$r = db_consultar($c);

while ($f = mysqli_fetch_assoc($r))
{
    $c = sprintf('INSERT INTO `flores_kiosko_transacciones` (`codigo_kiosko_articulo`, `codigo_usuario`, `operacion`, `cantidad`, `precio_grabado`, `fecha`, `transaccion`, `metodo_pago`,`descripcion`,`flag_arreglo`) VALUES(100, %s,"venta",1,%s,"%s","%s","%s","%s",1)', $f['codigo_usuario'],$f['monto'], $f['fecha'], $f['codigo_compra'], $f['metodo_pago'], 'A: #'.$f['codigo_compra']);
    //echo $c.'<br />';
    db_consultar($c);
}

?>