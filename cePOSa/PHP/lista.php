<?php
$c = 'SELECT `codigo_compra`, `orden`, `direccion_entrega`, `notas2` FROM `flores_SSL_compra_contenedor` WHERE fecha_entrega="2014-05-10" ORDER BY orden DESC';
$r = db_consultar($c);

echo '<table class="tabla-estandar zebra borde-abajo">';
echo '<tr><th>Ref</th><th>Orden</th><th>Dirección de entrega</th><th>Notas</th></tr>';
while ($r && $f = db_fetch($r))
{
    $notas = '<input type="text" id="txt_'.$f['codigo_compra'].'" class="notas" value="'.$f['notas2'].'" /><button rel="'.$f['codigo_compra'].'" class="guardar">Guardar</button>';
    echo sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', $f['codigo_compra'], $f['orden'], $f['direccion_entrega'], $notas);
}
echo '</table>';
?>
<script type="text/javascript">
    $(function(){
        $('.guardar').click(function(){
            $.post('ajax',{pajax:'guardar_notas_lista', nota: $('#txt_'+$(this).attr('rel')).val(), codigo_compra: $(this).attr('rel')}, function(){
                alert('Guardada');
            });
        });
    });
</script>