<?php
$tablausuarios = db_prefijo.'usuarios';

function _F_usuario_existe($correo,$campo="correo"){
    global $tablausuarios;
    $nombre_completo = db_codex($correo);
    $resultado = db_consultar ("SELECT correo FROM $tablausuarios where $campo='$correo'");
    if ($resultado) {
        if ( mysqli_num_rows($resultado) == 1 )
        {
            return true;
        }
    }
    return false;
}

function _F_usuario_agregar($datos){
    global $tablausuarios;
    if ( !_F_usuario_existe($datos['nombre_completo']) ){
        return db_agregar_datos ($tablausuarios, $datos);
    } else {
        return false;
    }
}

function _F_usuario_acceder($correo, $clave,$enlazar=true){
    global $tablausuarios;
    $correo = db_codex (trim($correo));
    $clave =db_codex (trim($clave));

    $c = "SELECT * FROM $tablausuarios WHERE LOWER(correo)=LOWER('$correo') AND clave=SHA1('$clave') LIMIT 1";
    $resultado = db_consultar ($c);
    if ($resultado && mysqli_num_rows($resultado)) {
        $_SESSION['autenticado'] = true;
        $_SESSION['cache_datos_nombre_completo'] = mysqli_fetch_assoc($resultado);
        return 1;
    } else {
        unset ($_SESSION);
        return 0;
    }

}


function _F_usuario_via_token($token){
    global $tablausuarios;
    $token = db_codex (trim($token));

    $c = "SELECT * FROM $tablausuarios WHERE clave='$token' LIMIT 1";
    $resultado = db_consultar ($c);
    if ($resultado && mysqli_num_rows($resultado)) {
        $_SESSION['autenticado'] = true;
        $_SESSION['cache_datos_nombre_completo'] = mysqli_fetch_assoc($resultado);
        return 1;
    } else {
        unset ($_SESSION);
        return 0;
    }

}

function _F_usuario_cache($campo){
    return @$_SESSION['cache_datos_nombre_completo'][$campo];
}

function _autenticado()
{
    return (isset($_SESSION['autenticado']));
}
?>
