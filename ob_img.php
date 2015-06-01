<?php
ini_set('memory_limit', '128M');
set_time_limit(0);

require_once ("config.php");
require_once(__BASE__ARRANQUE.'PHP/vital.php');
require_once(__BASE__ARRANQUE.'PHP/phmagick/phmagick.php');

$c = 'SELECT `foto`, `codigo_producto`, `codigo_variedad` FROM `flores_producto_variedad` LEFT JOIN `flores_producto_contenedor` USING(codigo_producto) WHERE `descontinuado` = "no" ORDER BY codigo_producto ASC, codigo_variedad ASC';
$r = db_consultar($c);
while ($f = mysqli_fetch_assoc($r))
{
    $escalado = 'IMG/ob/'.$f['codigo_producto'].'.'.$f['codigo_variedad'].'.jpg';
    $origen = 'IMG/i/'.$f['foto'];

    $phMagick = new phMagick ($origen, $escalado);
    $phMagick->resize(1024);
    unset($phMagick);
}
?>