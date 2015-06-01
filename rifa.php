<?php
require_once ("config.php");
    require_once(__BASE__ARRANQUE.'PHP/vital.php');
    require_once(__BASE__ARRANQUE.'PHP/facebook-php-sdk/facebook.php');

    $URL_COMMENT = 'https://www.facebook.com/floristeria.flor360/app_262165247201504';

    $app_id = '262165247201504';
    $app_secret = 'b8e41eb912cef9b8e9cb94e057899a67';

$facebook = new Facebook(array(
    'appId'  => $app_id,
    'secret' => $app_secret,
    'cookie' => true
));

$signedrequest = $facebook->getSignedRequest();

// Obtengamos la siguiente rifa
$consulta = 'SELECT codigo_rifa, titulo, codigo_variedad, foto, titulo_rifa, descripcion_rifa, fecha_rifa, UNIX_TIMESTAMP(fecha_rifa) AS fecha_rifa_UNIX, DATE_FORMAT(`fecha_rifa`,"%W %e de %M de %Y %r") fecha_rifa_formato FROM flores_rifa LEFT JOIN flores_producto_variedad USING(codigo_variedad) LEFT JOIN flores_producto_contenedor USING(codigo_producto) WHERE 1 ORDER BY codigo_rifa DESC LIMIT 1';
$resultado = db_consultar($consulta);
$rifa = mysqli_fetch_assoc($resultado);

$URL_COMMENT .= $rifa['fecha_rifa_UNIX'];

function cmp($a, $b)
{
    return ($a['comentarios'] < $b['comentarios']);
}

function Ob_top3()
{
global $URL_COMMENT;
$grap_rifa = file_get_contents('https://graph.facebook.com/comments/?ids='.urlencode($URL_COMMENT).'&limit=500&offset=0');
$arr_rifa = json_decode($grap_rifa, true);

$pagina = current($arr_rifa);


if (count($pagina['comments']['data']))
{
    foreach($pagina['comments']['data'] as $data )
    {
        $participantes[] = array('nombre' => @$data['from']['name'], 'mensaje' => @$data['message'], 'likes' => @$data['likes'], 'comentarios' => (empty($data['comments']['count']) ? 0 : $data['comments']['count']) );
    }
    
    echo '<!--';
    //print_r($participantes);
    echo '!-->';
    
    usort($participantes,'cmp');
    reset($participantes);
    
    
    echo '<h1>Las 3 personas con mas comentarios (de '.count($pagina['comments']['data']).' participantes) | Tiempo para finalizar la rifa: <span id="tiempoRifa">finalizada!</span></h1>';
    echo '<table style="width:100%;">';
    echo '<tr><th>Nombre</th><th>Piropo</th><th>Número de <b>comentarios</b></th></tr>';
    for($i=0; $i<min(3,count($pagina['comments']['data']));$i++)
        echo sprintf('<tr><td>%s</td><td>%s</td><td>%s</td></tr>', $participantes[$i]['nombre'], $participantes[$i]['mensaje'], $participantes[$i]['comentarios']);
    echo '</table>';
}
}
?>
<head>
    <title>Floristería Flor360.com - RIFAS SEMANAL</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Style-type" content="text/css" />
    <meta http-equiv="Content-Script-type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="es" />
    <meta name="description" content="Floristería Flor360.com - rifamos un arreglo semanal en San Salvador" />
    <meta name="keywords" content="Floristería, Rifa, Sorteo, Promoción, San Salvador" />
    <meta name="robots" content="index, follow" />
    <meta property="og:title" content="Floristería Flor360.com - RIFAS SEMANAL" />
    <meta property="og:description" content="Floristería Flor360.com - rifamos un arreglo semanal en San Salvador" />
    <meta property="og:image" content="'http://flor360.com/imagen_130_110_c3fb196d30dde4a0c7a211cc1060f414acaf2310.jpg" />
    
    <style>
        html,body {margin:0;padding:0;border:0;width:100%;height:100%;}
        body {font-size:12px;font-family:Arial, sans-serif;}
        #base_concurso li {text-align: left;}
        #principal {width:100%;height: 400px;table-layout: fixed; border-collapse:collapse;}
        #principal td {vertical-align: top;overflow: hidden;}
        #foto_arreglo {width:300px;}
        #foto_arreglo img {width:266px;height:400px;}
        h1 {background-color: #a9d67b;color:#000; padding:5px; font-size:13px;}
        table th {font-weight:bold;font-size:12px;text-align:center;}
        table td {font-size:12px;text-align:center;}
        ol {}
    </style>
    <script type="text/javascript" src="https://flor360.com/JS/jquery-1.7.2.js"></script>
    
</head>
<body>    
<div id="fb-root"></div>
<div id="contenido">
<img style="width:810px;height:66px;" src="<?php echo PROY_URL_ESTATICA ?>IMG/portada/superior.fb.png" />
<div style="text-align: center;"><div style="margin: auto;width:800px;">
<?php if ($signedrequest['page']['liked'] == 1): ?>
    <h1>Participa en la rifa de este fabuloso arreglo</h1>
    <p>
        Fecha del sorteo: <b><?php echo $rifa['fecha_rifa_formato']; ?></b>.&nbsp;&nbsp;&nbsp;
        Arreglo a rifar: <b><?php echo $rifa['titulo']; ?></b> (imagen abajo).
    </p>
    <h1>¿Cómo participar?</h1>
    <p><?php echo $rifa['titulo_rifa']; ?></p>
    <?php Ob_top3(); ?>
    <table id="principal"  style="margin-top: 15px;">
    <td id="foto_arreglo">
        <div><img src="<?php echo PROY_URL_ESTATICA; ?>imagen_266_400_<?php echo $rifa['foto']; ?>.jpg" /></div>
    </td>
    <td style="padding: 0 10px;">
    <p>
        <?php echo $rifa['descripcion_rifa']; ?>
    </p>
    <div style="height:360px;overflow:auto;">
        <p>- RIFA finalizada -</p>
        <div class="fb-comments" data-href="<?php echo $URL_COMMENT; ?>" data-num-posts="100" data-width="450"></div>
    </div>
    </td>
    </tr>
    </table>
    <p>¿No encuentra inspiración?, ¡visite <a target="_blank" href="http://flor360.com">www.flor360.com</a>!</p>
    <p><b>Bases del concurso:</b></p>
    <p>
    <ol id="base_concurso">
    <li>Quedan excluidos de este concurso todas las personas que se desempeñen en relación de dependencia en cualquiera de las empresas que forman Floristería Flor360.com.</li>
    <li>Si ud ya gano 1 rifa semanal, no podra volver a ganarla en un periodo de 2 meses</li>
    <li>El premio no podrá ser distribuido entre dos o más concursantes.</li>
    <li>Se incluye envío gratuito en San Salvador, Antiguo Cuscatlán, Santa Tecla, Nuevo Cuscatlán y Ciudad Merliot. Caso contrario el ganador deberá pasar a recoger su arreglo a nuestra sucursal ubicada en Centro Comercial La Gran Vía, Kiosko KGV26.</li>
    <li>El arreglo debe ser reclamado en un máximo de 7 días después de la fecha de </li>
    <li>El arreglo rifado es el que aparece en la fotografía del sorteo en el que Ud. participó.</li>
    <li>El ganador será publicado en Facebook y contactado a traves de 1 mensaje privado en Facebook.</li>
    </ol>
    </p>
    <h1>Ganadores de sorteos anteriores</h1>
    <p>Jose Rivera - Miércoles 28 de Marzo de 2012</p>
    <p>Jimmy Molina - Viernes 6 de abril de 2012</p>
<?php else: ?>
    <div>
    <h1>Participa en la rifa de un fabuloso arreglo todos los viernes</h1>
    <div style="margin-top: 50px;">Para participar, lo primero que debes hacer es dar click en "Me Gusta" <div style="display: inline-block;"><div style="display:inline-block;" class="fb-like" data-href="http://www.facebook.com/floristeria.flor360" data-send="false" data-layout="button_count" data-width="80" data-show-faces="false"></div></div> y luego sigue las instrucciones.</div>
    </div>
    
    <p style="margin-top: 50px;">¿Que esperas?, ¡tus amigos ya estan participando!</p>
    <div class="fb-facepile" data-href="https://www.facebook.com/floristeria.flor360" data-size="large" data-max-rows="10" data-width="700" appId="262165247201504"></div>
<?php endif; ?>
</div></div>
</div>
<script>
(function(d, s, id) {var js, fjs = d.getElementsByTagName(s)[0];if (d.getElementById(id)) return;js = d.createElement(s); js.id = id;js.src = "//connect.facebook.net/es_LA/all.js#xfbml=1&appId=262165247201504";fjs.parentNode.insertBefore(js, fjs);}(document, 'script', 'facebook-jssdk'));

window.fbAsyncInit = function() {
    FB.init({appId  : '262165247201504', status:true, cookie: true, xfbml: true});
    FB.Event.subscribe('edge.create',function(href, widget){top.location.href="https://www.facebook.com/floristeria.flor360/app_262165247201504";});
    FB.Canvas.setSize({ height: ($('#contenido').height()  + 100) });
    FB.Canvas.setDoneLoading();
}

function getTime() {
now = new Date();
y2k = new Date(<?php echo ($rifa['fecha_rifa_UNIX'] * 1000); ?>);
days = (y2k - now) / 1000 / 60 / 60 / 24;
daysRound = Math.floor(days);
hours = (y2k - now) / 1000 / 60 / 60 - (24 * daysRound);
hoursRound = Math.floor(hours);
minutes = (y2k - now) / 1000 /60 - (24 * 60 * daysRound) - (60 * hoursRound);
minutesRound = Math.floor(minutes);
seconds = (y2k - now) / 1000 - (24 * 60 * 60 * daysRound) - (60 * 60 * hoursRound) - (60 * minutesRound);
secondsRound = Math.round(seconds);
sec = (secondsRound == 1) ? " segundo." : " segundos.";
min = (minutesRound == 1) ? " minuto " : " minutos, ";
hr = (hoursRound == 1) ? " hora " : " horas, ";
dy = (daysRound == 1)  ? " día " : " días, "
$("#tiempoRifa").html(daysRound  + dy + hoursRound + hr + minutesRound + min + secondsRound + sec);
}

//window.setInterval('getTime()',1000);
</script>
</body>