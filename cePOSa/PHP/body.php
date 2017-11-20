<?php
ob_start();

require_once(__BASE_cePOSa__.'PHP/traductor.php');
$BODY = ob_get_clean();

if (isset($_GET['fb']))
    $arrCSS[] = 'CSS/estilo.fb';

if (PLATAFORMA_MOBIL)
    $arrCSS[] = 'CSS/estilo.mobil';

if ( 0 && !$GLOBAL_IMPRESION && PROY_URL_NOPROTOCOL === 'flor360.com/')
  $arrCSS[] = 'CSS/estilo.valentine';

ob_start();
?>
<body>
<?php if (defined('GOOGLE_ANALYTICS')) : ?>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?php echo GOOGLE_ANALYTICS; ?>']);
  _gaq.push(['_setDomainName', '<?php echo @$_SERVER["SERVER_NAME"]; ?>']);
  _gaq.push(['_setAllowLinker', true]);
  _gaq.push(['_trackPageview']);
  <?php echo $_gaq; ?>

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
<script type="text/javascript">
function is_touch_device()
{
    return !!('ontouchstart' in window) || (!!('onmsgesturechange' in window) && !!window.navigator.maxTouchPoints);
}
</script>
<?php endif; ?>
<div id="fb-root"></div>
<?php if(!isset($GLOBAL_IMPRESION)) { ?>
    <?php if (empty($_GET['sin_cabeza'])) require_once(__BASE_cePOSa__.'PHP/'.__CABEZA__.'.php'); ?>
    <div id="wrapper">
    <div id="header">
    <?php if (empty($_GET['sin_cabeza'])) require_once(__BASE_cePOSa__.'PHP/'.__MENU__.'.php'); ?>
    </div>
    <div id="secc_general">
    <?php echo $BODY; ?>
    <?php if (empty($_GET['nochat'])) require_once(__BASE_cePOSa__.'PHP/chat.cliente.php'); ?>
    </div> <!-- secc_general !-->
    </div> <!-- wrapper !-->
<?php } else { ?>
    <style>
    @page { size:8.5in 11in; margin: 0cm; }
    *{background:#FFF !important;color:#000;font-size:12pt;}
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
      channelUrl : '//beta.flor360.com/channel.html',
      oauth: true
    });
    
    <?php if (isset($_GET['fb'])): ?>
    FB.Canvas.setSize({ height: (($('#secc_general').height() + $('#header').height()) + 10) });
    FB.Canvas.scrollTo(0,0);
    FB.Canvas.setDoneLoading();
    <?php endif; ?>
  };
</script>
<?php endif; ?>
</body>
</html>
<?php
$BODY = ob_get_clean();
if (!empty($_LOCATION)) header ("Location: $_LOCATION");
?>
