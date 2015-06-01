<?php

// Nota: ya se ha incluido ssl.comun y se ha pasado por "protegerme()" antes de entrar aqui.

$GLOBAL_MOSTRAR_PIE = false;
$HEAD_titulo = 'Visor de ventas - version mobil';

$buffer = '';
$total = 0;
$WHERE  = '';
$ORDER_BY = '`orden` DESC';

if(isset($_GET['de']))
    $WHERE = 'AND tarjeta_de LIKE "%'.$_GET['de'].'%"';

if(isset($_GET['para']))
    $WHERE = 'AND tarjeta_para LIKE "%'.$_GET['para'].'%"';
    
if(isset($_GET['cc']) && strlen($_GET['cc']) == 40)
    $WHERE = sprintf('AND indicerapido="%s"',$_GET['cc']);

if(isset($_GET['correo']))
    $WHERE = sprintf('AND correo_contacto="%s"',db_codex($_GET['correo']));

if(isset($_GET['c']))
    $WHERE = sprintf('AND codigo_compra="%s"',$_GET['c']);
    
if(isset($_GET['f']))
    $WHERE = sprintf('AND transaccion="%s"',$_GET['f']);

if(isset($_GET['fecha']))
    $WHERE = sprintf('AND fecha BETWEEN DATE("%s") AND (DATE("%s") + INTERVAL 24 HOUR - INTERVAL 1 SECOND)',mysql_date($_GET['fecha']),mysql_date($_GET['fecha']));

if(isset($_GET['pendientes']))
    $WHERE = 'AND flag_cobrado IN (0,1) AND flag_enviado=0  AND flag_suspendido=0 AND fecha_entrega < DATE(NOW())';

if(isset($_GET['futuro']))
    $WHERE = 'AND flag_cobrado IN (0,1) AND flag_suspendido=0 AND fecha_entrega > DATE(NOW())';
    
if(isset($_GET['fecha_entrega']))
    $WHERE = sprintf('AND fecha_entrega="%s"',mysql_date($_GET['fecha_entrega']));
    
if(isset($_GET['fecha_entrega']) && isset($_GET['ruta']))
{
    $_GET['ruta'] = str_replace('{ninguna}','',$_GET['ruta']);
    $WHERE = sprintf('AND fecha_entrega="%s" AND ruta="%s"',mysql_date($_GET['fecha_entrega']), db_codex($_GET['ruta']));
}   
if (isset($_GET['fecha_inicio']) && isset($_GET['fecha_final']))
    $WHERE = sprintf('AND fecha BETWEEN "%s" AND "%s"',mysql_date($_GET['fecha_inicio']),mysql_date($_GET['fecha_final']));

if (isset($_GET['fecha_entrega_inicio']) && isset($_GET['fecha_entrega_final']))
    $WHERE = sprintf('AND fecha_entrega BETWEEN "%s" AND "%s"',mysql_date($_GET['fecha_entrega_inicio']),mysql_date($_GET['fecha_entrega_final']));

if(isset($_GET['cobrar']))
    $WHERE = 'AND comcon.flag_cobrado = 0  AND flag_suspendido=0 AND flag_eliminado=0';

if(isset($_GET['buscar']))
{
    $busqueda = trim($_GET['buscar']);
    
    if (is_numeric($busqueda))
    {
        $WHERE = sprintf('AND codigo_compra="%s"',$busqueda);
    } else {
        $cl = new SphinxClient();
        $cl->SetServer( "localhost", 9312 );
        //$cl->SetMatchMode( SPH_MATCH_ANY  );
        
        //echo '<!--'.preg_replace('/([^\w|\d])/','\\\$1',$busqueda).'!-->';
        
        // Probamos la primera busqueda de forma amplia
        $result = $cl->Query( preg_replace('/([^\w|\d])/','\\\$1',$busqueda), 'f360_ventas' );
        
        if ( !is_array($result) || empty($result["matches"])) {
            echo '<p class="ui-state-error">No se encontraron arreglos que coincidiera con el texto búscado</p>';
        } else {
            $WHERE .= ' AND codigo_compra IN ('.join(',', array_keys($result["matches"])).')';
            $ORDER_BY = 'FIELD (codigo_compra,'.join(',', array_keys($result["matches"])).')';
        }
    }

}

$WHERE .= procesar_flags_arreglos();    

$c = sprintf('SELECT flores_usuarios.nombre_completo, comcon.indicerapido, comcon.metodo_pago, comcon.cobrar_a, comcon.cobrar_en, comcon.salt, comcon.flag_cobrado, comcon.flag_enviado, comcon.flag_elaborado, comcon.flag_eliminado, comcon.flag_suspendido, comcon.cantidad, provar.compra_minima, provar.foto, provar.descripcion AS "variedad_titulo", provar.receta, procon.codigo_producto, procon.titulo AS "contenedor_titulo",`codigo_compra`, `codigo_usuario`, `codigo_variedad`, `precio_grabado`, `cargo_adicional`, `precio_envio`, `nombre_t_credito`, `correo_contacto`, `direccion_entrega`, `fecha`, `fecha_entrega`, DATE_FORMAT(fecha,"%%e de %%M de %%Y [%%r]") fecha_formato, DATE_FORMAT(fecha_entrega,"%%e de %%M de %%Y") fecha_entrega_formato, `telefono_destinatario`, `telefono_remitente`, `tarjeta_de`, `tarjeta_para`, `tarjeta_cuerpo`, `estado_notas`, `ruta`, `usuario_notas`, `transaccion`, `cupon`, `orden`, (SELECT COUNT(*) FROM `'.db_prefijo.'SSL_compra_contenedor` WHERE `correo_contacto`=comcon.`correo_contacto` GROUP BY `correo_contacto`) AS "num_compras" FROM `'.db_prefijo.'SSL_compra_contenedor` AS comcon LEFT JOIN '.db_prefijo.'producto_variedad AS provar USING(codigo_variedad) LEFT JOIN flores_producto_contenedor AS procon USING(codigo_producto) LEFT JOIN flores_usuarios USING(codigo_usuario) WHERE flag_eliminado=0 %s ORDER BY %s',$WHERE,$ORDER_BY);

$r = db_consultar($c);

// Si no hay fecha_entrega o fecha entonces anular la busqueda!
if (mysqli_num_rows($r) >= 20)
{
    echo '<p>Se canceló la salida de resultados. Razón: <b>se excedió los <u>20</u> resultados</b><br />Se canceló para no saturar su navegador mobil.</p>';
}

$numero_de_resultados = mysqli_num_rows($r);

while ($r && ($numero_de_resultados < 20) && $f = mysqli_fetch_assoc($r))
{
    $info_estado = '';
    
    $total += ($f['cantidad']*($f['precio_grabado']+$f['cargo_adicional']))+$f['precio_envio'];
    
    $info_producto_foto =
    '<a target="_blank" href="'.PROY_URL.URL_SUFIJO_VITRINA.SEO($f['contenedor_titulo'].'-'.$f['codigo_producto']).'">'.
    '<img style="width:133px;height:200px" src="'.imagen_URL($f['foto'],133,200).'" /></a>'.
    '<p class="medio-oculto">
    <strong>Cod. Producto: </strong>'.$f['codigo_producto'].BR.
    '<strong>Nombre producto: </strong>'.BR.$f['contenedor_titulo'].BR.
    '<strong>Nombre variedad: </strong>'.BR.$f['variedad_titulo'].BR.
    '</p><hr /><p class="medio-oculto">'.
    '<strong>Cantidad:</strong> '.$f['cantidad'].'<br /> '.
    '<strong>Precio:</strong> $'.number_format($f['precio_grabado'],2,'.',',').'<br />'.
    '<strong>Adicional:</strong> $'.number_format($f['cargo_adicional'],2,'.',',').'<br />'.
    '<strong>Envio:</strong> $'.number_format($f['precio_envio'],2,'.',',').'<br />'.
    '<strong>Total: </strong>'.'$'.number_format((($f['precio_grabado']*$f['cantidad'])+$f['precio_envio']+$f['cargo_adicional']),2,'.',','). BR.
    ($f['cupon'] ? '<strong>Cupón:</strong> '.$f['cupon'] : '').
    '</p>';

    switch ($f['metodo_pago'])
    {
        case 'tarjeta':
            $info_estado .= '';
            if ($f['flag_cobrado'] <> 1)
                $info_estado .= '<img style="width:171px;height:48px;" src="'.PROY_URL.'imagen_SSL_'.$f['transaccion'] . '.png" />' . BR . '<strong>Nombre en tarjeta</strong><br />'.$f['nombre_t_credito'];
            else
                $info_estado .= '<img style="width:171px;height:48px;" src="'.PROY_URL.'imagen_SSLC_'.$f['transaccion'] . '.png" />' . BR . '<strong>Nombre en tarjeta</strong><br />'.$f['nombre_t_credito'];
            break;
        
        case 'domicilio':
            $info_estado .= sprintf('Cobrar en domicilio a <strong>%s</strong> en <strong>%s</strong>',$f['cobrar_a'],$f['cobrar_en']);
            break;
        
        case 'abono':
            $info_estado .= '<img src="'.PROY_URL.'IMG/stock/BAC.gif" style="width:139px;height:36px" /> Abono a cuenta';
            break;
        
        case 'kiosko':
            $info_estado .= 'Cliente escogió pasar a pagar en kiosko';
            break;
        
        case 'kiosko_efectivo':
            $info_estado .= 'Pago en kiosko en efectivo';
            break;
        
        case 'kiosko_credito':
            $info_estado .= 'Pago en kiosko con tarjeta de crédito';
            break;
    }
    
    $info_estado_admin =
    '<div class="ajax_estado" codigo_compra="'.$f['codigo_compra'].'">'.
    '<hr /><br />'.
    '<span class="medio-oculto">Notas</span><br />'.
    '<textarea style="width:98%;height:30px;"class="estado_notas">'.$f['estado_notas'].'</textarea>'.
    '<table style="width:100%;border-collapse:collapse;"><tr><td class="medio-oculto">Ruta: </td><td style="text-align:right;"><input class="ruta" type="text" name="ruta" style="width:150px;" value="'.$f['ruta'].'" /></td></tr></table>'.
    '<hr />
        <div class="medio-oculto" >
        Estado:<br />
        <input type="checkbox" disabled="disabled" value="1" '.(($f['flag_cobrado'] == 1) ? 'checked="checked"' : "").'" name="flag_cobrado" class="flag" /><span style="color:#F00">Cobrado</span>
        <input type="checkbox" disabled="disabled" value="1" '.(($f['flag_elaborado'] == 1) ? 'checked="checked"' : "").'" name="flag_elaborado" class="flag" /><span style="color:#F00">Elaborado</span>
        <input type="checkbox" disabled="disabled" value="1" '.(($f['flag_enviado'] == 1) ? 'checked="checked"' : "").'" name="flag_enviado" class="flag" /><span style="color:#F00">Enviado</span>
        </div>'.
    '</div>';

    $info_estado_admin .=
    '<hr />
    Correo:<br />
    <a href="mailto:'.$f['correo_contacto'].'">'.$f['correo_contacto'].'</a>
    <br />
    <br />
    Tip: clic en el correo para enviarle mensaje a traves de su celular.
    ';

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
    <input id="fecha_entrega_'.$f['codigo_compra'].'" type="text" style="width:60px;padding:0;border:1px solid grey;display:none;"  />'.
    '</td>'.
    '</tr><tr>'.
    '<td>'.
    '<strong>Código compra: </strong> <a href="'.PROY_URL_ACTUAL.'?cc='.$f['indicerapido'].'">'.$f['codigo_compra'].$f['salt'].'</a> | <b>No. total de compras:</b> <a href="'.PROY_URL_ACTUAL.'?correo='.$f['correo_contacto'].'">'.$f['num_compras'].'</a>'.
    '</td>'.
    '<td>'.
    '<strong>Ingresado por: </strong>'.($f['nombre_completo'] ? $f['nombre_completo'] : 'cliente vía web').
    '</td>'.
    '</tr>'.
    '</table>'.
    '<hr />'.
    '<table class="tabla-estandar">'.
    '<tr>
        <td><b>De:</b></td>
        <td><input type="text" style="width:100%;padding:0;border:none;" class="autoguardar" objetivo="tarjeta_de" plantilla="tarjeta_de" value="'.$f['tarjeta_de'].'" /></td>
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
    '<p class="medio-oculto">'.
    '<strong>Tarjeta Cuerpo</strong><textarea style="width:98%;height:100px;" plantilla="cambio.dedicatoria" objetivo="tarjeta_cuerpo">'.$f['tarjeta_cuerpo'].'</textarea><br />'.
    '<strong>Dirección entrega</strong><textarea style="width:98%;height:70px;" plantilla="cambio.direccion" objetivo="direccion_entrega">'.$f['direccion_entrega'].'</textarea>'.
    '<strong>Notas del comprador</strong><textarea style="width:98%;height:70px;" plantilla="cambio.notas" objetivo="usuario_notas">'.$f['usuario_notas'].'</textarea>'.
    '<strong>Elementos para preparación</strong>'.BR.ui_textarea('',$f['receta'],'','width:98%;height:35px;','readonly="readonly"').
    '</p>';
    
    // Heuristicas
    $heuristica = '';
    $arrSub = array();
    if (preg_match_all('/.*(an(ó|o|ò)nimo|sin firma|no decir).*/ism',$f['tarjeta_cuerpo'].$f['direccion_entrega'].$f['usuario_notas'],$arrSub))
    {   
        $heuristica .= '<div style="background-color:#AAF200;color:black;font-size:12px;text-align:center;font-weight:bold;">Heurística: parece que este envío es anónimo.</div>';
    }
    
    if (preg_match_all('/.*(llamar|contactar|enviar|correo|recoger|pasar(a|e|á|é)).*/ism',$f['tarjeta_cuerpo'].$f['direccion_entrega'].$f['usuario_notas'],$arrSub))
    {   
        $heuristica .= '<div style="background-color:cyan;color:black;font-size:12px;text-align:center;font-weight:bold;">Heurística: parece que este envío necesita algún objeto que proporcionará el cliente para estar completo.</div>';
    }
    
    if (preg_match_all('/.*(factura|recibo|comprobante|.oucher|comprobante|fiscal|cr.dito).*/ism',$f['direccion_entrega'].$f['usuario_notas'],$arrSub))
    {   
        $heuristica .= '<div style="background-color:red;color:white;font-size:12px;text-align:center;font-weight:bold;">Heurística: parece que este envío necesita factura o algún comprobante.</div>';
    }

    if (preg_match_all('/.*(tarjeta|dedicatoria).*/ism',$f['tarjeta_cuerpo'].$f['usuario_notas'],$arrSub))
    {
        $heuristica .= '<div style="background-color:blue;color:white;font-size:12px;text-align:center;font-weight:bold;">Heurística: parece que hay algo especial que hacer con la tarjeta de este pedido.</div>';
    }

    if (preg_match_all('/.*(kiosko|quiosco|kiosco|gran v(í|i)a).*/ism',$f['tarjeta_cuerpo'].$f['usuario_notas'],$arrSub))
    {   
        $heuristica .= '<div style="background-color:magenta;color:black;font-size:12px;text-align:center;font-weight:bold;">Heurística: parece que este pedido necesita algo de el kiosko.</div>';
    }
        
    if (!preg_match_all('/.*(cualquier hora|transcurso del d(í|i|ì)a).*/ism',$f['usuario_notas'],$arrSub))
    {
        if (!preg_match_all('/.*(antes|ma(n|ñ)i?ana|tempran).*/ism',$f['usuario_notas'],$arrSub) && preg_match_all('/.*(tarde|despues|medio\s?d(í|i|ì)a|(ú|u|ù)ltim|(\s|\d)p\.?m\.?).*/ism',$f['usuario_notas'],$arrSub)) {
            $heuristica .= '<div style="background-color:yellow;color:black;font-size:12px;text-align:center;font-weight:bold;">Heurística: parece que este envío es para la tarde  o tiene hora exacta.</div>';
        } elseif (preg_match_all('/.*(ma(n|ñ)i?ana|tempran|exacta|antes|urge|primer|sale|sin falta|(\s|\d)a\.?m\.?[^\w]|hora|\d+\:\d+).*/ism',$f['usuario_notas'],$arrSub)) {
            $heuristica .= '<div style="background-color:#FFCC00;color:black;font-size:12px;text-align:center;font-weight:bold;">Heurística: parece que este envío es para la mañana o tiene hora exacta.</div>';
        }
    }
    
    if (preg_match_all('/.*(color|adicional|flores|ramo|llev(o|a)|cambiar|choco|cargo|ferrero|globo|lirio|base|roj(o|a)|amarill(o|a)|salm(ó|o|ò)n|naranja|verde|azul|gris|blanc(o|a)|negr(o|a)|mixta|multicolor|circo|pintad(o|a)|\$\d+).*/ism',$f['usuario_notas'],$arrSub))
    {
        $heuristica .= '<div style="background-color:#66CCFF;color:black;font-size:12px;text-align:center;font-weight:bold;">Heurística: parece que este arreglo lleva flores personalizadas o algo adicional.</div>';
    }

    $buffer .= '
    <div class="numerador-compra">#'.$f['orden'].'</div>
    '.$heuristica .'
    <div id="codigo_compra_'.$f['codigo_compra'].'" class="contenedor-compra" codigo_compra="'.$f['codigo_compra'].'" salt="'.$f['salt'].'">
    <div class="info-producto-foto">
    '.$info_producto_foto.'
    </div>

    <div class="info-importante">
    '.$info_importante.'
    </div>
    
    <div class="info-derecha">
    '.$info_derecha.'
    </div>
    '.( $f['flag_suspendido'] == 1 ? '<div class="overlay_nocobrados" style="position:absolute;top:0px;bottom:0px;left:0px;right:0px;background-color:black;opacity:0.7;font-size:36px;line-height:225px;height:450px;color:white;font-family:mono;text-align:center;">Arreglo suspendido.<br />Doble-clic para quitar temporalmente.</div>' : '').'
    </div>';
}

echo '<table id="tabla-ventas" class="tabla-estandar">';
echo '<tr><th>Ventas ($)<t/h><th>Arreglos (#)</th><th>Pedidos [solicitado]</th><th>Pedidos [entrega]</th>';
echo sprintf('<tr><td>$%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', number_format($total,2), mysqli_num_rows($r),'<a href="'.PROY_URL.'ventas?fecha=-1 day">ayer</a> / <a href="'.PROY_URL.'ventas?fecha=now">ahora</a> / <form style="display:inline" method="get" action="'.PROY_URL_ACTUAL.'"><input name="fecha" type="text" class="datepicker" value="'.mysql_date().'" /><input type="submit" value="Ir" class="ir"/></form>','<a href="'.PROY_URL.'ventas?fecha_entrega=-1 day">ayer</a> / <a href="'.PROY_URL.'ventas?fecha_entrega=now">ahora</a> / <a href="'.PROY_URL.'ventas?fecha_entrega=+1 day">mañana</a> | <form style="display:inline" method="get" action="'.PROY_URL_ACTUAL.'"><input name="fecha_entrega" class="datepicker" type="text" value="'.(empty($_GET['fecha_entrega']) ? mysql_datetime() : $_GET['fecha_entrega']).'" /> <input name="flags" type="text" value="'.@$_GET['flags'].'" title="e=elaborado,c=cobrado,E=Enviado. Ej. +c-E = cobrados no enviados"/> <input type="submit" value="Ir" class="ir"/> </form>' );
echo '</table>';
echo $buffer;
?>

