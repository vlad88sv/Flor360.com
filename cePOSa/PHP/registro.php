<?php
$c = 'SELECT `tarjeta_de`, `tarjeta_para`, `nombre_completo`, `grupo`, `codigo_compra`, `salt`, `accion`, DATE_FORMAT(`timestamp`,"%a %e %H:%i") Fecha, valor_anterior FROM `flores_registro` LEFT JOIN `flores_usuarios` USING (codigo_usuario) LEFT JOIN `flores_SSL_compra_contenedor` USING(codigo_compra) WHERE codigo_compra="'.$_GET['cc'].'" ORDER BY timestamp DESC';
$r = db_consultar($c);

if (mysqli_num_rows($r) > 0)
{
    $tabla = '';
    $tabla .= '<table class="tabla-estandar zebra" style="width:900px;">';
    $tabla .= '<tbody>';
    while ($ft = mysqli_fetch_assoc($r))
    {
        $tabla .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',$ft['nombre_completo'], $ft['grupo'], $ft['Fecha'], $ft['accion'], strip_tags ($ft['valor_anterior'],'<br><p>'));
    }
    $tabla .= '</tbody>';
    $tabla .= '<thead><tr><th>Por</th><th>Grupo</th><th>Fecha</th><th>Acción</th><th>Valor anterior</th></tr></thead>';
    $tabla .= '</table>';
    echo $tabla;
} else {
    echo 'No hay registros';
}
?>