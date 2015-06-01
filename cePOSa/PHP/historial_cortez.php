<?php
protegerme(false,array(_N_vendedor));
ACTIVAR_PAQUETES(array('ui'));
require_once(__BASE_cePOSa__.'PHP/ssl.comun.php');

$fecha = (isset($_GET['fecha']) ? $_GET['fecha'] : mysql_date());
$c = 'SELECT ID_cortez, TIME(fecha) AS tiempo FROM '.db_prefijo.'cortez WHERE DATE(fecha) = "'.$fecha.'"';
$r = db_consultar($c);
$html = null;
while ($f = mysqli_fetch_assoc($r))
{
    $html .= '<li><a target="_blank" href="'.PROY_URL.'+impresion?objetivo=CorteZ&transaccion='.$f['ID_cortez'].'">'.$f['tiempo'].'</a></li>'."\n";
}
?>
<form style="display:inline" method="get" action="<?php echo PROY_URL_ACTUAL; ?>">
Ver cortes Z del día: <input name="fecha" type="text" class="datepicker" value="<?php echo $fecha; ?>" />
<input type="submit" value="Ir" class="ir"/>
</form>
<hr />
<h1>Detalles de venta de día <?php echo $fecha; ?></h1>
<?php

    $totalCredito = 0;
    $totalEfectivo = 0;

    // Detallemos lo que se vendió
    $cArreglosVendidos = 'SELECT codigo_compra, `flores_SSL_compra_contenedor`.transaccion, codigo_kiosko_transaccion, `flores_kiosko_transacciones`.metodo_pago, codigo_compra, flores_producto_variedad.descripcion, (flores_SSL_compra_contenedor.cantidad*flores_SSL_compra_contenedor.precio_grabado)+flores_SSL_compra_contenedor.cargo_adicional+flores_SSL_compra_contenedor.precio_envio AS monto FROM `flores_SSL_compra_contenedor` LEFT JOIN `flores_producto_variedad` USING(`codigo_variedad`) LEFT JOIN `flores_kiosko_transacciones` ON flores_SSL_compra_contenedor.codigo_compra = `flores_kiosko_transacciones`.transaccion WHERE flag_arreglo=1 AND `flores_kiosko_transacciones`.fecha BETWEEN "'.$fecha.' 00:00:00" AND "'.$fecha.' 23:59:59"';
    $r = db_consultar($cArreglosVendidos);   
    
    echo '<h2>Arreglos vendidos</h2>';
    echo '<table class="tabla-estandar tabla-centrada zebra" style="width:100%;">';
    echo '<tr><th style="width:25px;"></th><th>Código Compra</th><th>Descripción</th><th>Metodo pago</th><th>Monto</th><th>Acción</th></tr>';
    while ($f = mysqli_fetch_assoc($r))
    {
        
        list($f['extras'], $f['extrasPrecio']) = SSL_COMPRA_OBTENER_EXTRAS($f['codigo_compra']);
        $f['monto'] += $f['extrasPrecio'];
        
        echo '<tr><td><img rel="'.$f['codigo_kiosko_transaccion'].'" class="eliminar_transaccion" src="/IMG/iconos/delete.jpeg" /></td><td>'.$f['codigo_compra'].'</td><td>'.$f['descripcion'].'</td><td>'.$f['metodo_pago'].'</td><td>$'.$f['monto'].'</td><td><a rel="nofollow" target="_blank" href="/+impresion?objetivo=Boucher&nocache=nocache&transaccion='.$f['transaccion'].'">Reimprimir voucher</a></td></tr>';

        if ($f['metodo_pago'] == 'kiosko_efectivo')
            $totalEfectivo  += $f['monto'];
        else
            $totalCredito  += $f['monto'];

    }
    echo '</table>';
    
    $cDetallesVendidos = 'SELECT codigo_kiosko_transaccion, `cantidad` AS nArticulos, IF(t1.descripcion="",t2.descripcion,t1.descripcion) AS "descripcion_articulo", `precio_grabado`, `transaccion`, COALESCE(`cantidad`*`precio_grabado`,0) AS total, metodo_pago FROM `flores_kiosko_transacciones` AS t1 LEFT JOIN `flores_kiosko_articulos` AS t2 USING(codigo_kiosko_articulo) WHERE `operacion` = "venta" AND codigo_kiosko_articulo <> 100 AND fecha BETWEEN "'.$fecha.' 00:00:00" AND "'.$fecha.' 23:59:59"';
    $r = db_consultar($cDetallesVendidos); 
    
    echo '<h2>Articulos vendidos</h2>';
    echo '<table class="tabla-estandar tabla-centrada zebra" style="width:100%;">';
    echo '<tr><th style="width:25px;"></th><th>Cantidad</th><th>Descripción</th><th>Metodo de pago</th><th>Precio Unitario</th><th>Monto</th><th>Acción</th><tr/>';
    while ($f = mysqli_fetch_assoc($r))
    {
        if ($f['metodo_pago'] == 'kiosko_efectivo')
            $totalEfectivo  += $f['total'];
        else
            $totalCredito  += $f['total'];
    

        $f['descripcion_articulo'] = $f['descripcion_articulo'] ? $f['descripcion_articulo'] : '-art. removido-';
        echo '<tr><td><img rel="'.$f['codigo_kiosko_transaccion'].'" class="eliminar_transaccion" src="/IMG/iconos/delete.jpeg" /></td><td>'.$f['nArticulos'].'</td><td>'.$f['descripcion_articulo'].'</td><td>'.$f['metodo_pago'].'<td>$'.number_format ($f['precio_grabado'],2,'.',',').'</td><td>$'.number_format ($f['total'],2,'.',',').'</td><td><a rel="nofollow" target="_blank" href="/+impresion?objetivo=Tiquete&nocache=nocache&transaccion='.$f['transaccion'].'">Reimprimir voucher</a></td></tr>';
    }
    echo '</table>';
    
    echo '<h2>Totales</h2><table style="width:100%;" >';
    echo sprintf('<tr><td style="text-align:right;">POS:</td><td style="width:100px;text-align:center;"><strong>$%s</strong></td></tr>',money_format('%!i',$totalCredito));
    echo sprintf('<tr><td style="text-align:right;">Efectivo:</td><td style="width:100px;text-align:center;"><strong>$%s</strong></td></tr>',money_format('%!i',$totalEfectivo));
    echo sprintf('<tr><th style="text-align:right;">Total:</th><th style="width:100px;text-align:center;"><strong>$%s</strong></th></tr>',money_format('%!i',$totalCredito+$totalEfectivo));
    echo '</table>';
?>
<h1>Mostrando los cortes z de <strong><?php echo $fecha; ?></strong></h1>
<ul>
    <?php if ($html) echo $html; else echo '<p>No hay cortes Z para este día</p>'; ?>
</ul>
<script type="text/javascript">
    $(function(){
        $.datepicker.regional["es"];
        $(".datepicker").datepicker({constrainInput: true, dateFormat : "yy-mm-dd", defaultDate: +0});
    
        $('.eliminar_transaccion').click(function(){
            objetivo = $(this);
            if (confirm('¿Desea eliminar esta transaccion #' + objetivo.attr('rel')))
            {
                $.post('/ajax',{pajax:'eliminar_transaccion',codigo_kiosko_transaccion:objetivo.attr('rel')}, function(data){
                    alert(data.resultado);
                }, 'json');
            }
        });
    });
</script>