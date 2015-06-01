<?php
if (!isset($_GET['codigos']))
    return;

$codigos = db_codex($_GET['codigos']);

$cContenedor = sprintf('SELECT t1.`codigo_producto`, t2.`codigo_variedad`, t1.`titulo`, t2.`descripcion` AS "titulo_variedad", t1.`descripcion`, FORMAT(t2.`precio` * 0.75,2) AS "precio_descuento", t2.precio, t2.`foto` FROM `flores_producto_contenedor` AS t1 LEFT JOIN `flores_producto_variedad` AS t2 USING(codigo_producto) WHERE codigo_producto IN (%s)', $codigos);
$rContenedor = db_consultar($cContenedor);

echo '<button id="exportar">EXPORTAR</button><hr style="margin:10px 0;" />';
echo '<table id="tabla_exportacion" class="tabla-estandar">';
echo sprintf('<tr><th></th><th>NOMBRE</th><th>CODIGO DE BARRA</th><th>DESCRIPCION</th><th>PRECIO</th><th>PRECIO SUGERIDO</th><th>COSTO S. DOMICILIO</th></tr>');
while ( $contenedor = db_fetch($rContenedor))
{
    $titulo = (strtolower($contenedor['titulo']) == strtolower($contenedor['titulo_variedad']) ? $contenedor['titulo'] : $contenedor['titulo'] . ' ' . $contenedor['titulo_variedad'] );
    
    echo sprintf('<tr height="150" rowspan="2"><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',$contenedor['codigo_producto'].'.'.$contenedor['codigo_variedad'],$titulo,$contenedor['codigo_variedad'],'','$'.$contenedor['precio_descuento'], '$'.$contenedor['precio'], '$0.00');
}
echo '</table>';