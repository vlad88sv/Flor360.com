<?php
protegerme(false,array(_N_vendedor));

/* Ingreso, Egreso, Venta */
switch (@$_GET['modo'])
{
    case 'ingresos':
        $modo = 'ingreso';
        $nombreBoton = 'Ingresar';
        break;
    
    case 'egresos':
        $modo = 'egreso';
        $nombreBoton = 'Egresar';
        break;
    
    case 'pagos':
        require_once(__BASE_cePOSa__.'PHP/caja.pagos.php');
        return;
    
    case 'devoluciones':
        require_once(__BASE_cePOSa__.'PHP/caja.devoluciones.php');
        return;       
        
    default:
        $modo = 'venta';
        $nombreBoton = 'Vender';
        break;
}

if (isset($_POST['vender']))
{
    $_POST['articulo'] = (array_filter($_POST['articulo']));
    
    if (isset($_POST['articulo']) && count($_POST['articulo']))
    {
        $transaccion = sha1(microtime(true));
        foreach($_POST['articulo'] as $codigo_producto => $cantidad)
        {
           $c = sprintf('INSERT INTO `flores_kiosko_transacciones` (`codigo_kiosko_articulo`, `codigo_usuario`, `operacion`, `cantidad`, `precio_grabado`, `fecha`, `transaccion`, `metodo_pago`,`descripcion`) VALUES(%s,%s,"'.$modo.'",%s,%s,NOW(),"%s","%s",%s)', $codigo_producto, _F_usuario_cache('codigo_usuario'),$cantidad,'(SELECT `precio` FROM `flores_kiosko_articulos` WHERE `codigo_kiosko_articulo`='.$codigo_producto.')',$transaccion,@$_POST['metodo_pago'],'(SELECT `descripcion` FROM `flores_kiosko_articulos` WHERE `codigo_kiosko_articulo`='.$codigo_producto.')');
           $r = db_consultar($c);
        }
        if ($modo == 'venta') {
            echo '<p>Un momento... redirigiendo a impresión del comprobante. Si no le redirige, <a href="'.PROY_URL.'+impresion?nocache=nocache&objetivo=Tiquete&transaccion='.$transaccion.'">haga clic aquí</a></p>';
            echo '<script>window.location.href="'.PROY_URL.'+impresion?nocache=nocache&objetivo=Tiquete&transaccion='.$transaccion.'";</script>';
        } else {
            echo '<p>Cambio realizado.</p>';
        }
        return;
    }
}
?>
<form autocomplete="off" action="<?php echo PROY_URL_ACTUAL_DINAMICA; ?>" method="post">
<table id="tabla-caja" class="tabla-estandar zebra">
    <tr><th style="width:490px;">Artículo</th><th>Inventario</th><th>Cantidad</th><th>Precio</th><th>Sub total</th></tr>
    <?php
    $c = 'SELECT `codigo_kiosko_articulo`, t1.`descripcion`, COALESCE(SUM(IF(`operacion`="ingreso",`cantidad`,-`cantidad`)),0) AS "inventario", `precio` FROM `flores_kiosko_articulos` AS t1 LEFT JOIN `flores_kiosko_transacciones` USING (codigo_kiosko_articulo) WHERE mostrar="si" GROUP BY `codigo_kiosko_articulo` ORDER BY FIELD(`permanencia`,"permanente","temporal"), descripcion ASC ';
    $r = db_consultar($c);
    while ($f = mysqli_fetch_assoc($r))
        echo '<tr><td>'.$f['descripcion'].'</td><td style="text-align:center;width:100px;text-align:right;">'.$f['inventario'].' <b>[</b> <a target="_blank" href="+stock?codigo_articulo='.$f['codigo_kiosko_articulo'].'">Stock <img style="vertical-align:middle;" src="IMG/iconos/ver.gif" /></a> <b>]</b></td><td style="text-align:center;"><input autocomplete="off" type="text" class="cantidad" style="text-align:center;" name="articulo['.$f['codigo_kiosko_articulo'].']" value="0" /></td><td style="text-align:center;">$<span class="precio_unitario">'.$f['precio'].'</span></td><td style="text-align:center;width:200px;">$<span class="sub_total">0.00</span></td></tr>';
    ?>
    <tr><th colspan="4" style="text-align:right;"></th><th>$<span id="total">0.00</span></th></tr>
</table>
<hr style="border:1px solid gray;" />
<div style="text-align: center;">
    <?php if ($modo == 'venta'): ?>
    <span style="font-size:24px;"><input type="radio" name="metodo_pago" checked="checked" value="kiosko_efectivo" /> Efectivo  <input type="radio" name="metodo_pago" value="kiosko_credito" /> POS</span><br />
    <?php endif; ?>
    <input style="font-size:28px;font-weight:bold;" type="submit" name="vender" value="<?php echo $nombreBoton; ?>" />
</div>
</form>

<script type="text/javascript">
$(document).ready(function(){
    $('.cantidad').keydown(function(event) {
        if ((event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 ) && event.keyCode != 46 && event.keyCode != 8) {
            event.preventDefault(); 
        }  
    });
        
    $('.cantidad').keyup(function(){
        var indice = $(this).closest('tr').prevAll().length;
        $('#tabla-caja tr:eq('+indice+') .sub_total').html(
            ($('#tabla-caja tr:eq('+indice+') .cantidad').val() * $('#tabla-caja tr:eq('+indice+') .precio_unitario').html()).toFixed(2)
        );
        
        var sum = 0;
        $('.sub_total').each(function() {
            sum += Number($(this).html());
        });

        $('#total').html(sum.toFixed(2));
    });
});
</script>