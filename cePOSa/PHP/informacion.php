<h1>Conocimiento general</h1>

<table class="tabla-estandar">
<tr>
<td style="width: 200px;">Dirección del Kiosko (para copiar y pegar):</td>
<td><input style="width:98%;" value="Kiosko KGV26, Paso Peatonal de CC. La Gran Vía, Antiguo Cuscatlán, La Libertad" /></td>
</tr>
</table>

<table class="tabla-estandar" style="table-layout:fixed;">
<tr>
<th>Conocimiento general</th>
<th>Chats hoy</th>

</tr>
<tr>
<td style="vertical-align:top">
<h2>Productos y adicionales especiales</h2>
<ul class="sin_margen">
    <li>Botella adicional de vino tinto o blanco: $15.00</li>
    <li>Cambio de botella Sunset por vino tinto/blanco: $10.00 adicionales.</li>
    <li>Rosa impresa "Speaking roses" con mensaje predefinido, unidad: $10.00</li>
    <li>Ramo de 12 rosas, 1 color: $25.00</li>
    <li>Ramo de 12 rosas azules, 1 color: $39.00</li>
    <li>Ramo de 18 rosas azules, 1 color: $50.00</li>
    <li>Bouquet de 24 rosas, 1 color: $58.00</li>
    <li>Rosas azules - mínimo: docena - <b color="red">cambio</b> por docena: $15.00</li>
    <li>Rosas azules - mínimo: docena - <b color="blue">adicional</b> por docena: $30.00</li>
</ul>
</td>
<td>
<h2>Zonas y precios de envio</h2>
<ul class="sin_margen">
<?php
foreach($destinos as $destino => $precio)
{
    echo '<li>'.$destino.'</li>';
}
?>
</ul>
</td>
</tr>
</table>
<?php
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
        $bufferOp[$iextra] .= $detalle['nombre_extra'].' $'.$detalle['precio'].'<br />';
    }    
    $iextra++;
}

echo '<table class="tabla-estandar" id="extras">';
echo '<tr class="extra_img"><td>'.join('</td><td>',$bufferImg).'</td></tr>';
echo '<tr class="extra_op"><td>'.join('</td><td>',$bufferOp).'</td></tr>';
echo '</table>';
?>