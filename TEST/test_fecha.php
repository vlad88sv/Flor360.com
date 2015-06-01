<?php
require_once ("../config.php");
require_once(__BASE__ARRANQUE.'PHP/vital.php');

$fecha = 'Viernes 11 de Noviembre de 2011';

echo '<div>Fecha a interpretar: ' . $fecha . '</div>';
$a = strptime($fecha,'%A %e de %B de %Y');
$timestamp = mktime(0, 0, 0, $a['tm_mon']+1, $a['tm_mday'], $a['tm_year']+1900);

//print_r($a);

echo '<div>Fecha interpretada: ' . date('Y-m-d',$timestamp) . '</div>';


?>
