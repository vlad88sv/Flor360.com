<?php
protegerme(false,array(_N_vendedor));

if(!isset($_GET['objetivo']))
{
    echo '<p>¿Objetivo?. Cancelando</p>';
    return;
    
}
?>
<style>
    @media print,screen
    {
        *{background-color:#FFF !important;}
        .gris {color: grey;}
    }
    
    #factura{width:100%;border-collapse:collapse;}
    #factura td{border:1px solid #ccc;}
</style>
<?php
require_once(__BASE_cePOSa__.'PHP/ssl.comun.php');
$arrJS[] = 'jquery.barcode';

$GLOBAL_IMPRESION = true;
switch($_GET['objetivo'])
{    
    case 'Factura':
        echo '<div style="width:7.8in;overflow:hidden;">';
        IMPRIMIR_factura();
        echo '</div>';
        break;
    
    case 'Boucher':
        echo '<div style="width:7.8in;overflow:hidden;">';
        IMPRIMIR_boucher();
        echo '</div>';
        break;
    
    case 'Firma':
        IMPRIMIR_firma(false);
        break;
        
    case 'Pedido':
        echo '<div style="width:7.8in;overflow:hidden;">';
        IMPRIMIR_pedido();
        echo '</div>';
        break;

    case 'Tarjeta':
        IMPRIMIR_tarjeta_tipo_1();
        break;

    case 'FirmaAvanzada':
        echo '<div style="width:7.8in;overflow:hidden;">';
        IMPRIMIR_firmaAvanzada();
        echo '</div>';
        break;

    case 'Tiquete':
        IMPRIMIR_tiquete();
        break;
    
    case 'CorteZ':
        IMPRIMIR_cortez();
        break;
}

function IMPRIMIR_tarjeta_tipo_1()
{
    if(!isset($_GET['transaccion']))
    {
        echo 'Necesita un codigo de transaccion para utilizar esta herramienta';
        return;
    }

   list($buffer,$f) = SSL_COMPRA_FACTURA($_GET['transaccion']);
   
   $str_len = strlen($f['tarjeta_cuerpo']);

   if( $str_len > 450) {
    IMPRIMIR_tarjeta_tipo_2();
    return;
   }
   
   if( $str_len > 150)
    $font_size = "10pt";
   elseif( $str_len > 125)
    $font_size = "11pt";
   elseif( $str_len > 100)
    $font_size = "12pt";
   elseif( $str_len > 75)
    $font_size = "13pt";
   else
    $font_size = "14pt";

    echo '<div style="position: relative;margin-left:10px;width:230px;">';
    echo '<table style="border-collapse:collapse;width:100%;">';
    
    echo '<tr><td style="vertical-align:middle;height:250px;font-size:14pt;overflow:hidden;text-align:middle;">';
    echo '<div contenteditable="true">Para:</div><div contenteditable="true">'.ucwords(strtolower($f['tarjeta_para'])).'</div>';
    echo '</td></tr>';
    
    echo '<tr><td style="vertical-align:middle;height:240px;overflow:hidden;">';
    echo '<div style="font-size:'.$font_size.';text-align:middle;" contenteditable="true">'.nl2br($f['tarjeta_cuerpo']).'</div>';
    echo '</td></tr>';
    echo '</table>';
   
    echo '<div style="position:absolute;top:20px;left:0px;right:0px;text-align:center;font-size:7pt;">'.$f['orden'].'</div>'; 
    
    echo '</div>';
}

function IMPRIMIR_tarjeta_tipo_2()
{
    if(!isset($_GET['transaccion']))
    {
        echo 'Necesita un codigo de transaccion para utilizar esta herramienta';
        return;
    }

   list($buffer,$f) = SSL_COMPRA_FACTURA($_GET['transaccion']);
   echo '<div style="width: 230px; height:400px; margin-left:10px;  margin-top:10px; position: relative;">';
   $str_len = strlen($f['tarjeta_cuerpo']);

   if( $str_len > 550)
    $font_size = "11px";
   elseif( $str_len > 500)
    $font_size = "12px";
   else
    $font_size = "13px";

    echo '<table style="width:100%;height:450px;">';
    echo '<tr><td style="height:50px;font-size:13px;overflow:hidden;">Para:<br /><div contenteditable="true">'.ucwords(strtolower($f['tarjeta_para'])).'</div></td></tr>';
    echo '<tr><td style="height:400px;overflow:hidden;"><section style="font-size:'.$font_size.' !important;text-align:center;" contenteditable="true">'.nl2br($f['tarjeta_cuerpo']).'</section></td></tr>';
    echo '</table>';
   
    echo '<div style="position:absolute;top:5px;right:5px;">'.$f['orden'].'</div>';
    
    echo '</div>';
}

function implotar_rango($m)
{
    return implode(',', range($m[1], $m[2]));
}

function IMPRIMIR_firmaAvanzada()
{
    if (isset($_GET['fecha_entrega']) && !empty($_GET['ordenes']))
    {
        //Expandir 1-5 a 1,2,3,4,5
        $_GET['ordenes'] = preg_replace_callback('/(\d+)-(\d+)/', "implotar_rango", $_GET['ordenes']);
        $c = sprintf('SELECT transaccion FROM flores_SSL_compra_contenedor WHERE flag_eliminado=0 AND flag_suspendido=0 AND fecha_entrega = "%s" AND orden IN (%s) ORDER BY `orden` ASC',$_GET['fecha_entrega'], $_GET['ordenes']);
    } elseif ( isset($_GET['fecha_entrega']) && isset($_GET['ruta']) ) {
        $c = sprintf('SELECT transaccion FROM flores_SSL_compra_contenedor WHERE flag_eliminado=0 AND flag_suspendido=0 AND fecha_entrega = "%s" AND ruta = "%s" ORDER BY `orden` ASC',$_GET['fecha_entrega'], $_GET['ruta']);
    } elseif (isset($_GET['fecha_entrega'])) {
        $c = sprintf('SELECT transaccion FROM flores_SSL_compra_contenedor WHERE flag_eliminado=0 AND flag_suspendido=0 AND fecha_entrega = "%s" %s ORDER BY `orden` ASC',$_GET['fecha_entrega'], procesar_flags_arreglos());
    } else {
        echo ("ERROR impresion::IMPRIMIR_firmaAvanzada()");
        return;
    }
    
    $r = db_consultar($c);
    $i = 0;
    
    if (mysqli_num_rows($r) == 0)
    {
        echo ("<p>No se encontró nada que imprimir!. Verifique si los FLAGS son correctos.</p>");
        return;     
    }
    
    while ($f = mysqli_fetch_assoc($r))
    {
        $i++;
        $_GET['transaccion'] = $f['transaccion'];
        if (isset($_GET['tarjetas'])) {
            
            if (isset($_GET['modelo2']))
                IMPRIMIR_tarjeta_tipo_2();
            else
                IMPRIMIR_tarjeta_tipo_1();
        
            echo '<div style="page-break-after: always;"></div>';
            
        } else {    
        
            if (isset($_GET['modelo2']))
                IMPRIMIR_firma_2();
            else
                IMPRIMIR_firma();
            
            if ( $i == 1 ) {
                echo '<div style="height:0px;border:1px solid black;background:black;color:black;margin:10px;"></div>';
            }
            
            if ( $i == 2 ) {
                echo '<div style="page-break-after: always;"></div>';
                $i = 0;
            }
        }

    }
}

function IMPRIMIR_pedido()
{
    if(!isset($_GET['transaccion']))
    {
        echo 'Necesita un codigo de transaccion para utilizar esta herramienta';
        return;
    }

    list($buffer,$f) = SSL_COMPRA_FACTURA($_GET['transaccion']);
    $buffer = '';

    $info_producto_foto =
    '<img style="width:133px;height:200px" src="'.imagen_URL($f['foto'],133,200).'" />'.
    '<p class="medio-oculto">'.
    '<strong>Código pedido</strong><br />'.$f['codigo_compra'].$f['salt'].BR.
    '<strong>Código producto:</strong><br />'.$f['codigo_producto'].BR.
    '<strong>Nombre producto</strong>'.BR.$f['titulo_contenedor'].BR.
    '<strong>Nombre variedad</strong>'.BR.$f['titulo_variedad'].BR.
    '</p>';

    $info_importante =
    '<table class="tabla-estandar" style="height:55px;width:99%">'.
        '<tr>'.
        '<td>'.
        '<p class="medio-oculto">'.
        '<strong>Fecha entrega:</strong><br />'.$f['fecha_entrega_formato'] . BR.
        '<strong>Fecha pedido:</strong><br />'.$f['fecha_formato'] . BR.
        '<strong>Correo contacto</strong><br />'.$f['correo_contacto'].
        '</p>'.
        '</td>'.
        '<td>'.
        '<p class="medio-oculto">'.
        '<strong>Tarjeta De</strong><br />'.$f['tarjeta_de'] . BR.
        '<strong>Telefono remitente</strong><br />'.$f['telefono_remitente'] . BR.
        '</p>'.
        '</td>'.
        '<td>'.
        '<p class="medio-oculto">'.
        '<strong>Tarjeta Para </strong><br />'.$f['tarjeta_para'] . BR.
        '<strong>Telefono destinatario</strong><br />'.$f['telefono_destinatario'].
        '</p>'.
        '</td>'.
        '<td>'.
        '<p style="font-size:40px;font-family:mono">'.
        $f['orden'].
        '</p>'.
        '</td>'.
        '</tr>'.
    '</table>'.
    '<p class="medio-oculto">'.
    '<strong>Tarjeta Cuerpo</strong>'.BR.ui_textarea('',preg_replace('/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/','',$f['tarjeta_cuerpo']),'','width:98%;height:110px;'). BR.
    '<strong>Dirección entrega</strong>'.BR.ui_textarea('',$f['direccion_entrega'],'','width:98%;height:110px;') . BR.
    '<strong>Notas adicionales</strong>'.BR.ui_textarea('',$f['usuario_notas'],'','width:98%;height:110px;') . BR.
    '<strong>Detalles extras</strong>'.BR.ui_textarea('',$f['extras'],'','width:98%;height:110px;') . BR.
    '<strong>Detalles de elaboración</strong>'.BR.ui_textarea('',$f['receta'],'','width:98%;height:110px;') . BR.
    '</p>'.
    '<br />'.
    '<hr />'.
    
    '<table id="factura">'.
        '<tr><th style="width:90%">Descripcion</td><th>Precio</th></tr>'.
        '<tr><td>Cantidad</td><td>'.$f['cantidad'].'</td></tr>'.
        '<tr><td>Producto: '.$f['titulo_contenedor'].'</td><td>$'.$f['precio_grabado'].'</td></tr>'.
        '<tr><td>Cargo adicional</td><td>$'.$f['cargo_adicional'].'</td></tr>'.
        '<tr><td>Cargo por extras</td><td>$'.$f['extrasPrecio'].'</td></tr>'.
        '<tr><td>Cargo de envío</td><td>$'.$f['precio_envio'].'</td></tr>'.
        '<tr><td style="font-weight:bold;">Total</td><td style="font-weight:bold;">$'.number_format($f['cantidad']*($f['precio_grabado']+$f['precio_envio']+$f['cargo_adicional']+$f['extrasPrecio']),2,'.',',').'</td></tr>'.
    '</table>'
    ;

    echo sprintf('
    <table style="height:350px;">
    <tr>
    <td style="border-right:1px solid #CCC;padding:0 0.1em;vertical-align:top;width: 160px;">
    %s
    </td>

    <td style="width:800px;">
    %s
    </td>
    </tr>
    </table>
    ',$info_producto_foto,$info_importante);
}

function IMPRIMIR_firma($anonimo = false)
{
    if(!isset($_GET['transaccion']))
    {
        echo 'Necesita un codigo de transaccion para utilizar esta herramienta';
        return;
    }

    list($buffer,$f) = SSL_COMPRA_FACTURA($_GET['transaccion']);
    $buffer = '';
    
    list($heuristica, $heuristicas) = obtener_heuristicas($f);
    
    if (isset($heuristica['anonimo']))
    {
        $anonimo = true;
    }
    

    $info_producto_foto = '';
    $info_producto_foto .= '<img style="width:133px;height:200px" src="'.imagen_URL($f['foto'],133,200).'" />';
    $info_producto_foto .= '<p class="medio-oculto">';
    $info_producto_foto .= '['.$f['codigo_producto'] . '] ' . $f['producto'];
    $info_producto_foto .= '</p>';
    $info_producto_foto .= '<div>'.$heuristicas.'</div>';

    $atenciones = '';

    if (isset($heuristica['dulces']))
        $atenciones .= '<span style="font-size:18pt;font-weight:bold;">[ ### CHOCOLATE ### ]</span>';
    
    if (isset($heuristica['globos']))
        $atenciones .= '<span style="font-size:18pt;font-weight:bold;">[ ### GLOBO ### ]</span>';
    
    if (isset($heuristica['peluche']))
        $atenciones .= '<span style="font-size:18pt;font-weight:bold;">[ ### PELUCHE ### ]</span>';

    if (isset($heuristica['kiosko']))
        $atenciones .= '<span style="font-size:18pt;font-weight:bold;">[ ### KIOSKO ### ]</span>';

    $atenciones = '<div>'.$atenciones.'</div>';
    
    if (isset($heuristica['difunto']))
    {
        $f['usuario_notas'] = "Difunto: " . $f['tarjeta_para'] . "<br />" . $f['usuario_notas'];
    }
    
    $info_importante =
    '<div style="text-align:left;">'.
    '<table style="width:100%;border-collapse:collapse;">'.
    '<tr>'.
    '<td style="font-size:42pt;font-family:Courier;font-weight:bold;">'.$f['orden'].'</td>'.
    '<td><div style="margin:auto;" rel="'.str_pad($f['codigo_compra'],7,'0',STR_PAD_LEFT).'" class="bcTarget"></div></td>'.
    '<td style="text-align:center;"><span style="font-size:15pt;font-family:Courier;font-weight:bold;">#' . $f['codigo_compra'] . '</span> - ' . $f['fecha_entrega_formato'].'</td>'.
    '</tr>'.
    '</table>'.
    '</div><hr />'.
    '<div><strong>Dirección entrega</strong></div><div style="font-size:10pt;width:98%;height:115px;border:1px solid #CCCCCC">'.preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", " ", $f['direccion_entrega']) . '</div>'.
    '<div><strong>Detalles</strong></div><div style="font-size:10pt;width:98%;height:155px;border:1px solid #CCCCCC">'.preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", " ",$f['usuario_notas']. "<br />" . $f['extras'] . $atenciones) . '</div>'
    ;
    
    $info_contacto = '';
    
    if ($anonimo) {
        $info_contacto .= 'anónimo';
    } else {
        $info_contacto .= ucwords(strtolower($f['tarjeta_de'])).' '.preg_replace(array('/[^\d\s]/','/(\d{4})(\d{4})/'),array('','$1-$2'),trim($f['telefono_remitente']));
    }

    $quien_firma = ucwords(strtolower($f['tarjeta_para'])) . ' <span style="font-size:9pt">' . ($f['telefono_destinatario'] ? $f['telefono_destinatario'] : '') . '</span><br />Recibido';        
    
    if (isset($heuristica['difunto']))
    {
        $quien_firma = 'Firma recibido';
    }

    
    echo '<div style="width:7.8in;">';
    
    echo '
        <table style="height:350px;width:100%;">
        <tr>
        <td style="border-right:1px solid #CCC;padding:0 0.1em;vertical-align:top;width: 135px;">
        '.$info_producto_foto.'
        </td>
    
        <td style="width:*;vertical-align:top;">
        '.$info_importante.'    
        </td>
        
        </tr>
        </table>
        
        <table style="margin-top:30px;width:100%;">
        <tr>
        <td style="text-align:center;">
        _______________________<br />
        '.$quien_firma.'
        </td>
        <td style="text-align:right;">
            <img src="/IMG/portada/logo_mini.png" alt="Logotipo Flor360.com"/>
            <p style="text-align:right; padding:0px; margin-top:1px;font-size:10pt" class="gris">'.$info_contacto.' | '.$f['ruta'].'</p>
        </td>
        </tr>
        </table>        
    ';
    
    echo '</div>';
}

function IMPRIMIR_firma_2($anonimo = false)
{
    if(!isset($_GET['transaccion']))
    {
        echo 'Necesita un codigo de transaccion para utilizar esta herramienta';
        return;
    }

    list($buffer,$f) = SSL_COMPRA_FACTURA($_GET['transaccion']);
    $buffer = '';
    
    list($heuristica, $heuristicas) = obtener_heuristicas($f);
    
    if (isset($heuristica['anonimo']))
    {
        $anonimo = true;
    }
    

    $info_producto_foto = '';
    $info_producto_foto .= '<img style="width:133px;height:200px" src="'.imagen_URL($f['foto'],133,200).'" />';
    $info_producto_foto .= '<p class="medio-oculto">';
    $info_producto_foto .= '<strong>Código pedido</strong><br />'.$f['codigo_compra'].$f['salt'].BR;
    $info_producto_foto .= '<strong>Código producto:</strong><br />'.$f['codigo_producto'].BR;
    $info_producto_foto .= '<strong>Nombre producto</strong>'.BR.$f['titulo_contenedor'].BR;
    $info_producto_foto .= '<strong>Nombre variedad</strong>'.BR.$f['titulo_variedad'].BR;
    $info_producto_foto .= '</p>';

    $info_numerica = $f['orden'];

    $info_importante =
    '<table class="tabla-estandar" style="height:55px;width:99%">'.
    '<tr>'.
    '<td>'.
    '</td>'.
    '<td style="font-size:56px;font-family:mono;padding:0;margin:0;text-align:center;">'.
    $info_numerica.
    '<hr />'.
    '</td>'.
    '</tr>'.
    '</table>'.
    '<div>'.
    '<strong>Dirección entrega</strong>'.BR.ui_textarea('',preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", " ", $f['direccion_entrega']),'','width:98%;height:100px;') . BR.
    '<strong>Detalles</strong>'.BR.ui_textarea('',preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", " ",$f['usuario_notas']. "; " . $f['extras']),'','width:98%;height:100px;') . BR.
    '</div>'
    ;    
    
    $info_contacto = '';
    
    if ($anonimo) {
        $info_contacto .= 'anónimo';
    } else {
        $info_contacto .= ucwords(strtolower($f['tarjeta_de'])).' '.preg_replace(array('/[^\d\s]/','/(\d{4})(\d{4})/'),array('','$1-$2'),trim($f['telefono_remitente']));
    }

    echo '
    <table>
    <tr>
    <td style="width:50%;padding-right:10px;vertical-align:top;">
        <table style="height:350px;width:100%;">
        <tr>
        <td style="border-right:1px solid #CCC;padding:0 0.1em;vertical-align:top;width: 160px;">
        '.$info_producto_foto.'
        </td>
    
        <td style="width:870px;vertical-align:top;">
        '.$info_importante.'
        </td>
        
        </tr>
        </table>
        
        <div style="text-align:center;margin-top:25px;">
        _______________________<br />
        <strong style="font-size:9pt;">'.ucwords(strtolower($f['tarjeta_para'])) . '</strong> <span style="font-size:8pt;">' . ($f['telefono_destinatario'] ? $f['telefono_destinatario'] : 'No especificado') .'</span>
        <br />
        <div style="font-size:8pt;">Recibido - '.strftime('%e de %B de %Y').'</div>
        </div>
        
        <br /><hr />
        <div class="medio-oculto" style="font-size:8pt;text-align:right;">'.$info_contacto.' | '.$f['ruta'].'</div>
        <br />
    </td>
    <td style="width:50%;vertical-align:top;border-left:1px solid grey;padding-left:10px;">
        <table style="width:100%;">
        <tr>
        <td style="border-right:1px solid #CCC;padding:0 0.1em;vertical-align:top;width: 160px;">
        <img style="width:200px;height:300px" src="'.imagen_URL($f['foto'],200,300).'" /><br />
        </td>
    
        <td style="width:800px;vertical-align:top;">
        <br />
        <div style="font-size:56px;font-family:mono;">'.
        $info_numerica.'
        </div>        
        <div>'.
        '<div style="font-size:9pt;">'.$f['fecha_entrega'] .'</div>' . BR.
        $f['codigo_compra'].$f['salt'].
        '</div>
        <div style="margin:auto;" rel="'.str_pad($f['codigo_compra'],7,'0',STR_PAD_LEFT).'" class="bcTarget"></div>
        
        <hr />
        <div>'.$heuristicas.'</div>
        </td>
        </tr>
        </table>
        <div style="font-size:9pt;font-weight:bold;">['.$f['codigo_producto'].'] '.$f['titulo_contenedor'].' '.($f['titulo_contenedor'] == $f['titulo_variedad'] ? '' : $f['titulo_variedad']).'</div>
        <div>'
        .ui_textarea('',preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", " ",$f['usuario_notas']. "; " . $f['extras']),'','width:98%;height:100px;')
        .BR
        .ui_textarea('',preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", " ", $f['receta']),'','width:98%;height:50px;').'
        </div>
    </td>
    </tr>
    </table>
    ';
}

function IMPRIMIR_factura()
{
    if(!isset($_GET['transaccion']))
    {
        echo 'Necesita un codigo de transaccion para utilizar esta herramienta';
        return;
    }

    list($buffer,$f) = SSL_COMPRA_FACTURA($_GET['transaccion']);
    $buffer = '';

    $info_producto_foto =
    '<img style="width:133px;height:200px" src="'.imagen_URL($f['foto'],133,200).'" />'.
    '<p class="medio-oculto">'.
    '<strong>Código pedido</strong><br />'.$f['codigo_compra'].$f['salt'].BR.
    '<strong>Código producto:</strong><br />'.$f['codigo_producto'].BR.
    '<strong>Nombre producto</strong>'.BR.$f['titulo_contenedor'].BR.
    '<strong>Nombre variedad</strong>'.BR.$f['titulo_variedad'].BR.
    '</p>';

    $info_importante =
    '<table class="tabla-estandar" style="height:55px;width:99%;">'.
    '<tr>'.
    '<td>'.
    '<p class="medio-oculto">'.
    '<strong>Fecha entrega:</strong><br />'.$f['fecha_entrega_formato'] . BR.
    '<strong>Fecha pedido:</strong><br />'.$f['fecha_formato'] . BR.
    '<strong>Correo contacto</strong><br />'.$f['correo_contacto'].
    '</p>'.
    '</td>'.
    '<td>'.
    '<p class="medio-oculto">'.
    '<strong>Tarjeta De</strong><br />'.$f['tarjeta_de'] . BR.
    '<strong>Telefono remitente</strong><br />'.$f['telefono_remitente'] . BR.
    '</p>'.
    '</td>'.
    '<td>'.
    '<p class="medio-oculto">'.
    '<strong>Tarjeta Para </strong><br />'.$f['tarjeta_para'] . BR.
    '<strong>Telefono destinatario</strong><br />'.$f['telefono_destinatario'].
    '</p>'.
    '</td>'.
    '</tr>'.
    '</table>'.
    '<br /><hr />'.
    '<table id="factura">'.
    '<tr><td>Cantidad</td><td>'.$f['cantidad'].'</td></tr>'.
    '<tr><td>Producto: '.$f['titulo_contenedor'].'</td><td>$'.$f['precio_grabado'].'</td></tr>'.
    '<tr><td>Cargo adicional</td><td>$'.$f['cargo_adicional'].'</td></tr>'.
    '<tr><td>Cargo por extras</td><td>$'.$f['extrasPrecio'].'</td></tr>'.
    '<tr><td>Cargo de envío</td><td>$'.$f['precio_envio'].'</td></tr>'.
    '<tr><td style="font-weight:bold;">Total</td><td style="font-weight:bold;">$'.number_format($f['cantidad']*($f['precio_grabado']+$f['precio_envio']+$f['cargo_adicional']+$f['extrasPrecio']),2,'.',',').'</td></tr>'.
    '</table>'.
    '</p>';

    echo sprintf('
    <table style="height:350px;">
    <tr>
    <td style="border-right:1px solid #CCC;padding:0 0.1em;vertical-align:top;width: 160px;">
    %s
    </td>

    <td style="width:800px;vertical-align:top;">
    %s
    </td>
    </tr>
    </table>
    ',$info_producto_foto,$info_importante);
    
    echo '<hr style="border: 1px dashed #000000;margin:100px 0px;" />';
    
    $info_estado  = '<div style="margin:auto;width:50%;">';
    
    switch ($f['metodo_pago'])
    {
        case 'tarjeta':
            $info_estado .= '<p>Cancelado con tarjeta de crédito</p>';
            $info_estado .= sprintf('Factura o crédito fiscal<br /><br />Con atención a: <strong>%s</strong><br />En domicilio: <strong>%s</strong>',$f['cobrar_a'],$f['cobrar_en']);
            break;
        
        case 'domicilio':
            $info_estado .= sprintf('Cobrar en domicilio<br /><br />Con atención a: <strong>%s</strong><br />En domicilio: <strong>%s</strong>',$f['cobrar_a'],$f['cobrar_en']);
            break;
        
        case 'abono':
            $info_estado .= 'Este arreglo se pago con abono a cuenta';
            break;
        
        case 'kiosko':
            $info_estado .= 'Este arreglo se pago en kiosko (con pago diferido)';
            break;
        
        case 'kiosko_efectivo':
            $info_estado .= 'Pago en kiosko en efectivo';
            break;
        
        case 'kiosko_credito':
            $info_estado .= 'Pago en kiosko con tarjeta de crédito';
            break;
    }
    $info_estado .=  '</div>';
    
    echo '<div style="font-size:18px;">'.$info_estado.'</div>';
}

function IMPRIMIR_boucher()
{
    if(!isset($_GET['transaccion']))
        die();

    list($buffer,$f) = SSL_COMPRA_FACTURA($_GET['transaccion']);
    
    echo '<style>';
    echo 'table {border-collapse:collapse;table-layout:fixed;}';
    echo 'table td {border-top:1px solid grey;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;padding:2px;font-size:12px;}';
    echo '</style>';
    echo '<div style="width:200px;text-align:center;font-size:15px;">';
    echo '<div style="width:100%;font-weight:bold;font-size:18px;">'.PROY_NOMBRE_CORTO.'</div>';
    echo '<div style="width:100%">Tel. '.PROY_TELEFONO_PRINCIPAL.'</div>';
    echo '<div style="width:100%">'.PROY_URL_AMIGABLE.'</div>';
    echo '<div style="width:100%">'.strftime('%c').'</div>';
    echo '<br /><br />';
    echo '<div><img src="'.imagen_URL($f['foto'],160,235,'').'" /></div>';
    echo '<br /><br />';
    echo '<table style="width:100%">';
    echo '<tr><td style="text-align:left;">Producto:</td><td style="text-align:right;">'.$f['titulo_variedad'].'</td></tr>';
    echo '<tr><td style="text-align:left;">Precio producto:</td><td style="text-align:right;">$'.number_format($f['precio_grabado'],2,'.',',').'</td></tr>';
    echo '<tr><td style="text-align:left;">Cargo adicional:</td><td style="text-align:right;">$'.number_format($f['cargo_adicional'],2,'.',',').'</td></tr>';
    echo '<tr><td style="text-align:left;">Cargo extras:</td><td style="text-align:right;">$'.number_format($f['extrasPrecio'],2,'.',',').'</td></tr>';
    echo '<tr><td style="text-align:left;">Precio envio:</td><td style="text-align:right;">$'.number_format($f['precio_envio'],2,'.',',').'</td></tr>';
    echo '<tr><td style="text-align:left;">Cantidad:</td><td style="text-align:right;">'.$f['cantidad'].'</td></tr>';
    echo '<tr><td><strong>Total:</strong></td><td style="text-align:right;"><strong>$'.number_format($f['precio_total'],2,'.',',').'</strong></td></tr>';
    echo '</table>';
    
    echo '<br /><br /><div style="margin:auto;font-size:11px;";>¡GRACIAS POR SU COMPRA!</div>';
    echo '<div style="text-align:center;"><img src="http://flor360.com/IMG/stock/logo_8bit.jpg" /></div>';
    
    echo '</div>';

}

function IMPRIMIR_tiquete()
{
    if(!isset($_GET['transaccion']))
        die();
        
    $total = 0;

    $c = sprintf('SELECT `codigo_kiosko_transaccion`, `codigo_kiosko_articulo`, `codigo_usuario`, `operacion`, `cantidad`, `precio_grabado`, `fecha`, IF (t1.`descripcion`="", t2.`descripcion`, t1.`descripcion`) AS "descripcion_articulo" FROM `flores_kiosko_transacciones` AS t1 LEFT JOIN `flores_kiosko_articulos` AS t2 USING(codigo_kiosko_articulo) WHERE `transaccion`="'.db_codex($_GET['transaccion']).'"');
    $r = db_consultar($c);

    
    echo '<style>';
    echo 'table {border-collapse:collapse;table-layout:fixed;}';
    echo 'table td {border-top:1px solid grey;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;padding:2px;font-size:12px;}';
    echo '</style>';
    echo '<div style="width:200px;text-align:center;font-size:15px;">';
    echo '<div style="width:100%;font-weight:bold;font-size:18px;">'.PROY_NOMBRE_CORTO.'</div>';
    echo '<div style="width:100%">Tel. '.PROY_TELEFONO_PRINCIPAL.'</div>';
    echo '<div style="width:100%">'.PROY_URL_AMIGABLE.'</div>';
    echo '<div style="width:100%">'.strftime('%c').'</div>';

    echo '<br /><br />';
    echo '<table style="width:200px;">';
    echo '<tr><th style="width:30px;"></th><th style="width:80px;">Producto</th><th style="width:60px;">c/u</th><th style="width:60px;">Total</th></tr>';
    while ($f = mysqli_fetch_assoc($r))
    {
        $total += ($f['cantidad']*$f['precio_grabado']);
        echo sprintf('<tr><td style="text-align:left;font-weight:bold;">%s</td><td style="text-align:left;">%s</td><td style="text-align:left;">$%0.2f</td><td style="text-align:left;">$%0.2f</td></tr>',$f['cantidad'],$f['descripcion_articulo'],$f['precio_grabado'],($f['cantidad']*$f['precio_grabado']));
    }   
    echo '</table>';
    echo '<p style="font-size:22px;">Total: $'.number_format($total,2,'.',',').'</p>';
    
    echo '<div style="margin:auto;font-size:11px;";>¡GRACIAS POR SU COMPRA!</div>';
    echo '<div style="text-align:center;"><img src="http://flor360.com/IMG/stock/logo_8bit.jpg" /></div>';
    echo '</div>';

}

function IMPRIMIR_cortez()
{
    if(!isset($_GET['transaccion']))
        die();
    
    $cCorteZ = 'SELECT ID_cortez, fecha, valor_efectivo, valor_pos, html FROM '.db_prefijo.'cortez WHERE ID_cortez="'.db_codex($_GET['transaccion']).'"';
    $r = db_consultar($cCorteZ);
    $fZ = mysqli_fetch_assoc($r);
    
    echo $fZ['html'];
}
?>
<script type="text/javascript">
$(".bcTarget").each(function(){
    $(this).barcode($(this).attr('rel'), "ean8" );
});

javascript:print();
</script>
