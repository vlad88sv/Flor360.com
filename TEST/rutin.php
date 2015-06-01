<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>
<title>Floristeria El Salvador - Flores a El Salvador - Regalos a El Salvador - floristerias El Salvador - Flor360.com</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="Content-Style-type" content="text/css"/>
<meta http-equiv="Content-Script-type" content="text/javascript"/>
<meta http-equiv="Content-Language" content="es"/>
</head>
<?php
require_once ("../config.php");
require_once(__BASE__ARRANQUE.'PHP/vital.php');

$c = 'SELECT ruta, orden, direccion_entrega
FROM `flores_SSL_compra_contenedor` 
WHERE 1
AND fecha_entrega = "2014-02-14"
AND ruta <> ""
ORDER BY ruta ASC';
$r = db_consultar($c);

while ($f = db_fetch($r))
{
    $rutas[$f['ruta']][] = '#'.$f['orden']. ' - ' . $f['direccion_entrega'];
}

echo '<p>Cargados: '. mysqli_num_rows($r).'</p>';

foreach ($rutas AS $ruta => $ordenes)
{
    echo '<div>';
    echo '<h1>'.$ruta.'</h1>';
    echo '<ul>';
    foreach ($ordenes as $orden)
    {
        //echo $orden.', ';
        echo '<li>'.$orden.'</li>';
    }
    echo '</ul>';
    echo '</div>';
}
?>
