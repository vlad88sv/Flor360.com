<?php
protegerme(false,array(_N_vendedor));
ACTIVAR_PAQUETES(array('ui'));

echo '<form action="" method="get">Mes: '.ui_combobox('mes',ui_combobox_o_meses()).' Año: '.ui_combobox('ano',ui_combobox_o_anios()).' Metodo de pago:&nbsp;
<select name="metodo_pago">
<option value="efectivoYcredito">kiosko_efectivo,kiosko_credito</option>
<option value="kiosko_credito">kiosko_credito</option>
<option value="kiosko_efectivo">kiosko_efectivo</option>
<option value="domicilio">domicilio</option>
<option value="tarjeta">tarjeta</option>
<option value="abono">abono</option>
<option value="diferido">diferido</option>
<input type="submit" value="Mostrar" /></select></form>';

if (!empty($_GET['mes']) && !empty($_GET['ano']))
{
    $c = 'SELECT DATE( `fecha` ) AS "Fecha" , t1.metodo_pago, FORMAT(SUM( cantidad * precio_grabado ),2) AS "total", t2.notas AS nota_abono,  IF(t2.dia IS NULL,"",CONCAT("verificó: ", t3.nombre_completo, " fecha: ", t2.fecha_confirmado)) AS abonado FROM `flores_kiosko_transacciones` AS t1 LEFT JOIN `kiosko_abonos` AS t2 ON DATE(t1.`fecha`) = t2.`dia` AND t1.metodo_pago = t2.metodo_pago LEFT JOIN flores_usuarios AS t3 ON t2.codigo_usuario = t3.codigo_usuario WHERE operacion="venta" AND t1.metodo_pago IN ("'.($_GET['metodo_pago'] == "efectivoYcredito" ? 'kiosko_efectivo","kiosko_credito' : db_codex($_GET['metodo_pago'])).'") AND MONTH( `fecha` ) = "'.db_codex($_GET['mes']).'" AND YEAR( `fecha` ) = "'.db_codex($_GET['ano']).'" GROUP BY DATE( `fecha` ) , metodo_pago';

    $r = db_consultar($c);
    
    echo '<h1>En tabla de Kiosko (contando lo que se cobro ese día)</h1>';
        
    echo '<table class="zebra">';
    echo '<tr><th>Fecha</th><th>Método</th><th>Total</th><th>Abono</th></tr>';
    while ($r && $f = mysqli_fetch_assoc($r))
    {
        $cheque = '<input id="nota_'.$f['Fecha'].'" type="text" class="notas_abono" value="'.$f['nota_abono'].'" /><button class="realizar_abono" metodo_pago="'.$f['metodo_pago'].'" fecha="'.$f['Fecha'].'">Abono verificado</button> ' . $f['abonado'] ;
        echo sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',$f['Fecha'],$f['metodo_pago'],$f['total'],$cheque);
    }
    echo '</table>';

    $c = 'SELECT DATE( `fecha` ) AS "Fecha", COUNT(cantidad) AS "Cantidad", FORMAT(SUM(COALESCE((cantidad*precio_grabado)+precio_envio+cargo_adicional,0) + (SELECT COALESCE(SUM(precio),0) FROM `extras_compras` AS ec LEFT JOIN `extras` USING(codigo_extra) WHERE ec.codigo_compra = cc.codigo_compra )),2) AS monto FROM `flores_SSL_compra_contenedor` AS cc WHERE flag_cobrado=1 AND flag_eliminado=0 AND metodo_pago IN ("'.($_GET['metodo_pago'] == "efectivoYcredito" ? 'kiosko_efectivo","kiosko_credito' : db_codex($_GET['metodo_pago'])).'") AND MONTH( `fecha` ) = "'.db_codex($_GET['mes']).'" AND YEAR( `fecha` ) = "'.db_codex($_GET['ano']).'" GROUP BY DATE( `fecha` ) , metodo_pago';

    $r = db_consultar($c);
    
    echo '<h1>En tabla general (monto reflejado en el dia de la compra)</h1>';
    echo db_ui_tabla($r, 'class="zebra"');

}
?>
<script type="text/javascript">
    $(function(){
        $('.realizar_abono').click(function(){
            var txt_nota = $('#nota_' + $(this).attr('fecha')).val();
            $.post('ajax',{pajax:'realizar_abono', notas: txt_nota, fecha: $(this).attr('fecha'), metodo_pago: $(this).attr('metodo_pago')});
        });
    });
</script>