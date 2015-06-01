<?php
require_once ("config.php");
require_once(__BASE__ARRANQUE.'PHP/vital.php');

$r = db_consultar("SELECT `correo_contacto` FROM `flores_SSL_compra_contenedor` WHERE CHAR_LENGTH( `correo_contacto` ) >3");
while ($f = mysqli_fetch_assoc($r))
{
    echo $f["correo_contacto"] . ", ";
}

$r = db_consultar("SELECT `correo_contacto` FROM `flores_SLL_compra_contenedor_20JUL` WHERE CHAR_LENGTH( `correo_contacto` ) >3");
while ($f = mysqli_fetch_assoc($r))
{
    echo $f["correo_contacto"] . ", ";
}
?>