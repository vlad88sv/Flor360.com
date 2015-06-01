<?php
protegerme();
// Productos mas vistos
$c = 'SELECT var.foto AS foto, con.`titulo`,con.`codigo_producto`, (SELECT COUNT(*) FROM `'.db_prefijo.'visita` AS vis WHERE vis.codigo_producto=con.codigo_producto AND `fecha` > (NOW() - INTERVAL 2 MONTH)) AS vistas FROM `'.db_prefijo.'producto_contenedor` AS con LEFT JOIN `'.db_prefijo.'producto_variedad` AS var USING(codigo_producto) WHERE 1 GROUP BY var.codigo_producto ORDER BY vistas DESC';
$pmv = db_consultar($c);

// Productos mas comprados
$c = 'SELECT var.foto AS foto, con.`titulo`, var.`descripcion`, con.`codigo_producto`, SUM(IF(coc.codigo_variedad IS NULL, 0, 1)) AS vistas FROM `flores_producto_contenedor` AS con LEFT JOIN `flores_producto_variedad` AS var USING(codigo_producto) LEFT JOIN flores_SSL_compra_contenedor coc USING(codigo_variedad) WHERE `fecha` > (NOW() - INTERVAL 2 MONTH) GROUP BY con.codigo_producto ORDER BY vistas DESC';
$pmc = db_consultar($c);

// Categorias mas compradas
$c = 'SELECT  COUNT(*) AS compras, fc.titulo FROM `flores_producto_contenedor` AS con LEFT JOIN `flores_producto_variedad` AS var USING(codigo_producto) LEFT JOIN `flores_productos_categoria` AS fpc USING(codigo_producto) LEFT JOIN flores_categorias AS fc USING(codigo_categoria) LEFT JOIN flores_SSL_compra_contenedor coc USING(codigo_variedad) WHERE descontinuado="no" GROUP BY fc.codigo_categoria ORDER BY compras DESC';
$cmc = db_consultar($c);


$c = 'SELECT MIN(precio) AS min, FORMAT(AVG(precio),2) avg, MAX(precio) max FROM '.db_prefijo.'producto_variedad LEFT JOIN '.db_prefijo.'producto_contenedor USING(codigo_producto) WHERE precio > 0 AND descontinuado="no"';
$epre = db_consultar($c);
$epreassoc = mysqli_fetch_assoc($epre);

$c = 'SELECT t1.codigo_producto, t2.titulo, t2.descripcion, t1.foto, t1.precio FROM '.db_prefijo.'producto_variedad AS t1 LEFT JOIN '.db_prefijo.'producto_contenedor AS t2 USING(codigo_producto) WHERE precio > 0 AND descontinuado="no" ORDER BY precio ASC LIMIT 1';
$emin = mysqli_fetch_assoc(db_consultar($c));

$c = 'SELECT t1.codigo_producto, t2.titulo, t2.descripcion, t1.foto, t1.precio FROM '.db_prefijo.'producto_variedad AS t1 LEFT JOIN '.db_prefijo.'producto_contenedor AS t2 USING(codigo_producto) WHERE precio > 0 AND descontinuado="no" ORDER BY precio DESC LIMIT 1';
$emax = mysqli_fetch_assoc(db_consultar($c));

$c = 'SELECT COUNT(codigo_usuario) AS cantidad, nombre_completo, FORMAT(SUM(precio_grabado+precio_envio+cargo_adicional),2) AS monto_vendido, FORMAT((SELECT COALESCE(SUM(cantidad*precio_grabado),0) FROM flores_kiosko_transacciones AS t2 WHERE t2.codigo_usuario=t1.codigo_usuario AND t2.operacion="venta" AND flag_arreglo=0),2) AS monto_vendido_menudeo, FORMAT(SUM(precio_grabado+precio_envio+cargo_adicional)+(SELECT COALESCE(SUM(cantidad*precio_grabado),0) FROM flores_kiosko_transacciones AS t2 WHERE t2.codigo_usuario=t1.codigo_usuario AND t2.operacion="venta" AND flag_arreglo=0),2) AS total FROM '.db_prefijo.'SSL_compra_contenedor AS t3 LEFT JOIN '.db_prefijo.'usuarios AS t1 USING(codigo_usuario) WHERE codigo_usuario > 0 AND t3.flag_eliminado=0 AND t3.flag_suspendido=0 GROUP BY codigo_usuario ORDER BY cantidad DESC';
$evendedores = db_consultar($c);

$vendedores = '';
while ($f = mysqli_fetch_assoc($evendedores))
{
    $vendedores .= '<tr><td>'.$f['nombre_completo'].'</td><td>'.$f['cantidad'].'</td><td>$'.$f['monto_vendido'].'</td><td>$'.$f['monto_vendido_menudeo'].'</td><td>$'.$f['total'].'</td></tr>';
}

$c = 'SELECT COUNT(*) AS cantidad, IF(codigo_usuario=0, "*Sin atender*", nombre_completo) AS nombre_completo, codigo_usuario FROM flores_chat LEFT JOIN flores_usuarios USING(codigo_usuario) GROUP BY codigo_usuario ORDER BY cantidad DESC';
$echats = db_consultar($c);

$chats = '';
while ($f = mysqli_fetch_assoc($echats))
{
    $chats .= '<tr><td>'.$f['nombre_completo'].'</td><td>'.$f['cantidad'].'</td></tr>';
}


$c = "SELECT COUNT(*) AS 'cantidad', DATE_FORMAT(t1.fecha,'%M/%y') AS 'mes', SUM(IF(t1.flag_arreglo=0, t1.cantidad*t1.precio_grabado, 0)) AS 'monto_vendido_menudeo', SUM(IF(t1.flag_arreglo=1, t1.cantidad*t1.precio_grabado, 0)) AS 'monto_vendido' FROM flores_kiosko_transacciones AS t1 LEFT JOIN flores_usuarios USING(codigo_usuario) LEFT JOIN flores_SSL_compra_contenedor AS t3 ON t1.transaccion=t3.codigo_compra WHERE t1.operacion='venta' AND nivel=7 AND t3.flag_eliminado=0 AND t3.flag_suspendido=0  GROUP BY DATE_FORMAT(t1.fecha,'%M/%y') ORDER BY t1.fecha DESC";
$ekiosko = db_consultar($c);

$kiosko = "";
while ($f = mysqli_fetch_assoc($ekiosko))
{
    $kiosko .= '<tr><td>'.$f['mes'].'</td><td>'.$f['cantidad'].'</td><td>$'.$f['monto_vendido'].'</td><td>$'.$f['monto_vendido_menudeo'].'</td><td>$'.($f['monto_vendido']+$f['monto_vendido_menudeo']).'</td></tr>';
}

echo '<h1>Chats</h1>';
echo '<table class="tabla-estandar ancha tabla-centrada zebra">';
echo '<tr><th>Agente</th><th>Cantidad chats atendidos</th></tr>';
echo $chats;
echo '</table>';

echo '<h1>Vendedores</h1>';
echo '<table class="tabla-estandar ancha tabla-centrada zebra">';
echo '<tr><th>Vendedor</th><th>Cantidad arreglos vendidos</th><th>Monto vendido (arreglos)</th><th>Monto vendido (menudeo)</th><th>Total</th></tr>';
echo $vendedores;
echo '</table>';

echo '<h1>Ventas totales por mes (kiosko)</h1>';
echo '<table class="tabla-estandar ancha tabla-centrada zebra">';
echo '<tr><th>Mes</th><th>Cantidad arreglos vendidos</th><th>Monto vendido (arreglos)</th><th>Monto vendido (menudeo)</th><th>Total</th></tr>';
echo $kiosko;
echo '</table>';

$c = "SELECT DATE_FORMAT(fecha,'%M/%y') AS mes, COUNT(cantidad) AS cantidadTotal, FORMAT(_precio_compra(t1.codigo_compra),2) AS monto_vendido, FORMAT(SUM(IF(flag_cobrado=1 AND flag_eliminado=0 AND flag_suspendido=0,_precio_compra(t1.codigo_compra),0)),2) AS monto_vendido_real, FORMAT(COALESCE(SUM(IF(metodo_pago IN ('tarjeta', 'kiosko_credito') AND flag_cobrado=1 AND flag_eliminado=0 AND flag_suspendido=0,_precio_compra(t1.codigo_compra),0)),0),2) AS monto_vendido_credito, FORMAT((SELECT COALESCE(SUM(cantidad*precio_grabado),0) FROM flores_kiosko_transacciones AS t2 WHERE DATE_FORMAT(t2.fecha,'%M/%y')=DATE_FORMAT(t1.fecha,'%M/%y') AND t2.flag_arreglo=0 AND t2.operacion='venta'),2) AS monto_vendido_menudeo, (COALESCE(SUM(IF(flag_cobrado=1,(precio_grabado+precio_envio+cargo_adicional),0)),0)+(SELECT COALESCE(SUM(cantidad*precio_grabado),0) FROM flores_kiosko_transacciones AS t2 WHERE DATE_FORMAT(t2.fecha,'%M/%y')=DATE_FORMAT(t1.fecha,'%M/%y') AND t2.flag_arreglo=0 AND t2.operacion='venta')) AS total FROM flores_SSL_compra_contenedor AS t1 GROUP BY DATE_FORMAT(t1.fecha,'%M/%y') ORDER BY t1.fecha DESC";
$emes = db_consultar($c);

$mestotal = '';
while ($f = mysqli_fetch_assoc($emes))
{
    $mestotal .= '<tr><td>'.$f['mes'].'</td><td>'.$f['cantidadTotal'].'</td><td>$'.$f['monto_vendido'].'</td><td>$'.$f['monto_vendido_real'].'</td><td>$'.$f['monto_vendido_credito'].'</td><td>$'.$f['monto_vendido_menudeo'].'</td><td>$'.($f['total']).'</td></tr>';
}

echo '<h1>Ventas totales por mes (kiosko+web)</h1>';
echo '<table class="tabla-estandar ancha tabla-centrada zebra">';
echo '<tr><th>Mes</th><th>Cantidad arreglos vendidos</th><th>Monto vendido (arreglos)</th><th>Monto vendido (real, arreglos)</th><th>Monto vendido (real, arreglos, POSes)</th><th>Monto vendido (menudeo)</th><th>Total</th></tr>';
echo $mestotal;
echo '</table>';


$c = "SELECT DATE_FORMAT(fecha,'%M/%y') AS mes, COUNT(cantidad) AS cantidadTotal, IF(flag_cobrado=1 AND flag_eliminado=0 AND flag_suspendido=0, _precio_compra(t1.codigo_compra), 0) AS monto_vendido_real, FORMAT(IF(metodo_pago = 'tarjeta' AND flag_cobrado=1 AND flag_eliminado=0 AND flag_suspendido=0, _precio_compra(t1.codigo_compra), 0),2) AS monto_vendido_credito, FORMAT(COALESCE(SUM(IF(metodo_pago = 'kiosko_credito' AND flag_cobrado=1 AND flag_eliminado=0 AND flag_suspendido=0,_precio_compra(t1.codigo_compra),0)),0),2) AS monto_vendido_credito_kiosko FROM flores_SSL_compra_contenedor AS t1 LEFT JOIN flores_registro AS t2 USING(codigo_compra) WHERE t2.grupo='estado.cobrado' GROUP BY DATE_FORMAT(t2.timestamp,'%M/%y') ORDER BY t2.timestamp DESC";
$emes = db_consultar($c);

$mestotal = '';
while ($f = mysqli_fetch_assoc($emes))
{
    $mestotal .= '<tr><td>'.$f['mes'].'</td><td>'.$f['cantidadTotal'].'</td><td>$'.$f['monto_vendido_real'].'</td><td>$'.$f['monto_vendido_credito'].'</td></tr>';
}

echo '<h1>Ventas totales por mes según fecha de cobro (kiosko+web)</h1>';
echo '<table class="tabla-estandar ancha tabla-centrada zebra">';
echo '<tr><th>Mes</th><th>Cantidad arreglos cobrados</th><th>Monto vendido (real, arreglos)</th><th>Monto vendido (POS Esmeralda)</th></tr>';
echo $mestotal;
echo '</table>';


$mestotal = '';
while ($f = mysqli_fetch_assoc($emes))
{
    $mestotal .= '<tr><td>'.$f['mes'].'</td><td>'.$f['cantidadTotal'].'</td><td>$'.$f['monto_vendido'].'</td><td>$'.$f['monto_vendido_real'].'</td><td>$'.$f['monto_vendido_credito'].'</td><td>$'.$f['monto_vendido_menudeo'].'</td><td>$'.($f['total']).'</td></tr>';
}


echo '<h1>Precios</h1>';
echo sprintf('El precio mas bajo en el sistema es: $%s, el precio promedio es: $%s, el mas alto es: $%s. Arreglos descontinuados no incluidos.',$epreassoc['min'],$epreassoc['avg'],$epreassoc['max']);
echo '<hr />';
echo '<table class="tabla-estandar ancha tabla-centrada">';
echo '<td>Arreglo mas barato:<br /><a href="'.PROY_URL.URL_SUFIJO_VITRINA.SEO($emin['titulo'].'-'.$emin['codigo_producto']).'"><img src="'.imagen_URL($emin['foto'],133,200).'" /></a></td>';
echo '<td>Arreglo mas caro:<br /><a href="'.PROY_URL.URL_SUFIJO_VITRINA.SEO($emax['titulo'].'-'.$emax['codigo_producto']).'"><img src="'.imagen_URL($emax['foto'],133,200).'" /></a></td>';
echo '</table>';

echo '<h1>Categorìas mas comprados</h1>';
echo '<table class="tabla-estandar ancha tabla-centrada zebra">';
echo '<tr><th>Categoría</th><th>No. Compras</th></tr>';
while ($f = mysqli_fetch_assoc($cmc))
	echo sprintf('<tr><td>%s</td><td>%s</td></tr>', $f['titulo'], $f['compras']);
echo '</table>';


echo '<h1>Los productos mas comprados en los últimos 2 meses</h1>';
echo '<table class="tabla-estandar ancha tabla-centrada zebra">';
echo '<tr><th>Fotografia</th><th>Titulo del producto</th><th>No. Compras</th></tr>';
while ($f = mysqli_fetch_assoc($pmc))
	echo sprintf('<tr><td>%s</td><td>%s</td><td>%s</td></tr>', '<img src="'.imagen_URL($f['foto'],133,200).'" />', '#'.$f['codigo_producto'].' ~ <a href="'.PROY_URL.URL_SUFIJO_VITRINA.SEO($f['titulo'].'-'.$f['codigo_producto']).'">'.$f['titulo'] . ' - ' . $f['descripcion'].'</a>', $f['vistas']);
echo '</table>';


echo '<h1>Los productos mas vistos en los últimos 2 meses</h1>';
echo '<table class="tabla-estandar ancha tabla-centrada zebra">';
echo '<tr><th>Fotografia</th><th>Titulo del producto</th><th>No. Visitas</th></tr>';
while ($f = mysqli_fetch_assoc($pmv))
	echo sprintf('<tr><td>%s</td><td>%s</td><td>%s</td></tr>', '<img src="'.imagen_URL($f['foto'],133,200).'" />', '#'.$f['codigo_producto'].' ~ <a href="'.PROY_URL.URL_SUFIJO_VITRINA.SEO($f['titulo'].'-'.$f['codigo_producto']).'">'.$f['titulo'].'</a>', $f['vistas']);
echo '</table>';
?>
