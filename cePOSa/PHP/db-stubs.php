<?php
//Timestamp to MYSQL DATETIME
function mysql_datetime($tiempo = 'now'){
    return date( 'Y-m-d H:i:s',strtotime($tiempo) );
}

//Timestamp to MYSQL DATE
function mysql_date($tiempo = 'now'){
    return date( 'Y-m-d',strtotime($tiempo) );
}

//Timestamp to MYSQL TIME
function mysql_time($tiempo = 'now'){
    return date( 'H:i:s',strtotime($tiempo) );
}

//MYSQL DATETIME a fecha normal (sin hora)
function fecha_desde_mysql_datetime($tiempo){
    return date( 'd-m-Y',strtotime($tiempo) );
}

//MYSQL DATETIME a hora (sin fecha)
function tiempo_desde_mysql_datetime($tiempo){
    return date( 'H:i:s',strtotime($tiempo) );
}

//MYSQL DATETIME a fecha y hora
function fechatiempo_desde_mysql_datetime($tiempo){
    return date( 'd-m-Y H:i:s',strtotime($tiempo) );
}

//MYSQL DATETIME a fecha y hora (mas humana)
function fechatiempo_h_desde_mysql_datetime($tiempo){
    if (!$tiempo)
    {
        return "";
    }
    return date( 'd-m-Y h:i:sa',strtotime($tiempo) );
}

function registrar($codigo_compra, $grupo, $accion, $valor_anterior = '')
{
    $datos['codigo_compra'] = $codigo_compra;
    $datos['codigo_usuario'] = _F_usuario_cache('codigo_usuario');
    $datos['grupo'] = $grupo;
    $datos['accion'] = $accion;
    $datos['valor_anterior'] = $valor_anterior;
    db_agregar_datos('flores_registro',$datos);
}
?>