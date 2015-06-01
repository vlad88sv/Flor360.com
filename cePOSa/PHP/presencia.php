<?php
    ACTIVAR_PAQUETES(array('ui'));
?>
<h1>Registro de presencia de usuarios en el sistema</h1>
<form style="padding:20px;border:1px dashed #CCC;" method="get" action="">
<label for="codigo_usuario">Usuario: </label> <select name="codigo_usuario" id="codigo_usuario"><?php echo db_ui_opciones('codigo_usuario','CONCAT(codigo_usuario, ". ", correo, " - ", nombre_completo)','flores_usuarios'); ?></select>&nbsp;
<label for="Dia">Día: </label> <input id="dia" name="dia" type="text" />&nbsp;
<input type="submit" value="Filtrar" />
</form>
<?php
$WHERE = '';
if (!empty($_GET['codigo_usuario']))
{
    $WHERE .= ' AND l.ID_usuario="'.db_codex($_GET['codigo_usuario']).'" ';
}

if (!empty($_GET['dia']))
{
    $WHERE .= ' AND DATE(l.fechatiempo)="'.db_codex($_GET['dia']).'" ';
}

$c = 'SELECT nombre_completo AS "Nombre", DATE(`fechatiempo`) AS "Fecha", DAYNAME(fechatiempo) AS "Día", TIME(MIN(`fechatiempo`)) AS "Entrada", TIME(MAX(`fechatiempo`)) AS "Salida" FROM `latidos` AS l LEFT JOIN `flores_usuarios` AS fu ON (ID_usuario = codigo_usuario) WHERE 1 '.$WHERE.' GROUP BY DATE(fechatiempo) ORDER BY fechatiempo DESC';
$r = db_consultar($c);
echo '<h1>Reporte administrativo de asistencia diaria</h1>';
echo db_ui_tabla($r, 'class="zebra"');


$c = 'SELECT `fechatiempo` AS "Fecha", `nombre_completo` AS "Usuario", `categoria` AS "Razón" FROM `latidos` AS l LEFT JOIN `flores_usuarios` AS fu ON (ID_usuario = codigo_usuario) WHERE 1 '.$WHERE.' ORDER BY ID_latido DESC LIMIT 100';
$r = db_consultar($c);
echo '<h1>Reporte detallado de las últimas 100 presencias</h1>';
echo db_ui_tabla($r, 'class="zebra"');
?>
<script type="text/javascript">
    $(function(){
        $.datepicker.regional["es"];
        $("#dia").datepicker({constrainInput: true, dateFormat : "yy-mm-dd", defaultDate: +0});
    });
</script>