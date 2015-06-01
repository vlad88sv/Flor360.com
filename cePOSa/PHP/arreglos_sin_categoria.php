<?php

// Productos sin categoria
$c = 'SELECT var.foto AS foto, con.`titulo`, con.`codigo_producto` FROM `'.db_prefijo.'producto_contenedor` AS con LEFT JOIN `'.db_prefijo.'producto_variedad` AS var USING(codigo_producto) WHERE descontinuado="no" AND codigo_producto NOT IN (SELECT codigo_producto FROM `flores_productos_categoria`) GROUP BY var.codigo_producto';
$pmv = db_consultar($c);

echo '<h1>Productos que <b>no</b> descontinuados pero que no los ve el público porque no estan en ninguna categoría</h1>';
echo '<table class="tabla-estandar ancha tabla-centrada">';
echo '<tr><th>Fotografia</th><th>Titulo del producto</th></tr>';
while ($f = mysqli_fetch_assoc($pmv))
	echo sprintf('<tr><td>%s</td><td>%s</td></tr>', '<img src="'.imagen_URL($f['foto'],133,200).'" />', '#'.$f['codigo_producto'].' ~ <a href="'.PROY_URL.URL_SUFIJO_VITRINA.SEO($f['titulo'].'-'.$f['codigo_producto']).'">'.$f['titulo'].'</a>');
echo '</table>';


// Productos descontinuados
$c = 'SELECT var.foto AS foto, con.`titulo`, con.`codigo_producto` FROM `'.db_prefijo.'producto_contenedor` AS con LEFT JOIN `'.db_prefijo.'producto_variedad` AS var USING(codigo_producto) WHERE descontinuado="si" GROUP BY var.codigo_producto';
$pmv = db_consultar($c);

echo '<h1>Productos que estan descontinuados y no los ve el público</h1>';
echo '<table class="tabla-estandar ancha tabla-centrada">';
echo '<tr><th>Fotografia</th><th>Titulo del producto</th></tr>';
while ($f = mysqli_fetch_assoc($pmv))
	echo sprintf('<tr><td>%s</td><td>%s</td></tr>', '<img src="'.imagen_URL($f['foto'],133,200).'" />', '#'.$f['codigo_producto'].' ~ <a href="'.PROY_URL.URL_SUFIJO_VITRINA.SEO($f['titulo'].'-'.$f['codigo_producto']).'">'.$f['titulo'].'</a>');
echo '</table>';


?>