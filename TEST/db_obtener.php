<?php
require_once ("../config.php");
require_once(__BASE__ARRANQUE.'PHP/vital.php');

$precio_extra = 0;
$precio_extra = $precio_extra + doubleval( db_obtener('extras','precio','codigo_extra = "1"') );

echo $precio_extra;
?>