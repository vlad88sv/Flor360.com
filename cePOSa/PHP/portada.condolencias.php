<?php
$arrJS[] = 'jquery.maphilight';

$codigos = array(1246, 1247, 1248, 1249, 1250, 1251, 1252, 1253, 1254, 1255, 1256);
$c = 'SELECT fpc.titulo AS "contenedor_titulo", codigo_producto, precio FROM flores_producto_contenedor AS fpc LEFT JOIN flores_producto_variedad USING (codigo_producto) WHERE codigo_producto IN ('.join(',', $codigos).') GROUP BY fpc.codigo_producto';
$r = db_consultar($c);

while ($f = mysqli_fetch_assoc($r))
{
    $arreglos[$f['codigo_producto']] = array( 'enlace' => URL_SUFIJO_VITRINA.SEO(strip_tags($f['contenedor_titulo']).'-'.$f['codigo_producto']) , 'titulo' => $f['contenedor_titulo'], 'precio' => $f['precio']);
}
?>
<style>
.qtipcondolencias
{
    /*width: 300px;*/
}

.qtipcondolencias .ui-tooltip-titlebar, .qtipcondolencias .ui-tooltip-content
{
    background-color: transparent;
    border: none;
}

.ver_producto
{
    width:90px;color:white;background-color:#FF8296;font-size:9px;padding:1px;font-weight:bold;
}

.qtipcontenedor
{
    border:1px solid pink;position:relative;background-color:white;margin:3px 0 0 0px;padding:4px 4px 4px 25px;
}

.qtipimg
{
    position:absolute;left:-10px;top:-10px;
}

a.enlace_portada
{
    color: #012e83;
    font-style: normal;
    text-decoration: underline;
    font-size: 1.2em;
    margin: 0 10px;
}

#portada_condolencias
{
    margin: auto;
    width:932px;
    border-collapse:collapse;
}
#portada_condolencias td
{
    padding: 0px;
    margin:0px;
    border:0px;
}
</style>
<table id="portada_condolencias">
    <tr>
        <td>
            <img src="/IMG/stock/condolencias.seleccione_color.gif" />
        </td>
        <td>
            <a href="#" class="pestaña colorear" style="background-color:#ababab;" rel="blanco">Blanco</a>
            <a href="#" class="pestaña colorear" style="background-color:#eac1df;" rel="rosado">Rosado</a>
            <a href="#" class="pestaña colorear" style="background-color:#781f25;" rel="rojo">Rojo</a>
            <a href="#" class="pestaña colorear" style="background-color:#dfce5a;" rel="amarillo">Amarillo</a>
            <a href="#" class="pestaña colorear" style="background-color:#f3bf6b;" rel="naranja">Naranja</a>
        </td>
        <td style="text-align: right;">
            <a href="/ayuda?tema=como_comprar" class="enlace_portada">Como comprar</a>
            <a href="/ayuda?tema=PF" class="enlace_portada">Preguntas frecuentes</a>
        </td>
    </tr>
</table>

<map id="map" name="map">
<area shape="circle" class="arriba" coords="64,132,61"  title="<div class='qtipcontenedor'><img class='qtipimg' src='/IMG/iconos/lupa.png' /> <?php echo $arreglos['1246']['titulo'] . ' - $' . $arreglos['1246']['precio']; ?></div><div class='ver_producto'>VER PRODUCTO &gt;</div>" href="<?php echo $arreglos['1246']['enlace']; ?>" />
<area shape="circle" class="arriba" coords="270,131,85" title="<div class='qtipcontenedor'><img class='qtipimg' src='/IMG/iconos/lupa.png' /> <?php echo $arreglos['1247']['titulo'] . ' - $' . $arreglos['1247']['precio']; ?></div><div class='ver_producto'>VER PRODUCTO &gt;</div>" href="<?php echo $arreglos['1247']['enlace']; ?>" />
<area shape="circle" class="arriba" coords="661,88,71" title="<div class='qtipcontenedor'><img class='qtipimg' src='/IMG/iconos/lupa.png' /> <?php echo $arreglos['1251']['titulo'] . ' - $' . $arreglos['1251']['precio']; ?></div><div class='ver_producto'>VER PRODUCTO &gt;</div>" href="<?php echo $arreglos['1251']['enlace']; ?>" />
<area shape="circle" class="abajo" coords="189,349,69" title="<div class='qtipcontenedor'><img class='qtipimg' src='/IMG/iconos/lupa.png' /> <?php echo $arreglos['1249']['titulo'] . ' - $' . $arreglos['1249']['precio']; ?></div><div class='ver_producto'>VER PRODUCTO &gt;</div>" href="<?php echo $arreglos['1249']['enlace']; ?>" />
<area shape="circle" class="abajo" coords="659,344,83" title="<div class='qtipcontenedor'><img class='qtipimg' src='/IMG/iconos/lupa.png' /> <?php echo $arreglos['1255']['titulo'] . ' - $' . $arreglos['1255']['precio']; ?></div><div class='ver_producto'>VER PRODUCTO &gt;</div>" href="<?php echo $arreglos['1255']['enlace']; ?>" />
<area shape="circle" class="arriba" coords="753,236,47" title="<div class='qtipcontenedor'><img class='qtipimg' src='/IMG/iconos/lupa.png' /> <?php echo $arreglos['1254']['titulo'] . ' - $' . $arreglos['1254']['precio']; ?></div><div class='ver_producto'>VER PRODUCTO &gt;</div>" href="<?php echo $arreglos['1254']['enlace']; ?>" />
<area shape="rect" class="arriba" coords="362,9,477,161" title="<div class='qtipcontenedor'><img class='qtipimg' src='/IMG/iconos/lupa.png' /> <?php echo $arreglos['1250']['titulo'] . ' - $' . $arreglos['1250']['precio']; ?></div><div class='ver_producto'>VER PRODUCTO &gt;</div>" href="<?php echo $arreglos['1250']['enlace']; ?>" />
<area shape="rect" class="arriba" coords="446,99,594,205" title="<div class='qtipcontenedor'><img class='qtipimg' src='/IMG/iconos/lupa.png' /> <?php echo $arreglos['1253']['titulo'] . ' - $' . $arreglos['1253']['precio']; ?></div><div class='ver_producto'>VER PRODUCTO &gt;</div>" href="<?php echo $arreglos['1253']['enlace']; ?>" />
<area shape="rect" class="arriba" coords="803,30,895,266" title="<div class='qtipcontenedor'><img class='qtipimg' src='/IMG/iconos/lupa.png' /> <?php echo $arreglos['1252']['titulo'] . ' - $' . $arreglos['1252']['precio']; ?></div><div class='ver_producto'>VER PRODUCTO &gt;</div>" href="<?php echo $arreglos['1252']['enlace']; ?>" />
<area shape="circle" class="arriba" coords="127,225,55" title="<div class='qtipcontenedor'><img class='qtipimg' src='/IMG/iconos/lupa.png' /> <?php echo $arreglos['1248']['titulo'] . ' - $' . $arreglos['1248']['precio']; ?></div><div class='ver_producto'>VER PRODUCTO &gt;</div>" href="<?php echo $arreglos['1248']['enlace']; ?>" />
</map>


<div style="border-width:7px 7px 0px; border-color:#dbdad8; border-style: solid;border-radius:8px;height:418px;width:932px;margin:0 auto;">
    <img class="img_color" rel="principal" src="/IMG/condolencias/principal/blanco.jpg" usemap="#map" border="0" />
</div>
<div style="background-color: #dbdad8; border-bottom: #dbdad8 7px solid;border-radius:0 0 8px 8px;margin:-8px auto 0px;text-align:center;z-index:10;font-family: Arial; font-style: italic; font-size: 1.4em;padding-top: 10px;width:946px;">
    Pocisiona el cursor sobre el arreglo que desees para ver mas información, también puedes dar click sobre cada arreglo y ampliar la información.
</div>
<table style="width:100%;table-layout:fixed;border-collapse:collapse;margin:0;border:none;padding:0;margin-top:20px;">
    <tbody>
        <tr>
        <?php
        $i = 0;
        
        foreach ($arreglos as $codigo_producto => $datos)
        {
        ?>
        <td style="text-align:center;vertical-align:top;">
            <div class="categoria-elemento ">
                <a class="enlace-elemento" href="<?php echo $datos['enlace']; ?>">
                    <div class="categoria-elemento-imagen">
                        <img alt="" class="categoria-elemento-foto img_color" rel="<?php echo $codigo_producto; ?>" src="/IMG/condolencias/<?php echo $codigo_producto; ?>/blanco.jpg">
                    </div>
                    <div class="titulo"><?php echo $datos['titulo']; ?></div>
                    <div class="precio">$<?php echo $datos['precio']; ?></div>
                    <div class="categoria-elemento-clic-aqui">ampliar</div>
                </a>
            </div>
        </td>
        <?php
            $i++;
            if ($i == 5)
            {
                echo '</tr><tr>';
                $i = 0;
            }
        }
        ?>
        </tr>
    </tbody>
</table>
<?php
unset($datos);
$r = db_consultar('SELECT IF(MIN(pv.precio)=MAX(pv.precio),CONCAT("$",pv.precio),CONCAT("Desde $" , MIN(pv.precio))) AS "precio_combinado", CONCAT("$",(IF(MIN(pv.precio_oferta)=MAX(pv.precio_oferta),pv.precio_oferta,CONCAT(MIN(pv.precio_oferta), " - $",MAX(pv.precio_oferta))))) AS "precio_oferta_combinado", AVG(pv.precio_oferta) AS "tiene_oferta", pv.codigo_variedad, pv.foto AS "variedad_foto", pv.receta AS "variedad_receta", IF(pc.titulo="","sin titulo",pc.titulo) AS "contenedor_titulo", pc.descripcion AS "contenedor_descripcion", pc.codigo_producto, pc.creacion, cat.codigo_categoria, cat.titulo AS "titulo_categoria" FROM flores_producto_contenedor AS pc LEFT JOIN flores_producto_variedad AS pv USING(codigo_producto) LEFT JOIN flores_productos_categoria AS pcat USING(codigo_producto) LEFT JOIN flores_categorias AS cat USING(codigo_categoria) WHERE codigo_categoria=15 AND descontinuado="no" AND color="blanco" GROUP BY foto ORDER BY (COS(codigo_producto*(curdate()+0))) ASC');
echo '<div id="solo_si_colo_blanco">';
echo Rejilla_Resultados($r);
echo '</div>';
?>

<script type="text/javascript">
    color = 'blanco';
    function preload(arrayOfImages) {
   $(arrayOfImages).each(function () {
       $('<img />').attr('src',this).appendTo('body').css('display','none');
   });
}
    $(function(){
        
        $("#map area").click(function(event){
            event.preventDefault();
            window.location = this.href + "?color=" + color;
        });
        
        $('.colorear').click(function (event){
            event.preventDefault();
            color = $(this).attr('rel');
            
            $('#solo_si_colo_blanco').toggle(color == 'blanco');
            
            $.each($('.img_color'), function(index, objeto){
                var src = '/IMG/condolencias/' + $(objeto).attr('rel') + '/' + color + '.jpg';
                $(objeto).attr('src',src);
            });
            $('img[usemap]').maphilight();
        });
        
        $('img[usemap]').maphilight();
    });
    
    $(document).ready(function (){
        $('#map area.arriba').qtip({delay:0, style: {classes: "qtipcondolencias"}, position: {my: 'top center',at: 'bottom center'}});
        $('#map area.abajo').qtip({delay:0, style: {classes: "qtipcondolencias"}, position: {my: 'top left',at: 'top right', adjust: {y: -20}}});
        preload(["/IMG/condolencias/principal/rosado.jpg","/IMG/condolencias/principal/rojo.jpg","/IMG/condolencias/principal/amarillo.jpg","/IMG/condolencias/principal/naranja.jpg"]);
    });
</script>