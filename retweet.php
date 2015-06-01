<?php
require_once ("config.php");
    require_once(__BASE__ARRANQUE.'PHP/vital.php');
set_time_limit(0);

$c = 'SELECT codigo_producto, titulo, descripcion FROM flores_producto_contenedor WHERE twitted=0 ORDER BY codigo_producto ASC LIMIT 1';
$r = db_consultar($c);

if (mysqli_num_rows($r) == 0) exit;

$f = mysqli_fetch_assoc($r);
$status = preg_replace(array('/Medida.*/i','/Tamaño.*/i'),'',PROY_URL.'arreglos-florales-floristerias-en-el-salvador-'.SEO($f['titulo'].'-'.$f['codigo_producto']) . ' - ' . $f['descripcion']);
if (tweet($status))
{
    echo '<p>Twitted: '.$status.'</p>';
    $datos['twitted'] = "1";
    db_actualizar_datos(db_prefijo.'producto_contenedor',$datos,'codigo_producto='.$f['codigo_producto']);
}
exit ($status);
?>
