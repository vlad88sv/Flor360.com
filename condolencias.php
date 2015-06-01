<?php
define('MEMCACHE_ACTIVO', true);
define('MODO_MUERTO',true);

if (empty($_GET['peticion']))
    $_GET['peticion'] = 'portada';

// Ruta del archivo de configuracion y personalizacion
require_once('config.condolencias.php');

if(!defined('__BASE__'))
    die('Error, no puede arrancar el sistema');
define('__BASE_cePOSa__',str_replace('//','/',dirname(__FILE__).'/cePOSa/'));
require_once (__BASE_cePOSa__."PHP/vital.php");
// Auxiliar para HEAD
$arrHEAD = array();
$arrJS = array();
// Inclusiones JS
$arrJS[] = 'jquery-1.7.2';
$arrJS[] = 'jquery.cookie';
$arrJS[] = 'jquery.scrollTo';
$arrJS[] = 'jquery.jgrowl';
$arrJS[] = 'jquery.qtip2';

// Inclusiones CSS
$arrCSS[] = 'CSS/estilo';
$arrCSS[] = 'CSS/estilo.condolencias';
$arrCSS[] = 'CSS/jquery.jgrowl';
$arrCSS[] = 'CSS/jquery.qtip';
ob_start();
require_once (__BASE_cePOSa__."PHP/condolencias.php");
$BODY = ob_get_clean();
ob_start();
?>
<body>
<div id="fb-root"></div>
<?php if(!isset($GLOBAL_IMPRESION)) { ?>
    <div id="wrapper">
    <div id="header"><img src="/IMG/portada/condolencias.superior.png" /></div>
    <div id="secc_general">
    <?php echo $BODY; ?>
    </div> <!-- secc_general !-->
    </div> <!-- wrapper !-->
<?php } else { ?>
    <style>
    *{background:#FFF !important;color:#000 !important;font-size:16px;}
    .medio-oculto{font-size:11pt;}
    </style>
    <?php echo $BODY; ?>
<?php } ?>
<?php if(empty($_GET['desactivar_fb'])) : ?>
<script type="text/javascript">
  (function() {
    var e = document.createElement('script'); e.async = true;
    e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
    document.getElementById('fb-root').appendChild(e);
  }());

  window.fbAsyncInit = function() {
    FB.init({
      appId: '315669512918',
      cookie: true,
      xfbml: true,
      oauth: true
    });
    
    FB.Canvas.setSize({ height: (($('#secc_general').height() + $('#header').height()) + 10) });
    FB.Canvas.scrollTo(0,0);
    FB.Canvas.setDoneLoading();
  };
  
    $.ajaxSetup({timeout: 5000});
</script>
<?php endif; ?>
</body>
</html>
<?php
$BODY = ob_get_clean();
if (!empty($_LOCATION)) header ("Location: $_LOCATION");

/* CAPTURAR <head> */
ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>
    <title><?php echo $HEAD_titulo; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Style-type" content="text/css" />
    <meta http-equiv="Content-Script-type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="es" />
    <meta name="description" content="<?php echo $HEAD_descripcion; ?>" />
    <meta name="keywords" content="<?php echo HEAD_KEYWORDS; ?>" />
    <meta name="robots" content="index, follow" />
    <meta property="og:title" content="<?php echo $HEAD_titulo; ?>" />
    <meta property="og:description" content="<?php echo $HEAD_descripcion; ?>" />
    <meta property="og:image" content="<?php echo $HEAD_ogimage; ?>" />
    <link href="condolencias.ico" rel="icon" type="image/x-icon" />
    <link rel="canonical" href="<?php echo PROY_URL_ACTUAL; ?>" />
<?php
HEAD_CSS();
HEAD_JS();
HEAD_EXTRA();
?>
<?php if (defined('GOOGLE_ANALYTICS')) : ?>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?php echo GOOGLE_ANALYTICS; ?>']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
<?php endif; ?>
<script type="text/javascript">
$(function(){
    $('.blink').each(function() {
        var elem = $(this);
        setInterval(function() {
            if (elem.css('visibility') == 'hidden') {
                elem.css('visibility', 'visible');
            } else {
                elem.css('visibility', 'hidden');
            }    
        }, 500);
    });
});
</script>
</head>
<?php
$HEAD = ob_get_clean();

/* MOSTRAR TODO */
if(isset($GLOBAL_TIDY_BREAKS))
    echo $HEAD.$BODY;
else
{
    $tidy_config = array('output-xhtml' => true,'doctype' => 'transitional');
    $tidy = tidy_parse_string($HEAD.$BODY,$tidy_config,'UTF8');
    $tidy->cleanRepair();
    echo  trim($tidy);
}
?>
