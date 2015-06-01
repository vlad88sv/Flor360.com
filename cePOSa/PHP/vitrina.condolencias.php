<?php   
/********************** bCategoria***************************************/
$bCategoria= '';

// Obtengamos las categorias del producto!!!
$c = sprintf('SELECT b.codigo_menu, a.codigo_categoria, b.titulo, b.descripcion FROM %s AS a LEFT JOIN %s AS b ON a.codigo_categoria = b.codigo_categoria WHERE a.codigo_producto="%s" ORDER BY b.titulo ASC', db_prefijo.'productos_categoria', db_prefijo.'categorias',$contenedor['codigo_producto']);
$rCategoria = db_consultar($c);
$bCategoria.= '<div style="text-align:center;margin:5px;">';
while ($f = mysqli_fetch_assoc($rCategoria))
{
    switch ($f['codigo_menu'])
    {
        case '5':
            $modoHorizontal = true;
            $arrCSS[] = 'CSS/estilo.formal';
            $arrCSS[] = 'CSS/estilo.horizontal';
            break;
        
        case '6':
            $arrCSS[] = 'CSS/estilo.alegre';
            break;
        
        case '7':
            $modoHorizontal = false;
            $arrCSS[] = 'CSS/estilo.formal';
            break;
    }
    $bCategoria.= '<span class="etiqueta-categoria">'.$f['titulo'].SI_ADMIN(' <form style="display:inline;" action="'.PROY_URL_ACTUAL.'" method="POST">'.ui_input('codigo_categoria',$f['codigo_categoria'],'hidden').ui_input('btn_eliminar_categoria','x','submit','btnlnk').'</form>').'</span> ';
}
$bCategoria.= '</div>';
//$bCategoria.= SI_ADMIN(BR.flores_db_ui_obtener_categorias_cmb('cmb_agregar_categoria',$contenedor['codigo_producto']).ui_input('btn_agregar_categoria','Agregar','submit'));
$bCategoria.= SI_ADMIN(BR.'<form action="'.PROY_URL_ACTUAL.'" method="POST">'.flores_db_ui_obtener_categorias_chkbox('chk_agregar_categoria',$contenedor['codigo_producto']).ui_input('btn_agregar_categoria_v2','Agregar','submit','btnlnk').'</form>');


/*************** variedades ********************************************/
// Luego obtenemos toda la información de sus variedades
$c = sprintf('SELECT `codigo_variedad`, `codigo_producto`, `foto`, `descripcion`, `precio`, `precio_oferta`, `deshabilitado` FROM `%s` WHERE codigo_producto="%s" ORDER BY precio DESC, descripcion ASC',db_prefijo.'producto_variedad',$contenedor['codigo_producto']);
$variedad = db_consultar($c);

$precargar_img = array();

$VARIEDADES_ADMIN = '<h2>Administración de variedades</h2>';
$VARIEDADES = '';
$VARIEDADES .= '<table style="width:100%;border-collapse:collapse;" id="contenedor_variedades">';
$PRECIO = 0;
for ($i=0; $i<mysqli_num_rows($variedad); $i++) {
    $f = mysqli_fetch_assoc($variedad);
    if ($f['deshabilitado'] == 0)
    {
    $VARIEDADES .= '<input type="hidden" name="variedad" value="'.$f['codigo_variedad'].'" />';
    $VARIEDADES .= '<input type="hidden" id="txt_usuario_notas" name="txt_usuario_notas" value="Color seleccionado: blanco." />';
    
    if ($modoHorizontal) {
        if (isset($_GET['fb']))
        {
            $IMG = imagen_URL($f['foto'],500,333,'img0.');
            $class = 'fb-horizontal';
        } else {
            $IMG = imagen_URL($f['foto'],600,400,'img0.');
            $class = 'horizontal';
        }
    } else {
        $IMG = imagen_URL($f['foto'],400,600,'img0.');
        $class = 'vertical';
    }

    if (empty($flag_selected) || (!empty($_GET['variedad']) && $_GET['variedad'] == $f['codigo_variedad']) )
    {
        $HEAD_ogimage = imagen_URL($f['foto'],130,110,'img0.');
        $IMG_CONTENEDOR = '<img alt="Imagen del producto" id="imagen_contenedor" class="'.$class.'" style="" src="'.$IMG.'" />';
    }
    
    $precargar_img[] = $IMG;
    
    $precio_etiqueta = ($f['precio_oferta'] > 0 ? '<span style="text-decoration:line-through;">$'.$f['precio'].'</span> - Oferta: <span style="color:red;">$' . $f['precio_oferta'] . '</span>' : '$'.$f['precio']);

    }
    $VARIEDADES_ADMIN .= '<form action="'.PROY_URL_ACTUAL.'" method="POST"><p style="white-space:nowrap;clear:both;display:block;"><span style="float:left">' . $f['descripcion'] .'</span> <span style="float:right">'. ui_input('codigo_variedad',$f['codigo_variedad'],'hidden').' '.ui_input('btn_editar_variedad','Editar','submit','btnlnk btnlnk-mini').ui_input('btn_eliminar_variedad','Eliminar','submit','btnlnk btnlnk-mini').ui_input('btn_clonar_foto_variedad','Clonar Foto','submit','btnlnk btnlnk-mini').ui_input('btn_clonar_receta_variedad','Clonar prep.','submit','btnlnk btnlnk-mini').'</span></p></form>';
    $flag_selected=true;
}
$VARIEDADES .= '</table>';
$VARIEDADES_ADMIN = '<div style="display:block;clear:both">'.
$VARIEDADES_ADMIN . '</div><form action="'.PROY_URL_ACTUAL.'" method="POST">'.BR . ui_input('btn_agregar_variedad','Agregar variedad', 'submit', 'btnlnk btnlnk-mini').'</form>';
    
/* Desplegar lo que conseguimos */
if( $contenedor['descontinuado'] == "si" )
    echo '<p class="error">Lo sentimos, este producto esta descontinuado y no se encuentra disponible.</p>';

// Tabla

echo '<div style="background-color:#e4e5e5;font-size:13px;padding:5px 300px;"><span style="float:left;">Nombre: <b style="color:grey;">'.$contenedor['titulo'].'</b></span>&nbsp;<span style="float:right;">Código: <b style="color:grey;">'.$contenedor['codigo_producto'].'</b></span></div>';

echo '<table style="table-layout:fixed;width:100%;">';
echo '<tr>';
echo '<td id="vitrina_imagen" class="'.$class.'">';

// Mostrar los datos del contenedor
if (!isset($IMG_CONTENEDOR))
    $IMG_CONTENEDOR = '<img src="IMG/stock/sin_imagen.jpg" title="Sin Imagen" />';
    
echo '<table style="width:100%;"></tr>';

$consulta = sprintf('SELECT codigo_producto, titulo, foto FROM '.db_prefijo.'producto_variedad LEFT JOIN '.db_prefijo.'producto_contenedor USING (codigo_producto) LEFT JOIN '.db_prefijo.'productos_categoria USING (codigo_producto) WHERE codigo_categoria IN (SELECT codigo_categoria FROM '.db_prefijo.'productos_categoria WHERE codigo_producto = '.$contenedor['codigo_producto'].') AND foto <> "" AND descontinuado="no" AND '.db_prefijo.'producto_variedad.codigo_producto <> %s AND precio BETWEEN (%s)*0.60 AND (%s)*1.40 GROUP BY '.db_prefijo.'producto_variedad.codigo_producto ORDER BY RAND() LIMIT 2',$contenedor['codigo_producto'],$PRECIO,$PRECIO);
$resultado = db_consultar($consulta);
$fsimilar = mysqli_fetch_assoc($resultado);
//echo '<td><a href="'.PROY_URL.URL_SUFIJO_VITRINA.SEO($fsimilar['titulo'].'-'.$fsimilar['codigo_producto']).'"><img src="'.PROY_URL_ESTATICA.'IMG/stock/flecha.izq.gif" /></a></td>';

echo '<td>';
//echo '<h1 style="font-family: helvetica;color:grey;background-color:white;font-size:16px;border:1px solid grey;">Nombre: <span style="font-style: italic;color:black;">'.$contenedor['titulo'], '</span> <span style="display:inline-block;float:right;">Código: <span style="color:black;">#', $contenedor['codigo_producto'], '</span></span></h1><hr />';
echo '<div style="text-align:center">'.$IMG_CONTENEDOR.'</div>';
echo '</td>';

$fsimilar = mysqli_fetch_assoc($resultado);
//echo '<td><a href="'.PROY_URL.URL_SUFIJO_VITRINA.SEO($fsimilar['titulo'].'-'.$fsimilar['codigo_producto']).'"><img src="'.PROY_URL_ESTATICA.'IMG/stock/flecha.derecha.gif" /></a></td>';
echo '</tr></table>';

echo '</td>';
echo '<td id="vitrina_info" style="vertical-align:top">';

$bInfoCompra  = $bInfoAdicional = '';

if( $contenedor['descontinuado'] == "no" || S_iniciado())
{
    echo SI_ADMIN($VARIEDADES_ADMIN);
        
    $bInfoCompra .= '<div style="margin:10px 0px;text-align:center;font-size:18px;font-weight:bold;font-family:Calibri, Arial, sans-serif;">';
    $bInfoCompra .= 'Precio: ' . $precio_etiqueta;
    $bInfoCompra .= '</div>';

    $bInfoCompra .= '<div style="background-color:#e4e5e5;font-size:12px;font-weight:bold;padding:5px">Seleccione el color</div>';
    $bInfoCompra .= '
    <div style="text-align:center;margin-top:5px;">
    <table style="margin:0 auto;border-collapse:collapse;padding:0;"><tr>
        <td style="vertical-align:bottom;padding:0px;"><a href="#" class="pestaña colorear '.(empty($_GET['color']) || @$_GET['color'] == 'blanco' ? 'pestana_sel' : '').'" style="background-color:#ababab;" rel="blanco">Blanco</a></td>
        <td style="vertical-align:bottom;padding:0px;"><a href="#" class="pestaña colorear '.(@$_GET['color'] == 'rosado' ? 'pestana_sel' : '').'" style="background-color:#eac1df;" rel="rosado">Rosado</a></td>
        <td style="vertical-align:bottom;padding:0px;"><a href="#" class="pestaña colorear '.(@$_GET['color'] == 'rojo' ? 'pestana_sel' : '').'" style="background-color:#781f25;" rel="rojo">Rojo</a></td>
        <td style="vertical-align:bottom;padding:0px;"><a href="#" class="pestaña colorear '.(@$_GET['color'] == 'amarillo' ? 'pestana_sel' : '').'" style="background-color:#dfce5a;" rel="amarillo">Amarillo</a></td>
        <td style="vertical-align:bottom;padding:0px;"><a href="#" class="pestaña colorear '.(@$_GET['color'] == 'naranja' ? 'pestana_sel' : '').'" style="background-color:#f3bf6b;" rel="naranja">Naranja</a></td>
    </tr></table>
    <div style="position:relative;height:234px;">
    <img style="position:absolute;left:0;top:0;" class="img_color" rel="vitrina" src="/IMG/condolencias/vitrina/'.(empty($_GET['color']) ? 'blanco' : @$_GET['color']).'.jpg" border="0" />
    <img style="position:absolute;left:0;top:0;z-index:50;" src="/IMG/condolencias/'.$contenedor['codigo_producto'].'/vitrina.overlay.png" border="0" />
    </div>
    ';
 

    $bInfoCompra .= '<p style="text-align:center;"><input type="submit" id="btn_comprar_ahora" name="btn_comprar_ahora" value="COMPRAR"></p>';
    $bInfoCompra .= '<div style="text-align:center;"><img style="margin:auto;" src="/IMG/stock/tarjetas.gif" /></div>';
    
    $bInfoCompra .= '
    <div style="color:black;font-weight:normal;font-size:12px;padding:3px;text-align:center;margin-top:15px;">
    Al presionar el botón "COMPRAR" se te mostrará un formulario en el que podrás especificar la fecha de entrega e ingresar todos los demas datos necesarios de tu pedido.<br />
    <br /><br />Podrás escoger los siguientes metodos de pago:<br /><br />
        <div style="margin:auto;width:400px;text-align:left;">
        <span class="li">Tarjeta de crédito o débito, nacionales o internacionales</span><br />
        <span class="li">Abono a nuestra cuenta en <i>Banco de America Central</i></span><br />
        <span class="li">Solicitar cobro a domicilio</span><br />
        <span class="li">Pagar en nuestra sucursal en CC. La Gran Vía</span>
        </div>
    </div>
    
    <div style="padding:3px;margin-bottom:5px !important;margin-top:30px;text-align:center;border:1px solid #DDD;">
    Los colores de las flores dependen de la disponibilidad. Si deseas un color de flores en especial ¡contáctanos!. En '.PROY_NOMBRE_CORTO.' tratamos de que nuestros arreglos sean exactamente iguales al de nuestras fotografías, sin embargo si al momento de tu compra no se encuentra alguno de los elementos de preparacion del arreglo, se le reemplazara por los mas similares disponibles que sean de igual o mejor calidad al exhibido.
    </div>
    ';
}
else
{
    $bInfoCompra .= '<h2>Producto descontinuado</h2>';
    $bInfoCompra .= '<p>La elaboracion de este producto ha sido descontinuada.</p>';
    $bInfoAdicional .= '';
}

echo '<form action="'.PROY_URL_SSL.'comprar-articulo.html" method="POST">';
echo $VARIEDADES;

if ($modoHorizontal || S_iniciado())
    echo ui_input('con_cantidad','si','hidden');
    
echo $bInfoCompra;

echo '</form>';
echo SI_ADMIN($bCategoria);
echo '</td></tr></table>';

echo $bInfoAdicional;
/* -------------------------------------------+-------------------------------------------*/    
/* Nuevo contador de visitas JS asincrono (para aprovechar el cache de la página)  */
?>
<script type="text/javascript">
function preload(arrayOfImages) {
   $(arrayOfImages).each(function () {
       $('<img />').attr('src',this).appendTo('body').css('display','none');
   });
}
preload(["<?php echo join('","',$precargar_img); ?>"]);

$(function(){
    if ($.cookie("vista", { expires: 1, path:  window.location.pathname }) == null)
        $.post('ajax',{pajax: "arreglo_visto", codigo_producto: "<?php echo $contenedor['codigo_producto']; ?>"});
    $.cookie("vista", "1", { expires: 1, path:  window.location.pathname });
    
    $(".variedad").click(function(){
        $("#imagen_contenedor").attr("src",$(this).attr("src"));
        $('.variedades').css('background-color','white');
        $('.variedades .variedad:checked').parents('.variedades').css('background-color','#d5d5d5');
    });
    
    $(".variedad:checked").click();
    
    $('.colorear').click(function (event){
        event.preventDefault();
        var color = $(this).attr('rel');
        $('.colorear').removeClass('pestana_sel');
        $(this).addClass('pestana_sel');
        $('#txt_usuario_notas').val('Color seleccionado: ' + color + '.');
        $.each($('.img_color'), function(index, objeto){
            var src = '/IMG/condolencias/' + $(objeto).attr('rel') + '/' + color + '.jpg';
            $(objeto).attr('src',src);
        });
    });
    
    $('.pestana_sel').click();
});


comprar_estado_titulo = false;

function cambiarTituloBtnComprar()
{
    if (comprar_estado_titulo)
        $("#btn_comprar_ahora").val('COMPRAR');
    else
        $("#btn_comprar_ahora").val('CLIC AQUI');
    
    comprar_estado_titulo = !comprar_estado_titulo;
}

window.setInterval('cambiarTituloBtnComprar()',1000);
</script>