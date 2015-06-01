<?php if ( !S_iniciado()): ?>
<script type="text/javascript">
    setTimeout(function(){
        $("nav.dropdown li:eq(1)").qtip({show: {event: false, ready: true}, content: {text: '<p style="color:red;font-weight:bold;font-size:1.2em;">Ver nuestro catálogo</p>'}, style: {classes: "ui-tooltip-shadow ui-tooltip-cream ui-tooltip-shadow", tip: "top left"}, position: {my: "top left",at: "bottom center"}});
    }, 1000);
    
    setTimeout(function(){
        $("#busqueda").qtip({show: {event: false, ready: true}, content: {text: '<p style="color:red;font-weight:bold;font-size:1.2em;">Búscar arreglos</p>'}, style: {classes: "ui-tooltip-shadow ui-tooltip-cream ui-tooltip-shadow", tip: "top right"}, position: {my: "top right",at: "bottom center"}});
    }, 2000);
</script>
<?php endif; ?>
<map name="map">
<!-- #$-:Image map file created by GIMP Image Map plug-in -->
<!-- #$-:GIMP Image Map plug-in by Maurits Rijk -->
<!-- #$-:Please do not edit lines starting with "#$" -->
<!-- #$VERSION:2.3 -->
<!-- #$AUTHOR:Vladimir Hidalgo -->
<area shape="rect" coords="30,32,141,65" alt="Rosas" href="/categoria-rosas-2.html" />
<area shape="rect" coords="120,90,224,134" alt="Amor" href="/categoria-amor-4.html" />
<area shape="rect" coords="17,137,120,175" alt="Felicitaciones" href="/categoria-felicitaciones-9.html" />
<area shape="rect" coords="125,184,288,230" alt="Simpatia - Condolencias" href="/categoria-condolencias-15.html" />
<area shape="rect" coords="231,6,344,32" alt="Lo siento" href="/categoria-lo-siento-12.html" />
<area shape="rect" coords="179,44,329,89" alt="Aniversario" href="/categoria-aniversario-8.html" />
<area shape="rect" coords="536,4,640,35" alt="Ofertas" href="/categoria-superior-arreglos-1.html?refinado=solo_ofertas" />
<area shape="rect" coords="604,65,722,95" alt="Romance - Amistad" href="/categoria-amistad-5.html" />
<area shape="rect" coords="646,151,770,190" alt="Recién nacido" href="/categoria-recien-nacido-14.html" />
<area shape="rect" coords="595,222,695,259" alt="Agradecimientos" href="/categoria-agradecimientos-16.html" />
<area shape="rect" coords="676,11,888,48" alt="Solo porque si" href="/categoria-solo-porque-si-13.html" />
<area shape="rect" coords="758,211,919,245" alt="Solo porque si" href="/categoria-solo-porque-si-13.html" />
<area shape="rect" coords="730,98,891,140" alt="Te extraño!" href="/categoria-valentines-36.html" />
</map>
<?php
$IMG_CENTRAL = '<div style="text-align:center;"><a href="categoria-superior-arreglos-1.html"><img width="939" height="264" usemap="#map" src="'.PROY_URL_ESTATICA.'IMG/portada/nov12_portada.jpg"/></a></div>';
//$IMG_CENTRAL = '<div style="text-align:center;"><a href="categoria-superior-arreglos-1.html"><img width="800" height="259" usemap="#map" src="'.PROY_URL_ESTATICA.'IMG/portada/may10_portada.jpg"/></a></div>';

// Valentines:
//$IMG_CENTRAL = '<div style="text-align:center;"><a href="'.PROY_URL.'categoria-valentines-36.html?orden=color&valor=Rojo"><img width="939" height="330" src="'.PROY_URL_ESTATICA.'IMG/portada/feb13_portada.jpg"/></a></div>';

//Array de Blobs
$ab = array(
'Amor' => 'categoria-amor-4.html',
'Amistad' => 'categoria-amistad-5.html',
'Cumpleaños' => 'categoria-amistad-5.html',
'Condolencias' => 'categoria-condolencias-15.html',
'Recien Nacido' => 'categoria-recien-nacido-14.html',

'Regalos Corporativos' => 'categoria-regalos-corporativos-10.html',
'Mejorate pronto' => 'categoria-mejorate-pronto-11.html',
'Lo Siento' => 'categoria-lo-siento-12.html',
'Graduación' => 'categoria-graduacion-43.html',
'Agradecimientos' => 'categoria-agradecimientos-16.html',

'Felicitaciones' => 'categoria-felicitaciones-9.html',
'Regalos Para Hombre' => 'categoria-regalos-para-hombre-44.html',
'Globos' => 'categoria-superior-globos-6.html',
'Bouquets' => 'categoria-bouquet-42.html',
'Centros de Mesa' => 'categoria-centro-de-mesa-37.html'
);

$conteo = 0;
$blobs = '';
foreach ($ab as $categoria => $enlace)
{
    $blobs .= '<div class="blob"><a class="imagen" href="'.$enlace.'">';
    $blobs .= '<div class="titulo">'.$categoria;
    $blobs .= '<img title="'.$categoria.'" alt="'.$categoria.'" src="'.PROY_URL_ESTATICA.'/IMG/blob/'.$conteo.'.jpg" />';
    $blobs .= '</div></a></div>';
    $conteo++;
}
?>
<?php echo $IMG_CENTRAL; ?>
<p class="portada_info" style="width:930px;padding:4px; margin: 2px auto;text-align:center;">
Flor360.com es la mas destacada entre las <span style="font-weight:bold;">Floristerias El Salvador</span> ya que contamos con diseños florales exclusivos para enviar <span style="font-weight:bold;">Flores a El Salvador</span> y <span style="font-weight:bold;">Regalos a El Salvador</span>.<br />
Are you an international costumer looking to <span style="font-weight:bold;">send present for birthday, valentine, congratulations</span>?, don't worry, you can send flowers to El Salvador contact us at <a class="bloque" href="mailto:info@flor360.com">info@flor360.com</a>.
</p>

<p class="portada_info" style="width:930px;padding:4px; margin: 2px auto;text-align:center;">
Visita nuestra <a class="bloque" target="_blank" href="https://www.facebook.com/floristeria.flor360" rel="nofollow">Fan Page de Facebook</a>&nbsp;
o siguenos en nuestra <a class="bloque" target="_blank" href="http://twitter.com/flor360" rel="nofollow">cuenta de Twitter</a>.
Teléfonos: PBX <b><?php echo PROY_TELEFONO_PRINCIPAL; ?></b> - Kiosko La Gran Vía: <b><?php echo PROY_TELEFONO_SECUNDARIO; ?></b>.
<a class="bloque" href="http://blog.flor360.com/" target="_blank">Acerca de Flor360.com</a>
</p>

<div id="blobs">
    <?php echo $blobs; ?>
</div>

<div class="fondo_defecto" style="padding:12px;"></div>

<p style="text-align: center; margin: 10px 0;">© 2010 FLOR360.COM - Floristerias El Salvador® - <strong><?php echo PROY_TELEFONO_PRINCIPAL; ?></strong> - <a href="ayuda?tema=terminos_y_condiciones">terminos y condiciones</a>

<p style="text-align: justify;">Flor360.com te pone a dispocisión
cualquier tipo de arreglo floral, detalle o regalo; contamos con
los mas especiales arreglos utilizando para esto flores&nbsp;
naturales con diseños que le dan un giro a la imaginación y
que hacen de todos nuestros productos el regalo más exclusivo y
especial que recibira esa persona tan importante en su vida.</p>
<p>Simplemente no encontrará un mejor lugar para ordenar flores en
línea en El Salvador. Estaremos contigo en cada día especial o
simplemente recordarle a un ser querido cuanto lo aprecias.</p>

<p style="text-align: justify;">Nuestros servicios: arreglos
florales en El Salvador, floristeria en El Salvador, globos, San
Valentin, Dia de la Madre, decoraciones para boda, quince años,
eventos empresariales, eventos familiares, graduaciones, centros de
mesa y arreglos frutales.</p>

<p style="text-align: justify;">En flor360.com somos importadores
directos de flores, lo cual asegura no solamente&nbsp; que los
regalos que envies serán entregados a tiempo, sino tambien
significa que el precio de nuestros arreglos es el mas bajo del
mercado y que el producto que te entregaremos ha sido preparado con
las flores más frescas como sea posible&nbsp; y ofrecen un gran
valor para la persona que lo recibe como para quien lo envía.</p>

<p style="text-align: justify;">Nuestro equipo de atención al
cliente esta preparado para ayudarte con cualquier orden. Ya sea
que envías una&nbsp; rosa para celebrar tu amor o una canasta de
regalo para felicitar a un compañero de trabajo; si tienes
preguntas nuestro equipo tiene las respuestas. Llamanos al
<?php echo PROY_TELEFONO_PRINCIPAL; ?> o haz tu orden en línea. Nosotros te ayudaremos a
encontrar y entregar las flores adecuadas, plantas o regalos para
celebrar los momentos mas significativos de la vida.</p>

<p style="text-align: justify;">Tu mejor opción cuando compras en
línea por flores, ramos y regalos en cualquier momento del año,
especialmente en el día de San Valentin y el Día de las Madres.
Tenemos una maravillosa selección de flores y plantas frescas
incluyendo rosas, gerberas, orquidias, tulipanes, lirios, y más.
Ademas ofrecemos deliciosas canastas de regalo de fruta fresca,
comida gourmet, chocolates y dulces.</p>

<p style="text-align: justify;">También encontrarás ramos para
todas las ocasiones: cumpleaños, aniversarios, Solo porque Si,
Nuevo Bebe, Amor y Romance, Lo siento, Que te mejores, Simpatía y
condolencias.&nbsp; Ya sea que este buscando por un ramo de flores
o una planta de maceta, nuestra variedad de flores y arreglos le
proveeran de suficientes opciones. Las entregas del mismo dia estan
disponibles.</p>

<p style="text-align: justify;">En 2010, Flor360.com, abre su
primera floristería y cambió para siempre la forma en que las
flores son compradas para cumpleaños, aniversarios y ocasiones
especiales. nuestra pasión ha sido ayudarte a conectarte y
expresarte a la gente importante en tu vida a travez de la
selección mas fina de bellas flores y arreglos florales, desde
rosas a tulipanes a gerberas a orquidias a fullys o gerberas,
disponibles para el mismo día o entrega para el siguiente día.
Tambien puedes encontrar plantas, canastas de regalo, comida
gourmet, animalitos de peluche perfectos para cada ocasion y
cubiertos por nuestras Garantía de Satisfacción Total.</p>
