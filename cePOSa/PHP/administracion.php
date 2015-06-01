<?php
ADMINISTRACION_PROCESAR_PORCENTAJE_PRECIOS();
?>
<h1>Opciones globales de administracion</h1>
<p>En esta seccion se encuentran opciones generales de adminsitracion y modificacion de datos del sistema.</p>
<p>
    [ <a href="http://flor360.zapto.org:81/SS/<?php echo mysql_date(); ?>/">VER CAPTURAS DE PANTALLA DE ESTE DÍA</a> ] [ <a href="http://flor360.zapto.org:81/ss.php?inicio=0&fecha=<?php echo mysql_date(); ?>/">VER GALERIA DE CAPTURAS DE PANTALLA DE ESTE DÍA</a> ] [ <a href="http://flor360.zapto.org:81/logkeys.php?fecha=<?php echo mysql_date(); ?>">VER CAPTURAS DE TECLADO DE ESTE DÍA</a> ] [ <a href="http://flor360.zapto.org:8000/rift.oga">ESCUCHAR MICROFONO</a> ]
</p>

<?php
/* Opcion para modificacion global de precios */
echo '<hr />
<form action="'.PROY_URL_ACTUAL.'" method="POST" >
<p>Modificar <strong>todos los precios</strong> en base al siguiente porcentaje '.ui_input('txt_porcentaje_precio').'</p>
<p>Ejemplo: ingresar 100 significa mantener los precios, ingresar 125 significa incrementar los precios en 25%, ingresar 50 seria poner todo a mitad de precio.</p><p>¿<strong style="color:red;">Confuso?</strong>, la siguiente formula es la que se ejecutara: <code>precio=ceil((precio*(<strong>valor_ingresado</strong>/100)))</code></p>
<p style="color:#F00"><strong>¡Advertencia!</strong> esta operación no puede ser revertida!</p>'.
ui_input('btn_porcentaje_precio','Realizar cambio','submit','btnlnk').
'</form>';

function ADMINISTRACION_PROCESAR_PORCENTAJE_PRECIOS()
{
    if (!(isset($_POST['btn_porcentaje_precio']) && isset($_POST['txt_porcentaje_precio']) && is_numeric($_POST['txt_porcentaje_precio'])))
        return;

    $c = sprintf('UPDATE %s SET precio=ceil((precio*(%s/100))), precio_oferta=ceil((precio_oferta*(%s/100)))',db_prefijo.'producto_variedad',$_POST['txt_porcentaje_precio'],$_POST['txt_porcentaje_precio']);
    db_consultar($c);    
}
?>
