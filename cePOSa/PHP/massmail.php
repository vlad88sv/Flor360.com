<?php
protegerme();
set_time_limit(0);

$c = "SELECT correo FROM `cuponclub` WHERE enviado=0 ORDER BY correo ASC LIMIT 400";
//$c = 'SELECT "vladimiroski@gmail.com" AS "correo"';
$resultado = db_consultar($c);

$extra[] = array('ruta' => 'estatico/dia_de_la_madre_2013.jpg', 'cid' => 'arte', 'alt' => 'Arte Día de la Madre 2013', 'tipo' => 'image/jpeg');

while ($resultado && $f = mysqli_fetch_assoc($resultado))
{    
    $asunto = 'Día de la Madre';
    $mensaje  = '<p>Este 10 de Mayo alegra a mamá con uno de nuestros preciosos arreglos.</p>';
    $mensaje .= '<br />';
    $mensaje .= '<p style="text-align:center;"><a style="font-height:14px;" href="http://flor360.com/categoria-dia-de-la-madre-47.html">Ver catálogo.</a></p>';
    $mensaje .= '<div><img src="cid:arte" /></div>';
    $mensaje .= '<br />';
    $mensaje .= '<p>Contáctanos: <strong>2243-6017</strong> / <strong>info@flor360.com</strong> / <strong><a href="http://flor360.com">wwww.flor360.com</a></strong></p>';
    
    echo correoSMTP($f['correo'],$asunto, $mensaje, true, $extra);
    usleep(30000);
    
    db_consultar("UPDATE `cuponclub` SET enviado=1 WHERE correo='".$f["correo"]."' LIMIT 1");
}
?>