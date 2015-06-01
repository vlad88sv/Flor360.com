<?php
require_once('../cePOSa/PHP/facebook-php-sdk/facebook.php');

    $app_id = '329988580395495';
    $app_secret = '31f009494fd8c0e145743489f997527a';
    $facebook = new Facebook(array(
        'appId'  => $app_id,
        'secret' => $app_secret,
        'cookie' => true
    ));

$signedrequest = $facebook->getSignedRequest();

$rifa['fecha_rifa_formato'] = 'Miércoles 4 de Abril de 2012 10:00am';
$rifa['titulo'] = '15 pases, 3 por ganador.';
$rifa['fecha_rifa_UNIX'] = strtotime('2012-04-04 10:00:00');
$rifa['descripcion_rifa'] = 'Recuerda: 5 palabras que describan tu mejor experiencia en RIFT, escribelas como un comentario a continuación. Se creativo y preparate para ganar!.';
$rifa['como_participar'] = 'Para participar describe tu mejor experiencia en RIFT <b>en <u>5</u> palabras</b>, los 5 participantes que mas "Likes" tengan al momento del sorteo serán los ganadores. En caso de empate los pases serán sorteados entre ellos.';

function cmp($a, $b)
{
    return ($a['likes'] < $b['likes']);
}

function Ob_top3()
{

$grap_rifa = file_get_contents('https://graph.facebook.com/comments/?ids=https://www.facebook.com/riftsv/app_329988580395495&limit=500&offset=0');
$arr_rifa = json_decode($grap_rifa, true);

$pagina = current($arr_rifa);

if (count($pagina['comments']['data']))
{
    foreach($pagina['comments']['data'] as $data )
    {
        $participantes[] = array('nombre' => @$data['from']['name'], 'mensaje' => @$data['message'], 'likes' => @$data['likes']);
    }
    
    
    usort($participantes,'cmp');
    reset($participantes);

    echo '<h1>Los 5 participantes con mas "Likes" (de '.count($pagina['comments']['data']).' participantes) | Tiempo para finalizar la rifa: <span id="tiempoRifa">¡Ya tenemos ganadores!</span></h1>';
    echo '<table style="width:100%;">';
    echo '<tr><th>Nombre</th><th>Nombre de arreglo</th><th>Número de "Likes"</th></tr>';
    for($i=0; $i< min(5,count($pagina['comments']['data']));$i++)
        echo sprintf('<tr><td>%s</td><td>%s</td><td>%s</td></tr>', $participantes[$i]['nombre'], $participantes[$i]['mensaje'], $participantes[$i]['likes']);
    echo '</table>';
}

}
?>
<head>
    <title>Turismo El Salvador - RIFT Laser Tag - Paintball El Salvador - Fiestas El Salvador</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Style-type" content="text/css" />
    <meta http-equiv="Content-Script-type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="es" />
    <meta property="og:title" content="Turismo El Salvador - RIFT Laser Tag - Paintball El Salvador - Fiestas El Salvador"/>
    <meta property="og:image" content="http://riftelsalvador.com/img/icono.gif"/>
    <meta property="og:description" content="RIFT Laser Tag: (paintball laser) diversión familiar en El Salvador. No se necesita vestimenta especial ni reservas!."/>    

    <style>
        html,body {margin:0;padding:0;border:0;width:100%;height:100%;overflow: hidden;}
        body {font-size:12px;font-family:Arial, sans-serif;}
        #base_concurso li {text-align: left;}
        #principal {width:100%;height: 400px;table-layout: fixed; border-collapse:collapse;}
        #principal td {vertical-align: top;overflow: hidden;}
        #foto_arreglo {width:300px;}
        #foto_arreglo img {width:300px;height:400px;}
        h1 {background-color: #F89009;color:#FFF; padding:5px; font-size:13px;}
        table th {font-weight:bold;font-size:12px;text-align:center;}
        table td {font-size:12px;text-align:center;}
        ol {}
    </style>
    <script type="text/javascript" src="../JS/jquery-1.7.2.js"></script>
    
</head>
<body>    
<div id="fb-root"></div>
<div id="contenido">
<div style="text-align: center;"><div style="margin: auto;width:800px;">
<?php if ($signedrequest['page']['liked'] == 1): ?>
    <h1>Participa en la rifa de entradas para jugar en RIFT El Salvador</h1>
    <p>
        Fecha del sorteo: <b><?php echo $rifa['fecha_rifa_formato']; ?></b>.&nbsp;&nbsp;&nbsp;
        Cantidad de pases a rifar: <b><?php echo $rifa['titulo']; ?></b>
    </p>
    <h1>¿Cómo participar?</h1>
    <p><?php echo $rifa['como_participar']; ?></p>
    <?php Ob_top3(); ?>
    <table id="principal"  style="margin-top: 15px;">
    <td id="foto_arreglo">
        <div><img src="foto_lateral.jpg" /></div>
    </td>
    <td style="padding: 0 10px;">
    <p>
        <?php echo $rifa['descripcion_rifa']; ?>
    </p>
    <div style="height:360px;overflow:auto;">
        <p>Ya tenemos a los 5 ganadores de esta rifa!</p>
        <!--<div class="fb-comments" data-href="https://www.facebook.com/riftsv/app_329988580395495" data-num-posts="100" data-width="450"></div>!-->
    </div>
    </td>
    </tr>
    </table>
    <p>¿Mas información?, ¡visite <a target="_blank" href="http://riftelsalvador.com">www.riftelsalvador.com</a>!</p>
    <p><b>Bases del concurso:</b></p>
    <p>
    <ol id="base_concurso">
    <li>Quedan excluidos de este concurso todas las personas que laboren <b>RIFT El Salvador</b>.</li>
    <li>El ganador será publicado en Facebook y contactado a traves de 1 mensaje privado en Facebook.</li>
    </ol>
    </p>
<?php else: ?>
    <div>
    <h1>Participa en la rifa de entradas todos los miércoles!</h1>
    <div style="margin-top: 50px;">Para participar, lo primero que debes hacer es dar click en "Me Gusta" <div style="display: inline-block;"><div style="display:inline-block;" class="fb-like" data-href="https://www.facebook.com/riftsv" data-send="false" data-layout="button_count" data-width="80" data-show-faces="false"></div></div> y luego sigue las instrucciones.</div>
    </div>
    
    <p style="margin-top: 50px;">¿Que esperas?, ¡tus amigos ya estan participando!</p>
    <div class="fb-facepile" data-href="https://www.facebook.com/riftsv" data-size="large" data-max-rows="10" data-width="700" appId="329988580395495"></div>
<?php endif; ?>
</div></div>
</div>
<script>
(function(d, s, id) {var js, fjs = d.getElementsByTagName(s)[0];if (d.getElementById(id)) return;js = d.createElement(s); js.id = id;js.src = "//connect.facebook.net/es_LA/all.js#xfbml=1&appId=329988580395495";fjs.parentNode.insertBefore(js, fjs);}(document, 'script', 'facebook-jssdk'));

window.fbAsyncInit = function() {
    FB.init({appId  : '329988580395495', status:true, cookie: true, xfbml: true});
    FB.Event.subscribe('edge.create',function(href, widget){top.location.href="https://www.facebook.com/riftsv/app_329988580395495";});
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