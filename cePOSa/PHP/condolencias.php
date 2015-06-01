<?php
$codigos = array(1246, 1247, 1248, 1249, 1250, 1251, 1252, 1253, 1254, 1255, 1256);
$c = 'SELECT fpc.titulo AS "contenedor_titulo", codigo_producto FROM flores_producto_contenedor AS fpc WHERE codigo_producto IN ('.join(',', $codigos).')';
$r = db_consultar($c);

while ($f = mysqli_fetch_assoc($r))
{
    $arreglos[$f['codigo_producto']] = array( 'enlace' => URL_SUFIJO_VITRINA.SEO(strip_tags($f['contenedor_titulo']).'-'.$f['codigo_producto']) , 'titulo' => $f['contenedor_titulo']);
}
?>
<style>
a.enlace_portada
{
    color: #012e83;
    font-style: normal;
    text-decoration: underline;
    font-size: 1.2em;
    margin: 0 10px;
}

a.pestaña
{
  text-decoration: none;
  background-color:grey;
  width:73px;
  height:28px;
  line-height:28px;
  display:inline-block;
  color: white;
  border-width: 1px 1px 0;
  border-color: #ababab;
  border-style: solid;
  border-radius:  8px 8px 0px 0px;
  padding: 0 6px;
  text-align:center;
  margin:0px 4px;
  font-size: 1.4em;
  font-weight: bold;
}

a.pestaña:hover
{
text-decoration: none;
 opacity: 0.8;
}

#portada_condolencias
{
    width:100%;
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
            <a href="#como_comprar" class="enlace_portada">Como comprar</a>
            <a href="#como_comprar" class="enlace_portada">Preguntas frecuentes</a>
        </td>
    </tr>
</table>

<map name="map">
<!-- #$-:Image map file created by GIMP Image Map plug-in -->
<!-- #$-:GIMP Image Map plug-in by Maurits Rijk -->
<!-- #$-:Please do not edit lines starting with "#$" -->
<!-- #$VERSION:2.3 -->
<!-- #$AUTHOR:Vladimir Hidalgo -->

<!-- "Corazon" -->
<area shape="poly" coords="75,197,116,141,109,95,80,85,60,105,37,91,13,107,9,138,34,163,56,178" alt="<?php echo $arreglos['1246']['titulo']; ?>" href="<?php echo $arreglos['1246']['enlace']; ?>" />
<!-- "Canasta de gladiolas" -->
<area shape="poly" coords="56,178,56,183,36,194,16,198,3,287,93,280,105,328,130,288,216,230,173,147,137,129,116,148,91,190,76,210,62,195,60,212,60,214,61,220,62,192" alt="<?php echo $arreglos['1248']['titulo']; ?>" href="<?php echo $arreglos['1248']['enlace']; ?>" />
<area shape="poly" coords="87,390,116,381,124,330,143,285,221,281,259,335,277,394,186,396" alt="<?php echo $arreglos['1249']['titulo']; ?>" href="<?php echo $arreglos['1249']['enlace']; ?>" />
<!-- "Corona no tupida" -->
<area shape="poly" coords="232,206,191,175,184,115,209,66,249,45,302,51,349,95,361,141,344,169,273,180,274,183" alt="<?php echo $arreglos['1247']['titulo']; ?>" href="<?php echo $arreglos['1247']['enlace']; ?>" />
<!-- "Cruz" -->
<area shape="poly" coords="392,152,392,83,361,72,363,34,393,26,426,0,456,18,448,38,477,53,477,82,450,90,432,155,410,166" alt="<?php echo $arreglos['1250']['titulo']; ?>" href="<?php echo $arreglos['1250']['enlace']; ?>" />
<!-- "Helechos" -->
<area shape="poly" coords="579,119,581,119,637,183,588,224,437,208,378,184,405,158,441,164,453,108,506,88,558,87" alt="<?php echo $arreglos['1253']['titulo']; ?>" href="<?php echo $arreglos['1253']['enlace']; ?>" />
<!-- "Corona tupida" -->
<area shape="poly" coords="602,121,607,54,677,23,725,88,707,144,644,156,629,147,616,139" alt="<?php echo $arreglos['1251']['titulo']; ?>" href="<?php echo $arreglos['1251']['enlace']; ?>" />
<!-- "Canasta picuda" -->
<area shape="poly" coords="568,387,662,218,750,329,751,396" alt="<?php echo $arreglos['1255']['titulo']; ?>" href="<?php echo $arreglos['1255']['enlace']; ?>" />
<!-- "1254" -->
<area shape="poly" coords="763,299,698,257,720,182,753,170,801,222,823,250,813,261,813,260,812,259" alt="<?php echo $arreglos['1254']['titulo']; ?>" href="<?php echo $arreglos['1254']['enlace']; ?>" />
<!-- "12" -->
<area shape="poly" coords="793,212,751,168,750,86,846,20,902,27,928,113,930,221,885,280" alt="<?php echo $arreglos['1252']['titulo']; ?>" href="<?php echo $arreglos['1252']['enlace']; ?>" />
</map>

<div style="border-width:7px 7px 0px; border-color:#dbdad8; border-style: solid;border-radius:8px;height:418px;">
    <img class="img_color" rel="principal" src="/IMG/condolencias/principal/blanco.jpg" usemap="#map" border="0" />
</div>
<div style="background-color: #dbdad8; border-bottom: #dbdad8 7px solid;border-radius:0 0 8px 8px;margin-top:-8px;text-align:center;z-index:10;font-family: Arial; font-style: italic; font-size: 1.4em;padding-top: 10px;">
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
                        <img alt="" class="categoria-elemento-foto" src="/IMG/condolencias/<?php echo $codigo_producto; ?>/blanco.jpg">
                    </div>
                    <div class="titulo"><?php echo $datos['titulo']; ?> </div>
                    <div class="precio">$</div>
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
<script type="text/javascript">
    $(function(){
        $('.colorear').click(function (event){
            event.preventDefault();
            var color = $(this).attr('rel');
            $.each($('.img_color'), function(index, objeto){
                var src = '/IMG/condolencias/' + $(objeto).attr('rel') + '/' + color + '.jpg';
                $(objeto).attr('src',src);
            });
        });
    });
</script>