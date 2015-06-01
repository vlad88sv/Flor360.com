<?php
//token kiosko = 60f5a36cdd7856e18c5255185e5c4004d190d906

if (empty($_GET['evento']) || empty($_GET['token']))
    return;

$token = $_GET['token'];
$evento = $_GET['evento'];
    
require_once('config.php');
require_once (__BASE__ARRANQUE.'PHP/vital.php');

$codigo_usuario = db_obtener(db_prefijo.'usuarios', 'codigo_usuario', 'clave="'.$token.'"');

if ($codigo_usuario !== false)
{
    $DATOS['evento'] = $evento;
    $DATOS['codigo_usuario'] = $codigo_usuario;
    db_agregar_datos('notificaciones',$DATOS);    
    
    echo (string) correoSMTP('info@flor360.com', 'Nuevo evento - '.date('y-m-d'), '<p>Se notifica del evento <b>'.$evento.'</b></p><p>IP:'.$_SERVER['REMOTE_ADDR'].'</p><p>Hora: '.date('r').'</p>', true);
} else {
    echo 'El token es invalido';
}
?>