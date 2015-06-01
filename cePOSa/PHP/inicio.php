<?php
if (!session_id())
    session_start();

if (isset($_POST['iniciar_proceder']))
{
    ob_start();
    $ret = _F_usuario_acceder($_POST['iniciar_campo_correo'],$_POST['iniciar_campo_clave']);
    $buffer = ob_get_clean();
    if ($ret != 1)
    {
        echo mensaje ("Datos de acceso erroneos, por favor intente de nuevo",_M_ERROR);
        echo mensaje ($buffer,_M_INFO);
    }
}

if (S_iniciado())
{
    db_agregar_datos('latidos',array('ID_usuario' => _F_usuario_cache('codigo_usuario'), 'fechatiempo' => mysql_datetime(), 'categoria' => 'inicio'));
    
    if (!empty($_POST['iniciar_retornar']))
    {
        header("location: ".$_POST['iniciar_retornar']);
    }
    else
    {
        header("location: ./");
    }
    return;
}

$HEAD_titulo = PROY_NOMBRE . ' - Iniciar sesion';

if (isset($_GET['ref']))
    $_POST['iniciar_retornar'] = $_GET['ref'];

$retorno = empty($_POST['iniciar_retornar']) ? PROY_URL : $_POST['iniciar_retornar'];
echo '<h1>Inicio de sesión</h1>';
echo '<form action="/iniciar" method="POST">';
echo ui_input("iniciar_retornar", $retorno, "hidden");
echo "<table>";
echo ui_tr(ui_td("Usuario") . ui_td(ui_input("iniciar_campo_correo")));
echo ui_tr(ui_td("Clave") . ui_td(ui_input("iniciar_campo_clave","","password")));
echo "</table>";
echo ui_input("iniciar_proceder", "Iniciar sesión", "submit")."<br />";
echo "</form>";
?>
