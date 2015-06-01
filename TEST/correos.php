<?php
require_once ("../config.php");
require_once(__BASE__ARRANQUE.'PHP/vital.php');

$r = db_consultar("SELECT DISTINCT TRIM(correo_contacto) AS correo FROM `flores_SSL_compra_contenedor` WHERE correo_contacto REGEXP '(.+)@(.+)\.(.{2,4})' ORDER BY correo ASC");
$i = 0;
while ($f = mysqli_fetch_assoc($r))
{
    db_agregar_datos('cuponclub', array('correo' => $f["correo"], "enviado" => "0"));
    $i++;
}

echo '<p>Agregados de compras: '.$i.'</p>';
?>