<?php
protegerme();
$c = 'SELECT `correo` FROM `flores_correo_oferta` WHERE 1';
$r = db_consultar($c);

while ($f = mysqli_fetch_assoc($r))
    echo $f['correo'].', ';

?>
