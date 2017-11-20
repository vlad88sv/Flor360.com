<?php
set_time_limit(0);
ini_set('memory_limit',         '128M');
ini_set('max_input_time',       '6000');
ini_set('max_execution_time',   '6000');
require_once(__BASE_cePOSa__.'PHP/ssl.comun.php');

ACTIVAR_PAQUETES(array('ui','facebox'));

$flags = array();

$destinos = array_merge(array('Seleccione uno' => ''),$destinos);

if (isset($_GET['tipo']) && isset($_GET['pin']))
{
    switch ($_GET['tipo'])
    {
        case 'estado':
            $c = 'SELECT codigo_compra, salt, flag_cobrado, flag_elaborado, flag_enviado FROM '.db_prefijo.'SSL_compra_contenedor WHERE indicerapido="'.db_codex($_GET['pin']).'"';
            $resultado = db_consultar($c);
            if ($resultado && $pedido = mysqli_fetch_assoc($resultado))
            {
                $estado = '<strong>' . ($pedido['flag_enviado'] == '0' ? 'confirmado, su pedido ha ingresado exitosamente a nuestra agenda de entregas' : 'entregado').'</strong>';
                
                echo '<h1>Rastreo del estado de su pedido</h1>';
                echo sprintf('<p>Pedido <strong>#%s</strong><br />Su pedido se encuentra en el siguiente estado: <strong>%s</strong></p>',$pedido['codigo_compra'].$pedido['salt'],$estado);
                echo sprintf('<p>Si necesita mas información no dude en comunicarse con nosotros en cualquiera de las siguientes formas:<br/>
                             <ul><li>Llamandonos al '.PROY_TELEFONO.'</li><li>Usando nuestro <a href="'.PROY_URL.'contactanos">formulario de contacto</a></li><li>Enviarnos un correo a <a href="mailto:'.PROY_MAIL_POSTMASTER_NOMBRE.PROY_MAIL_POSTMASTER.'">informacion@flor360.com</a></li></ul></p>');
                echo '<p>¿Necesita enviar otro regalo?, ¿le gustan nuestros arreglos?, entonces lo invitamos a <a href="'.PROY_URL.'">regresar a página principal</a>.</p>';
            } else {
                echo 'Pin erroneo';
                echo '<p><a href="'.PROY_URL.'">Regresar a página principal</a></p>';
            }
            break;

        case 'factura':
            list($buffer,$f) = SSL_COMPRA_FACTURA($_GET['pin']);
            echo $buffer;
            //ob_end_clean();
            //exit;
            break;
        
        default:
            echo '<p>Petición erronea</p>';
    }
    return;
}
if (empty($_POST['variedad']))
{
    echo 'Error de selección';
    echo '<p><a href="'.PROY_URL.'">Regresar a página principal</a></p>';
    return;
}

if (!is_numeric($_POST['variedad']))
{
    echo '<p>Lo siento, hubo un error en la transaccion y no se puede continuar</p>';
    echo '<p><a href="'.PROY_URL.'">Regresar a página principal</a></p>';
    return;
}

$variedad_sql = sprintf('SELECT procon.`codigo_producto`, procon.`titulo` AS "contenedor_titulo", procon.`descripcion` AS "contenedor_descripcion", procon.`vistas`, procon.`color`, provar.`codigo_variedad`, provar.`compra_minima`, provar.`codigo_producto`, provar.`foto`, provar.`descripcion` AS "variedad_titulo", provar.`precio`, provar.`precio_oferta`, provar.`receta` FROM `%s` AS provar LEFT JOIN `%s` AS procon USING(`codigo_producto`) WHERE provar.codigo_variedad=%s LIMIT 1',db_prefijo.'producto_variedad',db_prefijo.'producto_contenedor', $_POST['variedad']);
$variedad_r = db_consultar($variedad_sql);

if (mysqli_num_rows($variedad_r) == 0)
{
    echo '<p>Lo siento, hubo un error en la transaccion y no se puede continuar</p>';
    echo '<p><a href="'.PROY_URL.'">Regresar a página principal</a></p>';
    return;
}

// Todo bien...
$variedad = mysqli_fetch_assoc($variedad_r);

// Compatibilidad con "compra_minima" para los arreglos antiguos
if ($variedad['compra_minima'] < 1)
    $variedad['compra_minima'] = 1;

// Veamos si aplica cupon...
$cupon_invalidar_codigo = '';
$variedad['precio_original'] = $variedad['precio'];

if (!empty($_POST['codigo_cupon']))
{
    $_POST['codigo_cupon'] = trim($_POST['codigo_cupon']);
    $cupon_sql = 'SELECT codigo_cupon, `codigo`, tipo, valor, precio, ilimitado, cantidad_usos, cantidad_usado, enviado_a, facturar_a FROM `flores_cupones` WHERE (`ilimitado` = 1 OR `expiracion` > DATE(NOW()) ) && `codigo`="'.db_codex($_POST['codigo_cupon']).'" AND (codigo_producto='.$variedad['codigo_producto'].' OR codigo_producto=0) AND (variedad='.$variedad['codigo_variedad'].' OR variedad=0)';
    $cupon_r = db_consultar($cupon_sql);

    if (mysqli_num_rows($cupon_r) == 0)
    {
        $mensaje = 'Lo siento, el cupón de descuento especifícado no existe o ya expiró.';
        echo '<p class="ui-state-error">'.$mensaje.'</p>';
    }
    else
    {
        $cupon = mysqli_fetch_assoc($cupon_r);
        
        if ( ($cupon['ilimitado'] == 0 && ($cupon['cantidad_usado'] >= $cupon['cantidad_usos'])) || ($variedad['precio_oferta'] > 0 && ($cupon['tipo'] != 'diferido' || $cupon['tipo'] != 'descuento') ) )
        {
            // Ya esta expirado
            $mensaje = 'Lo siento, el cupón de descuento especifícado no existe o expiró o no es válido para este producto.';
            echo '<p class="ui-state-error">'.$mensaje.'</p>';
        } else {
            // Todo bien...
            switch($cupon['tipo'])
            {
                case "precio":
                    $variedad['precio'] = $cupon['precio'];
                    $mensaje = 'Cupón aceptado, nuevo precio: $'.number_format($variedad['precio'],'2','.',',');
                    break;

                case 'descuento':
                    $variedad['precio'] = max(0,($variedad['precio']-$cupon['valor']));
                    $mensaje = 'Cupón aceptado, descuento de $'.$cupon['valor'].' aplicado. Nuevo precio: $'.number_format($variedad['precio'],'2','.',',');
                    break;
                
                case 'diferido':
                    $flags['diferido'] = true;
                    $mensaje = 'Código para pago diferido aceptado';
                    break;
                
                case 'porcentaje':
                    $variedad['precio'] = $variedad['precio']*(1-($cupon['valor']/100));
                    $mensaje = 'Cupón aceptado, descuento de '.$cupon['valor'].'% aplicado. Nuevo precio: $'.number_format($variedad['precio'],'2','.',',');
                    break;
            }
            
            $cupon_invalidar_codigo = $cupon['codigo_cupon'];
        }
    }
    
    echo '<script type="text/javascript">alert("'.$mensaje.'");</script>';
}

// Override precio con precio_oferta si es necesario:
$precio_etiqueta = ($variedad['precio_oferta'] > 0 ? '<span style="text-decoration:line-through;">$'.$variedad['precio'].'</span> - Oferta: <span style="color:red;">$' . $variedad['precio_oferta'] . '</span>' : '$'.$variedad['precio']);
$variedad['precio'] = $variedad['precio_oferta'] > 0 ? $variedad['precio_oferta'] : $variedad['precio'];

$precio_sin_formato = number_format($variedad['precio'],'2','.','');
$variedad['precio'] = number_format($variedad['precio'],'2','.',',');

// Tratemos de procesar la compra...
$id_factura = SSL_COMPRA_PROCESAR();
if (is_numeric($id_factura))
{
    $f = SSL_MOSTRAR_FACTURA($id_factura);
    
    // Si detectamos que es un fraude, lo menos que podemos
    // hacer es agradecerle crasheandole su navegador
    if ($f['fraude'] == true) {
        echo '<script type="text/javascript">txt="x"; while(1){txt += "x";} window.location="http://fbi.gov/"</script>';
    }
    
    /* ecommerce - Google Analytics */
    $_gaq .= "\n _gaq.push(['_trackEvent', 'Compras', 'Realizada', '".$variedad['contenedor_titulo']. ' - '.$variedad['variedad_titulo']."']);";
    $_gaq .= "\n _gaq.push(['_addTrans','$id_factura','Flor360.com','".(@$f['precio_total'])."','0','".@(double)$_POST['destino']."']);";
    $_gaq .= "\n _gaq.push(['_addItem','$id_factura','".$variedad['codigo_variedad']."','".$variedad['contenedor_titulo']."','".$variedad['variedad_titulo']."', '".@$variedad['precio']."', '".@$_POST['cantidad']."']);";
    $_gaq .= "\n _gaq.push(['_trackTrans']);";

    $HEAD_titulo = 'Compra realizada. ¡Muchas gracias!';
    
    echo '<hr />';
    echo '<p><a href="'.PROY_URL.'">Regresar a página principal</a></p>';
    return;
}

$_gaq .= "\n _gaq.push(['_trackEvent', 'Compras', 'Intento', '".$variedad['contenedor_titulo']. ' - '.$variedad['variedad_titulo']."']);";

$HEAD_titulo = 'Comprando "' . $variedad['contenedor_titulo'].'"';

if (empty($_POST['txt_fecha_entrega']))
    $_POST['txt_fecha_entrega'] = mysql_date();
    
// Advirtamos a los usuarios que no han hecho login

if (isset($_COOKIE['socio']) && !S_iniciado())
{
    echo '<div style="background-color:pink;color:black;font-size:28px;text-align:center;padding:10px;border-radius:10px;border: 1px solid red;margin:15px 0px;">Usted ha iniciado sesión en el pasado pero actualmente no esta <a href="/iniciar">iniciado</a>.</div>';
}

if (defined('modo_beta') && modo_beta == true)
    echo '<div style="border:2px dashed yellow;background-color:black;font-size:13px;color:white;padding:4px;">Herramientas BETA: <button id="beta__llenarformulario">Llenar formulario</button></div>';

echo '<form id="formulario-compra" autocomplete="off" action="'.PROY_URL.'comprar-articulo.html" method="POST">';

//              Código de promoción
    echo '<p class="titulo">1. Si ha recibido un <a target="_blank" href="/ayuda?tema=cupones">cúpon de descuento</a> o <a target="_blank" href="/ayuda?tema=asociados">número de asociado</a> introduzcalo aquí <input type="text" style="width:100px !important;" value="'.@$_POST['codigo_cupon'].'" name="codigo_cupon" /> <input id="aplicar_cupon" name="aplicar_cupon" style="border: 1px solid black;background-color:#FFCC00;color:black;border-radius:5px;width:120px !important;font-size:13px;padding:3px 0px;" type="submit" value="Aplicar" ></p>';

if (!empty($mensaje))
    echo '<div style="padding:15px;border:2px solid #CC7110;border-radius:5px;margin-left:10px;"><p class="ui-state-highlight">Resultado del código ingresado: '.$mensaje.'</p></div>';

//              Revisión de producto
echo '<p class="titulo">2. Revise que el producto a continuación es el que desea comprar</p>';
echo '<div style="padding:1px 1px;border:2px solid #CC7110;border-radius:5px;margin-left:10px;">';
echo '<table id="compras-resumen" style="width:100%">';
echo '<tr>';
echo '<td id="compras-resumen-fotografia"><img title="Clic para ampliar imagen" style="border:1px solid #f0f0f0;" src_grande="'.imagen_URL($variedad['foto'],400,600).'" src="'.imagen_URL($variedad['foto'],57,86).'" /></td>';
echo '<td>';
echo '<table style="width:100%;border-collapse:collapse;table-layout:fixed;">';
echo sprintf('<tr><td style="width:70px;color: #e18522;">Producto:</td><td>%s</td></tr>',$variedad['contenedor_titulo'],$variedad['contenedor_descripcion']);
if (!defined('MODO_MUERTO'))
echo sprintf('<tr><td style="color: #e18522;">Variedad:</td><td>%s</td></tr>',$variedad['variedad_titulo']);

if (S_iniciado())
    echo sprintf('<tr><td style="color: #e18522;">Preparación:</td><td>%s</td></tr>',$variedad['receta']);

if ($variedad['codigo_variedad'] != '237')
    echo sprintf('<tr><td style="width:120px;color: #e18522;">Precio:</td><td>%s</td></tr>',$precio_etiqueta);

if (S_iniciado())
    echo sprintf('<tr><td style="color: #e18522;">'.(($variedad['codigo_variedad'] == '237') ? 'Precio' : 'Cargos adicionales:').'</td><td>%s</td></tr>','$<input name="cargos_adicionales" id="cargos_adicionales" type="text" value="'.(!empty($_POST['cargos_adicionales']) ? $_POST['cargos_adicionales'] : '0.00').'" style="width:50px;" />');
else
    echo sprintf('<input name="cargos_adicionales" id="cargos_adicionales" type="hidden" value="0.00" style="width:50px;" />');

if (isset($_POST['con_cantidad']))
{
    echo ui_input('con_cantidad', 'si','hidden');
    echo sprintf('<tr><td style="color: #e18522;">Cantidad:</td><td>%s - (compra mínima: %s unidades)</td></tr>', ui_input('cantidad', (empty($_POST['cantidad']) ? $variedad['compra_minima'] : $_POST['cantidad']),'text','','width:50px;'),$variedad['compra_minima']);
} else { 
    echo ui_input('cantidad', '1','hidden');
}

echo '</table>';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';

echo ui_input('transaccion',sha1(microtime()),'hidden');
echo ui_input('variedad',$variedad['codigo_variedad'],'hidden');

//              Información del pedido
echo '<p class="titulo">3. Ingrese sus datos</p>';
echo '<div style="padding:1px 5px;border:2px solid #CC7110;border-radius:5px;margin-left:10px;">';
echo '<table class="tabla-estandar">';
echo '<tr><td style="width: 170px;"><span class="li">Nombre:</span></td><td>' . ui_input('txt_tarjeta_de',@$_POST['txt_tarjeta_de']).'&nbsp;&nbsp;<span style="color:red;" id="validacion_remitente"></span></td></tr>';
echo '<tr><td><span class="li">Email:</span></td><td>'.ui_input('txt_correo_contacto',@$_POST['txt_correo_contacto']).'&nbsp;&nbsp;<span id="compras_anteriores"></span></td></tr>';
echo '<tr><td><span class="li">Teléfono:</span></td><td><input type="text" id="txt_telefono_remitente" name="txt_telefono_remitente" value="'.@$_POST['txt_telefono_remitente'].'" /></td></tr>';
echo '</table>';
echo '</div>';

if (!defined('MODO_MUERTO'))
    echo '<p class="titulo">4. ¿Quién recibirá el arreglo?</p>';
else
    echo '<p class="titulo">4. ¿Donde se entregará el arreglo?</p>';
echo '<div style="padding:1px 5px;border:2px solid #CC7110;border-radius:5px;margin-left:10px;">';
echo '<table class="tabla-estandar">';
echo '<tr><td style="width:610px;">';
echo '<table class="tabla-estandar">';
if (!defined('MODO_MUERTO'))
    echo '<tr><td style="width: 170px;"><span class="li">Nombre:</span></td><td>' . ui_input('txt_tarjeta_para',@$_POST['txt_tarjeta_para']).'&nbsp;&nbsp;<span style="color:red;" id="validacion_destinatario"></span></td></tr>';
else
    echo '<tr><td style="width: 170px;"><span class="li">Nombre del difunto:</span></td><td>' . ui_input('txt_tarjeta_para',@$_POST['txt_tarjeta_para']).'</td></tr>';
if (!defined('MODO_MUERTO'))
echo '<tr><td><span class="li">Teléfono:</span></td><td>' . ui_input('txt_telefono_destinatario', @$_POST['txt_telefono_destinatario']) . '</td></tr>';
echo '<tr><td style="vertical-align:top;"><span class="li">Dirección de entrega:</span></td><td><textarea id="txt_direccion_entrega" name="txt_direccion_entrega"  style="height: 105px; ">'.@$_POST['txt_direccion_entrega'].'</textarea></td></tr>';
echo '<tr><td><span class="li">Zona de entrega:</span></td><td>'.ui_combobox('destino',ui_array_a_opciones($destinos, true), @$_POST['destino']).'</td></tr>';
echo '</table>';
echo '<td style="text-align:left;">
<span class="li">Fecha de entrega:</span>&nbsp;<input type="text" id="txt_fecha_entrega" style="width:200px;font-size:11px;" name="txt_fecha_entrega" value="'.@$_POST['txt_fecha_entrega'].'">'.
'<div style="text-align:center;"><div id="div_fecha_entrega"></div></div></td>';
echo '</td></tr>';
echo '</table>';

echo '</div>';

if (!defined('MODO_MUERTO'))
    echo '<p class="titulo">5. Dedicatoria</p>';
else
    echo '<p class="titulo">5. Mensaje</p>';
echo '<div style="padding:1px 5px;border:2px solid #CC7110;border-radius:5px;margin-left:10px;">';
echo '<table class="tabla-estandar">';
if (!defined('MODO_MUERTO'))
{
    echo '<tr><td>';
    echo '<span class="li">¿Este envío es anónimo?</span>&nbsp;';
    echo '<input type="radio" value="si" '.(@$_GET['anonimo'] == 'si' ? 'checked="checked"' : '').' id="anonimo_si" name="anonimo" /> <label for="anonimo_si">SI</label> <input type="radio" value="no" '.( (@$_GET['anonimo'] == 'no' || empty($_GET['anonimo']) )  ? 'checked="checked"' : '').' id="anonimo_no" name="anonimo" /> <label for="anonimo_no">NO</label>';
    echo '</td></tr>';
}

echo '<tr><td>';
echo '<span class="li">Ingrese la dedicatoria que desee se escriba en la tarjeta que acompaña al arreglo</span><br />';
echo '<textarea id="txt_tarjeta_cuerpo" name="txt_tarjeta_cuerpo" style="width: 940px; margin: 10px; height: 80px;">'.@$_POST['txt_tarjeta_cuerpo'].'</textarea>';
echo '</td></tr>';
echo '</table>';
echo '</div>';

echo '<p class="titulo">6. Extras</p>';
echo '<div style="padding:1px 5px;border:2px solid #CC7110;border-radius:5px;margin-left:10px;">';
echo '<table class="tabla-estandar">';
if (!defined('MODO_MUERTO'))
{
echo '<tr><td>';
echo '<span class="li">¿Desea agregar algunas de estas opciones? (seleccione)</span><br />';
$c = 'SELECT codigo_extra_grupo, t0.nombre AS "nombre_grupo", foto, especificable, codigo_extra, t1.nombre AS "nombre_extra", precio FROM `extras_grupo` AS t0 LEFT JOIN `extras` AS t1 USING(codigo_extra_grupo) WHERE t0.habilitado=1 AND t1.habilitado=1';
$r = db_consultar($c);
$extras = array();
while ($f = db_fetch($r)){
    $extras[$f['nombre_grupo']]['datos'][] = $f;
    $extras[$f['nombre_grupo']]['foto'] = $f['foto'];
    $extras[$f['nombre_grupo']]['grupo'] = $f['nombre_grupo'];
    $extras[$f['nombre_grupo']]['especificable'] = $f['especificable'];
}

$iextra = 1;
$extrasXfila = 5;
$bufferImg = $bufferOp = array();

foreach ($extras as $extra)
{    
    $bufferImg[$iextra] = '<img style="width:130px;height:130px;" src="/IMG/extras/'.$extra['foto'].'.jpg" />';
    
    $bufferOp[$iextra] = '';
    foreach ($extra['datos'] as $detalle)
    {
        $bufferOp[$iextra] .= '<input grupo="'.$extra['grupo'].'" '.(@isset($_POST['extra'][$detalle['codigo_extra']]) ? 'checked="checked"' : '').' type="checkbox" nombre="'.$detalle['nombre_extra'].'" id="extra_'.$detalle['codigo_extra'].'" class="extra" precio="'.$detalle['precio'].'" name="extra['.$detalle['codigo_extra'].']" value="1"/><label for="extra_'.$detalle['codigo_extra'].'">'.$detalle['nombre_extra'].' $'.$detalle['precio'].'</label><br />';
    }
    
    if ($extra['especificable'] == '1')
    {
        $bufferOp[$iextra] .= '<br /><p>Ocasión:<br /><input type="text" style="width:130px;" name="extra_esp['.$detalle['codigo_extra'].']" onclick="if ($(this).val() == \'Especifique\') $(this).val(\'\');" value="Especifique" /></p>';
    }
    
    $iextra++;
}

echo '<table class="tabla-estandar" id="extras">';
echo '<tr class="extra_img"><td>'.join('</td><td>',$bufferImg).'</td></tr>';
echo '<tr class="extra_op"><td>'.join('</td><td>',$bufferOp).'</td></tr>';
echo '</table>';
echo '</td></tr>';
}

echo '<tr><td>';
echo '<span class="li">Notas adicionales</span>.</span><br /><textarea id="txt_usuario_notas" name="txt_usuario_notas" style="width: 940px; margin: 10px; height: 80px;">'.@$_POST['txt_usuario_notas'].'</textarea><br /><div style="font-weight:bold;font-style:italic;text-align:center;">Nuestro horario de entrega es de 9:00a.m. a 5:00p.m. según ruta de entrega.</div>';
echo '</td></tr>';
echo '</table>';
echo '</div>';

echo '<p class="titulo">7. Resumen de su orden</p>';
echo '<div style="padding:1px 5px;border:2px solid #CC7110;border-radius:5px;margin-left:10px;">';

echo '<table id="compras-resumen" style="width:100%">';
echo '<tr>';
echo '<td id="compras-resumen-fotografia"><img style="border:1px solid #f0f0f0;" src="'.imagen_URL($variedad['foto'],57,86).'" /></td>';
echo '<td style="color:black;">';
echo '<table style="width:320px;border-collapse:collapse;">';
echo sprintf('<tr><td><span style="display:inline-block;width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="%s (%s)">Arreglo %s</span></td><td>$%s</td></tr>',$variedad['contenedor_titulo'],$variedad['variedad_titulo'],$variedad['contenedor_titulo'],$variedad['precio']);
echo '<tr><td><span style="display:inline-block;width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" class="lugar_de_envio"></span></td><td><span class="precio_de_envio"></span></td></tr>';
echo '<tr id="fila_total"><td style="text-align:right;color:#e18522;padding-right:6px;font-weight:bold;">TOTAL</td><td style="color:#e18522;font-weight:bold;border-top:3px solid #e18522;"><span class="precio_total"></span></td></tr>';
echo '</table>';
echo '</td>';
echo '<td style="color:black;vertical-align:top;">';
echo '<p><span>Fecha de entrega:</span> <span class="fecha_entrega" style="color:#e18522;"></span></p>';
echo '<p><span>Para:</span> <span class="resumen_para" style="color:#e18522;">No ha especificado quién recibe</span></p>';
echo '<p><span>Dirección:</span> <span class="resumen_direccion" style="color:#e18522;">No ha ingresado ninguna dirección</span></p>';
echo '</td>';
echo '</tr>';
echo '</table>';


echo '</div>';

//          Información de cobro
echo '<p class="titulo">8. Forma de pago</p>';
echo '<div id="pago_cero" style="display:none;padding:1px 5px;border:2px solid #CC7110;border-radius:5px;margin-left:10px;">Su arreglo es gratuito!.</div>';
echo '<div id="escoger_metodo_pago" style="padding:1px 5px;border:2px solid #CC7110;border-radius:5px;margin-left:10px;">';

if (isset($flags['diferido']))
{
    echo '<p>Estimado cliente, su pago será diferido a su respectiva empresa asociada. Recuerde que su compra será efectiva cuando la empresa asociada valide la transacción.</p>';
    echo '<input type="hidden" name="tipo_pago" value="diferido" /';

} else {
    echo '<span class="li" style="display:block;margin:5px">Seleccione su método de pago</span>';
    echo '<div style="margin:5px 0px;" id="opciones_de_pago">';
    echo '<input type="radio" name="tipo_pago" id="tipo_pago_tarjeta" class="tipo_pago" '.(@$_POST['tipo_pago'] == 'tarjeta' || empty($_POST['tipo_pago']) ? 'checked="checked"' : '').'   value="tarjeta"/> <label for="tipo_pago_tarjeta">Tarjeta de crédito / débito</label>&nbsp;';
    echo '<input type="radio" name="tipo_pago" id="tipo_pago_abono" class="tipo_pago" '.(@$_POST['tipo_pago'] == 'abono' ? 'checked="checked"' : '').'     value="abono" /> <label for="tipo_pago_abono">Abonar a cuenta de Banco de America Central</label>&nbsp;';
    //echo '<input type="radio" name="tipo_pago" id="tipo_pago_puntoexpress" class="tipo_pago" '.(@$_POST['tipo_pago'] == 'puntoexpress' ? 'checked="checked"' : '').' value="puntoexpress" /> <label for="tipo_pago_puntoexpress">PuntoExpress</label>&nbsp;';
    if ( isset($flags['diferido']) ) echo '<input type="radio" name="tipo_pago" id="tipo_pago_domicilio" class="tipo_pago" '.(@$_POST['tipo_pago'] == 'domicilio' ? 'checked="checked"' : '').' value="domicilio" /> <label for="tipo_pago_domicilio"> Solicitar cobro a domicilio</label>&nbsp;';
    
    
    if (S_iniciado())
    {
        echo '<br /><div style="border:1px dashed pink;padding:4px;margin-top:8px;">';
        echo '<input type="radio" name="tipo_pago" class="tipo_pago" '.(@$_POST['tipo_pago'] == 'kiosko_efectivo' ? 'checked="checked"' : '').' value="kiosko_efectivo" id="tipo_pago_kiosko_efectivo"/> <label for="tipo_pago_kiosko_efectivo">El cliente ya pagó en kiosko en efectivo</label>&nbsp;';
        echo '<input type="radio" name="tipo_pago" class="tipo_pago" '.(@$_POST['tipo_pago'] == 'kiosko_credito' ? 'checked="checked"' : '').' value="kiosko_credito" id="tipo_pago_kiosko_credito"/> <label for="tipo_pago_kiosko_credito">El cliente ya pagó en kiosko con tarjeta</label>';
        echo '<input type="radio" name="tipo_pago" id="tipo_pago_domicilio" class="tipo_pago" '.(@$_POST['tipo_pago'] == 'domicilio' ? 'checked="checked"' : '').' value="domicilio" /> <label for="tipo_pago_domicilio"> Solicitar cobro a domicilio</label>&nbsp;';
        echo '<input type="radio" name="tipo_pago" id="tipo_pago_kiosko" class="tipo_pago" '.(@$_POST['tipo_pago'] == 'kiosko' ? 'checked="checked"' : '').' value="kiosko"/> <label for="tipo_pago_kiosko">Pagaré en kiosko</label>';
        echo '</div>';
    }
}
echo '</div>';

if (!isset($flags['diferido']))
{

    echo '<table id="pago-tipo-tarjeta" class="tabla-estandar table_tipo_pago">';
    echo '<tr>';
    echo '<td>Ingrese el nombre del titular de la tarjeta</td><td>' . ui_input('txt_nombre_t_credito',@$_POST['txt_nombre_t_credito']). '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td style="width:300px;">Ingrese el número de su tarjeta</td><td>'. ui_input('txt_numero_t_credito',@$_POST['txt_numero_t_credito']).'&nbsp;<span style="color:red;" id="validacion_numero_t_credito"></span></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>Ingrese la fecha de vencimiento de su tarjeta</td><td>'.  ui_combobox('txt_tarjeta_mes_vencimiento',ui_combobox_o_meses(),@$_POST['txt_tarjeta_mes_vencimiento'],'','width:100px;') .' '.ui_combobox('txt_tarjeta_ano_vencimiento',ui_combobox_o_anios_futuro(),@$_POST['txt_tarjeta_ano_vencimiento'],'','width:100px;'). '</td>';
    echo '</tr>';
    echo '</table>';
    
    echo '<table id="pago-tipo-domicilio" style="display:none;" class="tabla-estandar table_tipo_pago">';
    echo '<tr>';
    echo '<td style="width:250px;">Enviar factura con atención a</td><td>'. ui_input('txt_cobrar_a',@$_POST['txt_cobrar_a']).'</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td style="width:250px;">Ingrese la dirección de cobro</td><td>'. ui_textarea('txt_direccion_cobro',@$_POST['txt_direccion_cobro'],'','height:90px;').'</td>';
    echo '</tr>';
    echo '</table>';
    
    echo '<table id="pago-tipo-abono" style="display:none;" class="tabla-estandar table_tipo_pago">';
    echo '<tr>';
    echo '<td style="width:250px;"><p>Realizará el depósito bancario al número de cuenta <strong>200721538</strong> del Banco de America Central en la <strong>cuenta corriente</strong> a nombre de <strong>Laura Cañas</strong></p>.<p>El pedido será despachado hasta que se haga efectivo este pago.</p>
    </td>';
    echo '</tr>';
    echo '</table>';

    echo '<table id="pago-tipo-puntoexpress" style="display:none;" class="tabla-estandar table_tipo_pago">';
    echo '<tr>';
    echo '<td style="width:250px;"><p>Realizará el pago mediante <b>PuntoExpress</b>.</p><p>El comprobante de pago para <b>PuntoExpress</b> será enviado a su dirección de correo.</p><p>El pedido será despachado hasta que se haga efectivo este pago.</p></td>';
    echo '</tr>';
    
    if (S_iniciado())
    {
    echo '<table id="pago-tipo-kiosko_efectivo" class="tabla-estandar table_tipo_pago">';
    echo '<tr>';
    echo '<td style="width:250px;">El cliente ya pagó en efectivo en kiosko de Flor360.com en Centro Comercial La Gran Vía.</td>';
    echo '</tr>';
    echo '</table>';
    
    echo '<table id="pago-tipo-kiosko_credito" class="tabla-estandar table_tipo_pago">';
    echo '<tr>';
    echo '<td style="width:250px;">El cliente ya pagó con tarjeta en kiosko de Flor360.com en Centro Comercial La Gran Vía.</td>';
    echo '</tr>';
    echo '</table>';
    }
    
    echo '<table id="pago-tipo-kiosko" class="tabla-estandar table_tipo_pago">';
    echo '<tr>';
    echo '<td style="width:250px;">
    <p>Este modo de pago le permite cancelar el monto total de su pedido en nuestro kiosko de Flor360.com en Centro Comercial La Gran Vía.</p>
    <p>El pedido será despachado hasta que se haga efectivo este pago.</p>
    <p>Horarios: Domingo a Jueves de 11:00a.m. a 9:00a.m. | Viernes y Sábado de 12:00p.m. a 10:00p.m.</p>
    </td>';
    echo '</tr>';
    echo '</table>';

}

echo '</div>';

echo '
<div style="text-align:center;margin-top:10px;">
    <!-- <div class="blink2" style="background:black;color:yellow;padding:10px;font-size:12pt;margin-bottom:10px;">NO SE ACEPTAN MAS PEDIDOS PARA EL 14 DE FEBRERO O 15 DE FEBRERO</div>!-->
    <input type="submit" id="btn_comprar" name="btn_comprar" value="Comprar">    
</div>
';

echo '</form>';

global $GLOBAL_MOSTRAR_PIE;
$GLOBAL_MOSTRAR_PIE = false;
	
$defaultDate = 'new Date('. (time() * 1000) .')';

if (!empty($_POST['txt_fecha_entrega']))
{
    
    $a = strptime(@$_POST['txt_fecha_entrega'],'%A %e de %B de %Y');
    $timestamp = (mktime(0, 0, 0, $a['tm_mon']+1, $a['tm_mday'], $a['tm_year']+1900))*1000;
    $defaultDate = 'new Date('.$timestamp.')';
    
    //$defaultDate = 'new Date('. strtotime('2012-05-21') .')';    
}

// Horarios que se pueden recibir los pedidos para el mismo día: Lunes a Viernes hasta las 3pm, Sabados hasta las 10am, Domingos no hay entregas.
$minDate = 'new Date('. (time() * 1000) .')';

// Mandar al siguiente dia si es mas de las 3:00pm
if (date('G') > 15)
    $minDate = 1;

// Mandar al siguiente dia si es Domingo sin importar la hora
if (date('N') == 7)
    $minDate = 1;

// Mandar al siguiente Lunes si es Sábado y arriba de las 11:00a.m.
if (date('N') == 6 && date('G') > 11)
    $minDate = 2;

// Descomentar para anular el día actual    
//    $minDate = 1;


echo '<script type="text/javascript">precio = '.$precio_sin_formato.';</script>';

// Días cerrado
$cDC = 'SELECT dia, DAY(dia) AS fdia, MONTH(dia) AS fmes, YEAR(dia) AS fano FROM `dias_cerrados` WHERE dia <= (NOW() + INTERVAL 60 DAY) ORDER BY dia ASC';
$rDC = db_consultar($cDC);

if ($rDC)
{
    $hoy = mysql_date();
    while ($fDC = db_fetch(($rDC)))
    {

        if ( $fDC['dia'] == $hoy ) {
            $minDate = 1;
        }
        
        $DC[] = '['.$fDC['fmes'].', '.$fDC['fdia'].', '.$fDC['fano'].']';
    }
}

$constructor_datepicker = '$("#div_fecha_entrega").datepicker({ defaultDate: '.$defaultDate.', inline:true, numberOfMonths: 1, beforeShowDay: nonWorkingDates,  maxDate: "+60D", minDate: '.$minDate.', constrainInput: true, dateFormat : "DD dd \'de\' MM \'de\' yy", altField : "#txt_fecha_entrega", onSelect: ActualizarPrecio});';
?>
<script type="text/javascript">
function nonWorkingDates(date){
    var day = date.getDay(), Sunday = 0, Monday = 1, Tuesday = 2, Wednesday = 3, Thursday = 4, Friday = 5, Saturday = 6;
    var closedDates = [<?php echo join(',', $DC); ?>];
    for (i = 0; i < closedDates.length; i++) {
        var test1 = (date.getMonth() +'-'+ date.getDate() + '-' + date.getFullYear());
        var test2 = ((closedDates[i][0] - 1)+ '-' + closedDates[i][1] + '-' + closedDates[i][2]);
        
        if (  test1 == test2 ) {
            return [false];
        }
    }
    
    <?php if (S_iniciado()): ?>
    return [true];
    <?php else: ?>
    return [day != Sunday, ""];
    <?php endif; ?>
}


function ActualizarPrecio(){
    
    var precio_extras = 0;
    $("table#compras-resumen .resumen-detalle-precio-extra").remove();
    $.each($('.extra:checked'), function(){
        precio_extras = precio_extras + parseFloat($(this).attr('precio'));
        $("tr#fila_total").before('<tr class="resumen-detalle-precio-extra"><td>'+$(this).attr('nombre')+'</td><td>$'+parseFloat($(this).attr('precio')).toFixed(2)+'</td></tr>');
    });
    
    var precio_total = ((precio * $("#cantidad").val()) + $("#destino option:selected").val()*1 + $("#cargos_adicionales").val()*1 + precio_extras*1).toFixed(2);
    
    $(".precio_total").html("$"+precio_total);
    
    $(".lugar_de_envio").html(($("#destino option:selected").val() ? 'Envio a ' + $("#destino option:selected").text() : 'Falta zona de entrega'));
    $(".lugar_de_envio").attr('title',($("#destino option:selected").val() ? $("#destino option:selected").text() : 'Falta zona de entrega'));
    $(".precio_de_envio").html(($("#destino option:selected").val() ? '$'+$("#destino option:selected").val() : '$?'));
    $(".fecha_entrega").html($("#txt_fecha_entrega").val());
    
    
    
    if (precio_total == 0)
    {
        $("#escoger_metodo_pago").hide();
        $("#pago_cero").show();
        $("#tipo_pago_abono").click();
    } else {
        $("#pago_cero").hide();
        $("#escoger_metodo_pago").show();
    }
}

$(document).ready(function(){
    setTimeout(ActualizarPrecio, 500);
});

function luhn(b, y, t, e, s, u) {
b = b.replace(/[^0-9]/g, '');     
s = 0; u = y ? 1 : 2;
for (t = ( b = b + '').length; t--;) {
    e = b[t] * (u^=3);
    s += e-(e>9?9:0);
}
t = 10 - (s % 10 || 10);
return y ? b + t : !t;
}

$(function () {
 
    function makeid(len)
    {
        return (Math.random() +1).toString(36).substr(2, len);
    }
    
    $("#beta__llenarformulario").click(function(event){
        $("#cargos_adicionales").val((Math.random()*100).toFixed(2));
        $("#txt_tarjeta_de").val(makeid(10) + ' ' + makeid(10));
        $("#txt_correo_contacto").val('c.duran@flor360.com');
        $("#txt_telefono_remitente").val(makeid(10));
        $("#destino option").eq(10).prop('selected',true);
        
        $("#txt_tarjeta_para").val(makeid(10) + ' ' + makeid(10));
        $("#txt_telefono_destinatario").val(makeid(10));
        
        $("#txt_direccion_entrega").val(makeid(10) + ' ' + makeid(10) + ' ' + makeid(10) + ' ' + makeid(10) + ' ' + makeid(10));
        $("#txt_tarjeta_cuerpo").val(makeid(10) + ' ' + makeid(10) + ' ' + makeid(10) + ' ' + makeid(10) + ' ' + makeid(10));
        ActualizarPrecio();
    });
        
    $("#txt_correo_contacto").keypress(function(){
        $('#compras_anteriores').html('');
    });
    
    $("#txt_correo_contacto").blur(function(){
        $("#txt_correo_contacto").val($.trim($("#txt_correo_contacto").val()));
        $("#compras_anteriores").load('ajax',{pajax:'compras_anteriores', correo:$("#txt_correo_contacto").val()});
    });
    
    $("#txt_tarjeta_de").keypress(function(){
        $('#validacion_remitente').html('');
    });
    
    $("#txt_tarjeta_de").blur(function(){
        if ($(this).val().length < 5)
        {
            $('#validacion_remitente').html('Favor ingrese nombre y apellido.');
        } else {
            $('#validacion_remitente').html('');
        }
    });

    $("#txt_numero_t_credito").keypress(function(){
        $('#validacion_numero_t_credito').html('');
    });
    
    $("#txt_numero_t_credito").blur(function(){
        if (luhn($(this).val()))
        {
            $('#validacion_numero_t_credito').html('');
        } else {
            $('#validacion_numero_t_credito').html('Número de tarjeta inválido.');
        }
    });

    $("#txt_tarjeta_para").keypress(function(){
        $('#validacion_destinatario').html('');
    });
    
    $("#txt_tarjeta_para").blur(function(){
        if ($(this).val().length < 5)
        {
            $('#validacion_destinatario').html('Favor ingrese nombre y apellido.');
        } else {
            $('#validacion_destinatario').html('');
        }
    });
    
    $('#opciones_de_pago input[type="radio"]').button();
    
    $("#aplicar_cupon").click(function (event){
        $(window).unbind("beforeunload");
    });
    
    $("#btn_comprar").click(function (event){
        if ($("#destino option:selected").val() == "")
        {
            $("#destino").focus();
            alert ("Favor seleccionar zona de entrega.");
            $("#destino").effect("highlight", {color: "orange"}, 10000);
            event.preventDefault();
            return false;
        }
        $(window).unbind("beforeunload");
        return true;
    });
    
    $(".tipo_pago").click(function(){
        $(".table_tipo_pago").hide();$("#pago-tipo-"+$(this).attr("value")).show();
        //$(".info-tipo-pago").hide();$("#info-tipo-"+$(this).attr("value")).show();
    });
    
    $(".tipo_pago:checked").trigger("click");
    
    $(".tipo_pago").each(function(){$(this).css("display","");});
    
    $.datepicker.regional["es"];
    <?php echo $constructor_datepicker; ?>
    $("#destino").change(function(){ActualizarPrecio();});
    $(".extra").click(function(){
        var grupo = $(this).attr('grupo');
        $('.extra[grupo="'+grupo+'"]:checked').not(this).removeAttr('checked');
        ActualizarPrecio();
    });
    $("#cargos_adicionales, #cantidad").keyup(function(){ActualizarPrecio();});
    
    
    $("#txt_fecha_entrega").attr("readonly","readonly");

    $("td#compras-resumen-fotografia>img").click(function(){$.facebox('<img src="'+$("td#compras-resumen-fotografia>img").attr('src_grande')+'" />')});
    
    $("#txt_telefono_remitente").qtip({content: {text: '<ul class="sin_margen"><li>numero telefonico local o extranjero</li><li>fijo o móvil (celular)</li><li>Agregar código de área si es extranjero</li><li>Colocar No. de extensión si es necesario</li></ul></p>'}, style: {classes: "ui-tooltip-shadow ui-tooltip-cream ui-tooltip-shadow", tip: "bottom center"}, position: {my: "bottom center",at: "top center"}});
    $("#txt_usuario_notas").qtip({content: {text: '<ul class="sin_margen"><li>Horas en las que puede encontrarse la persona</li><li>Con quien dejar si no se encuentra la persona</li><li>Si es envío anónimo</li><li>Preferencias de color de flores o globos.</li></ul><b>Recuerde que los colores de flores, estilos de bases y globos dependen de las existencias</b></p>'}, style: {classes: "ui-tooltip-shadow ui-tooltip-cream ui-tooltip-shadow", tip: "bottom center"}, position: {my: "bottom center",at: "top center"}});
    $("#txt_tarjeta_cuerpo").qtip({content: {text: '<p>Incluir el texto tal y como desee que aparezca en la tarjeta que acompaña el arreglo:</p><h1>Ejemplo 1</h1><p>Para: Rocio</p><p>¡te amo mucho!</p><p>De: Franklin</p>'}, style: {classes: "ui-tooltip-shadow ui-tooltip-cream ui-tooltip-shadow", tip: "bottom center"}, position: {my: "bottom center",at: "top center"}});
    $("#txt_correo_contacto").qtip({content: {text: '<p>Utilizar un correo válido, pues en esta dirección recibirá:</p><ul class="sin_margen"><li>Comprobante de compra</li><li>Datos de facturación</li><li>Confirmación de entrega de su pedido</li></ul>'}, style: {classes: "ui-tooltip-shadow ui-tooltip-cream ui-tooltip-shadow", tip: "top center"}, position: {my: "top center",at: "bottom center"}});
    $("#txt_direccion_entrega").qtip({content: {text: "<?php echo addslashes(preg_replace('/(\r\n|\n|\r)/','',file_get_contents('TXT/buenas_direcciones.ayuda.editable'))); ?>"}, style: {classes: "ui-tooltip-shadow ui-tooltip-cream ui-tooltip-shadow", tip: "left center"}, position: {my: "left center",at: "right center"}});
    $("#txt_tarjeta_de").qtip({content: {text: '<p>Este nombre es exclusivo para contactarlo a usted, este nombre no aparecerá en el texto de la dedicatoria. Si desea favor incluir la firma de quien envía en el texto de dedicatoria.</p>'}, style: {classes: "ui-tooltip-shadow ui-tooltip-cream ui-tooltip-shadow", tip: "bottom center"}, position: {my: "bottom center",at: "top center"}});
    
    $("#txt_direccion_entrega").keyup(function(){$(".resumen_direccion").html($("#txt_direccion_entrega").val());});
    $("#txt_tarjeta_para").keyup(function(){$(".resumen_para").html($("#txt_tarjeta_para").val());});
    
    $(window).bind('beforeunload', function(){
        return "¿Esta seguro de salir?. Si sale de esta página perderá todos los datos ingresados.";
    });
});
</script>

<?php
/*****************************************************************************/
function SSL_COMPRA_PROCESAR()
{
    global $variedad, $cupon_invalidar_codigo, $cupon, $flags;
        
    if (!isset($_POST['btn_comprar']) || !isset($_POST['variedad']))
    {
        return false;
    }

    // Revisamos si ya envió la compra, no vaya a ser doble compra.
    if (db_contar(db_prefijo.'SSL_compra_contenedor', 'transaccion="'.db_codex($_POST['transaccion']).'"'))
    {
        header("Location: " . PROY_URL);
        exit;
    }

    // Caja negra
    $DATOS['respaldo'] = json_encode($_POST);
    
    // Verificamos que todos los datos sean válidos
    $ERRORES = array();
    

    if (@$_POST['tipo_pago'] == 'tarjeta')
    {
        $_POST['txt_numero_t_credito_regexed'] = db_codex(preg_replace('/[^\d]/','',trim($_POST['txt_numero_t_credito'])));
    
        $_POST['txt_fecha_expiracion'] = $_POST['txt_tarjeta_mes_vencimiento'].'/'.$_POST['txt_tarjeta_ano_vencimiento'];
        
        if (!luhn($_POST['txt_numero_t_credito_regexed']))
        {
            $ERRORES[] = 'La tarjeta de crédito ingresada parece inválida.';
        }
        
        if ($_POST['txt_tarjeta_ano_vencimiento'] <= date('Y') && $_POST['txt_tarjeta_mes_vencimiento'] < date('n'))
        {
            $ERRORES[] = 'Parece que la tarjeta de crédito ha expirado (vencido).';
        }
    }
    
    // Tratamos de ver si la direccion de entrega es valida
    if (strlen(preg_replace('[^\w]','',$_POST['txt_direccion_entrega'])) < 5)
    {
        $ERRORES[] = 'Por favor revise que la dirección de entrega sea correcta y suficimientemente detallada.';
    }    // Tratamos de ver si la direccion de entrega es valida

    $_POST['txt_fecha_entrega'] = html_entity_decode($_POST['txt_fecha_entrega'],null, 'UTF-8');

    // Si tiene JS apagado, verificar que la fecha de entrega no este vacía
    if (empty($_POST['txt_fecha_entrega']))
    {
        $ERRORES[] = 'Por favor revise la fecha de entrega.';
    }
    
    // Si tiene JS apagado la fecha sera en formato Y-m-d
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$_POST['txt_fecha_entrega']))
    {
        // Convertimos la fecha de entrega a lo que esperamos
        $a = strptime($_POST['txt_fecha_entrega'],'%A %e de %B de %Y');
        $timestamp = mktime(0, 0, 0, $a['tm_mon']+1, $a['tm_mday'], $a['tm_year']+1900);
        $_POST['txt_fecha_entrega_traducida'] = date('Y-m-d',$timestamp);
        
        if ($a['tm_year']+1900 < date("Y"))
        {
            $ERRORES[] = 'La fecha de entrega esta para un año anterior al actual, favor verifique. ['.$_POST['txt_fecha_entrega'].']';
        }
    } else {
        if (substr ($_POST['txt_fecha_entrega'],0,4) < date("Y") )
        {
            $ERRORES[] = 'La fecha de entrega esta para un año diferente al actual, favor verifique. ['.$_POST['txt_fecha_entrega'].']';
        }
        $_POST['txt_fecha_entrega_traducida'] = $_POST['txt_fecha_entrega'];
    }
    
    /*
    if (date('N', strtotime($_POST['txt_fecha_entrega_traducida'])) == '7')
    {
        $ERRORES[] = 'La fecha de entrega NO PUEDE SER DOMINGO.';
    }
    */
    
    if (db_contar('dias_cerrados', 'dia="'.$_POST['txt_fecha_entrega_traducida'].'"') > 0)
    {
        $ERRORES[] = 'La fecha de entrega seleccionada no es permitida.';
    }
    
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$_POST['txt_fecha_entrega_traducida']))
    {
        $ERRORES[] = 'Por favor revise la fecha de entrega.';
    }
    
    if (strlen($_POST['txt_tarjeta_de']) < 5)
    {
        $ERRORES[] = 'Ingrese un nombre y un apellido para contactarlo.';
    }
    
    if (strlen($_POST['txt_tarjeta_para']) < 5)
    {
        $ERRORES[] = 'Ingrese un nombre y un apellido para contactar a la persona que recibe.';
    }
    
    if (strlen($_POST['destino']) == "")
    {
        $ERRORES[] = 'Favor especifique el destino de envío';
    }
    
    if (strlen($_POST['txt_telefono_remitente']) < 5)
    {
        $ERRORES[] = 'Ingrese un número de teléfono para contactarlo.';
    }

    $_POST['txt_correo_contacto'] = strtolower(preg_replace('/\s/','',$_POST['txt_correo_contacto']));
    if (!validcorreo($_POST['txt_correo_contacto']))
    {
        $ERRORES[] = 'El correo ingresado no parece valido, por favor compruebelo o utilice otro (hotmail / gmail / yahoo).';
    }
    
    if (!is_numeric($_POST['cargos_adicionales']) || $_POST['cargos_adicionales'] < 0)
    {
        $ERRORES[] = 'Cargo adicional debe ser un número positivo o 0.00';
    }

    if (!is_numeric($_POST['destino']) || $_POST['destino'] < 0)
    {
        $ERRORES[] = 'Precio de destino debe ser un número positivo o 0.00';
    }
    
    if (!is_numeric($_POST['cantidad']) || $_POST['cantidad'] < $variedad['compra_minima'])
    {
        $ERRORES[] = 'La cantidad de arreglos debe ser igual o mayor que '.$variedad['compra_minima'].' unidades';
    }
    
    if (count($ERRORES) > 0)
    {
        echo '<div style="background-color:#fef1b9;font-size:14px;padding:10px;border-radius:10px;">';
        echo '<p class="blink" style="color:red;">Hemos detectado los siguientes errores en los datos introducidos y no podremos procesar su compra a menos que sean corregidos:</p>';
        echo '<p class="ui-state-error" style="padding:3px;">'.join('</p><p style="padding:3px;" class="ui-state-error">',$ERRORES).'</p>';
        echo '</div>';
        
        reset($ERRORES);
        global $_gaq;
        
        $_gaq .= "\n _gaq.push(['_trackEvent', 'Compras', 'Bloqueo', 'Uno o mas errores impidieron la compra']);";
        
        foreach($ERRORES as $ERROR)
        {
            $_gaq .= "\n _gaq.push(['_trackEvent', 'Compras', 'Error', '". addslashes ($ERROR) ."']);";
        }
        
        return;
    }
    
    if (isset($_POST['txt_numero_t_credito_regexed']))
    {
        // Encriptamos la tarjeta de credito
        $c = sprintf('SELECT AES_ENCRYPT("%s","%s") AS t_credito_AES',$_POST['txt_numero_t_credito_regexed'],db__key_str);
        $r = db_consultar($c);
        $f = mysqli_fetch_assoc($r);
    }

    // Swap de cargo_adicionales a precio y notas de usuario a preparacion personalizada, este es el arreglo especial #1000
    if ($variedad['codigo_variedad'] == '237')
    {
        $variedad['precio'] = @(double)$_POST['cargos_adicionales'];
        $_POST['cargos_adicionales'] = 0;
        
        $DATOS['preparacion_personalizada'] = $_POST['txt_usuario_notas'];
    }


    if (!empty($_POST['codigo_cupon'])) {
        $_POST['codigo_cupon'] = 'I:'.@$_POST['codigo_cupon'];
    } else {
        $_POST['codigo_cupon'] = '';
    }

    
    // IP del cliente
    
    $ip_cliente = '0.0.0.0';
    
    if ( !empty($_SERVER["REMOTE_ADDR"]) )    {
        $ip_cliente = $_SERVER["REMOTE_ADDR"];
    } else if ( !empty($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
        $ip_cliente = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else if ( !empty($_SERVER["HTTP_CLIENT_IP"]) )    {
        $ip_cliente = $_SERVER["HTTP_CLIENT_IP"];
    }     
    
    /* ORDEN */
    db_consultar('INSERT INTO `flores_maximo_orden` (`max_orden`,`fecha_entrega`) VALUES(1, "'.@$_POST['txt_fecha_entrega_traducida'] .'") ON DUPLICATE KEY UPDATE max_orden=max_orden+1');    
    $max_orden = db_obtener('flores_maximo_orden', 'max_orden', '`fecha_entrega` = "'.@$_POST['txt_fecha_entrega_traducida'] .'"');
    /* ORDEN */
    
    $DATOS['codigo_usuario'] =                  _F_usuario_cache('codigo_usuario');
    $DATOS['fecha'] =                           mysql_datetime();
    $DATOS['codigo_variedad'] =                 @$variedad['codigo_variedad'];
    $DATOS['cantidad'] =                        @$_POST['cantidad'];
    $DATOS['precio_original'] =                 @$variedad['precio_original'];
    $DATOS['precio_grabado'] =                  @$variedad['precio'];
    $DATOS['cargo_adicional'] =                 @(double)$_POST['cargos_adicionales'];
    $DATOS['precio_envio'] =                    @(double)$_POST['destino'];
    $DATOS['metodo_pago']   =                   @$_POST['tipo_pago'];
    $DATOS['n_credito'] =                       @$f['t_credito_AES'];
    $DATOS['telefono_destinatario'] =           @$_POST['txt_telefono_destinatario'];
    $DATOS['telefono_remitente'] =              @$_POST['txt_telefono_remitente'];
    $DATOS['fecha_exp_t_credito'] =             @$_POST['txt_fecha_expiracion'];
    $DATOS['nombre_t_credito'] =                @$_POST['txt_nombre_t_credito'];
    $DATOS['anonimo'] =                         (@$_POST['anonimo'] == 'si' ? '1' : '0');
    $DATOS['cobrar_a']   =                      @$_POST['txt_cobrar_a'];
    $DATOS['cobrar_en']   =                     @$_POST['txt_direccion_cobro'];
    $DATOS['direccion_entrega'] =               @$_POST['txt_direccion_entrega'];
    $DATOS['fecha_entrega'] =                   @$_POST['txt_fecha_entrega_traducida'];
    $DATOS['tarjeta_de'] =                      @$_POST['txt_tarjeta_de'];
    $DATOS['tarjeta_para'] =                    @$_POST['txt_tarjeta_para'];
    $DATOS['tarjeta_cuerpo'] =                  @$_POST['txt_tarjeta_cuerpo'];
    $DATOS['usuario_notas'] =                   @$_POST['txt_usuario_notas'];
    $DATOS['correo_contacto'] =                 @$_POST['txt_correo_contacto'];
    $DATOS['transaccion'] =                     @$_POST['transaccion'];
    $DATOS['cupon'] =                           @$_POST['codigo_cupon'];
    $DATOS['ip'] =                              $ip_cliente;
    $DATOS['orden'] =                           $max_orden;
    $DATOS['salt'] =                            genRandomString(4);

    $via = 'Web';

    if (S_iniciado()){
        $via = 'Agente';
    }
    
    if (isset($_GET['fb']))
    {
        $via = 'FaceBook';
    }
    
    $DATOS['referrer'] = $via;

    $ID = db_agregar_datos(db_prefijo.'SSL_compra_contenedor',$DATOS);
    
    db_consultar('UPDATE `flores_SSL_compra_contenedor` SET `indicerapido` = SHA1(CONCAT(`codigo_compra`,`salt`)) WHERE `codigo_compra` = "'.$ID.'" LIMIT 1');
    
    $precio_extra = 0;
    /* Grabamos los extras */
    if (isset($_POST['extra']) && is_array($_POST['extra']))
    {
        foreach($_POST['extra'] AS $codigo_extra => $valor)
        {
            $precio_extra_actual = doubleval( db_obtener('extras','precio','codigo_extra = "'.db_codex($codigo_extra).'"') );
            db_agregar_datos('extras_compras',array('codigo_compra' => $ID, 'codigo_extra' => $codigo_extra, 'especificacion' => @$_POST['extra_esp'][$codigo_extra], 'precio_extra' => $precio_extra_actual));
            $precio_extra += $precio_extra_actual;
        }
    }

    $precio_grabado =  doubleval($precio_extra) + (doubleval($DATOS['cantidad']*$DATOS['precio_grabado'])  +  doubleval($DATOS['cargo_adicional']) + doubleval($DATOS['precio_envio']));
    
    /* Grabamos la transaccion para el corte del kiosko */
    if (S_iniciado() && ($DATOS['metodo_pago'] == 'kiosko_efectivo' || $DATOS['metodo_pago'] == 'kiosko_credito'))
    {
        $c = sprintf('UPDATE `flores_SSL_compra_contenedor` SET referrer="Kiosko", flag_cobrado=1, estado_notas="[AUTO] Pagado en kiosko" WHERE codigo_compra="%s" LIMIT 1', $ID);
        db_consultar($c);
        registrar($ID,'pago.kiosko','Compra registrada en Kiosko via formulario de compra','');
        
        $c = sprintf('INSERT INTO `flores_kiosko_transacciones` (`codigo_kiosko_articulo`, `codigo_usuario`, `operacion`, `cantidad`, `precio_grabado`, `fecha`, `transaccion`, `metodo_pago`,`descripcion`, `flag_arreglo`) VALUES(100, %s,"venta",1,%s,NOW(),"%s","%s","%s",1)', _F_usuario_cache('codigo_usuario'),$precio_grabado, $ID, $DATOS['metodo_pago'], 'A: #'.$ID);
        $r = db_consultar($c);
    }


    
    if ( is_numeric($cupon_invalidar_codigo) )
    {
        $c = 'UPDATE `flores_cupones` SET cantidad_usado=cantidad_usado+1 WHERE codigo_cupon='.$cupon_invalidar_codigo.' LIMIT 1';
        db_consultar($c);

        if ($flags['diferido'] == true)
        {
            $cAsociados = 'SELECT `codigo_asociado`, `correo`, `nombre`, `asociacion` FROM `asociados` WHERE `codigo_asociado`="'.$cupon['facturar_a'].'"';
            
            $rAsociados = db_consultar($cAsociados);
            
            if ( mysqli_num_rows ($rAsociados) > 0)
            {
                while ($fAsociados = db_fetch($rAsociados))
                {
                    $mensaje = "
                    <p>Notificamos que el Sr./Sra. ".$cupon['enviado_a']." asociado a ".$fAsociados['asociacion']." ha realizado una compra según el detalle siguiente:</p><br />
                    
                    <p>Asociado: ".$cupon['enviado_a']."</p>
                    <p>Gafete No.: ".$cupon['codigo']."</p>
                    <p>Fecha de compra: ".mysql_datetime()." </p>
                    <p>Precio de compra: $".$precio_grabado."</p>
                    <p>Código de compra: ".$ID.$DATOS['salt']."</p><br />
                    
                    <p>Favor confirmar este pedido mediante una repuesta a este correo para proceder con la entrega.</p><br />
                    
                    <p>Atentamente,</p><br />
                    
                    <p>Floristería Flor360</p>
                    ";
                    correo($fAsociados['correo'],'Nueva compra [#'.$ID.'] en Flor360.com - necesita aprobación',$mensaje);
                }
            }
        }
    }
    
    return $ID;
}


/*****************************************************************************/
function SSL_MOSTRAR_FACTURA($id_factura)
{
    
    $transaccion=db_obtener(db_prefijo.'SSL_compra_contenedor','transaccion','codigo_compra="'.$id_factura.'"');
    list($factura,$f) = SSL_COMPRA_FACTURA($transaccion);

    // FRAUDE?
    $f['fraude'] = false;
    
    if (db_contar('blacklist', 'tarjeta="'.$f['n_credito_DAES'].'"') > 0)
      $f['fraude'] = true;

    if (db_contar('blacklist_correo', 'correo="'.$f['correo_contacto'].'"') > 0)
      $f['fraude'] = true;

    
    // Correo para el staff
    $to      = PROY_MAIL_POSTMASTER;        
    $subject = ($f['fraude'] ? '[FRAUDE?] - ' : '') . 'Compra en '.PROY_NOMBRE_CORTO.' [#'.$f['codigo_compra'].$f['salt'].']';
    $message = "";

    if (defined('modo_beta') && modo_beta == true)
    {
        $to = 'c.duran@flor360.com';
        $subject = '[BETA] '.($f['fraude'] ? '[FRAUDE?] - ' : '') . 'Compra en '.PROY_NOMBRE_CORTO.' [#'.$f['codigo_compra'].$f['salt'].']';
    }
    
    if ($f['fraude']) $message = "<p><strong>SE HA DETECTADO UN POSIBLE FRAUDE EN ESTA COMPRA</strong></p>";
    $message .= "<hr />" . $factura . "\n";
    $message .= '
    <hr />
    <p>Ver en sistema de ventas: <a href="'.PROY_URL.'ventas?c='.$f['codigo_compra'].'">clic aquí</a></p>
    <p>Este pedido tiene el número de orden: <b>'.$f['orden'].'</b> para este día</p>
    ';
    
    if ($f['correo_contacto'] != 'nada@flor360.com') correo($to, $subject, $message);

    // Correo para el cliente
    if(!empty($f['correo_contacto']))
    {
        $to      = @$f['correo_contacto'];
        $subject = 'Datos de su compra [#'.$f['codigo_compra'].$f['salt'].']';
        
        $message =  '<h1>¡Compra recibida!</h1>';
        $message .= "<p>Gracias por su compra en ".PROY_NOMBRE_CORTO.", su pedido ha sido recibido en nuestro sistema.</p>";
        
        $message .= '
        <h2>Importante para su conocimiento</h2>
        <p>Cuando su pedido sea entregado se le será notificado mediante un correo electrónico a esta misma dirección.</p>
        <p>Si Ud. pagó con tarjeta de crédito o débito su cargo no será realizado de forma inmediata debido a que revisamos y aprobamos cada pedido de forma individual para asegurarnos de no realizarle cobros erroneos.</p>
        <hr />';

        $message .= "<h2>Datos de la compra</h2>";
        $message .= "<p>Por favor corrobore que todos los datos presentandos a continuación sean correctos.</p><hr />\n" . $factura . "<hr />\n";
        
        $message .= sprintf('<p>Puede consultar el estado de su orden desde la siguiente dirección Web:<br /><a href="%s" target="_blank">%s</a></p>',PROY_URL.'informacion?tipo=estado&pin='.$f['indicerapido'],'Estado de la orden');
        $message .= sprintf('<p>Su copia del recibo virtual se encuentra en la siguiente dirección web<br /><a href="%s" target="_blank">%s</a></p>',PROY_URL.'informacion?tipo=factura&pin='.$transaccion, 'Recibo virtual');

        if ($f['correo_contacto'] != 'silenciarcorreo@flor360.com')
            correo($to, $subject, $message);
        
        if (strstr(@$f['usuario_notas'],'ACMULTI'))
        {
            $cAsociados = 'SELECT `codigo_asociado`, `correo`, `nombre`, `asociacion` FROM `asociados` WHERE `codigo_asociado`="ACMULTI" ';
            
            $rAsociados = db_consultar($cAsociados);
            
            if (mysqli_row_count($rAsociados) > 0)
            {
                while ($fAsociados = db_fetch($rAsociados))
                {
                    correo($fAsociados['correo'],'Nueva compra [#'.$ID.'] en Flor360.com',$message);
                }
            }
        }
    }
    
    echo '<h1>Transaccion completada</h1>';
    echo '<p>¡Gracias por su compra!, el equipo de Flor360.com comenzara a elaborar su pedido con las flores mas frescas disponibles en este preciso momento.</p>';
    
    echo '<hr />';
    
    if (@$f['metodo_pago'] == 'puntoexpress')
    {
        global $arrJS;
        global $arrCSS;
        global $arrHEAD;
        
        require_once (__BASE__ARRANQUE."PHP/lib_puntoexpress.php");
        $arrJS[] = 'jquery.jqprint';
        $arrCSS[] = 'CSS/pex';
        
        $arrHEAD[] = JS_onload('
            $("#lib_pex_control .btn-danger").remove();
            $("#lib_pex_control button.btn-primary").removeAttr("onclick");
            $("#lib_pex_control button.btn-primary").click(function(event){
                event.preventDefault();
                var impresion = $("#lib_pex_impresion").clone();
                impresion.show();
                impresion.jqprint();
                return false;
            });
        ');

        
        $pex = new puntoexpress();
        $pex->desarrollo = true;
        list ($control, $impresion) = $pex->enviar('lib_pex',$f['codigo_compra'].$f['salt'], $f['precio_total'],$f['correo_contacto'],$f['telefono_remitente']);
        echo $control;
        echo $impresion;
    }    

    echo '<hr />';
    echo '<h2>Factura</h2>';
    echo $factura;
    echo '<hr />';
    if (S_iniciado())
    {
        echo '<p style="margin:50px;padding:50px;border:1px solid red;font-size:24px;text-align:center;">Imprimir el boucher: <a href="'.PROY_URL.'+impresion?objetivo=Boucher&nocache=nocache&transaccion='.$transaccion.'" target="_blank">Imprimir Boucher</a></p>';
    } else {
        echo sprintf('<p>Puede consultar el estado de su orden desde la siguiente dirección Web:<br /><a href="%s" target="_blank">%s</a></p>',PROY_URL.'informacion?tipo=estado&pin='.$f['indicerapido'],'Estado de la orden');
        echo sprintf('<p>Su copia de los datos ingresados se encuentran en la siguiente dirección web<br /><a href="%s" target="_blank">%s</a></p>',PROY_URL.'informacion?tipo=factura&pin='.$transaccion, 'Recibo virtual');
    }

    
    return $f;
}
?>
