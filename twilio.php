<?php
require_once('config.php');
$db_link = @mysqli_connect(db__host, db__usuario, db__clave, db__db) or die("Fue imposible conectarse a la base de datos.<br /><hr />Detalles del error:<pre>" . mysqli_error($db_link) . "</pre>");

file_put_contents("test.txt", "\n\n".time()."\n",FILE_APPEND);
file_put_contents("test.txt", serialize(@$_POST)."\n",FILE_APPEND);
file_put_contents("test.txt", serialize(@$_GET)."\n",FILE_APPEND);

// Si no hay AccountSid posiblemente no viene de Twilio
if (empty($_POST['AccountSid']))
{
    return;
}

switch ( $_POST['From'] ) {
    
    case '+50374900731':
        $token = "6753f0841d07bbd2b4614d362460078ff1a213a8"; // El token de enococo
        break;
    
    case '+50376834377':
        $token = "2dff4fc90e2973f54d62e257480de234bc59e2c4"; // El token de oscar
        break;
        
    case '+50379859476':
        $token = "1290e07eb181e74d87241768d629f681122e0e39"; // El token de vlad
        break;
}

if ( empty($token) )
{
    $token = "40bd001563085fc35165329ea1ff5c5ecbdbbeef"; // Token de servidor
    //error_log("Asunto no contenia token: ".$subject);
    //return;
}

$compras = explode(',',mysqli_real_escape_string($db_link, $_POST['Body'])); // Transaccion de prueba: 3414 y 3241 = 9dea64c3aebeaf413730a7e9e9360c68fd759a2a

foreach ($compras as $codigo_compra)
{
    $codigo_compra = trim($codigo_compra);
    
    $consulta = 'SELECT `transaccion` FROM `flores_SSL_compra_contenedor` WHERE `codigo_compra` = "'.$codigo_compra.'" LIMIT 1';
    $resultado = mysqli_query($db_link,$consulta);
    
    if (mysqli_num_rows($resultado) > 0)
    {
        $consulta = 'UPDATE `flores_SSL_compra_contenedor` SET flag_enviado=1, flag_elaborado=1 WHERE `codigo_compra` = "'.$codigo_compra.'" LIMIT 1';
        mysqli_query($db_link,$consulta);
        
        $registro = mysqli_fetch_assoc($resultado);
        
        $transaccion = $registro['transaccion'];
        
        $url = sprintf('http://flor360.com/+notificacion?envio_rapido=super_rapido&nochat=silencio&sin_cabeza=descabezar&desactivar_fb=desactivar&transaccion=%s&plantilla=enviado&token=%s',$transaccion, $token);
        file_get_contents($url);
    
        $consulta = 'SELECT codigo_usuario FROM flores_usuarios WHERE clave="'.$token.'" LIMIT 1';
        $resultado = mysqli_query($db_link,$consulta);
        $f = mysqli_fetch_assoc($resultado);
      
        $codigo_usuario = $f['codigo_usuario'];
        
        $consulta = sprintf('INSERT INTO flores_registro (codigo_compra, codigo_usuario, grupo, accion) VALUES("%s","%s","%s","%s")', $codigo_compra, $codigo_usuario, 'estado.enviado', 'Marcado como enviado desde dispositivo móvil');
        mysqli_query($db_link,$consulta);
        
        file_put_contents("test.txt", "Marcado: $codigo_compra\n",FILE_APPEND);
    } else {
        error_log ( 'No existe tal codigo de compra: '.$codigo_compra);
    }
}

?>
