<?php
exit(1); // desactivado
ini_set('memory_limit', '128M');
set_time_limit(0);

require_once ("config.php");
require_once(__BASE__ARRANQUE.'PHP/vital.php');

$c = 'SELECT `foto`, `codigo_producto`, `codigo_variedad` FROM `flores_producto_variedad` LEFT JOIN `flores_producto_contenedor` USING(codigo_producto) WHERE `descontinuado` = "no" ORDER BY codigo_producto ASC, codigo_variedad ASC';
$r = db_consultar($c);
while ($f = mysqli_fetch_assoc($r))
{
    $origen = 'IMG/ip/'.$f['codigo_producto'].'.'.$f['codigo_variedad'].'.jpg';
    $destino = 'IMG/i/'.$f['foto'];
    
    if (file_exists($origen))
        rename($origen,$destino);
}
?>