<?php
protegerme(false,array(_N_vendedor));
if (!is_numeric(@$_GET['codigo_articulo']))
    return;

db_consultar('SET @total := 0;');
$r = db_consultar(sprintf('SELECT IF(flores_kiosko_transacciones.`descripcion` = "", flores_kiosko_articulos.`descripcion`, flores_kiosko_transacciones.`descripcion`) AS "descripcion", codigo_kiosko_articulo, IF (`operacion` = "ingreso", CONCAT("+",`cantidad`), CONCAT("-",`cantidad`)) AS Movimiento, IF (`operacion` = "ingreso", @total := @total  + `cantidad`, @total := @total  - `cantidad`) AS "total", `fecha`, `operacion`, `nombre_completo` FROM `flores_kiosko_transacciones` LEFT JOIN `flores_kiosko_articulos` USING(codigo_kiosko_articulo) LEFT JOIN flores_usuarios USING(codigo_usuario) WHERE `codigo_kiosko_articulo` = %s ORDER BY fecha ASC',$_GET['codigo_articulo']));

echo db_ui_tabla($r,'class="tabla-estandar tabla-centrada"');
?>