<?php
protegerme(false,array(_N_vendedor));
require_once(__BASE_cePOSa__.'PHP/ssl.comun.php');

$ventas = array();
$total = 0;
$totalCredito = 0;
$totalEfectivo = 0;

$fecha = (isset($_GET['fecha']) ? $_GET['fecha'] : mysql_date());

if (isset($_POST['imprimir']))
{
    ob_start();
    echo '<form id="cortar" style="width:200px;text-align:center;font-size:14px;">';
} else {
    echo '<form id="cortar" target="_blank" action="'.PROY_URL_ACTUAL.'" method="post" style="width:200px;text-align:center;font-size:14px;margin:auto;">';
}
?>
<style>
#corte table {border-collapse:collapse;table-layout:auto;width:200px;}
#corte table th {padding: 1px 0;}
#corte table td {border-top:1px solid grey;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;padding:2px 0;font-size:12px;}
</style>

<script type="text/javascript">
    function calcularTotalFisco()
    {
        var sum = 0;
        $(".total_fisico").each(function() {
            if(!isNaN(this.value) && this.value.length!=0) {
                sum += parseFloat(this.value);
            }
        });
        $("#total_manual").html(sum.toFixed(2));
    }
    $(function(){
        $(".total_fisico").change(calcularTotalFisco);
        calcularTotalFisco();
    });
</script>
<?php
echo '<div id="corte" style="width:200px;text-align:center;font-size:15px;">';
echo '<div style="width:100%;font-weight:bold;font-size:18px;">'.PROY_NOMBRE_CORTO.'</div>';
echo '<div style="width:100%">Tel. '.PROY_TELEFONO_PRINCIPAL.'</div>';
echo '<div style="width:100%">'.PROY_URL_AMIGABLE.'</div>';
echo '<div style="width:100%">Corte al '.date('Y-m-d H:i:s').'</div>';
echo '<div style="width:100%;margin-bottom:2em;">Para día '.$fecha.'</div>';

// Detallemos lo que se vendió
$cArreglosVendidos = 'SELECT codigo_compra, fkt.metodo_pago, codigo_compra, flores_producto_variedad.descripcion, (flores_SSL_compra_contenedor.cantidad*flores_SSL_compra_contenedor.precio_grabado)+flores_SSL_compra_contenedor.cargo_adicional+flores_SSL_compra_contenedor.precio_envio AS monto FROM `flores_SSL_compra_contenedor` LEFT JOIN `flores_producto_variedad` USING(`codigo_variedad`) LEFT JOIN `flores_kiosko_transacciones` AS fkt ON flores_SSL_compra_contenedor.codigo_compra = fkt.transaccion WHERE flag_arreglo=1 AND fkt.`fecha` BETWEEN "'.$fecha.' 00:00:00" AND "'.$fecha.' 23:59:59"';
$r = db_consultar($cArreglosVendidos);   

echo '<h2>Arreglos vendidos</h2>';
echo '<table class="tabla-centrada zebra" style="width:100%;">';
echo '<tr><th>#</th><th>Desc.</th><th>Precio</th></tr>';
while ($f = mysqli_fetch_assoc($r))
{
    list($f['extras'], $f['extrasPrecio']) = SSL_COMPRA_OBTENER_EXTRAS($f['codigo_compra']);
    $f['monto'] += $f['extrasPrecio'];

    echo '<tr><td>'.$f['codigo_compra'].'</td><td>'.$f['descripcion'].'</td><td>$'.$f['monto'].'</td></tr>';

    if ($f['metodo_pago'] == 'kiosko_efectivo')
        $totalEfectivo  += $f['monto'];
    else
        $totalCredito  += $f['monto'];

}
echo '</table>';

$cDetallesVendidos = 'SELECT codigo_kiosko_transaccion, SUM(`cantidad`) AS nArticulos, IF(t1.descripcion="",t2.descripcion,t1.descripcion) AS "descripcion_articulo", `precio_grabado`, COALESCE(SUM(`cantidad`*`precio_grabado`),0) AS total, metodo_pago FROM `flores_kiosko_transacciones` AS t1 LEFT JOIN `flores_kiosko_articulos` AS t2 USING(codigo_kiosko_articulo) WHERE `operacion` = "venta" AND codigo_kiosko_articulo <> 100 AND fecha BETWEEN "'.$fecha.' 00:00:00" AND "'.$fecha.' 23:59:59" GROUP BY codigo_kiosko_transaccion';
$r = db_consultar($cDetallesVendidos); 

echo '<h2>Articulos vendidos</h2>';
echo '<table class="tabla-centrada zebra" style="width:100%;">';
echo '<tr><th></th><th>Desc.</th><th>C/U</th><th>Total</th><tr/>';
while ($f = mysqli_fetch_assoc($r))
{
    if ($f['metodo_pago'] == 'kiosko_efectivo')
        $totalEfectivo  += $f['total'];
    else
        $totalCredito  += $f['total'];


    $f['descripcion_articulo'] = $f['descripcion_articulo'] ? $f['descripcion_articulo'] : '-art. removido-';
    echo '<tr><td>'.$f['nArticulos'].'</td><td style="width:100px;">'.$f['descripcion_articulo'].'</td><td>$'.number_format ($f['precio_grabado'],2,'.',',').'</td><td>$'.number_format ($f['total'],2,'.',',').'</td></tr>';
}
echo '</table>';

echo '<h2>Totales</h2><table style="width:100%;border-collapse:collapse;" >';
echo sprintf('<tr><td style="text-align:right;">POS:</td><td style="width:100px;text-align:center;"><strong>$%s</strong></td></tr>',money_format('%!i',$totalCredito));
echo sprintf('<tr><td style="text-align:right;">Efectivo:</td><td style="width:100px;text-align:center;"><strong>$%s</strong></td></td></tr>',money_format('%!i',$totalEfectivo));
echo sprintf('<tr><th style="text-align:right;">Total:</th><th style="width:100px;text-align:center;"><strong>$%s</strong></th></tr>',money_format('%!i',$totalCredito+$totalEfectivo));
echo '</table>';

echo '<br /><h2>Totales fisicos</h2><table style="width:100%;border-collapse:collapse;">';
echo '<tr><td style="text-align:right;">POS:</td><td>$<input style="width:80px;" class="total_fisico" name="valor_pos_ingresado" type="text" value="'.(@$_POST['valor_pos_ingresado'] ?: '0.00').'" /></td></tr>';
echo '<tr><td style="text-align:right;">Efectivo:</td><td>$<input style="width:80px;" class="total_fisico" name="valor_efectivo_ingresado" type="text" value="'.(@$_POST['valor_efectivo_ingresado'] ?: '0.00').'" /></td></tr>';
echo '<tr><th style="text-align:right;">Total:</th><th style="width:100px;text-align:center;">$<strong id="total_manual">0.00</strong></th></tr>';
echo '</table>';


echo '<p>';
echo '<br /><br />_______________________<br />';
echo _F_usuario_cache('nombre_completo');
echo '</p>';

if (isset($_POST['imprimir']))
{
    $html = ob_get_clean();
    
    unset($datos);
    $datos['valor_efectivo'] = $totalEfectivo;
    $datos['valor_pos'] = $totalCredito;
    $datos['valor_efectivo_ingresado'] = (double) @$_POST['valor_efectivo_ingresado'];
    $datos['valor_pos_ingresado'] = (double) @$_POST['valor_pos_ingresado'];
    $datos['codigo_usuario'] = _F_usuario_cache('codigo_usuario');
    $datos['html'] = $html;
    
    $id = db_agregar_datos(db_prefijo.'cortez',$datos);
    unset($datos);
    
    echo '<p>Un momento... redirigiendo a impresión de CorteZ</p>';
    echo '<script>window.location.href="'.PROY_URL.'+impresion?objetivo=CorteZ&transaccion='.$id.'";</script>';
    return;
} else {
    echo '<hr />';
    echo '<input type="submit" name="imprimir" value="Imprimir corte Z" />';
}
echo '</form>';
?>