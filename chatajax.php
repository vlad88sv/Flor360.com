<?php
require_once('config.php');
require_once (__BASE__ARRANQUE.'PHP/vital.php');

$ret['error'] = '';
$ret['html'] = '';

$modoSimple = isset($_GET['modo']) && $_GET['modo'] == 'simple';

if (S_iniciado() && !empty($_GET['ajax']) && $_GET['ajax'] == 'actualizar_ventana_chat')
{
    $cPedidos = 'SELECT CONCAT(`codigo_compra`,`salt`) AS "Código", tarjeta_de AS "De", tarjeta_para AS "Para", IF(comcon.flag_enviado = 1, "<b style=\"color:red\">SI</b>", "NO") AS "Enviado", CONCAT(procon.titulo, " - ", provar.descripcion) AS "arreglo" FROM `'.db_prefijo.'SSL_compra_contenedor` AS comcon LEFT JOIN '.db_prefijo.'producto_variedad AS provar USING(codigo_variedad) LEFT JOIN flores_producto_contenedor AS procon USING(codigo_producto) WHERE flag_eliminado=0 AND fecha_entrega = "'.db_codex($_GET['fecha_pedidos']).'" ORDER BY fecha ASC';
    $rPedidos = db_consultar($cPedidos);
    $html['pedidos_hoy'] = db_ui_tabla($rPedidos,'class="tabla-estandar zebra"');
    

    $cChats = 'SELECT CONCAT(\'<a canal="\',canal,\'" href="#" class="abrirCanal" >\',codigo_chat,\'</a>\') AS "#", estado, nombre_completo AS "Agente", DATE_FORMAT(creado,"%H:%i") AS "Iniciado", DATE_FORMAT(finalizado,"%H:%i") AS "Finalizado" FROM flores_chat LEFT JOIN flores_usuarios USING(codigo_usuario) WHERE DATE(creado)="'.db_codex($_GET['fecha_chat']).'"';
    $rChats = db_consultar($cChats);
    $html['chats_hoy'] = db_ui_tabla($rChats, 'class="tabla-estandar zebra tabla-centrada"');
    
    array_walk_recursive($html, 'encode_items');
    echo @json_encode($html);

    return;
}

if (S_iniciado() && !empty($_GET['ajax']) && $_GET['ajax'] == 'chats_pendientes')
{
    $ret['hay_nuevos'] = 'no';
    $ret['lista_pendientes'] = '';
    
    // Para que verifique si no hay algun chat de cualquier operador donde el cliente haya sido el ultimo en hablar y no este finalizado
    $c = 'SELECT codigo_chat, canal FROM `flores_chat` WHERE estado = "pendiente"';
    
    $r = db_consultar($c);
    if ($r && mysqli_num_rows($r) > 0)
    {
        $ret['hay_nuevos'] = 'si';
        
        while ($f = mysqli_fetch_assoc($r))
        {
            $ret['canales_abiertos'][] = $f['canal'];
        }
    }
    
    array_walk_recursive($ret, 'encode_items');
    echo @json_encode($ret);
    return;    
}

if (S_iniciado() && !empty($_GET['ajax']) && $_GET['ajax'] == 'verificar_nuevos_chats')
{
    // Damos un latido...
    apc_store('ultimo_acceso', time());

    $ret['hay_nuevos'] = 'no';
    $ret['lista_pendientes'] = '';
    
    // Para que verifique si no hay algun chat de cualquier operador donde el cliente haya sido el ultimo en hablar y no este finalizado
    $c = 'SELECT codigo_chat, canal, nombre_completo FROM `flores_chat` LEFT JOIN `flores_usuarios` USING(codigo_usuario) WHERE estado = "pendiente"';
    $r = db_consultar($c);
    if ($r && mysqli_num_rows($r) > 0)
    {
        $ret['hay_nuevos'] = 'si';
        
        while ($f = mysqli_fetch_assoc($r))
        {
            $ret['lista_pendientes'] .= sprintf('<li><a href="+chat?canal=%s">#%s [%s]</a></li>',$f['canal'],$f['codigo_chat'],($f['nombre_completo'] ? $f['nombre_completo'] : 'nadie'));
        }
    }   
    
    // Almacenamos el ultimo_acceso para cada usuario cada 5 minutos
    $hash = _F_usuario_cache('codigo_usuario').'_latido';
    if (apc_fetch($hash) < (time() - 300))
    {        
        apc_store($hash, time());
        db_consultar(sprintf('UPDATE flores_usuarios SET ultimo_acceso=NOW() WHERE codigo_usuario="%s"',_F_usuario_cache('codigo_usuario')));      
        db_agregar_datos('latidos',array('ID_usuario' => _F_usuario_cache('codigo_usuario'), 'fechatiempo' => mysql_datetime(), 'categoria' => 'chat.auto'));
    }
    
    array_walk_recursive($ret, 'encode_items');
    echo @json_encode($ret);
    return;    
}

if (!empty($_POST['ajax']) && $_POST['ajax'] == 'finalizar')
{
    // Solo puede existir finalizacion por el operador via ajax, el cliente no puede finalizar y el sistema (cron.daily) lo hace directo.
    $c = sprintf('UPDATE flores_chat SET finalizado=CURRENT_TIMESTAMP, estado="finalizado.operador" WHERE canal="%s"',db_codex($_POST['canal']));
    db_consultar($c);
    
    array_walk_recursive($ret, 'encode_items');
    echo @json_encode($ret);
    return;    
}

if (!empty($_POST['mensaje']))
{
    if (empty($_COOKIE['canal']) && empty($_POST['canal']))
    {
        $canal = sha1(microtime(true));
        db_consultar(sprintf('INSERT INTO flores_chat (`canal`,`estado`,`creado`) VALUES("%s","pendiente",CURRENT_TIMESTAMP)',db_codex($canal)));
        
        $_POST['canal'] = $ret['canal'] = $canal;
        
        /*
        $para      = PROY_MAIL_BROADCAST;
        $asunto = 'Cliente necesita ayuda en '.PROY_NOMBRE_CORTO.' [Ref. '.crc32 (microtime(true)).']';
        $mensaje = '<p>Un cliente ha iniciado un chat de consulta con '.PROY_NOMBRE_CORTO.'. Favor atender a traves de las siguientes direcciones:</p>';
        $mensaje .= '<p><b>Navegadores normales:</b> <a href="https://flor360.com/+chat?canal='.$canal.'">https://flor360.com/+chat?canal='.$canal.'</a></p>';
        $mensaje .= '<p><b>Navegadores móviles:</b> <a href="http://flor360.com/chat?canal='.$canal.'">http://flor360.com/chat?canal='.$canal.'</a></p>';
        correoSMTP($para, $asunto, $mensaje);
        */
    }

    $canal = isset($_POST['canal']) ?  $_POST['canal'] : $_COOKIE['canal'];
    
    if (S_iniciado() || $modoSimple )
    {
        // Veamos si fue via AlexBerry (codigo_usuario #33 = AlexBerry)
        $codigoUsuario = $modoSimple ? 33 : _F_usuario_cache('codigo_usuario');
        
        // Si un OP comienza a chatear, marcarlo como atendiendo y asignarselo.
        $c = sprintf('UPDATE flores_chat SET estado="atendiendo",codigo_usuario ="%s" WHERE canal="%s"',$codigoUsuario,db_codex($canal));
        db_consultar($c);
    } else {
        // Si es un cliente que manda un mensaje a un canal finalizado, entonces resumir.
        $c = sprintf('UPDATE flores_chat SET estado="pendiente" WHERE canal="%s"',db_codex($canal));
        db_consultar($c);
    }
    
    $c = sprintf('INSERT INTO flores_chat_mensajes (`mensaje`,`codigo_chat`,`direccion`,`URL`) VALUES("%s",(SELECT `codigo_chat` FROM flores_chat WHERE canal="%s"),"%s","%s")',db_codex($_POST['mensaje']),db_codex($canal),((S_iniciado() || $modoSimple) ? 'op>cliente' : 'cliente>op'),(empty($_SERVER['HTTP_REFERER']) ? '**oculto**' : $_SERVER['HTTP_REFERER']));
    db_consultar($c);
    
    array_walk_recursive($ret, 'encode_items');
    echo @json_encode($ret);
    return;
}

if ((!empty($_COOKIE['canal']) || !empty($_GET['canal'])) && !empty($_GET['ajax']) && $_GET['ajax'] == 'ver_ultimo_mensaje')
{
    $canal = isset($_GET['canal']) ?  $_GET['canal'] : $_COOKIE['canal'];
    $c = sprintf('SELECT * FROM flores_chat_mensajes WHERE codigo_chat = (SELECT codigo_chat FROM flores_chat WHERE canal="%s") ORDER BY fecha DESC LIMIT 1',db_codex($canal));  
    $r = db_consultar($c);
    
    while ($f = mysqli_fetch_assoc($r))
    {
        $direccion = ($f['direccion'] == 'op>cliente' ? 'destino' : 'origen');
        $texto = ($f['direccion'] == 'op>cliente' ? 'Flor360' : 'Ud.');
        
        $ret['html'] .= '<div class="mensaje '.$direccion.'"><span class="originador">'.$texto . '</span> ' . strip_tags($f['mensaje']).'</div>';
        
        $ret['atencion'] = ($f['direccion'] == 'op>cliente' ? 'no' : 'si');
    }
}

function tiempo_relativo($tiempo, $tiempo_referencia = '')
{
    if (!$tiempo_referencia) $tiempo_referencia = time();
    
    $diff = ($tiempo_referencia - $tiempo);
    
    if ($diff < 60)
        return $diff . 'Segundo(s)';
    elseif ($diff < 3600)
        return floor($diff / 60) . ':'. ($diff % 60) . ' Minutos (s)';
    else
        return 'varias horas';
}

if ((!empty($_COOKIE['canal']) || !empty($_GET['canal'])) && !empty($_GET['ajax']) && $_GET['ajax'] == 'ver_mensajes')
{
    $canales = isset($_GET['canal']) ?  $_GET['canal'] : $_COOKIE['canal'];
    
    if (!is_array($canales))
    {
        $canales = array($canales);
    }
    
    foreach($canales AS $canal)
    {
        $hash = (S_iniciado() ? _F_usuario_cache('codigo_usuario') : 'cliente').'_ultimo_acceso_canal_'.$canal;
        $hash_lugar = 'cliente_lugar_'.$canal;
        $hash_cliente_ip = 'cliente_ip_'.$canal;
        
        $ret[$canal]['actualizar'] = '1';
        $ret[$canal]['finalizar']  = '0';
        
        $ret[$canal]['html'] = '';
        
        if (S_iniciado() || $modoSimple)
        {
            $c = 'SELECT codigo_chat, nombre_completo FROM flores_chat LEFT JOIN flores_usuarios USING (codigo_usuario) WHERE canal="'.$canal.'"';
            $rAtendido_por = db_consultar($c);
            $fAtendido_por = mysqli_fetch_assoc($rAtendido_por);
            $ret[$canal]['codigo_chat'] = $fAtendido_por['codigo_chat'];
            $ret[$canal]['atendido_por'] = $fAtendido_por['nombre_completo'];
            $ret[$canal]['ultima_vez'] = tiempo_relativo(apc_fetch('cliente_ultimo_acceso_canal_'.$canal));
            
            $lugar = '<span title="'.apc_fetch($hash_cliente_ip).'">'.apc_fetch($hash_lugar).'</span>';
            $ret[$canal]['lugar'] = $lugar ? $lugar : 'Desconocido';
        }
        
        if (!S_iniciado())
        {
            apc_store('cliente_ultimo_acceso_canal_'.$canal, time());
            $geoip = @geoip_record_by_name(@$_SERVER['REMOTE_ADDR']);
            apc_store($hash_lugar, $geoip['city'].', '.$geoip['country_name']);
            apc_store($hash_cliente_ip, @$_SERVER['REMOTE_ADDR']);
        }
       
        $resultado_estado = db_obtener('flores_chat', 'estado', 'canal="'.db_codex($canal).'"');
    
        $c = sprintf('SELECT `mensaje`, `direccion`, `fecha`, TIME(`fecha`) AS "tiempo_fecha", UNIX_TIMESTAMP(`fecha`) AS "fecha_UNIX", `URL` FROM flores_chat_mensajes WHERE codigo_chat = (SELECT codigo_chat FROM flores_chat WHERE canal="%s")',db_codex($canal));  
        $r = db_consultar($c);
        
        $tiempo_anterior = 0;
            
        while ($f = mysqli_fetch_assoc($r))
        {         
            if (S_iniciado() || $modoSimple)
            {
                $direccion = ($f['direccion'] == 'op>cliente' ? 'origen' : 'destino');
                $texto = ($f['direccion'] == 'op>cliente' ? 'Flor360' : 'Cliente');
                
                if (strstr($f['mensaje'],'Flor360: '))
                {
                    $direccion = 'noticia';
                    $f['mensaje'] = str_replace('Flor360: ','',$f['mensaje']);
                    $texto = '<span style="color:red;">Noticia</span>';            
                }
                
                $texto .= '&nbsp;<img src="/IMG/iconos/clock_icon.png" title="Mensaje entregado a las '.$f['tiempo_fecha'].' (+'.tiempo_relativo($tiempo_anterior,$f['fecha_UNIX']).' desde el último mensaje)" />';
                
                if ((S_iniciado() || $modoSimple) && ($f['direccion'] == 'cliente>op'))
                    $texto .= '&nbsp;<a target="_blank" href="'.$f['URL'].'" title="'.htmlentities($f['URL']).'"><img src="/IMG/iconos/icon_url.png" /></a>';
            }
            else
            {
                $direccion = ($f['direccion'] == 'op>cliente' ? 'destino' : 'origen');
                $texto = ($f['direccion'] == 'op>cliente' ? 'Flor360' : 'Ud.');            
            }
            
            $mensaje = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.\-]*(\?\S+)?)?)?)@', '<a href="$1">$1</a>', strip_tags($f['mensaje']));
            $ret[$canal]['html'] .= '<div class="mensaje '.$direccion.'"><span class="originador">'.$texto . '</span> ' . $mensaje .'</div>';
    
            $ret[$canal]['atencion'] = ($f['direccion'] == 'op>cliente' ? 'no' : 'si');
            
            if (isset($_GET['ultima_actualizacion']))
                $ret[$canal]['actualizar'] = $f['fecha_UNIX'] > $_GET['ultima_actualizacion'] ? '1' : '0';
                
            $tiempo_anterior = $f['fecha_UNIX'];
        }
    
        if ($resultado_estado == 'finalizado.operador')
        {
            $ret[$canal]['atencion']  = '0';
        }
    }
}

if ($modoSimple)
{
    echo $ret['html'].'<p><b>Timestamp:</b> '.date('H:i:s').'</p>';
}
else
{
    array_walk_recursive($ret, 'encode_items');
    echo @json_encode($ret);
}
?>
