<?php
$__BENCH__['ventas_inicio'] = microtime(true);
protegerme(false);
require_once(__BASE_cePOSa__.'PHP/ssl.comun.php');

ACTIVAR_PAQUETES(array('ui','facebox'));

$arrJS[] = 'jquery.lazyload';
$arrJS[] = 'uri_ventas';

$GLOBAL_MOSTRAR_PIE = false;
$HEAD_titulo = 'Ventas';

echo '<hr />';
echo '<div id="notas_y_estados" style="display:none;">';
echo '<div id="timeline_estados" style="height:100px;margin:0px;border:none;padding:0px;overflow: auto;border:1px solid grey;margin-bottom:5px;"></div>';
echo '
<div style="position:relative;border:1px solid grey;margin-bottom:5px;">
    <div id="lista_compra" style="left:0px;top:0px;height:100px;margin:0px;border:none;padding:0px;overflow: auto;"></div>
    <div id="lista_compra_animar" style="position:absolute;z-index:99;right:20px;padding:5px;top:0px;width:auto;background-color:black;color:white;font-weight:bold;font-size:12px;cursor:pointer;">MAXIMIZAR</div>
</div>
';
echo '</div>';

$buffer = '';
$total = 0;
$WHERE  = '';
$ORDER_BY = '`fecha_entrega` DESC, `orden` DESC';
$LIMIT = '1,500';

if(isset($_GET['de']))
    $WHERE = 'AND tarjeta_de LIKE "%'.$_GET['de'].'%"';

if(isset($_GET['para']))
    $WHERE = 'AND tarjeta_para LIKE "%'.$_GET['para'].'%"';
    
if(isset($_GET['cc']) && strlen($_GET['cc']) == 40)
    $WHERE = sprintf('AND indicerapido="%s"',$_GET['cc']);

if(isset($_GET['correo']))
    $WHERE = sprintf('AND correo_contacto="%s"',db_codex($_GET['correo']));

if(isset($_GET['nt']))
    $WHERE = sprintf('AND n_credito=(SELECT n_credito FROM flores_SSL_compra_contenedor WHERE codigo_compra="%s")',$_GET['nt']);
    
if(isset($_GET['c']))
    $WHERE = sprintf('AND codigo_compra="%s"',$_GET['c']);
    
if(isset($_GET['f']))
    $WHERE = sprintf('AND transaccion="%s"',$_GET['f']);

if(isset($_GET['fecha']))
{
    $ORDER_BY = '`codigo_compra` DESC';
    $WHERE = sprintf('AND fecha BETWEEN DATE("%s") AND (DATE("%s") + INTERVAL 24 HOUR - INTERVAL 1 SECOND)',mysql_date($_GET['fecha']),mysql_date($_GET['fecha']));
}
if(isset($_GET['pendientes']))
{
    $WHERE = 'AND flag_cobrado IN (0,1) AND flag_enviado=0 AND flag_eliminado=0 AND flag_suspendido=0 AND fecha_entrega < DATE(NOW())';
    $HEAD_titulo = 'Pendientes';
}
if(isset($_GET['futuro']))
    $WHERE = 'AND flag_cobrado IN (0,1) AND flag_suspendido=0 AND fecha_entrega > DATE(NOW())';
    
if(isset($_GET['fecha_entrega']))
{
    $WHERE = sprintf('AND fecha_entrega="%s"',mysql_date($_GET['fecha_entrega']));
    $HEAD_titulo = 'Entregas ' . mysql_date($_GET['fecha_entrega']);
}   
if(isset($_GET['fecha_entrega']) && isset($_GET['ruta']))
{
    $_GET['ruta'] = str_replace('{ninguna}','',$_GET['ruta']);
    $WHERE = sprintf('AND fecha_entrega="%s" AND ruta="%s"',mysql_date($_GET['fecha_entrega']), db_codex($_GET['ruta']));
}   
if (isset($_GET['fecha_inicio']) && isset($_GET['fecha_final']))
    $WHERE = sprintf('AND fecha BETWEEN "%s" AND "%s"',mysql_date($_GET['fecha_inicio']),mysql_date($_GET['fecha_final']));

if (isset($_GET['fecha_entrega_inicio']) && isset($_GET['fecha_entrega_final']))
    $WHERE = sprintf('AND fecha_entrega BETWEEN "%s" AND "%s"',mysql_date($_GET['fecha_entrega_inicio']),mysql_date($_GET['fecha_entrega_final']));

if(isset($_GET['fecha_cobrado']))
    $WHERE = sprintf('AND comcon.flag_cobrado = 1 AND codigo_compra IN (SELECT codigo_compra FROM flores_registro WHERE grupo IN ("estado.cobrado","pago.kiosko") AND DATE(timestamp)="%s")',mysql_date($_GET['fecha_cobrado']));
    
if(isset($_GET['cobrar']))
{
    $ORDER_BY = '`codigo_compra` DESC';
    $WHERE = 'AND comcon.flag_cobrado = 0 AND flag_suspendido=0 AND flag_eliminado=0';
}
if(isset($_GET['buscar']))
{
    $busqueda = trim($_GET['buscar']);
    
    if (validcorreo($busqueda)){
        $WHERE = sprintf('AND correo_contacto="%s"',db_codex($busqueda));
    } elseif (strlen($busqueda) >= 2 && substr($busqueda,0,1) == "#") {
        $WHERE = 'AND fecha_entrega="2013-02-14" AND comcon.orden='.substr($busqueda,1);
    } elseif (is_numeric($busqueda)) {
        $WHERE = sprintf('AND codigo_compra="%s"',$busqueda);
    } else {
        $cl = new SphinxClient();
        $cl->SetServer( "localhost", 9312 );
        $cl->SetMatchMode( SPH_MATCH_ANY  );
        
        $result = $cl->Query( $busqueda, 'f360_ventas' );
        
        if ( !is_array($result) || empty($result["matches"])) {
            echo '<p style="color:red;font-size:3em;">No se encontraron arreglos que coincidiera con el texto búscado</p>';
            echo '<pre>'.$busqueda.'</pre>';
            $WHERE = ' AND 0';
        } else {
            $WHERE .= ' AND codigo_compra IN ('.join(',', array_keys($result["matches"])).')';
            //$ORDER_BY = 'FIELD (codigo_compra,'.join(',', array_keys($result["matches"])).')';
        }
    }

}

// BTC = Búsqueda de Tarjeta de Crédito
if (isset($_GET['btc']))
{
    $cBTC = 'SELECT codigo_compra FROM `flores_SSL_compra_contenedor` WHERE AES_DECRYPT(`n_credito`,"'.db__key_str.'") LIKE "%'.db_codex($_GET['btc']).'%"';
    $rBTC = db_consultar($cBTC);
    
    if ( mysqli_num_rows($rBTC) > 0 )
    {
        
        $codigosBTC = array();
        while ( $fBTC = db_fetch($rBTC) )
        {
            $codigosBTC[] = $fBTC['codigo_compra'];
        }
        
        $WHERE = ' AND codigo_compra IN ('.join(',', $codigosBTC).')';
        
    } else {
        $WHERE = ' AND 0';
    }
}

$WHERE .= procesar_flags_arreglos();

if (isset($_GET['ocultar_eliminados']))
    $WHERE .= ' AND flag_eliminado=0 ';

$__BENCH__['ventas_SQL_principal'] = microtime(true);
$c = sprintf('SELECT anonimo, ip, flores_usuarios.nombre_completo, comcon.indicerapido, comcon.metodo_pago, comcon.cobrar_a, comcon.cobrar_en, comcon.salt, comcon.flag_cobrado, comcon.flag_enviado, comcon.flag_elaborado, comcon.flag_eliminado, comcon.flag_suspendido, comcon.cantidad, provar.compra_minima, provar.foto, provar.descripcion AS "variedad_titulo", IF(comcon.preparacion_personalizada != "", comcon.preparacion_personalizada, provar.receta) AS receta, procon.codigo_producto, procon.titulo AS "contenedor_titulo",`codigo_compra`, `codigo_usuario`, `codigo_variedad`, `precio_grabado`, `cargo_adicional`, `precio_envio`, `nombre_t_credito`, `correo_contacto`, `direccion_entrega`, `fecha`, `fecha_entrega`, DATE_FORMAT(fecha,"%%e de %%M de %%Y [%%r]") fecha_formato, DATE_FORMAT(fecha_entrega,"%%e de %%M de %%Y") fecha_entrega_formato, `telefono_destinatario`, `telefono_remitente`, `tarjeta_de`, `tarjeta_para`, `tarjeta_cuerpo`, `estado_notas`, `ruta`, `usuario_notas`, `transaccion`, `cupon`, `orden` FROM `'.db_prefijo.'SSL_compra_contenedor` AS comcon LEFT JOIN '.db_prefijo.'producto_variedad AS provar USING(codigo_variedad) LEFT JOIN flores_producto_contenedor AS procon USING(codigo_producto) LEFT JOIN flores_usuarios USING(codigo_usuario) WHERE 1 %s ORDER BY %s LIMIT 0,500',$WHERE,$ORDER_BY);
$r = db_consultar($c);
$__BENCH__['ventas_SQL_principal_fin'] = microtime(true);

// Si no hay fecha_entrega o fecha entonces anular la busqueda!
if (mysqli_num_rows($r) >= 500)
{
    echo '<p class="ui-state-error">-- se limitó la salida de resultados. Razón: <b>se excedió los <u>500</u> resultados</b> --</p>';
}


$lazy_img = imagen_URL('cargando_imagen_vertical',133,200,'');

$buffer_acceso_rapido = array();

$numero_de_resultados = mysqli_num_rows($r);

$__BENCH__['ventas_loop_principal'] = microtime(true);
while ($r && $f = mysqli_fetch_assoc($r))
{
    
    // APC
    $f['num_compras'] = apc_fetch($f['codigo_compra']."_num_compras");
    $f['num_compras_2'] = apc_fetch($f['codigo_compra']."_num_compras_2");

    if ($f['num_compras'] === false) {
        $f['num_compras'] = db_obtener('flores_SSL_compra_contenedor', 'COUNT(*)', '`correo_contacto`="'.$f['correo_contacto'].'"');
        apc_store($f['codigo_compra']."_num_compras",$f['num_compras'],60);
    }
    
    if ($f['num_compras_2'] === false) {
        $f['num_compras_2'] = 0;
        if ($f['metodo_pago'] == "tarjeta")
        {
            $f['num_compras_2'] = db_obtener('flores_SSL_compra_contenedor', 'COUNT(*)', '`n_credito`=(SELECT n_credito FROM flores_SSL_compra_contenedor WHERE codigo_compra="'.$f['codigo_compra'].'")');
        }
        apc_store($f['codigo_compra']."_num_compras_2",$f['num_compras_2'],60);
    }
    
    
    $buffer_acceso_rapido[$f['codigo_compra']] = $f['orden'];
    
    /* Extras */
    list($f['extras'], $f['extrasPrecio']) = SSL_COMPRA_OBTENER_EXTRAS($f['codigo_compra']);
    
    list($heuristica, $heuristicas) = obtener_heuristicas($f);    
   
    $fraudimetro = apc_fetch($f['codigo_compra']."_fraudimetro");
    
    if ( $fraudimetro === false )
    {
        $fraudimetro = SSL_COMPRA_OBTENER_FRAUDIMETRO($f['transaccion']);
        apc_store($f['codigo_compra']."_fraudimetro",$fraudimetro,60);
    }
    
    
    $total_este_arreglo = (($f['precio_grabado']*$f['cantidad'])+$f['precio_envio']+$f['cargo_adicional']+$f['extrasPrecio']);
    
    $subtotal = $total_este_arreglo;
    $total += $subtotal;
    
    if ($f['flag_cobrado'] == 1)
    {
        @$totales[$f['metodo_pago']] += $subtotal; 
    }
    
    $info_producto_foto =
    '<a target="_blank" href="'.PROY_URL.URL_SUFIJO_VITRINA.SEO($f['contenedor_titulo'].'-'.$f['codigo_producto']).'?variedad='.$f['codigo_variedad'].'">'.
    '<img class="imgzoom lazy" data-original="'.imagen_URL($f['foto'],133,200).'" foto="'.$f['foto'].'" style="width:133px;height:200px" src="'.$lazy_img.'" /></a>'.
    '<div>
    <strong>Cod. Producto: </strong>'.$f['codigo_producto'].BR.
    '<strong>Nombre producto: </strong><div class="una-linea">'.$f['contenedor_titulo'].'</div>'.
    '<strong>Nombre variedad: </strong><div class="una-linea">'.$f['variedad_titulo'].'</div>'.
    '</div><br /><hr /><br /><table class="tabla-estandar borde-abajo">'.
    '<tr><td><strong>Cantidad:</strong></td><td><input type="text" style="width:60px;padding:0;border:none;" class="autoguardar" objetivo="cantidad" value="'.$f['cantidad'].'" /><br /> '.
    '<tr><td><strong>Precio:</strong></td><td>$<input type="text" style="width:60px;padding:0;border:none;" class="autoguardar" objetivo="precio_grabado" value="'.number_format($f['precio_grabado'],2,'.',',').'" /></td></tr>'.
    '<tr><td><strong>Adicional:</strong></td><td>$<input type="text" style="width:60px;padding:0;border:none;" class="autoguardar" objetivo="cargo_adicional" value="'.number_format($f['cargo_adicional'],2,'.',',').'" /></td</tr>'.
    '<tr><td><strong>Extras:</strong></td><td>$<input type="text" readonly="readonly" style="width:60px;padding:0;border:none;" value="'.number_format($f['extrasPrecio'],2,'.',',').'" /></td></tr>'.
    '<tr><td><strong>Envio:</strong></td><td>$<input type="text" style="width:60px;padding:0;border:none;" class="autoguardar" objetivo="precio_envio" value="'.number_format($f['precio_envio'],2,'.',',').'" /></td></tr>'.
    '<tr style="background-color:yellow;"><td><strong>Total:</strong></td><td>$<input type="text" readonly="readonly" style="width:60px;padding:0;border:none;" value="'.number_format($total_este_arreglo,2,'.',',').'" /></td></tr>'.
    ($f['cupon'] ? '<strong>Cupón:</strong> '.$f['cupon'] : '').
    '</table>';
    
    $info_producto_foto .= '<input type="checkbox" class="sel_pedido" value="'.$f['codigo_compra'].'" checked="checked" id="sel_pedido_'.$f['codigo_compra'].'"/> <label for="sel_pedido_'.$f['codigo_compra'].'">Seleccionar</label>';
    
    $info_producto_foto .= '<hr /><div style="text-align:center;">$'.number_format(($total_este_arreglo / 1.13),2,'.',',') . " - $" . number_format(($total_este_arreglo - ($total_este_arreglo / 1.13) ),2,'.',',').'</div>';

    //$info_estado .= '<div>IP: <b>'.$f['ip'].'</b></div>';
    
    if ($fraudimetro > 7)
        $fraudimetro_mensaje = '<b class="blink" style="color:red;">¡¡¡ ALTA [+'.$fraudimetro.']!!!</b>';
    elseif ($fraudimetro > 3)
        $fraudimetro_mensaje = '<b style="color:blue;">MEDIA</b>';
    else
        $fraudimetro_mensaje = 'BAJA';
    
    $info_estado = '';
    $info_estado .= '<div class="fraudimetro">Posibilidad de fraude: '.$fraudimetro_mensaje.'</div><hr />';
    $info_estado .= '<div style="font-size:12px;">Total: <b>$'.number_format($subtotal,2,'.',',').'</b></div><hr />';
    switch ($f['metodo_pago'])
    {
        case 'tarjeta':
            $info_estado .= '';
            $info_estado .= '<img title="'.$f['nombre_t_credito'].'" class="img_tc" src="'.PROY_URL.'imagen_SSL'.($f['flag_cobrado'] == 1 ? 'C' : '').'_'.$f['transaccion'] . '.png" />';
            break;
        
        case 'domicilio':
            $info_estado .= sprintf('Cobrar en domicilio a <strong>%s</strong> en <strong>%s</strong>',$f['cobrar_a'],$f['cobrar_en']);
            break;
        
        case 'abono':
            $info_estado .= '<center><img src="'.PROY_URL.'IMG/stock/BAC.gif" style="width:139px;height:36px" /></center>';
            break;
        
        case 'puntoexpress':
            $info_estado .= '<center><img src="'.PROY_URL.'IMG/stock/logo_pex.png" style="width:139px;height:36px" /></center>';
            break;
        
        case 'kiosko':
            $info_estado .= 'Cliente escogió pasar a pagar en kiosko.';
            break;
        
        case 'kiosko_efectivo':
            $info_estado .= 'Pago en kiosko en efectivo';
            break;
        
        case 'kiosko_credito':
            $info_estado .= 'Pago en kiosko con tarjeta de crédito';
            break;
        
        case 'diferido':
            $info_estado .= 'Pago será diferido a asociado';
            break;
    }
        
    $info_estado_admin =
    '<hr />'.
    '<div class="ajax_estado">'.
    '<span class="medio-oculto">Notas [ <a target="_blank" class="ver_historial bloque" href="+registro?nochat=silencio&sin_cabeza=descabezar&desactivar_fb=desactivar&cc='.$f['codigo_compra'].'">historial</a> ]</span> <input type="button" class="guardar btnlnk" style="width:30%" value="Guardar" /><br />'.
    '<textarea style="width:96%;height:40px;" class="estado_notas">'.$f['estado_notas'].'</textarea>'.
    '<hr />
        <div class="medio-oculto" >
        <input type="checkbox" value="1" '.(($f['flag_cobrado'] == 1) ? 'checked="checked"' : "").'" name="flag_cobrado" class="flag" /><span style="color:#F00">Cobrado</span>
        <input type="checkbox" value="1" '.(($f['flag_elaborado'] == 1) ? 'checked="checked"' : "").'" name="flag_elaborado" class="flag" /><span style="color:#F00">Elaborado</span>
        <input type="checkbox" value="1" '.(($f['flag_enviado'] == 1) ? 'checked="checked"' : "").'" name="flag_enviado" class="flag" /><span style="color:#F00">Enviado</span>
        </div>'.
    '</div>';

    $info_estado_admin .=
    '<hr />
    <input type="text" style="width:200px;padding:0;border:1px solid grey;" class="autoguardar" objetivo="correo_contacto" plantilla="correo_contacto" value="'.$f['correo_contacto'].'" />
    
    <div class="notificaciones" style="margin-top:1px;">
        <select style="width:50%;padding:5px 2px;margin:2px 0;" class="plantilla">
            <option value="">Seleccione plantilla</option>
            <option value="datos_basicos">Datos básicos</option>
            <option value="facturacion_correcta">Facturación correcta</option>
            <option value="facturacion_incorrecta">Facturación incorrecta</option>
            <option value="pedido_aclarar">Aclarar datos de pedido</option>
            <option value="enviado">Enviado</option>
            <option value="error_entrega">Error de entrega</option>
            <option value="confirmacion_eliminado">Confirmación de eliminación</option>
        </select>
        
        <input type="button" class="enviar_notificacion" value="ENVIAR" class="btnlnk"><input type="button" class="enviar_notificacion_rapida" value="!" class="btnlnk">
        <hr style="margin-top:2px;" />
        <input type="button" class="marcar_cobrado" value="COBRADO" style="color:blue;" title="Clic para marcar como cobrado" class="btnlnk">
        <input type="button" class="hacer_envio" value="ENTREGADO" style="color:red;" title="Clic para marcar como enviado" class="btnlnk">
    </div>
    <br />
    <form style="border:none;margin:0;padding:0;" action="'.PROY_URL.'+impresion" method="get" target="_blank">'.
    ui_input('transaccion',$f['transaccion'],'hidden').
    ui_input('nocache','nocache','hidden').
    '<input type="submit" class="btnlnk" style="width:60px" name="objetivo" value="Pedido" />'.
    '<input type="submit" class="btnlnk" style="width:40px" name="objetivo" value="Firma" />'.
    '<input type="submit" class="btnlnk" style="width:60px" name="objetivo" value="Factura" />'.
    '<input type="submit" class="btnlnk" style="width:60px" name="objetivo" value="Tarjeta" />'.
    '</form>'.
    '<br />'.
    '<form style="margin:0;border:none;padding:0;" action="'.PROY_URL.'+compras" method="post" target="_blank">'.
    ui_input('PME_sys_sfn[0]','0','hidden').
    ui_input('PME_sys_fl','0','hidden').
    ui_input('PME_sys_qfn','','hidden').
    ui_input('PME_sys_fm','0','hidden').
    ui_input('PME_sys_rec',$f['codigo_compra'],'hidden').
    ui_input('PME_sys_operation','Cambiar','hidden').
    ui_input('PME_sys_navfmdown','0','hidden').
    '<input type="submit" class="btnlnk" style="width:30%" value="Editar" />'.
    '<input type="button" class="eliminar" class="btnlnk" style="width:30%" value="Eliminar" />'.
    '</form>'.
    '<br />'.
    ($f['flag_suspendido'] == 0 ? '<input type="button" class="btnlnk suspender" style="width:30%" value="Suspender" />'  : '<input type="button" class="btnlnk reactivar" style="width:30%" salt="'.$f['salt'].'" value="REACTIVAR" />').
    '<input type="submit" class="btnlnk realizar_devolucion" style="width:30%" class="" value="Devolución" />';
    
    if (1) {
        $info_estado_admin .= '
        <hr />
        <form style="margin:auto;" method="post" action="https://www.paypal.com/cgi-bin/webscr" target="paypal">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="charset" value="utf-8" />
            <input type="hidden" name="business" value="l.canas@riftelsalvador.com">
            <input type="hidden" name="item_name" value="'.$f['contenedor_titulo'] . ' - ' . $f['variedad_titulo'].' | '.strtolower($f['tarjeta_de']).'">
            <input type="hidden" name="item_number" value="'.$f['codigo_compra'].$f['salt'].'">
            <input type="hidden" name="amount" value="'.number_format( $total_este_arreglo ,2,'.',',').'">
            <input type="hidden" name="currency_code" value="USD">
            <input type="hidden" name="quantity" value="1">
            <input type="image" src="http://www.paypalobjects.com/es_XC/i/btn/btn_buynow_SM.gif" border="0" name="submit" alt="Make payments with PayPal - fast, free and secure!">
        </form>
        ';
    }

    $info_derecha = sprintf('
    <p class="medio-oculto">%s</p>
    <div>%s</div>
    ',$info_estado,$info_estado_admin);

    $info_importante =
    '<table class="tabla-estandar">'.
    '<tr>'.
    '<td>'.
    '<strong>Fecha pedido:</strong> '.$f['fecha_formato'].
    '</td>'.
    '<td>'.
    '<strong>Fecha entrega:</strong> <span id="fecha_entrega_formato_'.$f['codigo_compra'].'">'.$f['fecha_entrega_formato'].'</span>
    <img rel="'.$f['codigo_compra'].'"" class="revelar_fecha_entrega" title="Cambiar fecha de entrega" src="IMG/stock/fecha.gif" />
    <input id="fecha_entrega_'.$f['codigo_compra'].'" type="text" style="width:60px;padding:0;border:1px solid grey;display:none;" class="autoguardar" objetivo="fecha_entrega" plantilla="fecha_entrega" value="'.$f['fecha_entrega'].'" />'.
    '</td>'.
    '</tr><tr>'.
    '<td>'.
    '<strong>Código de compra: </strong> <a style="font-family:monospace;font-size:1.1em;" href="'.PROY_URL_ACTUAL.'?cc='.$f['indicerapido'].'">'.$f['codigo_compra'].strtoupper($f['salt']).'</a> | <b>No. total de compras:</b> <a href="'.PROY_URL_ACTUAL.'?correo='.$f['correo_contacto'].'"><span title="Según correo electrónico">['.$f['num_compras'].']</a></span> | <a href="'.PROY_URL_ACTUAL.'?nt='.$f['codigo_compra'].'"><span title="Según número de tarjeta">['.$f['num_compras_2'].']</span></a>'.
    '</td>'.
    '<td>'.
    '<strong>Ingresado por: </strong>'.($f['codigo_usuario'] ? $f['nombre_completo'] : 'cliente vía web').
    '</td>'.
    '</tr>'.
    '</table>'.
    '<hr />'.
    '<table class="tabla-estandar">'.
    '<tr>
        <td><b>De:</b></td>
        <td style="width:400px;"><input type="text" style="width:100%;padding:0;border:none;'.(isset($heuristica['anonimo']) ? 'text-decoration: line-through;' : '').'" class="autoguardar" objetivo="tarjeta_de" plantilla="tarjeta_de" value="'.$f['tarjeta_de'].'" /></td>
        <td><b>Tel.</b></td>
        <td><input type="text" style="width:100%;padding:0;border:none;" class="autoguardar" objetivo="telefono_remitente" plantilla="telefono_remitente" value="' . preg_replace(array('/-/','/(\d{4})(\d{4})/'),array('','$1-$2'),trim($f['telefono_remitente'])). '"></td>
    </tr>'.
    '<tr>
        <td><b>Para:</b></td><td><input type="text" style="width:100%;padding:0;border:none;" class="autoguardar" objetivo="tarjeta_para" plantilla="tarjeta_para" value="'.$f['tarjeta_para'].'" /></td>
        <td><b>Tel.</b></td>
        <td><input type="text" style="width:100%;padding:0;border:none;" class="autoguardar" objetivo="telefono_destinatario" plantilla="telefono_destinatario" value="'. preg_replace(array('/-/','/(\d{4})(\d{4})/'),array('','$1-$2'),trim($f['telefono_destinatario'])). '" /></td>
    </tr>'.
    '</table>'.
    '<hr />'.
    ''.
    '<strong>Tarjeta Cuerpo</strong><textarea style="width:98%;height:90px;" class="autoguardar" plantilla="cambio.dedicatoria" objetivo="tarjeta_cuerpo">'.$f['tarjeta_cuerpo'].'</textarea><br />'.
    '<strong>Dirección entrega</strong> [ Ruta <input class="ruta" type="text" name="ruta" style="width:170px;height:10px;font-size:8pt;" value="'.$f['ruta'].'" /> ]'.
    '<textarea style="width:98%;height:70px;" class="autoguardar" plantilla="cambio.direccion" objetivo="direccion_entrega">'.$f['direccion_entrega'].'</textarea>'.
    '<table style="width:99%;table-layout:fixed;border-collapse:collapse;">'.
    '<tr><td>'.
    '<strong>Notas adicionales</strong><textarea style="width:98%;height:80px;" class="autoguardar" plantilla="cambio.notas" objetivo="usuario_notas">'.$f['usuario_notas'].'</textarea>'.
    '</td><td>'.
    '<strong>Extras</strong> [ <a class="editar_extras" href="#" rel="'.$f['codigo_compra'].'">editar</a> ]<br /><textarea style="width:98%;height:80px;">'.$f['extras'].'</textarea>'.
    '</tr>'.
    '</table>'.
    '<strong>Elementos para preparación</strong><textarea style="width:98%;height:35px;" class="autoguardar" plantilla="preparacion" objetivo="preparacion_personalizada">'.$f['receta'].'</textarea>';
    
    
    $buffer .= '
    <div id="cc_'.$f['codigo_compra'].'" class="numerador-compra">#'.$f['orden'].'.</div>
    <div style="text-align:center;">'.$heuristicas.'</div>
    <div id="codigo_compra_'.$f['codigo_compra'].'" class="contenedor-compra" codigo_compra="'.$f['codigo_compra'].'" salt="'.$f['salt'].'" transaccion="'.$f['transaccion'].'" total="'.number_format((($f['precio_grabado']*$f['cantidad'])+$f['precio_envio']+$f['cargo_adicional']),2,'.',',').'">
        <div class="info-producto-foto">
        '.$info_producto_foto.'
        </div>
    
        <div class="info-importante">
        '.$info_importante.'
        </div>
        
        <div class="info-derecha">
        '.$info_derecha.'
        </div>    
        '.( $f['flag_suspendido'] == 1 ? '<div class="overlay_nocobrados" style="position:absolute;top:0px;bottom:0px;left:0px;right:0px;background-color:black;opacity:0.7;font-size:36px;line-height:225px;height:450px;color:white;font-family:mono;text-align:center;">Arreglo suspendido.<br />Doble-clic para quitar temporalmente.</div>' : '').( $f['flag_eliminado'] == 1 ? '<div class="overlay_nocobrados" style="position:absolute;top:0px;bottom:0px;left:0px;right:0px;background-color:red;opacity:0.7;font-size:36px;line-height:225px;height:450px;color:white;font-family:mono;text-align:center;">Arreglo eliminado.<br />Doble-clic para quitar temporalmente.</div>' : '').'
    </div>';
}

if(isset($_GET['fecha_entrega']))
{
    $c = 'SELECT ruta, IF(`ruta` <> "",`ruta`,"{ninguna}") AS ruta2, COALESCE(COUNT(*),0) AS "cantidad", (SELECT COUNT(*) AS "cantidad" FROM `flores_SSL_compra_contenedor` AS t2 WHERE flag_enviado=1 AND `fecha_entrega`="'.mysql_date($_GET['fecha_entrega']).'" AND t2.ruta=t1.ruta GROUP BY `ruta`) AS cantidad_entregados FROM `flores_SSL_compra_contenedor` AS t1 WHERE flag_eliminado=0 AND `fecha_entrega`="'.mysql_date($_GET['fecha_entrega']).'" GROUP BY `ruta`';
    $rRuta = db_consultar($c);
    
    if (mysqli_num_rows($rRuta) > 1)
    {
        echo '<div style="border: 1px solid grey;padding:5px;">Ver ruta: ';
        while ($fRuta = mysqli_fetch_assoc($rRuta))    
            echo sprintf('<a style="background:#cc0066; color: white; padding:2px; border-radius:2px; margin: 4px; display: inline-block;" href="ventas?fecha_entrega=%s&ruta=%s">%s %s</a>',$_GET['fecha_entrega'],$fRuta['ruta2'],$fRuta['ruta2'], '['.$fRuta['cantidad_entregados'].'/'.$fRuta['cantidad'].']');
        echo '</div>';
    }
}

$c = 'SELECT COUNT(*) AS cantidad FROM flores_SSL_compra_contenedor WHERE flag_cobrado IN (0,1) AND flag_enviado=0 AND flag_eliminado=0 AND flag_suspendido=0 AND fecha_entrega < DATE(NOW())';
$rPendientes = db_consultar($c);
$fPendientes = mysqli_fetch_assoc($rPendientes);

if ($fPendientes['cantidad'] > 0)
    echo sprintf('<p class="ui-state-error blink" style="color:yellow;padding:3px;text-decoration:blink;"><strong>IMPORTANTE:</strong> ¡Hay <strong>%s</strong> pedidos de días anteriores que no se han <strong>enviado</strong>!. <a class="bloque" href="%s">Ver cúales</a>.</p>',$fPendientes['cantidad'],'ventas?pendientes');

$c = 'SELECT COUNT(*) AS cantidad, SUM((cantidad*precio_grabado)+cargo_adicional+precio_envio) AS monto FROM flores_SSL_compra_contenedor WHERE flag_eliminado=0 AND flag_cobrado=0 AND flag_suspendido=0';
$rNoCobrados = db_consultar($c);
$fNoCobrados = mysqli_fetch_assoc($rNoCobrados);

if ($fNoCobrados['cantidad'] > 0)
    echo sprintf('<p class="ui-state-error"><strong>Advertencia:</strong> ¡Hay <strong>%s</strong> pedidos (por $%s) que aún no se han <strong>cobrado</strong>!. <a class="bloque" href="%s">Ver todos</a> - <a class="bloque" href="%s">Solo tarjeta</a> - <a class="bloque" href="%s">Solo abonos</a> - <a class="bloque" href="%s">Solo asociados</a>.</p>',$fNoCobrados['cantidad'],number_format($fNoCobrados['monto'],2,'.',','),'ventas?cobrar','ventas?cobrar&flags=+C','ventas?cobrar&flags=+a','ventas?cobrar&flags=+A');

$c = 'SELECT COUNT(*) AS cantidad, SUM((cantidad*precio_grabado)+cargo_adicional+precio_envio) AS monto FROM flores_SSL_compra_contenedor WHERE flag_eliminado=0 AND flag_cobrado=0 AND flag_suspendido=0 AND metodo_pago="tarjeta"';
$rNoCobradosTarjeta = db_consultar($c);
$fNoCobradosTarjeta = mysqli_fetch_assoc($rNoCobrados);

if ($fNoCobradosTarjeta['cantidad'] > 0)
    echo sprintf('<p class="ui-state-error"><strong>Advertencia:</strong> ¡Hay <strong>%s</strong> pedidos (por $%s) que aún no se han <strong>cobrado con tarjeta</strong>!. <a class="bloque" href="%s">Ver cúales</a>.</p>',$fNoCobrados['cantidad'],number_format($fNoCobrados['monto'],2,'.',','),'ventas?cobrar&flags=+C');

$c = 'SELECT COUNT(*) AS cantidad, SUM((cantidad*precio_grabado)+cargo_adicional+precio_envio) AS monto FROM flores_SSL_compra_contenedor WHERE flag_eliminado=0 AND flag_cobrado=0  AND flag_suspendido=0 AND metodo_pago="domicilio"';
$rNoCobradosDomicilio = db_consultar($c);
$fNoCobradosDomicilio = mysqli_fetch_assoc($rNoCobradosDomicilio);

if ($fNoCobradosDomicilio['cantidad'] > 0)
    echo sprintf('<p class="ui-state-error"><strong>Advertencia:</strong> ¡Hay <strong>%s</strong> pedidos por ($%s) que aún no se han <strong>cobrado a domicilio</strong>!. <a class="bloque" href="%s">Ver cúales</a>.</p>',$fNoCobradosDomicilio['cantidad'],number_format($fNoCobradosDomicilio['monto'],2,'.',','),'ventas?cobrar&flags=+d');

/* Busquemos los pedidos movidos */
if(!empty($_GET['fecha_entrega']))
{
    $c = 'SELECT `codigo_compra` FROM `flores_registro` WHERE `grupo` = "fecha.entrega" AND `valor_anterior` = "'.mysql_date($_GET['fecha_entrega']).'"';
    $r = db_consultar($c);
    
    if (mysqli_num_rows($r) > 0)
    {
        $arreglos_movidos = '';
                
        while ($registro_movimientos = db_fetch($r))
        {
            $arreglos_movidos .= '<a href="/ventas?c='.$registro_movimientos['codigo_compra'].'">#'.$registro_movimientos['codigo_compra'].'</a>&nbsp;';
        }
        
        echo '<p style="padding:10px;color:blue;font-size:12px;border: 1px solid blue;"><strong>Arreglos que estaban para este día y se movieron:</strong> '.$arreglos_movidos.'</p>';
        unset($arreglos_movidos);
    }
} // while

$__BENCH__['ventas_loop_principal_fin'] = microtime(true);

/* Busquemos los pedidos eliminados */
// Deshabilitado porque ahora se muestran los eliminados con un overlay como los suspendidos
if(0 && !empty($_GET['fecha_entrega']))
{
    $c = 'SELECT `codigo_compra` FROM flores_SSL_compra_contenedor WHERE flag_eliminado=1 AND `fecha_entrega` = "'.mysql_date($_GET['fecha_entrega']).'"';
    $r = db_consultar($c);
    
    if (mysqli_num_rows($r) > 0)
    {
        $arreglos_eliminados = '';
        
        while ($registro_movimientos = db_fetch($r))
        {
            $arreglos_eliminados .= '<a href="/ventas?completo&c='.$registro_movimientos['codigo_compra'].'">#'.$registro_movimientos['codigo_compra'].'</a>&nbsp;';
        }
        echo '<p  style="padding:10px;color:red;font-size:12px;border: 1px solid red;"><strong>Arreglos que estaban para este día y se eliminaron:</strong> '.$arreglos_eliminados.'</p>';
    }
}


$c = 'SELECT COUNT(*) AS cantidad FROM flores_SSL_compra_contenedor WHERE flag_eliminado=0 AND flag_cobrado IN (0,1)  AND flag_suspendido=0 AND flag_enviado=0 AND fecha_entrega > DATE(NOW())';
$rPendientesFuturo = db_consultar($c);
$fPendientesFuturo = mysqli_fetch_assoc($rPendientesFuturo);

$futuro = sprintf('<a href="%s">futuro [%s]</a>','ventas?futuro',$fPendientesFuturo['cantidad']);

echo '<table id="tabla-ventas" class="tabla-ventas tabla-estandar tabla-centrada">';
echo '<tr><th>Ventas</th><th>#</th><th>Pedidos [solicitado]</th><th>Pedidos [entrega]</th><th>Pedidos [cobrado]</th><th>Herramientas</th>';
echo sprintf('<tr><td>$%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',number_format($total,2), $numero_de_resultados,'<a href="'.PROY_URL.'ventas?fecha=-1 day">ayer</a> / <a href="'.PROY_URL.'ventas?fecha=now">ahora</a> / <form style="display:inline" method="get" action="'.PROY_URL_ACTUAL.'"><input name="fecha" type="text" style="width:70px;" class="datepicker" value="'.mysql_date().'" /><input type="submit" value="Ir" class="ir"/></form>','<a href="'.PROY_URL.'ventas?fecha_entrega=-1 day">ayer</a> / <a href="'.PROY_URL.'ventas?fecha_entrega=now">ahora</a> [<a href="'.PROY_URL.'ventas?fecha_entrega=now&flags=-E-x">NE</a> | <a href="'.PROY_URL.'ventas?fecha_entrega=now&flags=+E">E</a> | <a href="'.PROY_URL.'ventas?fecha_entrega=now&flags=-c">NC</a> | <a href="'.PROY_URL.'ventas?fecha_entrega=now&flags=-e-x">NH</a> | <a href="'.PROY_URL.'ventas?fecha_entrega=now&flags=+e">H</a>] / <a href="'.PROY_URL.'ventas?fecha_entrega=+1 day">mañana</a> / '.$futuro.' | <form style="display:inline" method="get" action="'.PROY_URL_ACTUAL.'"><input name="fecha_entrega" class="datepicker" style="width:70px;" type="text" value="'.(empty($_GET['fecha_entrega']) ? mysql_date() : $_GET['fecha_entrega']).'" /> <input id="flags" name="flags" type="text" value="'.@$_GET['flags'].'" style="width:50px;" title="Usar herramienta filtro"/> <input type="submit" value="Ir" class="ir"/></form>','<a href="'.PROY_URL.'ventas?fecha_cobrado=-1 day">ayer</a> / <a href="'.PROY_URL.'ventas?fecha_cobrado=now">ahora</a> | <form style="display:inline" method="get" action="'.PROY_URL_ACTUAL.'"><input name="fecha_cobrado" class="datepicker" type="text" style="width:70px;" value="'.(empty($_GET['fecha_cobrado']) ? mysql_date() : $_GET['fecha_cobrado']).'" />  <input type="submit" value="Ir" class="ir"/></form>', '<input type="button" style="" onclick="mostrar_notas_y_estados();" value="Lista" /> <input type="button" style="" id="construir_filtro" value="Filtro" />' );
echo '</table>';

echo '<br />';

echo '<table class="tabla-ventas tabla-estandar tabla-centrada">';
echo '<tr><th>Total cobrado</th><th>Total Crédito</th><th>Cŕedito</th><th>Domicilio</th><th>Abono</th><th>Kiosko efectivo</th><th>Kiosko crédito</th><th>Kiosko</th></tr>';
echo '<tr><td>$'.number_format(@array_sum($totales),2,'.',',').'</td><td>$'.(@$totales['tarjeta']+@$totales['kiosko_credito']).'</td><td>$'.(@$totales['tarjeta'] ?: 0).'</td><td>$'.(@$totales['domicilio'] ?: 0).'</td><td>$'.(@$totales['abono'] ?: 0).'</td><td>$'.(@$totales['kiosko_efectivo'] ?: 0).'</td><td>$'.(@$totales['kiosko_credito'] ?: 0).'</td><td>$'.(@$totales['kiosko'] ?: 0).'</td></tr>';
echo '</table>';

echo '<br />';

// Mostrar acceso rapido solo si son 20 arreglos o menos
if ( count($buffer_acceso_rapido) <= 20 )
{
    $buffer_acceso_rapido = array_reverse($buffer_acceso_rapido, true);
    $estado_orden = true;
    echo '<div id="acceso_rapido_ordenes">';
    foreach($buffer_acceso_rapido as $codigo_compra => $orden) {
        echo '<a class="'.($estado_orden ? 'orden_x' : 'orden_y').'" href="#cc_'.$codigo_compra.'"><b>'.$orden.'</b> ['.$codigo_compra.']</a> ';
        $estado_orden = ! $estado_orden;
    }
    echo '<div id="quitar_acceso_rapido">X</div>';
    echo '</div>';
}
echo $buffer;

$__BENCH__['ventas_fin'] = microtime(true);

$str_result_bench=mini_bench_to($__BENCH__);
echo '<hr /><pre>'.$str_result_bench.'</pre>'; // string return
?>
