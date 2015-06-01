<?php
// Ruta del archivo de configuracion y personalizacion
require_once('config.php');
require_once (__BASE__ARRANQUE.'PHP/vital.php');

if (empty($_POST['pajax']) && empty($_GET['pajax']))
{
    @ob_end_clean();
    exit;
}

switch (@$_POST['pajax'])
{
    case 'guardar_nota':
        AJAX_compras_anteriores();
        break;    
    case 'compras_anteriores':
        AJAX_compras_anteriores();
        break;
    case 'modificar_estado_orden':
        AJAX_modificar_estado_de_orden();
        break;
    case 'obtenerLista':
        AJAX_obtener_lista();
        break;
    case 'eliminar_orden':
        AJAX_eliminar_orden();
        break;
    case 'suspender_orden':
        AJAX_suspender_orden();
        break;
    case 'reactivar_orden':
        AJAX_reactivar_orden();
        break;
    case 'modificar_orden':
        AJAX_cambio_en_estado_de_orden();
        break;
    case 'arreglo_visto':
        AJAX_arreglo_visto();
        break;
    case 'ayuda_telefonica':
        AJAX_ayuda_telefonica();
        break;
    case 'buscar_para_anexo':
        AJAX_buscar_para_anexo();
        break;
    case 'procesar_anexo':
        AJAX_procesar_anexo();
        break;
    case 'buscar_para_pago':
        AJAX_buscar_para_pago();
        break;
    case 'procesar_pago':
        AJAX_procesar_pago();
        break;
    case 'crear_devolucion':
        AJAX_crear_devolucion();
        break;
    case 'buscar_para_devolucion':
        AJAX_buscar_para_devolucion();
        break;
    case 'procesar_devolucion':
        AJAX_procesar_devolucion();
        break;
    case 'eliminar_transaccion':
        AJAX_eliminar_transaccion();
        break;
    case 'realizar_abono':
        AJAX_realizar_abono();
        break;
    case 'guardar_notas_lista':
        AJAX_guardar_notas_lista();
        break;
    case 'ubicacion':
        AJAX_ubicacion();
        break;
    case 'info_pedido':
        AJAX_info_pedido();
        break;
    case 'obtener_cambios':
        AJAX_obtener_cambios();
        break;
    case 'marcar_fraudulenta':
        AJAX_marcar_fraudulenta();
        break;
}

switch (@$_GET['pajax'])
{
    case 'ruta':
        AJAX_ruta();
        break;
    
    case 'timeline_estados':
        AJAX_timeline_estados();
        break;
    
    case 'img_kiosko':
        AJAX_img_kiosko();
        break;
}

function AJAX_marcar_fraudulenta()
{
    require_once(__BASE__ARRANQUE.'PHP/ssl.comun.php');
    list ($datos, $f) = SSL_COMPRA_FACTURA($_POST['codigo_compra'],true);
    
    $blacklist['tarjeta'] = $f['n_credito_DAES'];
    $blacklist['razon'] = 'Marcado desde interfaz';
    
    if (db_obtener('blacklist','tarjeta','tarjeta="'.$f['n_credito_DAES'].'"')) {
        db_consultar('DELETE FROM blacklist WHERE tarjeta="'.$f['n_credito_DAES'].'"');
    } else {
        db_agregar_datos('blacklist', $blacklist);
    }
    
    

    
}
function AJAX_obtener_cambios()
{
    $ordenes = join(',', $_POST['ordenes']);
    
    $ultimo_cambio = db_obtener('flores_registro','UNIX_TIMESTAMP(`timestamp`)','codigo_usuario != "'._F_usuario_cache('codigo_usuario').'" AND codigo_compra IN('.$ordenes.')','ORDER BY `timestamp` DESC');
    
    echo $ultimo_cambio;
}

function AJAX_buscar_para_anexo()
{

    require_once(__BASE__ARRANQUE.'PHP/ssl.comun.php');
        
    $HASH = sha1(trim(strtolower($_POST['hash'])));
    $c = sprintf('SELECT comcon.indicerapido, comcon.metodo_pago, comcon.cobrar_a, comcon.cobrar_en, comcon.salt, comcon.flag_cobrado, comcon.flag_enviado, comcon.flag_elaborado, comcon.flag_eliminado, comcon.flag_suspendido, comcon.cantidad, provar.compra_minima, provar.foto, provar.descripcion AS "variedad_titulo", IF(comcon.preparacion_personalizada, comcon.preparacion_personalizada, provar.receta) AS receta, procon.codigo_producto, procon.titulo AS "contenedor_titulo",`codigo_compra`, `codigo_usuario`, `codigo_variedad`, `precio_grabado`, `cargo_adicional`, `precio_envio`, `nombre_t_credito`, `correo_contacto`, `direccion_entrega`, `fecha`, `fecha_entrega`, DATE_FORMAT(fecha,"%%e de %%M de %%Y [%%r]") fecha_formato, DATE_FORMAT(fecha_entrega,"%%e de %%M de %%Y") fecha_entrega_formato, `telefono_destinatario`, `telefono_remitente`, `tarjeta_de`, `tarjeta_para`, `tarjeta_cuerpo`, `estado_notas`, `ruta`, `usuario_notas`, `transaccion`, `cupon`, `orden`, (SELECT COUNT(*) FROM `'.db_prefijo.'SSL_compra_contenedor` WHERE `correo_contacto`=comcon.`correo_contacto` GROUP BY `correo_contacto`) AS "num_compras" FROM `'.db_prefijo.'SSL_compra_contenedor` AS comcon LEFT JOIN '.db_prefijo.'producto_variedad AS provar USING(codigo_variedad) LEFT JOIN flores_producto_contenedor AS procon USING(codigo_producto) WHERE indicerapido="%s"',$HASH);
    
    $r = db_consultar($c);
    
    if ($f = mysqli_fetch_assoc($r))
    {
        
        list($f['extras'], $f['extrasPrecio']) = SSL_COMPRA_OBTENER_EXTRAS($f['codigo_compra']);
        
        if ($f['flag_eliminado'] != '0')
        {
            echo '<p>Este pedido fue eliminado del sistema, debe solicitar que lo restauren antes de poder marcarlo como pagado</p>';
            return;
        }
    
        if ($f['flag_suspendido'] != '0')
        {
            echo '<p>Este pedido fue suspendido (pausado) en el sistema, debe solicitar que lo desbloqueen antes de poder marcarlo como pagado</p>';
            return;
        }
        
        $info_producto_foto =
        '<a target="_blank" href="'.PROY_URL.URL_SUFIJO_VITRINA.SEO($f['contenedor_titulo'].'-'.$f['codigo_producto']).'?variedad='.$f['codigo_variedad'].'">'.
        '<img style="width:133px;height:200px" src="'.imagen_URL($f['foto'],133,200).'" /></a>'.
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
        '<tr style="background-color:yellow;"><td><strong>Total:</strong></td><td>$<input type="text" readonly="readonly" style="width:60px;padding:0;border:none;" value="'.number_format((($f['precio_grabado']*$f['cantidad'])+$f['precio_envio']+$f['cargo_adicional']+$f['extrasPrecio']),2,'.',',').'" /></td></tr>'.
        ($f['cupon'] ? '<strong>Cupón:</strong> '.$f['cupon'] : '').
        '</table>';
    
        $info_importante =
        '<table class="tabla-estandar">'.
        '<tr>'.
        '<td>'.
        '<strong>Fecha pedido:</strong> '.$f['fecha_formato'].
        '</td>'.
        '<td>'.
        '<strong>Fecha entrega:</strong> '.$f['fecha_entrega_formato'].
        '</td>'.
        '</tr><tr>'.
        '<td>'.
        '<strong>Código de compra: </strong> <a style="font-family:monospace;" href="'.PROY_URL_ACTUAL.'?cc='.$f['indicerapido'].'">'.$f['codigo_compra'].$f['salt'].'</a></span></a>'.
        '</td>'.
        '<td>'.
        '<strong>Ingresado por: </strong>'.($f['codigo_usuario'] ? 'Agente flor360.com' : 'cliente vía web').
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
        '<strong>Dirección entrega</strong><textarea style="width:98%;height:70px;" class="autoguardar" plantilla="cambio.direccion" objetivo="direccion_entrega">'.$f['direccion_entrega'].'</textarea>'.
        '<table style="width:99%;table-layout:fixed;border-collapse:collapse;">'.
        '<tr><td>'.
        '<strong>Notas del comprador</strong><textarea style="width:98%;height:80px;" class="autoguardar" plantilla="cambio.notas" objetivo="usuario_notas">'.$f['usuario_notas'].'</textarea>'.
        '</td><td>'.
        '<strong>Extras</strong> [ <a class="editar_extras" href="#" rel="'.$f['codigo_compra'].'">editar</a> ]<br /><textarea style="width:98%;height:80px;">'.$f['extras'].'</textarea>'.
        '</tr>'.
        '</table>'.
        '<strong>Elementos para preparación</strong><textarea style="width:98%;height:35px;" class="autoguardar" plantilla="preparacion" objetivo="preparacion_personalizada">'.$f['receta'].'</textarea>';
        
        echo '
        <div class="contenedor-compra">
            <div class="info-producto-foto">
            '.$info_producto_foto.'
            </div>
        
            <div class="info-importante">
            '.$info_importante.'
            </div>
            
            <div class="info-derecha">
                    <br />
                    <input type="checkbox" id="paquete" value="1" style="vertical-align:middle;" /><label for="paquete">paquete/tarjeta/objeto físisco</label><br />
                    <br />
                    <label for="descripcion">Descripción</label><br />
                    <textarea style="width:94%;height:100px;" id="descripcion"></textarea>
                    <input type="button" style="font-size:18px;" id="anexar_datos" rel="'.$f['codigo_compra'].'" value="Anexar datos" />
            </div>
        </div>';
    
    } else {
        echo '<p>No se encontró ningún pedido que coinicidiera con ese código de compra.</p>';
    }    
}

function AJAX_procesar_anexo()
{
    if (!S_iniciado()) return;
    
    require_once(__BASE__ARRANQUE.'PHP/ssl.comun.php');
    
    // Marcar como flag_cobrado=1
    // Metodo pago = kiosko_efectivo|kiosko_credito dependiendo de $_POST['metodo_pago']
    // registrar() = "Arreglo marcado como cobrado en Kiosko via kiosko_efectivo|kiosko_credito por codigo_usuario
    // Agregar la transaccion al kiosko como articulo #100
    
    unset($datos);
    $datos['codigo_compra']         = $_POST['codigo_compra'];
    $datos['descripcion']           = $_POST['descripcion'];
    $datos['flag_paquete']          = $_POST['paquete'];
    $datos['codigo_usuario_recibio']= _F_usuario_cache('codigo_usuario');
    $codigo_anexo = db_agregar_datos('anexos',$datos);
    
    $anexo = db_codex( "\n[ANEXO] " . ($datos['flag_paquete'] == '1' ? '[PAQUETE] ' : '') . $datos['descripcion'] );
    $c = 'UPDATE `flores_SSL_compra_contenedor` SET `usuario_notas` = CONCAT(`usuario_notas`, "'.$anexo.'") WHERE codigo_compra="'.$datos['codigo_compra'].'" LIMIT 1';
    db_consultar($c);
    
    $transaccion = db_obtener('flores_SSL_compra_contenedor', 'transaccion', 'codigo_compra="'.$datos['codigo_compra'].'"');
    
    // Mensaje para el que lo ingreso
    echo '<h1>Transacción exitosa</h1>';
    echo '<p>Anexado fue registrado en el sistema.</p>';
    
    registrar($datos['codigo_compra'], 'anexo', 'Anexo agregado via módulo de anexo', '');
    
    $texto_correo  = '<p>Codigo de compra: <strong>' . $datos['codigo_compra']. "</strong></p>\n";
    $texto_correo .= '<p>Descripcion: <strong>' . $datos['descripcion']. "</strong></p>\n";
    $texto_correo .= '<p>Paquete: <strong>' . ($datos['flag_paquete'] == '1' ? 'Si' : 'No'). "</strong></p>\n";
    $texto_correo .= '<hr />';
    $texto_correo .= '<h1>Datos de pedido</h1>';
    
    list($buffer,$f) = SSL_COMPRA_FACTURA($transaccion);
    
    $texto_correo .= $buffer;
    
    correoSMTP('info@flor360.com','Nuevo anexo [#' . $codigo_anexo . ']',$texto_correo,true);
    
}

function AJAX_info_pedido()
{
    require_once(__BASE__ARRANQUE.'PHP/ssl.comun.php');
    $codigo_compra = db_codex(ltrim($_POST['codigo_compra'],'0'));
    $codigo_compra = substr($codigo_compra,0,-1);
    
    $c = sprintf('SELECT anonimo, flores_usuarios.nombre_completo, comcon.indicerapido, comcon.metodo_pago, comcon.cobrar_a, comcon.cobrar_en, comcon.salt, comcon.flag_cobrado, comcon.flag_enviado, comcon.flag_elaborado, comcon.flag_eliminado, comcon.flag_suspendido, comcon.cantidad, provar.compra_minima, provar.foto, provar.descripcion AS "variedad_titulo", IF(comcon.preparacion_personalizada != "", comcon.preparacion_personalizada, provar.receta) AS receta, procon.codigo_producto, procon.titulo AS "contenedor_titulo",`codigo_compra`, `codigo_usuario`, `codigo_variedad`, `precio_grabado`, `cargo_adicional`, `precio_envio`, `nombre_t_credito`, `correo_contacto`, `direccion_entrega`, `fecha`, `fecha_entrega`, DATE_FORMAT(fecha,"%%e de %%M de %%Y [%%r]") fecha_formato, DATE_FORMAT(fecha_entrega,"%%e de %%M de %%Y") fecha_entrega_formato, `telefono_destinatario`, `telefono_remitente`, `tarjeta_de`, `tarjeta_para`, `tarjeta_cuerpo`, `estado_notas`, `ruta`, `usuario_notas`, `transaccion`, `cupon`, `orden`, (SELECT COUNT(*) FROM `'.db_prefijo.'SSL_compra_contenedor` WHERE `correo_contacto`=comcon.`correo_contacto` GROUP BY `correo_contacto`) AS "num_compras", IF(metodo_pago="tarjeta",(SELECT COUNT(*) FROM `'.db_prefijo.'SSL_compra_contenedor` WHERE `n_credito`=comcon.`n_credito`),"N/A") AS "num_compras_2" FROM `'.db_prefijo.'SSL_compra_contenedor` AS comcon LEFT JOIN '.db_prefijo.'producto_variedad AS provar USING(codigo_variedad) LEFT JOIN flores_producto_contenedor AS procon USING(codigo_producto) LEFT JOIN flores_usuarios USING(codigo_usuario) WHERE codigo_compra = "'.$codigo_compra.'"  LIMIT 1');
    $r = db_consultar($c);    
    $f = db_fetch($r);
    

    echo '<table style="width:100%;color:black;">';
    echo '<tr>';
    echo '<td><img style="width:400px;height:600px;" src="'. imagen_URL($f['foto'], 400, 600).'" /></td>';
    echo '<td style="vertical-align:top;font-size:1.5em;">';
    if ($f['flag_elaborado'] == '0')
        echo '<p style="text-align:center;"><button rel="'.$codigo_compra.'" id="barcode__marcar_como_elaborado" style="font-size:1.5em;font-weight:bold;501">Marcar como elaborado</button></p>';
    else
        echo '<p style="text-align:center;"><strong>Este arreglo ya fue elaborado</strong></p>';
        
    echo '<hr />';
    
    echo '<table style="width:100%;color:black;" class="borde">';
    echo '<tr><td style="text-align:right;width:200px;">Estado:</td><td style="font-weight:bold;">'.($f['flag_cobrado'] == '1' ? 'Cobrado' : 'No cobrado').'</td></tr>';
    echo '<tr><td style="text-align:right;">Ingresado por:</td><td style="font-weight:bold;">'.$f['nombre_completo'].'</td></tr>';
    echo '<tr><td style="text-align:right;">Variedad:</td><td style="font-weight:bold;">'.$f['variedad_titulo'].'</td></tr>';
    echo '</table>';
    
    echo '<strong>Preparación:</strong><br />'.$f['receta'] . '<br />';
    echo '<strong>Notas y detalles:</strong><br />'.$f['usuario_notas'] . '<br />';
    list ($extras, $extrasPrecio) = SSL_COMPRA_OBTENER_EXTRAS($codigo_compra);
    echo '<div>'.$extras.'</div>';
    echo '<div>'.implode('<br />',SSL_COMPRA_OBTENER_ANEXOS($codigo_compra)).'</div>';
    echo '<strong>Dedicatoria:</strong><br />'.$f['tarjeta_cuerpo'] . '<br />';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
    return;
}

function AJAX_ubicacion()
{
    $cl = new SphinxClient();
    $cl->SetServer( "localhost", 9312 );
    $cl->SetMatchMode( SPH_MATCH_EXTENDED  );

    $result = $cl->Query( '@direccion_entrega ' . $_POST['ubicacion'], 'f360_ventas' );

    if ( !is_array($result) || empty($result["matches"])) {
        echo '<p style="color:#ff0000;font-size:3.0em;">No se encontraron ubicaciones que coincidiera con el texto búscado</p>';
        echo '<pre>'.$busqueda.'</pre>';
    } else {
        $c = 'SELECT `direccion_entrega` FROM `flores_SSL_compra_contenedor` WHERE codigo_compra IN ('.join(',', array_keys($result["matches"])).')';
        $resultado = db_consultar($c);
        echo '<ul class="geo_resultados">';
        while ($resultado && $f = db_fetch($resultado))
        {
            if (!S_iniciado())
                $f['direccion_entrega'] = preg_replace('/((#)\s?|(casa\s+)|(apt.)|(apartamento\s)).*?\s/i', '$1? ', $f['direccion_entrega']);
            
            echo '<li>'.$f['direccion_entrega'].'</li>';
        }
        echo '<hr /><p>Servicio gratuito provisto por Floristería Flor360.com</p>';
        echo '</ul>';
    }
}

function AJAX_guardar_notas_lista()
{
    $c = 'UPDATE `flores_SSL_compra_contenedor` SET `notas2` = "'.db_codex($_POST['nota']). '" WHERE codigo_compra= '.db_codex($_POST['codigo_compra']);
    db_consultar($c);
}

function AJAX_realizar_abono()
{
    unset($DATOS);
    $DATOS['dia'] = $_POST['fecha'];
    $DATOS['notas'] = $_POST['notas'];
    $DATOS['metodo_pago'] = $_POST['metodo_pago'];
    $DATOS['codigo_usuario'] = _F_usuario_cache('codigo_usuario');
    
    db_reemplazar_datos('kiosko_abonos',$DATOS);
}

function AJAX_img_kiosko()
{
    $c = 'SELECT foto, t2.titulo, t2.codigo_producto FROM `flores_producto_variedad` LEFT JOIN `flores_producto_contenedor` AS t2 USING(codigo_producto)  LEFT JOIN  `flores_productos_categoria` USING(codigo_producto) WHERE codigo_categoria <> 37 ORDER BY RAND() LIMIT 1';
    $r = db_consultar($c);
    
    $f = db_fetch($r);
    
    echo json_encode($f);
}

function AJAX_compras_anteriores()
{
    if (empty($_POST['correo']))
    {
       echo '';
       return;
    }
    
    if (!validcorreo($_POST['correo']))
    {
        echo '<span style="color:red;">El correo es inválido.&nbsp;</span>';
        return;
    }
        
    if (!S_iniciado() )
    {        
        return;
    }
    
    $cantidad = db_obtener('`'.db_prefijo.'SSL_compra_contenedor`','COUNT(*)','`correo_contacto`="'.db_codex($_POST['correo']).'"','GROUP BY `correo_contacto`');

    if ($cantidad > 0)
        echo '<a href="/ventas?correo='.$_POST['correo'].'&completo" target="_blank" rel="nofollow">Ver '.$cantidad.' compras anteriores</a>';
    else
        echo 'Ninguna compra anterior registrada.';
    
    return;
}

function AJAX_crear_devolucion()
{
    if (!S_iniciado() || empty($_POST['codigo_compra']) || !is_numeric($_POST['codigo_compra']) || empty($_POST['monto']) || !is_numeric($_POST['monto']) || empty($_POST['salt']) || _F_usuario_cache('nivel') < 9) return;
    
    $DATOS['indicerapido'] = sha1($_POST['codigo_compra'].$_POST['salt']);
    $DATOS['creado_por'] = _F_usuario_cache('codigo_usuario');
    $DATOS['monto'] = $_POST['monto'];
    $DATOS['fecha_creado'] = mysql_datetime();
    
    db_agregar_datos('flores_devoluciones', $DATOS);

    registrar($_POST['codigo_compra'],'devolucion.creada','Monto autorizado a devolver: $'.$_POST['monto']);
}

function AJAX_eliminar_transaccion()
{
    if (!S_iniciado() || empty($_POST['codigo_kiosko_transaccion']) || !is_numeric($_POST['codigo_kiosko_transaccion']) || _F_usuario_cache('nivel') < 9)
    {
        $ret['resultado'] = 'No tiene permiso de ejecutar esta acción.';
    } else {
        $c = 'DELETE FROM `flores_kiosko_transacciones` WHERE `codigo_kiosko_transaccion` = '.$_POST['codigo_kiosko_transaccion'] . ' LIMIT 1';
        db_consultar($c);
        
        if (db_afectados() == 1)
        {
            $ret['resultado'] = 'Se eliminó la transacción';
        } else {
            $ret['resultado'] = 'No fue posible eliminar la transacción';
        }
    }
    
    echo json_encode($ret);
}

function cmplista($a, $b)
{
    $a = trim(strtolower(preg_replace(array('/(.*)\[.*/ims','/[\d|\W|\s]/'),array('$1',''),trim($a))));
    $b = trim(strtolower(preg_replace(array('/(.*)\[.*/ims','/[\d|\W|\s]/'),array('$1',''),trim($b))));
    
    //error_log($a . ' - vs - ' . $b);
    
    if ($a == $b) return 0;
    
    return ($a < $b) ? -1 : 1;
}

function AJAX_obtener_lista()
{   
    if (empty($_POST['ordenes']) || !is_array($_POST['ordenes']) || count($_POST['ordenes']) == 0)
    {
        echo 'No hay arreglos seleccionados';
        return;
    }
    
    $c = 'SELECT CONCAT(codigo_compra,salt) AS codigo, `orden`, CONCAT(`flores_producto_contenedor`.titulo, " - ", `flores_producto_variedad`.descripcion) nombre_arreglo, IF(preparacion_personalizada != "", preparacion_personalizada, receta) AS receta, usuario_notas, `flores_producto_variedad`.codigo_producto FROM `flores_SSL_compra_contenedor` LEFT JOIN `flores_producto_variedad` USING(`codigo_variedad`) LEFT JOIN `flores_producto_contenedor` USING(`codigo_producto`) WHERE codigo_compra IN ('.join(',',$_POST['ordenes']).') ORDER BY orden DESC';
    $r = db_consultar($c);
    
    $receta = array();
    $especial = array();
    $arrSub = null;
    
    while ($f = mysqli_fetch_assoc($r))
    {
        if (preg_match_all('/.*(color|adicional|agregar|flores|ramo|cambiar|choco|cargo|ferrero|globo|lirio|base|roj(o|a)|amarill(o|a)|salm(ó|o|ò)n|naranja|verde|azul|gris|blanc(o|a)|negr(o|a)|mixta|multicolor|circo|pintad(o|a)).*/ism',$f['usuario_notas'],$arrSub))
        {
            $especial[] = 'Orden #'. $f['orden'].' ("'.$f['nombre_arreglo'].'"): '.$f['usuario_notas'];
        }
        
        if ($f['receta'])
        {
            $f['receta'] = array_filter(explode(',',str_replace(' y ',',',$f['receta'])));
            
            foreach($f['receta'] AS $ingrediente)
            {
                $grupo = 'sin grupo';
                
                if (preg_match_all('/.*(orquidea).*/ism',$ingrediente,$arrSub)) { // Busquemos orquidias
                    $grupo = 'orquideas';
                    
                } elseif (preg_match_all('/.*(er(v|b)era).*/ism',$ingrediente,$arrSub)) { // Busquemos gerberas
                    $grupo = 'gerberas';
                    
                } elseif (preg_match_all('/.*(lirio).*/ism',$ingrediente,$arrSub)) { // Busquemos lirios
                    $grupo = 'lirios';
                    
                } elseif (preg_match_all('/.*(pita|rafia|gel|piedras|sal\s|semilla|viruta|grano|yute|papel|list.n).*/ism',$ingrediente,$arrSub)) { // Busquemos objetos
                    $grupo = 'objetos y utileria';
                    
                } elseif (preg_match_all('/.*(chasta|margarita|cristante).*/ism',$ingrediente,$arrSub)) { // Busquemos chasta
                    $grupo = 'chasta';
                    
                } elseif (preg_match_all('/.*(solidago).*/ism',$ingrediente,$arrSub)) { // Busquemos solidago
                    $grupo = 'solidago';
                    
                } elseif (preg_match_all('/.*(monteca).*/ism',$ingrediente,$arrSub)) { // Busquemos montecasino
                    $grupo = 'montecasino';
                    
                } elseif (preg_match_all('/.*(pinocho).*/ism',$ingrediente,$arrSub)) { // Busquemos montecasino
                    $grupo = 'pinocho';
                    
                } elseif (preg_match_all('/.*(girasol).*/ism',$ingrediente,$arrSub)) { // Busquemos girasoles
                    $grupo = 'girasoles';
                    
                } elseif (preg_match_all('/.*(dragon|clavel|avecilla|ave\s|paraiso|cartucho).*/ism',$ingrediente,$arrSub)) { // Busquemos girasoles
                    $grupo = 'flores raras';
                    
                } elseif (preg_match_all('/.*(bambu|chefle|cola|caballo|rush|perejil|aralia|chilindrina|follaje|croto|hoja|gardenia|mirto|gemela|baby|musgo|pandano|chiri).*/ism',$ingrediente,$arrSub)) { // Busquemos follaje
                    $grupo = 'follajes';
                    
                } elseif (preg_match_all('/.*(licor|liquor|cerveza|vino|sunset|whisky|wisky|tequila|botella|salchicha|queso|chorizo).*/ism',$ingrediente,$arrSub)) { // Busquemos guaro y gourmet
                    $grupo = 'Licor y Gourmet';
                    
                } elseif (preg_match_all('/.*(banan|mandarina|lim.n|lima|toronja|manzana|pera|mango|fresa|uva|guineo|banana|melecot|durazno|papaya|kiwi|cereza).*/ism',$ingrediente,$arrSub)) { // Busquemos fruta
                    $grupo = 'frutas';
                    
                } elseif (preg_match_all('/.*(full).*/ism',$ingrediente,$arrSub)) { // Busquemos fullys
                    $grupo = 'fullys';
                
                } elseif (preg_match_all('/.*(taza|base|cesta|bolsa|canasta|tronquito|tronco|balde|corteza|madera|florero|vidrio|copa|jarr).*/ism',$ingrediente,$arrSub)) { // Busquemos bases
                    $grupo = 'bases';

                } elseif (preg_match_all('/.*(globo|tarjeta|peluche|osito|oso|coraz).*/ism',$ingrediente,$arrSub)) { // Busquemos objetos adicionales
                    $grupo = 'objetos adicionales';
                    
                } elseif (preg_match_all('/.*(dulce|choco|hersh|bon|bom|ferrero|mash|chip|ahoy).*/ism',$ingrediente,$arrSub)) { // Busquemos dulces
                    $grupo = 'dulces';
                                        
                } elseif (preg_match_all('/.*(rosa).*/ism',$ingrediente,$arrSub)) { // Busquemos rosas
                    $grupo = 'rosas';
                }
                
                $receta[$grupo][] = $ingrediente . ' [para orden #'. $f['orden'].' - '.$f['nombre_arreglo'].' ]';
            }
            
        } else {
            $receta['errores'][] = 'Sin datos de preparacion para orden #'. $f['orden'].': '.$f['nombre_arreglo'] . ' | Cambie el mundo, agreguela Ud!: <a href="buscar?busqueda='.$f['codigo_producto'].'">clic aquí</a>';
        }
        
        
    }
    
    ksort ($receta);
    
    foreach ($receta AS $tipo => $contenido)
    {
        usort($contenido,'cmplista');
        echo '<h1>'.$tipo.'</h1>';
        echo '<ul><li>'.join ('</li><li>',$contenido).'</li></ul>';
    }
    
    if (count($especial))
    {
        echo '<hr />Tome en cuenta las siguientes notas para hacer los cambios respectivos:<br />';
        echo '<ul><li>'.join ('</li><li>',$especial).'</li></ul>';
    }
    return;
}

function AJAX_suspender_orden()
{
    if (empty($_POST['codigo_compra']) || !is_numeric($_POST['codigo_compra']) || empty($_POST['salt']))
        return;
    
    $c = sprintf('UPDATE '.db_prefijo.'SSL_compra_contenedor SET flag_suspendido=1 WHERE codigo_compra="%s" AND salt="%s" LIMIT 1',$_POST['codigo_compra'],$_POST['salt']);
    db_consultar($c);
    
    registrar($_POST['codigo_compra'], 'orden.suspendida', 'La orden fue marcada como suspendida');
}

function AJAX_reactivar_orden()
{
    if (empty($_POST['codigo_compra']) || !is_numeric($_POST['codigo_compra']) || empty($_POST['salt']))
        return;
    
    $c = sprintf('UPDATE '.db_prefijo.'SSL_compra_contenedor SET flag_suspendido=0 WHERE codigo_compra="%s" AND salt="%s" LIMIT 1',$_POST['codigo_compra'],$_POST['salt']);
    db_consultar($c);
    
    registrar($_POST['codigo_compra'], 'orden.reactivada', 'La orden fue marcada como activa nuevamente');
}

function AJAX_eliminar_orden()
{
    if (empty($_POST['codigo_compra']) || !is_numeric($_POST['codigo_compra']) || empty($_POST['salt']))
        return;
    
    $c = sprintf('UPDATE '.db_prefijo.'SSL_compra_contenedor SET flag_eliminado=IF(flag_eliminado=1, 0, 1) WHERE codigo_compra="%s" AND salt="%s" LIMIT 1',$_POST['codigo_compra'],$_POST['salt']);
    db_consultar($c);
    
    registrar($_POST['codigo_compra'], 'orden.eliminada', 'La orden fue marcada como eliminada');
}

function AJAX_timeline_estados()
{    
    $c = 'SELECT `tarjeta_de`, `tarjeta_para`, `nombre_completo`, `codigo_compra`, `salt`, `accion`, DATE_FORMAT(`timestamp`,"%a %e %H:%i") Fecha, valor_anterior, UNIX_TIMESTAMP(timestamp) AS tiempo FROM `flores_registro` LEFT JOIN `flores_usuarios` USING (codigo_usuario) LEFT JOIN `flores_SSL_compra_contenedor` USING(codigo_compra) WHERE (fecha_entrega >= DATE(NOW()) OR flag_cobrado = 0) AND timestamp > (NOW() - INTERVAL 20 HOUR) ORDER BY timestamp DESC';
    $r = db_consultar($c);
    
    if (mysqli_num_rows($r) > 0)
    {
        $ultima_actualizacion = 0;
        $tabla = '';
        $tabla .= '<table class="tabla-estandar zebra tabla-una-linea">';
        while ($ft = mysqli_fetch_assoc($r))
        {
            $tabla .= sprintf('<tr><td><a title="De: %s | Para: %s" href="ventas?c=%s">%s</a></td><td>%s</td><td>%s</td><td>%s%s</td></tr>',$ft['tarjeta_de'], $ft['tarjeta_para'],$ft['codigo_compra'],$ft['codigo_compra'].$ft['salt'], $ft['nombre_completo'], $ft['Fecha'], $ft['accion'], ($ft['valor_anterior'] && strlen($ft['valor_anterior']) < 25 ? ' | Valor anterior: ' . strip_tags($ft['valor_anterior']) : '' ));
            if (!$ultima_actualizacion)
            {
                $ultima_actualizacion = ($ft['tiempo'] * 1000);
            }
        }
        $tabla .= '</table>';
        echo json_encode(array('datos' => $tabla, 'ultima_actualizacion' => $ultima_actualizacion));
    }
}

function AJAX_modificar_estado_de_orden()
{
    if (!S_iniciado() && empty($_POST['codigo_compra']) || empty($_POST['objetivo']) || !isset($_POST['valor']))
        return;
        
    $DATOS[$_POST['objetivo']] = $_POST['valor'];
    
    if ($_POST['objetivo'] == 'flag_enviado' && $_POST['valor'] == 1)
        $DATOS['flag_elaborado'] = 1;
    
    $buscar_anterior = true;
    switch ($_POST['objetivo'])
    {
        case 'flag_enviado':
            $grupo = 'estado.enviado';
            $ret = ($_POST['valor'] == 0 ? 'Marcado como <b>no</b> enviado' : 'Marcado como enviado');
            $buscar_anterior = false;
            break;
        case 'flag_elaborado':
            $grupo = 'estado.elaborado';
            $ret = ($_POST['valor'] == 0 ? 'Marcado como <b>no</b> elaborado' : 'Marcado como elaborado');
            $buscar_anterior = false;
            break;
        case 'flag_cobrado':
            $grupo = 'estado.cobrado';
            $ret = ($_POST['valor'] == 0 ? 'Marcado como <b>no</b> cobrado' : 'Marcado como cobrado');
            $buscar_anterior = false;
            break;
        case 'estado_notas':
            $grupo = 'estado.notas';
            $ret = (empty ($_POST['valor']) ? 'Se borró la nota' : 'Nueva nota: <strong>'.$_POST['valor'] .'</strong>') ;
            break;
        case 'ruta':
            $grupo = 'estado.ruta';
            $ret = 'Cambio en ruta: <strong>'.$_POST['valor'] .'</strong>';
            break;
        case 'tarjeta_cuerpo':
            $grupo = 'dedicatoria';
            $ret = 'Cambio en dedicatoria: <strong>'.$_POST['valor'] .'</strong>';
            break;
        case 'direccion_entrega':
            $grupo = 'direccion';
            $ret = 'Cambio en dirección: <strong>'.$_POST['valor'] .'</strong>';
            break;
        case 'usuario_notas':
            $grupo = 'notas.usuario';
            $ret = 'Cambio en notas de usuario: <strong>'.$_POST['valor'] .'</strong>';
            break;
        case 'tarjeta_de':
            $grupo = 'nombre.contacto';
            $ret = 'Cambio en nombre de contacto: <strong>'.$_POST['valor'] .'</strong>';
            break;
        case 'tarjeta_para':
            $grupo = 'nombre.destinatario';
            $ret = 'Cambio en nombre de destinatario: <strong>'.$_POST['valor'] .'</strong>';
            break;
        case 'telefono_remitente':
            $grupo = 'telefono.contacto';
            $ret = 'Cambio en telefono de contacto: <strong>'.$_POST['valor'] .'</strong>';
            break;
        case 'telefono_destinatario':
            $grupo = 'telefono.destinatario';
            $ret = 'Cambio en telefono de destinatario: <strong>'.$_POST['valor'] .'</strong>';
            break;
        case 'correo_contacto':
            $grupo = 'correo';
            $ret = 'Cambio en correo de contacto: <strong>'.$_POST['valor'] .'</strong>';
            break;
        case 'fecha_entrega':
            $grupo = 'fecha.entrega';
            $ret = 'Cambio en fecha de entrega, nueva fecha: <strong>'.$_POST['valor'] .'</strong> | # orden anterior:'.db_obtener('flores_SSL_compra_contenedor', 'orden', 'codigo_compra='.$_POST['codigo_compra']);
            db_consultar('INSERT INTO `flores_maximo_orden` (`max_orden`,`fecha_entrega`) VALUES(1, "'.@$_POST['valor'] .'") ON DUPLICATE KEY UPDATE max_orden=max_orden+1');  
            $max_orden = db_obtener('flores_maximo_orden', 'max_orden', '`fecha_entrega` = "'.@$_POST['valor'] .'"');
            db_consultar('UPDATE `flores_SSL_compra_contenedor` SET `orden` = "'. $max_orden . '" WHERE codigo_compra='.$_POST['codigo_compra']);
            break;
        case 'cantidad':
            $grupo = 'cantidad';
            $ret = 'Cambio en cantidad de arreglos, nueva cantidad: <strong>'.$_POST['valor'] .'</strong>';
            break;
        case 'precio_grabado':
            $grupo = 'precio.arreglo';
            $ret = 'Cambio en precio de arreglo, nuevo monto: <strong>'.$_POST['valor'] .'</strong>';
            break;
        case 'cargo_adicional':
            $grupo = 'precio.extra';
            $ret = 'Cambio en cargo adicional, nuevo monto: <strong>'.$_POST['valor'] .'</strong>';
            break;
        case 'precio_envio':
            $grupo = 'precio.envio';
            $ret = 'Cambio en cargo de envio, nuevo monto: <strong>'.$_POST['valor'] .'</strong>';
            break;
        case 'preparacion_personalizada':
            $grupo = 'preparacion.personalizada';
            $ret = 'Cambio preparación de arreglo: <strong>'.$_POST['valor'] .'</strong>';
            break;
        default:
            $grupo = 'desconocido';
            $ret = 'ERROR: objetivo desconocido';
    }
    
    if ($buscar_anterior)
        $valor_anterior = db_obtener(db_prefijo.'SSL_compra_contenedor',$_POST['objetivo'],'codigo_compra="'.db_codex($_POST['codigo_compra']).'"');
    else
        $valor_anterior = 'N/A';
    
    db_actualizar_datos(db_prefijo.'SSL_compra_contenedor',$DATOS,'codigo_compra="'.db_codex($_POST['codigo_compra']).'"');
    
    registrar($_POST['codigo_compra'], $grupo, $ret, $valor_anterior);
    echo 'ok';
    return;
    
}

function AJAX_ayuda_telefonica()
{
    sms(78945941,'Ayuda telefonica - Telefono: '.$_POST['telefono']." - ".'Nombre: '.$_POST['nombre']);
    correoSMTP('ayuda.telefonica@flor360.com','Ayuda telefónica','Telefono: '.$_POST['telefono']."\n".'Nombre: '.$_POST['nombre'],false);
}

function AJAX_ruta()
{
    error_log('AJAX_ruta()');
    $c = 'SELECT DISTINCT `ruta` FROM flores_SSL_compra_contenedor WHERE fecha_entrega > "'.mysql_datetime('-15 day').'" AND `ruta` LIKE "%'.@$_GET['term'].'%"';
    $r = db_consultar($c);
    
    $ret = array();
    
    while ($f = mysqli_fetch_assoc($r))
        $ret[] = $f['ruta'];
        
    echo json_encode($ret);
    return;
}

function AJAX_arreglo_visto()
{
    unset($DATOS);
    $DATOS['codigo_producto'] = $_POST['codigo_producto'];
    $DATOS['fecha'] = mysql_datetime();
    db_agregar_datos(db_prefijo.'visita',$DATOS);
    unset($DATOS);
}

function AJAX_cambio_en_estado_de_orden()
{
    protegerme(true);
    
    $DATOS['estado_notas'] = $_POST['estado_notas'];
    $DATOS['ruta'] = $_POST['ruta'];
    $DATOS['flag_cobrado'] = empty($_POST['flag_cobrado']) ? 0 : $_POST['flag_cobrado'];
    $DATOS['flag_enviado'] = empty($_POST['flag_enviado']) ? 0 : 1;
    if ($DATOS['flag_enviado'] == 1) $_POST['flag_elaborado'] = 1;
    $DATOS['flag_elaborado'] = empty($_POST['flag_elaborado']) ? 0 : 1;
    
    
    db_actualizar_datos(db_prefijo.'SSL_compra_contenedor',$DATOS,'codigo_compra="'.db_codex($_POST['codigo_compra']).'"');
    
    switch ($_POST['flag_cobrado'])
    {
        case 0:
            $ret = 'No cobrado';
            break;
        case 1:
            $ret = 'Cobrado';
            break;
        case 2:
            $ret = 'Inválido';
            break;
        case 3:
            $ret = 'Cancelado';
            break;
        default:
            $ret = '¿?';
    }
    
    $elaborado = empty($_POST['flag_elaborado']) ? 'No elaborado' : 'Si elaborado';
    $enviado = empty($_POST['flag_enviado']) ? 'No enviado' : 'Si enviado';
    
    registrar($_POST['codigo_compra'],'estado','Nuevo estado: <strong>'.$ret.'</strong>, <strong>'.$elaborado.'</strong> y <strong>'.$enviado.'</strong><br />'.$_POST['estado_notas']);
    
    echo $_POST['codigo_compra'];
}

function AJAX_buscar_para_pago()
{

require_once(__BASE__ARRANQUE.'PHP/ssl.comun.php');
    
$HASH = sha1(trim(strtolower($_POST['hash'])));
$c = sprintf('SELECT comcon.indicerapido, comcon.metodo_pago, comcon.cobrar_a, comcon.cobrar_en, comcon.salt, comcon.flag_cobrado, comcon.flag_enviado, comcon.flag_elaborado, comcon.flag_eliminado, comcon.flag_suspendido, comcon.cantidad, provar.compra_minima, provar.foto, provar.descripcion AS "variedad_titulo", IF(comcon.preparacion_personalizada, comcon.preparacion_personalizada, provar.receta) AS receta, procon.codigo_producto, procon.titulo AS "contenedor_titulo",`codigo_compra`, `codigo_usuario`, `codigo_variedad`, `precio_grabado`, `cargo_adicional`, `precio_envio`, `nombre_t_credito`, `correo_contacto`, `direccion_entrega`, `fecha`, `fecha_entrega`, DATE_FORMAT(fecha,"%%e de %%M de %%Y [%%r]") fecha_formato, DATE_FORMAT(fecha_entrega,"%%e de %%M de %%Y") fecha_entrega_formato, `telefono_destinatario`, `telefono_remitente`, `tarjeta_de`, `tarjeta_para`, `tarjeta_cuerpo`, `estado_notas`, `ruta`, `usuario_notas`, `transaccion`, `cupon`, `orden`, (SELECT COUNT(*) FROM `'.db_prefijo.'SSL_compra_contenedor` WHERE `correo_contacto`=comcon.`correo_contacto` GROUP BY `correo_contacto`) AS "num_compras" FROM `'.db_prefijo.'SSL_compra_contenedor` AS comcon LEFT JOIN '.db_prefijo.'producto_variedad AS provar USING(codigo_variedad) LEFT JOIN flores_producto_contenedor AS procon USING(codigo_producto) WHERE indicerapido="%s"',$HASH);

$r = db_consultar($c);

if ($f = mysqli_fetch_assoc($r))
{
    
    list($f['extras'], $f['extrasPrecio']) = SSL_COMPRA_OBTENER_EXTRAS($f['codigo_compra']);
    
    if ($f['flag_eliminado'] != '0')
    {
        echo '<p>Este pedido fue eliminado del sistema, debe solicitar que lo restauren antes de poder marcarlo como pagado</p>';
        return;
    }

    if ($f['flag_suspendido'] != '0')
    {
        echo '<p>Este pedido fue suspendido (pausado) en el sistema, debe solicitar que lo desbloqueen antes de poder marcarlo como pagado</p>';
        return;
    }
    
    if ($f['flag_cobrado'] != '0')
    {
        echo '<p>Este pedido ya fue cobrado según el sistema, debe solicitar que lo verifiquen antes de poder marcarlo como pagado</p>';
        return;
    }
    
        $info_producto_foto =
    '<a target="_blank" href="'.PROY_URL.URL_SUFIJO_VITRINA.SEO($f['contenedor_titulo'].'-'.$f['codigo_producto']).'?variedad='.$f['codigo_variedad'].'">'.
    '<img style="width:133px;height:200px" src="'.imagen_URL($f['foto'],133,200).'" /></a>'.
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
    '<tr style="background-color:yellow;"><td><strong>Total:</strong></td><td>$<input type="text" readonly="readonly" style="width:60px;padding:0;border:none;" value="'.number_format((($f['precio_grabado']*$f['cantidad'])+$f['precio_envio']+$f['cargo_adicional']+$f['extrasPrecio']),2,'.',',').'" /></td></tr>'.
    ($f['cupon'] ? '<strong>Cupón:</strong> '.$f['cupon'] : '').
    '</table>';

    $info_importante =
    '<table class="tabla-estandar">'.
    '<tr>'.
    '<td>'.
    '<strong>Fecha pedido:</strong> '.$f['fecha_formato'].
    '</td>'.
    '<td>'.
    '<strong>Fecha entrega:</strong> '.$f['fecha_entrega_formato'].
    '</td>'.
    '</tr><tr>'.
    '<td>'.
    '<strong>Código de compra: </strong> <a style="font-family:monospace;" href="'.PROY_URL_ACTUAL.'?cc='.$f['indicerapido'].'">'.$f['codigo_compra'].$f['salt'].'</a></span></a>'.
    '</td>'.
    '<td>'.
    '<strong>Ingresado por: </strong>'.($f['codigo_usuario'] ? 'Agente flor360.com' : 'cliente vía web').
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
    '<strong>Dirección entrega</strong><textarea style="width:98%;height:70px;" class="autoguardar" plantilla="cambio.direccion" objetivo="direccion_entrega">'.$f['direccion_entrega'].'</textarea>'.
    '<table style="width:99%;table-layout:fixed;border-collapse:collapse;">'.
    '<tr><td>'.
    '<strong>Notas del comprador</strong><textarea style="width:98%;height:80px;" class="autoguardar" plantilla="cambio.notas" objetivo="usuario_notas">'.$f['usuario_notas'].'</textarea>'.
    '</td><td>'.
    '<strong>Extras</strong> [ <a class="editar_extras" href="#" rel="'.$f['codigo_compra'].'">editar</a> ]<br /><textarea style="width:98%;height:80px;">'.$f['extras'].'</textarea>'.
    '</tr>'.
    '</table>'.
    '<strong>Elementos para preparación</strong><textarea style="width:98%;height:35px;" class="autoguardar" plantilla="preparacion" objetivo="preparacion_personalizada">'.$f['receta'].'</textarea>';
    
    echo '
    <div class="contenedor-compra">
        <div class="info-producto-foto">
        '.$info_producto_foto.'
        </div>
    
        <div class="info-importante">
        '.$info_importante.'
        </div>
        
        <div class="info-derecha">
            <div style="text-align:center;">
                <form id="enviar_pago" action="ajax" method="post">
                    <p><input type="radio" name="metodo_pago" checked="checked" value="kiosko_efectivo" /> Efectivo <input type="radio" name="metodo_pago" value="kiosko_credito" /> POS</span></p>
                    <input type="button" style="font-size:18px;" id="pagar_arreglo" rel="'.$f['codigo_compra'].'" value="Pagar arreglo" />
                </form>
            </div>
        </div>
    </div>';

} else {
    echo '<p>No se encontró ningún pedido que coinicidiera con ese código de compra.</p>';
}    
}

function AJAX_procesar_pago()
{
    if (!S_iniciado()) return;
    
    // Marcar como flag_cobrado=1
    // Metodo pago = kiosko_efectivo|kiosko_credito dependiendo de $_POST['metodo_pago']
    // registrar() = "Arreglo marcado como cobrado en Kiosko via kiosko_efectivo|kiosko_credito por codigo_usuario
    // Agregar la transaccion al kiosko como articulo #100
    
    $codigo_compra  = db_codex($_POST['codigo_compra']);
    $metodo_pago    = db_codex($_POST['metodo_pago']);
    
    $c = sprintf('UPDATE `flores_SSL_compra_contenedor` SET metodo_pago="%s", flag_cobrado=1, estado_notas="Pago realizado en Kiosko via módulo de pago" WHERE codigo_compra="%s" LIMIT 1', $metodo_pago, $codigo_compra);
    $r = db_consultar($c);
    
    if (db_afectados() <> 1)
    {
        echo '<p>Hubo un error al efectuar el pago. ['.mysqli_affected_rows($r).']</p>';
        return;
    }
    
    $transaccion = db_obtener('flores_SSL_compra_contenedor','transaccion','codigo_compra='.$codigo_compra);
    
    echo '<p>Cobro efectuado exitosamente.</p>';
    echo '<p>Imprimir comprobante de pago: <a target="_blank" href="/+impresion?objetivo=Boucher&transaccion='.$transaccion.'">clic aqui</a>.</p>';
    
    registrar($codigo_compra,'pago.kiosko','Pago realizado en Kiosko via módulo de pago','');
    
    $precio_grabado = db_obtener('flores_SSL_compra_contenedor','(cantidad*precio_grabado)+cargo_adicional+precio_envio','codigo_compra='.$codigo_compra);
    
    $c = sprintf('INSERT INTO `flores_kiosko_transacciones` (`codigo_kiosko_articulo`, `codigo_usuario`, `operacion`, `cantidad`, `precio_grabado`, `fecha`, `transaccion`, `metodo_pago`,`descripcion`,`flag_arreglo`) VALUES(100, %s,"venta",1,%s,NOW(),"%s","%s","%s",1)', _F_usuario_cache('codigo_usuario'),$precio_grabado, $codigo_compra, $metodo_pago, 'A: #'.$codigo_compra);
    $r = db_consultar($c);
}

function AJAX_buscar_para_devolucion()
{

    $indicerapido = sha1(trim(strtolower($_POST['hash'])));
    $c = sprintf('SELECT comcon.indicerapido, comcon.metodo_pago, comcon.cobrar_a, comcon.cobrar_en, comcon.salt, comcon.flag_cobrado, comcon.flag_enviado, comcon.flag_elaborado, comcon.flag_eliminado, comcon.flag_suspendido, comcon.cantidad, provar.compra_minima, provar.foto, provar.descripcion AS "variedad_titulo", IF(comcon.preparacion_personalizada, comcon.preparacion_personalizada, provar.receta) AS receta, procon.codigo_producto, procon.titulo AS "contenedor_titulo",`codigo_compra`, `codigo_usuario`, `codigo_variedad`, `precio_grabado`, `cargo_adicional`, `precio_envio`, `nombre_t_credito`, `correo_contacto`, `direccion_entrega`, `fecha`, `fecha_entrega`, DATE_FORMAT(fecha,"%%e de %%M de %%Y [%%r]") fecha_formato, DATE_FORMAT(fecha_entrega,"%%e de %%M de %%Y") fecha_entrega_formato, `telefono_destinatario`, `telefono_remitente`, `tarjeta_de`, `tarjeta_para`, `tarjeta_cuerpo`, `estado_notas`, `ruta`, `usuario_notas`, `transaccion`, `cupon`, `orden`, (SELECT COUNT(*) FROM `'.db_prefijo.'SSL_compra_contenedor` WHERE `correo_contacto`=comcon.`correo_contacto` GROUP BY `correo_contacto`) AS "num_compras" FROM `'.db_prefijo.'SSL_compra_contenedor` AS comcon LEFT JOIN '.db_prefijo.'producto_variedad AS provar USING(codigo_variedad) LEFT JOIN flores_producto_contenedor AS procon USING(codigo_producto) WHERE indicerapido = "%s"', $indicerapido);
    $r = db_consultar($c);
    
    if ($f = mysqli_fetch_assoc($r))
    {    
        
        if ($f['flag_cobrado'] == '0')
        {
            echo '<p>Este pedido no fue cobrado según el sistema, debe solicitar que lo verifiquen antes de poder intentar una devolución</p>';
            return;
        }
        
        $consulta = 'SELECT `codigo_devolucion`, `indicerapido`, `creado_por`, `ejecutado_por`, `recibido_por`, `monto`, `fecha_creado`, `fecha_cobrado` FROM `flores_devoluciones` WHERE indicerapido="'.$indicerapido.'"';
        $resultado = db_consultar($consulta);
        
        if ($fdevolucion = mysqli_fetch_assoc($resultado))
        {

            if ($fdevolucion['ejecutado_por'] != '0')
            {
                echo '<p>Esta devolución ya fue efectuada a: '.$fdevolucion['recibido_por'].'</p>';
                return;
            }

            $info_devolucion = '<p style="font-size:22px;text-align:center;">devolución por</p><p style="font-size:22px;text-align:center;font-weight:bold;color:red;">$'.$fdevolucion['monto'].'</p>';
            $info_devolucion .= '
            <div>
                    <label style="font-size:18px;" for="recibido_por">a favor de</label>
                    <br />
                    <input style="width:195px;margin:auto;" id="recibido_por" value="" /><br />
                    <p class="medio-oculto" style="text-align:center;">(nombre y DUI)</p>
                    <input type="button" style="font-size:18px;margin-top:10px;" id="hacer_devolucion" codigo_devolucion="'.$fdevolucion['codigo_devolucion'].'" codigo_compra="'.$f['codigo_compra'].'" value="Hacer devolucion" />
            </div>
            ';
        } else {
            $info_devolucion = 'No hay devolución autorizada para este pedido';
        }
        
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
    
            $info_importante =
        '<table class="tabla-estandar">'.
        '<tr>'.
        '<td>'.
        '<strong>Fecha pedido:</strong> '.$f['fecha_formato'].
        '</td>'.
        '<td>'.
        '<strong>Fecha entrega:</strong> <span id="fecha_entrega_formato_'.$f['codigo_compra'].'">'.$f['fecha_entrega_formato'].'</span>
        <input id="fecha_entrega_'.$f['codigo_compra'].'" type="text" style="width:60px;padding:0;border:1px solid grey;display:none;" class="autoguardar" objetivo="fecha_entrega" plantilla="fecha_entrega" value="'.$f['fecha_entrega'].'" />'.
        '</td>'.
        '</tr><tr>'.
        '<td>'.
        '<strong>Código compra: </strong> '.$f['codigo_compra'].$f['salt'].' | <b>No. total de compras:</b> '.$f['num_compras'].
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
        '<strong>Tarjeta Cuerpo</strong><textarea style="width:98%;height:100px;" class="autoguardar" plantilla="cambio.dedicatoria" objetivo="tarjeta_cuerpo">'.$f['tarjeta_cuerpo'].'</textarea><br />'.
        '<strong>Dirección entrega</strong><textarea style="width:98%;height:100px;" class="autoguardar" plantilla="cambio.direccion" objetivo="direccion_entrega">'.$f['direccion_entrega'].'</textarea>'.
        '<strong>Notas del comprador</strong><textarea style="width:98%;height:100px;" class="autoguardar" plantilla="cambio.notas" objetivo="usuario_notas">'.$f['usuario_notas'].'</textarea>'.
        '</p>';
        
        echo '
        <div class="contenedor-compra">
            <div class="info-producto-foto">
            '.$info_producto_foto.'
            </div>
        
            <div class="info-importante">
            '.$info_importante.'
            </div>
            
            <div class="info-derecha">
                <div style="text-align:center;">
                    '.$info_devolucion.'
                </div>
            </div>
        </div>';
    
    } else {
        echo '<p>No se encontró ningún pedido que coinicidiera con ese código de compra.</p>';
    }    
}

function AJAX_procesar_devolucion()
{
    if (!S_iniciado() || empty($_POST['codigo_devolucion']) || !is_numeric($_POST['codigo_devolucion'])|| empty($_POST['codigo_compra']) || !is_numeric($_POST['codigo_compra'])) return;
    
    // Marcar ejecutado_por = sesion->codigo_usuario
    // recibidor_por = nombre de quien recibió
    // registrar() = "Devolución ejecutada en Kiosko por nombre_completo"
    // Agregar la transaccion al kiosko como articulo 101 = devolucion, tipo devolucion

    $recibido_por = trim(db_codex($_POST['recibido_por']));
    $codigo_devolucion  = $_POST['codigo_devolucion'];
    $codigo_compra      = $_POST['codigo_compra'];

    if ($recibido_por == '')
    {
        echo '<p>Debió especificar un nombre y DUI de la persona que recibirá el dinero.</p>';
        return;
    }
    
    $c = sprintf('UPDATE `flores_devoluciones` SET ejecutado_por="%s", recibido_por="%s", fecha_cobrado=NOW() WHERE codigo_devolucion="%s" LIMIT 1', _F_usuario_cache('codigo_usuario'), $recibido_por, $codigo_devolucion);
    $r = db_consultar($c);
    
    if (db_afectados() <> 1)
    {
        echo '<p>Hubo un error al efectuar la devolución.</p>';
        return;
    }
    
    echo '<p>Devolución efectuada exitosamente.</p>';
    echo '<p>Imprimir comprobante de devolución: <a target="_blank" href="/+impresion?objetivo=Devolucion&transaccion='.$codigo_devolucion.'">clic aqui</a>.</p>';
    
    registrar($codigo_compra,'devolucion.ejecutada','Devolución ejecutada en Kiosko a favor de: <b>'.$recibido_por.'</b>','');
    
    $precio_grabado = db_obtener('flores_devoluciones','monto','codigo_devolucion='.$codigo_devolucion);
    
    $c = sprintf('INSERT INTO `flores_kiosko_transacciones` (`codigo_kiosko_articulo`, `codigo_usuario`, `operacion`, `cantidad`, `precio_grabado`, `fecha`, `transaccion`, `descripcion`) VALUES(101, %s,"devolucion",1,%s,NOW(), "%s", "%s")', _F_usuario_cache('codigo_usuario'), $precio_grabado, $codigo_devolucion, 'D: #'.$codigo_devolucion);
    $r = db_consultar($c);
}
?>
