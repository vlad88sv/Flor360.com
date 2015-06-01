<?php
/*****************************************************************************/
/* Emite facturas virtuales para la compra. */
function SSL_COMPRA_FACTURA($transaccion, $cambiar_transaccion_por_codigo = false)
{
    $campo = ($cambiar_transaccion_por_codigo ? 'codigo_compra' : 'transaccion');
    $c = sprintf('SELECT AES_DECRYPT(`n_credito`,"'.db__key_str.'") AS n_credito_DAES, comcon.`ruta`, comcon.`flag_cobrado`, comcon.`flag_enviado`, comcon.`flag_suspendido`, comcon.`flag_eliminado`, comcon.`cupon`, comcon.`anonimo`, comcon.`indicerapido`, comcon.`cantidad`, comcon.`orden`, procon.`codigo_producto`, procon.`titulo` AS "titulo_contenedor", provar.`descripcion` AS "titulo_variedad", provar.foto, IF(comcon.preparacion_personalizada != "", comcon.preparacion_personalizada, provar.receta) AS receta, comcon.`codigo_compra`, comcon.`codigo_usuario`, nombre_artistico, comcon.`codigo_variedad`, FORMAT(comcon.`precio_original`,2) AS precio_original, FORMAT(comcon.`precio_grabado`,2) AS precio_grabado, FORMAT(comcon.`cargo_adicional`,2) AS cargo_adicional, FORMAT(comcon.`precio_envio`,2) AS precio_envio, comcon.`direccion_entrega`, comcon.`fecha_entrega`, DATE_FORMAT(comcon.`fecha`,"%%W %%e de %%M de %%Y [%%r]") fecha_formato, DATE_FORMAT(comcon.`fecha_entrega`,"%%W %%e de %%M de %%Y") fecha_entrega_formato, comcon.`tarjeta_de`, comcon.`tarjeta_para`, comcon.`tarjeta_cuerpo`, comcon.`usuario_notas`, comcon.`transaccion`, comcon.`fecha`, comcon.`salt`, comcon.`metodo_pago`, comcon.`cobrar_en`, comcon.`cobrar_a`, `correo_contacto`, `telefono_remitente`, `telefono_destinatario`, `usuario_notas`, `nombre_t_credito`, `estado_notas`, (SELECT COUNT(*) FROM `flores_SSL_compra_contenedor` AS sccx WHERE sccx.`correo_contacto`=comcon.`correo_contacto` GROUP BY `correo_contacto`) AS "num_compras", IF(metodo_pago="tarjeta",(SELECT COUNT(*) FROM `flores_SSL_compra_contenedor` AS scc WHERE scc.`n_credito`= comcon.`n_credito`),"N/A") AS "num_compras_2" FROM `flores_SSL_compra_contenedor` AS comcon LEFT JOIN `'.db_prefijo.'producto_variedad` AS provar USING(codigo_variedad) LEFT JOIN `flores_producto_contenedor` AS procon USING(codigo_producto)  LEFT JOIN flores_usuarios USING(codigo_usuario) WHERE %s="%s"',$campo, db_codex($transaccion));
    $r = db_consultar($c);

    if (!mysqli_num_rows($r))
    {
        echo '<p>Lo sentimos, tal factura no existe</p>';
        return;
    }

    $f = mysqli_fetch_assoc($r);
    
    list ($f['extras'], $f['extrasPrecio']) = SSL_COMPRA_OBTENER_EXTRAS($f['codigo_compra']);
    
    $f['precio_total'] = number_format((($f['precio_grabado']*$f['cantidad'])+$f['precio_envio']+$f['cargo_adicional']+$f['extrasPrecio']),2,'.',',');

    switch ($f['metodo_pago'])
    {
        case 'kiosko_credito':
            $f['metodo_pago_fmt'] = 'Pago realizado con tarjeta en sucursal';
            break;
        case 'kiosko_efectivo':
            $f['metodo_pago_fmt'] = 'Pago realizado en efectivo en sucursal';
            break;
        case 'tarjeta':
            $f['metodo_pago_fmt'] = 'Pago realizado con tarjeta en línea';
            break;
        case 'domicilio':
            $f['metodo_pago_fmt'] = 'Cobro a domicilio solicitado';
            break;
        case 'abono':
            $f['metodo_pago_fmt'] = 'Pago a realizar mediante abono a cuenta bancaria de BAC';
            break;
        case 'kiosko':
            $f['metodo_pago_fmt'] = 'Pago será realizado personalmente en sucursal';
            break;
        case 'diferido':
            $f['metodo_pago_fmt'] = 'Pago será diferido a asociado';
            break;
    }
    
    $f['producto'] = ($f['titulo_contenedor'] == $f['titulo_variedad'] ? $f['titulo_contenedor'] :  $f['titulo_contenedor'] . ' - ' . $f['titulo_variedad']);
    
    $campo = array(
        'Código de compra' => $f['codigo_compra'].$f['salt'],
        'Método de pago' =>  $f['metodo_pago_fmt'],
        'Ingresado por' =>  ( $f['nombre_artistico'] == '' ? 'Cliente' : $f['nombre_artistico'] ),
        'Fecha pedido' => $f['fecha_formato'],
        'Fecha entrega' => $f['fecha_entrega_formato'],
        'Producto' => "#" . $f['codigo_producto'] . " - " . $f['titulo_contenedor'],
        'Variedad' => $f['titulo_variedad'],
        'Cantidad' => $f['cantidad'],
        'Precio original' => '$'.$f['precio_original'],
        'Precio final' => '$'.$f['precio_grabado'],
        'Cargo adicional' => '$'.$f['cargo_adicional'],
        'Cargo por extras' => '$'.number_format($f['extrasPrecio'],2,'.',','),
        'Cargo de envio' => '$'.$f['precio_envio'],
        'Total' => '$'.$f['precio_total'],
        'Contacto' => $f['tarjeta_de'],
        'Tel. contacto' => $f['telefono_remitente'],
        'Correo contacto' => $f['correo_contacto'],
        'Destinatario' => $f['tarjeta_para'],
        'Tel. destinatario' => $f['telefono_destinatario'],
        'Tarjeta' => $f['tarjeta_cuerpo'],
        'Dirección' => $f['direccion_entrega'],
        'Extras' => $f['extras'],
        'Entrega anónima' => ($f['anonimo'] == 0 ? 'No' : 'Si'),
        'Código cupón/socio' => $f['cupon'] ?: '[No se utilizó]',
        'Notas adicionales' => $f['usuario_notas'] ? $f['usuario_notas'] : '[No especificó nada en especial]',
        'Facturación con atención a' => $f['cobrar_a'] ? $f['cobrar_a'] : '[No especificó]',
        'Facturación en dirección' => $f['cobrar_en'] ? $f['cobrar_en'] : '[No especificó]',
        
    );
    
    $buffer = '<table style="width:100%;border-collapse:collapse;">';
    
    foreach($campo AS $clave => $valor)
        $buffer .= sprintf('<tr><td style="border:1px solid #c0c0c0;width:150px;">%s</td><td style="font-weight:bold;border:1px solid #c0c0c0;">%s</td></tr>',$clave, $valor);
    
    $buffer .= '</table>';
    
    $buffer .= '<div style="text-align:middle;border:1px solid black;margin:5px 0;padding:5px;"><img style="margin:auto;width:266px;height:400px;" src="'.imagen_URL($f['foto'], 266, 400, $servidor=null).'" /></div>';

    return array($buffer,$f);
}

 /* Procesar Flags
  * e = elaborado
  * c = cobrado
  * E = enviado
  *
  * Ej.:
  * -e+c = no elaborados y cobrados
  * +c-E = cobrados y no enviados
  *
  * La omisión de un flag significa "no importa el estado de este flag"
  */

function procesar_flags_arreglos()
{
    $WHERE = '';
    if (!empty ($_GET['flags']))
    {
        $_GET['flags'] = str_replace(' ','+',$_GET['flags']);
        
        if (strstr($_GET['flags'],'+e'))
            $WHERE .= ' AND flag_elaborado = 1';
        
        if (strstr($_GET['flags'],'-e'))
            $WHERE .= ' AND flag_elaborado = 0';
            
        if (strstr($_GET['flags'],'+E'))
            $WHERE .= ' AND flag_enviado = 1';
            
        if (strstr($_GET['flags'],'-E'))
            $WHERE .= ' AND flag_enviado = 0';

        if (strstr($_GET['flags'],'+x'))
            $WHERE .= ' AND flag_eliminado = 1';
            
        if (strstr($_GET['flags'],'-x'))
            $WHERE .= ' AND flag_eliminado = 0';
            
        if (strstr($_GET['flags'],'+c'))
            $WHERE .= ' AND flag_cobrado = 1';
            
        if (strstr($_GET['flags'],'-c'))
            $WHERE .= ' AND flag_cobrado = 0';

        if (strstr($_GET['flags'],'+a'))
            $WHERE .= ' AND metodo_pago = "abono"';

        if (strstr($_GET['flags'],'-a'))
            $WHERE .= ' AND metodo_pago != "abono"';

        if (strstr($_GET['flags'],'+A'))
            $WHERE .= ' AND metodo_pago = "diferido"';

        if (strstr($_GET['flags'],'-A'))
            $WHERE .= ' AND metodo_pago != "diferido"';
            
        if (strstr($_GET['flags'],'+d'))
            $WHERE .= ' AND metodo_pago = "domicilio"';
        
        if (strstr($_GET['flags'],'-d'))
            $WHERE .= ' AND metodo_pago != "domicilio"';
            
        if (strstr($_GET['flags'],'+C'))
            $WHERE .= ' AND metodo_pago = "tarjeta"';
        
        if (strstr($_GET['flags'],'-C'))
            $WHERE .= ' AND metodo_pago != "tarjeta"';
        
        if (strstr($_GET['flags'],'+kiosko_credito'))
            $WHERE .= ' AND metodo_pago = "kiosko_credito"';

        if (strstr($_GET['flags'],'+kiosko'))
            $WHERE .= ' AND metodo_pago = "kiosko"';
            
    }
    
    return $WHERE;
}

function SSL_COMPRA_OBTENER_EXTRAS($codigo_compra)
{
    if (!is_numeric($codigo_compra)) return array('',0);
    
    $c = 'SELECT `nombre`, `especificacion`, `precio` FROM `extras_compras` LEFT JOIN `extras` USING(codigo_extra) WHERE `codigo_compra` = ' . $codigo_compra;
    $rExtra = db_consultar($c);
    $extras = '';
    $extrasPrecio = 0;
    
    while ($rExtra && $fExtra = mysqli_fetch_assoc($rExtra))
    {
        $extras .= $fExtra['nombre'] . ($fExtra['especificacion'] ? ' ['.$fExtra['especificacion'].']' : '' ). ' - $'. $fExtra['precio']."\r\n";
        $extrasPrecio += $fExtra['precio'];
    }
    
    if ($extras == '')
    {
        $extras = 'Sin detalles extras';
    }
    
    return array($extras, $extrasPrecio);
}

function SSL_COMPRA_OBTENER_ANEXOS($codigo_compra, $html = false)
{
    if (!is_numeric($codigo_compra)) return array('',0);
    
    $c = 'SELECT `codigo_anexo`, `codigo_compra`, `codigo_usuario_recibio`, t2.nombre_completo AS "nombre_usuario_recibio", `codigo_usuario_confirmo`, `descripcion`, `flag_paquete` FROM `anexos` AS t1 LEFT JOIN `flores_usuarios` AS t2 ON t1.codigo_usuario_recibio = t2.codigo_usuario WHERE `codigo_compra` = ' . $codigo_compra;
    $rAnexo = db_consultar($c);
    
    if (mysqli_num_rows($rAnexo) == 0)
    {
        return false;
    }
    
    $anexos = array();
    
    while ($rAnexo && $fAnexo = mysqli_fetch_assoc($rAnexo))
    {
        $anexos[] = $fAnexo;
    }
        
    return $anexos;
}

function SSL_COMPRA_OBTENER_FRAUDIMETRO($transaccion)
{
    list($factura, $f) = SSL_COMPRA_FACTURA($transaccion);

    list($f['extras'], $f['extrasPrecio']) = SSL_COMPRA_OBTENER_EXTRAS($f['codigo_compra']);
    
    list($heuristica, $heuristicas) = obtener_heuristicas($f);
    
    $nivel = 0;
 
    if (db_contar('blacklist', 'tarjeta="'.$f['n_credito_DAES'].'"') > 0)
        $nivel += 10;

    if (db_contar('blacklist_correo', 'correo="'.$f['correo_contacto'].'"') > 0)
        $nivel += 10;
    
    // Este envío necesita algún objeto que proporcionará el cliente para estar completo
    if (preg_match_all('/.*(soya|margaritas|campanera).*/ism',$f['direccion_entrega'].' '.$f['usuario_notas'],$arrSub))
        $nivel += 1;
    
    if (isset($heuristica['peluche']))
        $nivel += 1;
    
    if (isset($heuristica['dulces']))
        $nivel += 2;
    
    if (isset($heuristica['alcohol']))
        $nivel += 5;
    
    // La dedicatoria muy corta?
    if (strlen($f['tarjeta_cuerpo']) < 15)
        $nivel += 1;
        
    
    if ( $f['metodo_pago'] == 'tarjeta' ) {
        if ( $f['num_compras_2'] > $f['num_compras'] )
        {
            $nivel += 5;
        }
    
        if ( ( $f['num_compras'] - $f['num_compras_2'] ) > 2 )
        {
            $nivel += 5;
        }
    }
    
    if ( $f['precio_total'] > 50 )
        $nivel += 1;
    
    if ( $f['precio_total'] > 100 )
        $nivel += 2;
        
    
    // Implementar cache aqui
    $c = "SELECT regex FROM `blacklist_keywords` WHERE 1";
    $r = db_consultar($c);
    
    $cadena = $f['tarjeta_de']. ' ' . $f['tarjeta_para']. ' ' . $f['direccion_entrega'] . ' ' . $f['usuario_notas'];
    
    while ($f = db_fetch($r))
    {
        $palabras = explode('|',$f['regex']);
        
        if (in_string($palabras, $cadena, 'all'))
            $nivel += 8;
    }
    
    
    
    return $nivel;
    
}
?>
