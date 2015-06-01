<?php
function ES_SSL() {
    return ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443);
}

// Genera un simple contenedor JavaScript
function JS($script){
    return "<script type='text/javascript'>".$script."</script>";
}

// Genera un simple contenedor JavaScript para JQuery ON DOM READY
function JS_onload($script){
    return "<script type='text/javascript'>$(document).ready(function(){".$script."});</script>";
}

// Genera un pequeño GROWL
function JS_growl($mensaje){
    return "$.jGrowl('".addslashes($mensaje)."', { sticky: true });";
}

function suerte($una, $dos){
    if (rand(0,1)) {
        return $una;
    } else {
        return $dos;
    }
}

function Truncar($cadena, $largo) {
    if (strlen($cadena) > $largo) {
        $cadena = substr($cadena,0,($largo -3));
            $cadena .= '...';
    }
    return $cadena;
}


function _F_form_cache($campo)
{
    if (!isset($_POST))
        return '';
    if (array_key_exists($campo, $_POST))
    {
        return $_POST[$campo];
    }
    else
    {
        return '';
    }
}

// http://www.linuxjournal.com/article/9585
function validcorreo($correo)
{
   $isValid = true;
   $atIndex = strrpos($correo, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($correo, $atIndex+1);
      $local = substr($correo, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}


function scaleImage($x,$y,$cx,$cy) {
    //Set the default NEW values to be the old, in case it doesn't even need scaling
    list($nx,$ny)=array($x,$y);

    //If image is generally smaller, don't even bother
    if ($x>=$cx || $y>=$cx) {

        //Work out ratios
        if ($x>0) $rx=$cx/$x;
        if ($y>0) $ry=$cy/$y;

        //Use the lowest ratio, to ensure we don't go over the wanted image size
        if ($rx>$ry) {
            $r=$ry;
        } else {
            $r=$rx;
        }

        //Calculate the new size based on the chosen ratio
        $nx=intval($x*$r);
        $ny=intval($y*$r);
    }

    //Return the results
    return array($nx,$ny);
}
function Imagen__Redimenzionar($Origen, $Ancho = 640, $Alto = 480)
{
    $im=new Imagick($Origen);

    $im->setImageColorspace(255);
    $im->setCompression(Imagick::COMPRESSION_JPEG);
    $im->setCompressionQuality(80);
    $im->setImageFormat('jpeg');

    list($newX,$newY)=scaleImage($im->getImageWidth(),$im->getImageHeight(),$Ancho,$Alto);
    $im->scaleImage($newX,$newY,true);
    return $im->writeImage($Origen);
}

/*
 * Imagen__CrearMiniatura()
 * Crea una versión reducida de la imagen en $Origen
*/
function Imagen__CrearMiniatura($Origen, $Destino, $Ancho = 100, $Alto = 100)
{
    $im=new Imagick($Origen);

    $im->setImageColorspace(255);
    $im->setCompression(Imagick::COMPRESSION_JPEG);
    $im->setCompressionQuality(80);
    $im->setImageFormat('jpeg');

    list($newX,$newY)=scaleImage($im->getImageWidth(),$im->getImageHeight(),$Ancho,$Alto);
    $im->thumbnailImage($newX,$newY,false);
    return $im->writeImage($Destino);
}

function SEO($URL){
    $URL = preg_replace("`\[.*\]`U","",$URL);
    $URL = preg_replace('`&(amp;)?#?[a-z0-9]+;`i','-',$URL);
    $URL = htmlentities($URL, ENT_COMPAT, 'utf-8');
    $URL = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1", $URL );
    $URL = preg_replace( array("`[^a-z0-9]`i","`[-]+`") , "-", $URL);
    return strtolower(trim($URL, '-')).".html";
}
// http://www.webcheatsheet.com/PHP/get_current_page_url.php
// Obtiene la URL actual, $stripArgs determina si eliminar la parte dinamica de la URL
function curPageURL($stripArgs=false,$friendly=false,$forzar_ssl=false) {
$pageURL = '';
if (!$friendly)
{
   $pageURL = 'http';

   if ((ES_SSL() || $forzar_ssl) && $forzar_ssl != 'nunca') {$pageURL .= "s";}
   $pageURL .= "://";
}

$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

if ($stripArgs) {$pageURL = preg_replace("/\?.*/", "",$pageURL);}

if ($friendly)
{
    $pageURL = preg_replace('/www\./', '',$pageURL);
    $pageURL = "www.$pageURL";
}

return $pageURL;
}

function domain($forzar_ssl = false) {
$pageURL = 'http';
if ((ES_SSL() || $forzar_ssl) && $forzar_ssl != 'nunca') {$pageURL .= "s";}
$pageURL .= "://";
$pageURL .= $_SERVER["SERVER_NAME"];
$pageURL = preg_replace('/www\./', '',$pageURL);
$pageURL .= "/";
return $pageURL;
}

// http://www.php.net/manual/en/function.mt-rand.php#106645
function genRandomString($length = 10) {
    $chars = 'fghjkraeou';
    $result = '';
    
    for ($p = 0; $p < $length; $p++)
    {
        $result .= ($p%2) ? $chars[mt_rand(6, 9)] : $chars[mt_rand(0, 5)];
    }
    
    return $result;
}

// Wrapper de envío de correo electrónico. HTML/utf-8
function correo($para, $asunto, $mensaje,$exHeaders=null)
{
    
    if (correoSMTP($para, $asunto, $mensaje))
        return;
    
    $headers = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=UTF-8' . "\r\n" . 'Date: '.date("r") . "\r\n";
    $headers .= 'From: '. PROY_MAIL_POSTMASTER . "\r\n";
    
    if (!empty($exHeaders))
    {
        $headers .= $exHeaders;
    }
    $mensaje = sprintf('<html><head><title>%s</title></head><body>%s</body>',PROY_NOMBRE,$mensaje);
    return mail($para,'=?UTF-8?B?'.base64_encode($asunto).'?=',$mensaje,$headers);
}

/*
  Copyright (c) 2008, reusablecode.blogspot.com; some rights reserved.
 
  This work is licensed under the Creative Commons Attribution License. To view
  a copy of this license, visit http://creativecommons.org/licenses/by/3.0/ or
  send a letter to Creative Commons, 559 Nathan Abbott Way, Stanford, California
  94305, USA.
  */
 
// Luhn (mod 10) algorithm
function luhn($input)
{
    if (strlen($input) < 10)
        return false;
    
    $sum = 0;
    $odd = strlen($input) % 2;
     
    // Remove any non-numeric characters.
    if (!is_numeric($input))
        $input = preg_replace("/[^\d]/", "", $input);
     
    // Calculate sum of digits.
    for($i = 0; $i < strlen($input); $i++)
    {
        $sum += $odd ? $input[$i] : (($input[$i] * 2 > 9) ? $input[$i] * 2 - 9 : $input[$i] * 2);
        $odd = !$odd;
    }
     
    // Check validity.
    return ($sum % 10 == 0) ? true : false;
}

//Wrapper de envío de correo usando PHPMailer
function correoSMTP($para, $asunto, $mensaje, $html=true, $extra=null, $replyto = null)
{
    require_once(__BASE__ARRANQUE.'PHP/class.phpmailer.php');
    $Mail               = new PHPMailer();
    $Mail->IsHTML       ($html) ;
    $Mail->SetLanguage  ("es", __BASE__ARRANQUE.'PHP/language/');
    $Mail->PluginDir	= __BASE__ARRANQUE.'PHP/';
    $Mail->Mailer	= 'smtp';
    $Mail->Host		= "smtp.gmail.com";
    $Mail->SMTPSecure    = "ssl";
    $Mail->Port		= 465;
    $Mail->SMTPAuth	= true;
    $Mail->Username	= smtp_usuario;
    $Mail->Password	= smtp_clave;
    $Mail->CharSet	= "utf-8";
    $Mail->Encoding	= "quoted-printable";
    
    if ( isset($replyto['nombre']) && isset($replyto['correo']) )
    {
        $Mail->AddReplyTo( $replyto['correo'], $replyto['nombre'] );
    }
    
    $Mail->SetFrom	( PROY_MAIL_POSTMASTER, PROY_MAIL_POSTMASTER_NOMBRE );
    $Mail->Subject	= $asunto;
    $Mail->Body		= $mensaje;

    // Veamos si hay que hacer embed de imagenes
    if (is_array($extra))
    {
        foreach ($extra as $archivo)
        {
            $Mail->AddEmbeddedImage($archivo['ruta'], $archivo['cid'], $archivo['alt'], "base64", $archivo['tipo']);
        }
    }
    
    $correos = preg_split('/,/',$para);
    foreach($correos as $correo)
        $Mail->AddAddress ($correo);

    $x = $Mail->Send();
    
    if ($x)
       return $x;
    else
       return 0;
}

function correo_x_interes($asunto, $mensaje)
{
    $c = sprintf('SELECT `correo` FROM %s  WHERE `correo` <> ""', db_prefijo.'correo_oferta');
    $r = db_consultar($c);
    while ($f = mysqli_fetch_assoc($r)) {
        if (correo($f['correo'],PROY_NOMBRE." - $asunto",$mensaje));
        echo "Enviado: ".$f['correo']."<br />";
    }

    
    $c = 'SELECT `correo_contacto` FROM `flores_SSL_compra_contenedor` WHERE `correo_contacto` <> ""';
    $r = db_consultar($c);
    while ($f = mysqli_fetch_assoc($r)) {
        if (correo($f['correo_contacto'],PROY_NOMBRE." - $asunto",$mensaje))
        echo "Enviado: ".$f['correo_contacto']."<br />";
    }
}

function HEAD_JS()
{
    global $arrJS;
    $buffer = '';
    foreach ($arrJS as $JS)
        $buffer .= '<script type="text/javascript" src="'.PROY_URL_ESTATICA.'JS/'.$JS.'.js"></script>'."\n";

    echo $buffer;
}

function HEAD_CSS()
{
    global $arrCSS;
    $buffer = '';
    foreach ($arrCSS as $CSS)
        $buffer .= '<link rel="stylesheet" type="text/css" href="'.PROY_URL_ESTATICA.$CSS.'.css" />'."\n";
    
    echo $buffer;
}

function HEAD_EXTRA()
{
    global $arrHEAD;
    echo "\n";
    echo implode("\n",$arrHEAD);
    echo "\n";
}

function ACTIVAR_PAQUETES(array $paquetes)
{
    global $arrJS, $arrCSS;
    
    foreach ($paquetes as $paquete)
    {
        switch($paquete)
        {
            case 'ui':
                $arrJS[] = 'jquery.ui';
                $arrCSS[] = 'CSS/ui/jquery-ui';
                break;
            
            case 'facebox':
                $arrJS[] = 'jquery.facebox';
                $arrCSS[] = 'CSS/facebox';
                break;
        }
    }
}

function SI_ADMIN($texto)
{
    if (_F_usuario_cache('nivel') == _N_administrador)
    {
        return $texto;
    }
}

function protegerme($solo_salir=false,$niveles=array())
{
    if (_F_usuario_cache('nivel') == _N_administrador || in_array(_F_usuario_cache('nivel'),$niveles))
        return;
    
    // Tokens - 4-Ene-2013
    if ( isset($_GET['token']) && strlen($_GET['token']) == 40 )
    {        
        if ( _F_usuario_via_token(db_codex($_GET['token'])) )
        {
            return;
        }
    }
    if (!$solo_salir)
        header('Location: '. PROY_URL.'iniciar?ref='.curPageURL());
 
    ob_end_clean();
    exit;
}

function cargar_editable($archivo,$link=true,$noMCE=false,$include=true)
{
    if ($include)
        include(__BASE__.'/TXT/'.$archivo.'.editable');
    else
        readfile(__BASE__.'/TXT/'.$archivo.'.editable');

    if ($noMCE)
        $archivo = $archivo.'&noMCE=1';

    if ($link)
        echo SI_ADMIN('<div style="clear:both;display:block;margin:10px 0"><a class="btnlnk" href="'.PROY_URL.'editar?archivo='.$archivo.'">~editar</a></div>');
}

/**
 * http://www.php.net/manual/en/function.unlink.php#87045
 * Recursively delete a directory
 *
 * @param string $dir Directory name
 * @param boolean $deleteRootToo Delete specified top-level directory as well
 */
function unlinkRecursive($dir, $deleteRootToo)
{
    if(!$dh = @opendir($dir))
    {
        return;
    }
    while (false !== ($obj = readdir($dh)))
    {
        if($obj == '.' || $obj == '..')
        {
            continue;
        }

        if (!@unlink($dir . '/' . $obj))
        {
            unlinkRecursive($dir.'/'.$obj, true);
        }
    }

    closedir($dh);

    if ($deleteRootToo)
    {
        @rmdir($dir);
    }

    return;
}

function __regexar($regex)
{
    return '/'.$regex.' /i';
}

/*Tweeter*/
function tweet($status)
{
    $username = 'flor360';
    $password = '22436017';

    if (!$status)
        return false;

    $tweetUrl = 'http://www.twitter.com/statuses/update.xml';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "$tweetUrl");
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "status=$status");
    curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");

    $result = curl_exec($curl);
    $resultArray = curl_getinfo($curl);

    echo $result;

    curl_close($curl);

    return ($resultArray['http_code'] == 200);
}

function imagen_URL($HASH, $ancho, $alto, $servidor=null)
{
    if (!(defined('_B_FORZAR_SERVIDOR_IMG_NULO')) && ($servidor === null))
    {
        $servidor = 'img'.(hexdec(substr(md5($HASH),0,1)) % 2).'.';
    }
    else
    {
        $servidor = '';
    }

    return preg_replace('/(https?:\/\/)/','$1'.$servidor, PROY_URL_ESTATICA) . 'imagen_'.$ancho.'_'.$alto.'_'.$HASH.'.jpg';
}

function imagen_URL_2($HASH, $ancho, $alto, $prefijo = 'http://')
{
    return $prefijo . PROY_URL_NOPROTOCOL . 'imagen_'.$ancho.'_'.$alto.'_'.$HASH.'.jpg';
}

function sms($num,$msj)
{
    $msj = preg_replace('/\s/','.',$msj);
    $sesion = file_get_contents('http://interactivo.mensajito.com/interactivo_sv/client.php?orden=1&nick='.$msj.'&foo='.rand(1,9999));
    $sesion = str_replace('session=','',$sesion);
    return file_get_contents('http://interactivo.mensajito.com/interactivo_sv/client.php?orden=2&session='.$sesion.'&nick='.$msj.'&dstphone=503'.$num.'&pin=undefined&foo='.rand(1,9999));
}

function obtener_heuristicas($f)
{
    // Heuristicas
    $heuristica = array();
    $arrSub = array();
    
    // Anónimo
    if (@$f['anonimo'] == '1' || preg_match_all('/.*(an.nim|sin firma|no decir).*/ism',$f['tarjeta_cuerpo'].' '.$f['direccion_entrega'].' '.$f['usuario_notas'],$arrSub))
    {   
        $heuristica['anonimo'] = array('img' => 'anonimo', 'texto' => 'Anónimo');
    }
    
    // Este envío necesita algún objeto que proporcionará el cliente para estar completo
    if (preg_match_all('/.*(llamar|contactar|enviar|correo|recoger|pasar(a|e|á|é)).*/ism',$f['tarjeta_cuerpo'].' '.$f['direccion_entrega'].' '.$f['usuario_notas'],$arrSub))
    {   
        $heuristica['cliente'] = array('img' => 'cliente', 'texto' => 'Este envío necesita algún objeto que proporcionará el cliente para estar completo');
    }
    
    // Necesita factura o algún comprobante
    if (preg_match_all('/.*(factura|recibo|compro(v|b)ante|.oucher|f.scal\s|cr.dito).*/ism',$f['direccion_entrega'].' '.$f['usuario_notas'].' '.$f['estado_notas'],$arrSub))
    {   
        $heuristica['factura'] = array('img' => 'factura', 'texto' => 'Necesita factura o algún comprobante');
    }

    // Hay algo especial que hacer con la tarjeta de este pedido
    if (preg_match_all('/.*(tarjeta|dedicatoria).*/ism',$f['tarjeta_cuerpo'].' '.$f['usuario_notas'],$arrSub))
    {
        $heuristica['tarjeta'] = array('img' => 'tarjeta', 'texto' => 'Hay algo especial que hacer con la tarjeta de este pedido');
    }

    // Este pedido necesita algo de el kiosko
    if (preg_match_all('/.*(kiosko|quiosco|kiosco|gran v(í|i)a).*/ism',$f['tarjeta_cuerpo'].' '.$f['usuario_notas'],$arrSub))
    {   
        $heuristica['kiosko'] = array('img' => 'kiosko', 'texto' => 'Este pedido necesita algo de el kiosko');
    }
     
    // Este pedido lleva globos
    if (preg_match_all('/.*(glob|vejiga|vegiga).*/ism',$f['tarjeta_cuerpo'].' '.$f['usuario_notas'].' '.$f['receta'].' '.$f['extras'],$arrSub))
    {   
        $heuristica['globos'] = array('img' => 'globos', 'texto' => 'Este pedido lleva globo');
    }
    
    // Este pedido lleva peluche
    if (preg_match_all('/.*(peluc|\soso\s|\sosito|tedd?(yi)|bear).*/ism',$f['tarjeta_cuerpo'].' '.$f['usuario_notas'].' '.$f['receta'].' '.$f['extras'],$arrSub))
    {   
        $heuristica['peluche'] = array('img' => 'peluche', 'texto' => 'Este pedido lleva peluche');
    }
    
    // Este pedido lleva dulces
    if (preg_match_all('/.*(choco|fererro|ferero|roche|dulce|bonbon|bon o bon|bom o bom|shows|shaw).*/ism',$f['tarjeta_cuerpo'].' '.$f['usuario_notas'].' '.$f['receta'].' '.$f['extras'],$arrSub))
    {   
        $heuristica['dulces'] = array('img' => 'dulce', 'texto' => 'Este pedido lleva dulces o chocolates');
    }
    
    // Este pedido lleva alcohol
    if (preg_match_all('/.*(alcohol|\svino|tequila|\sron\s|vodka|cerveza|beer|sunset|botella).*/ism',$f['tarjeta_cuerpo'].' '.$f['usuario_notas'].' '.$f['receta'].' '.$f['extras'],$arrSub))
    {   
        $heuristica['alcohol'] = array('img' => 'alcohol', 'texto' => 'Este pedido lleva algun tipo de alcohol');
    }
    
    if (!preg_match_all('/.*(cualquier hora|transcurso del d(í|i|ì)a).*/ism',$f['usuario_notas'],$arrSub))
    {
        if (!preg_match_all('/.*(pronto|antes|ma(n|ñ)i?ana|tempran|[0-9]\s?am).*/ism',$f['usuario_notas'],$arrSub) && preg_match_all('/.*(tarde|despues|medio\s?d(í|i|ì)a|(ú|u|ù)ltim|(\s|\d)p\.?m\.?).*/ism',$f['usuario_notas'],$arrSub)) {
            // parece que este envío es para la tarde  o tiene hora exacta
            $heuristica['tarde'] = array('img' => 'tarde', 'texto' => 'Este envío es para la tarde  o tiene hora exacta');
        } elseif (preg_match_all('/.*(pronto|[0-9]\s?am|ma(n|ñ)i?ana|tempran|exacta|antes|urge|primer|sale|sin falta|(\s|\d)a\.?m\.?[^\w]|hora|\d+\:\d+).*/ism',$f['usuario_notas'],$arrSub)) {
            // parece que este envío es para la mañana o tiene hora exacta
            $heuristica['temprano'] = array('img' => 'temprano', 'texto' => 'Este envío es para la mañana o tiene hora exacta');
        }
    }
    
    // Lleva flores personalizadas o algo adicional
    if (preg_match_all('/.*(color|adicional|flores|ramo|llev(o|a)|cambiar|cargo|ferrero|globo|lirio|base|roj(o|a)|amarill(o|a)|salm(ó|o|ò)n|naranja|verde|azul|gris|blanc(o|a)|negr(o|a)|mixta|multicolor|circo|pintad(o|a)|\$\d+).*/ism',$f['usuario_notas'],$arrSub))
    {
        $heuristica['personalizado'] = array('img' => 'personalizado', 'texto' => 'Lleva flores personalizadas o algo adicional');
    }

    // difunto?
    if (preg_match_all('/.*(fallecido|dfto|difunto|funera|muerto|pesame).*/ism',$f['tarjeta_para'].' '.$f['tarjeta_cuerpo'].' '.$f['usuario_notas'].' '.$f['extras'].' '.$f['direccion_entrega'],$arrSub))
    {
        $heuristica['difunto'] = array('img' => 'cruz', 'texto' => 'Difunto');
    }
    
    $heuristicas = '';
    if (count($heuristica) > 0)
    {
        foreach ($heuristica as $dato)
            $heuristicas .= '<img src="/IMG/heuristica/'.$dato['img'].'.png" title="'.$dato['texto'].'" />';       
    }
    
    return array($heuristica, $heuristicas);
    
}

function encode_items(&$item, $key)
{
    //$item = iconv("ISO-8859-1", "UTF-8", $item);
}

function stripslashes_deep($value)
{
    $value = is_array($value) ?
                array_map('stripslashes_deep', $value) :
                stripslashes($value);

    return $value;
}

/**
* Checks if the given words is found in a string or not.
* 
* @param Array $words The array of words to be given.
* @param String $string The string to be checked on.
* @param String $option all - should have all the words in the array. any - should have any of the words in the array
* @return boolean True, if found, False if not found, depending on the $option
*/
function in_string ($words, $string, $option)
{
    if ($option == "all")
    {
        foreach ($words as $value)
            if (stripos($string, $value) === false)
                return false;
        return true;
    } else {
        foreach ($words as $value)
            if (stripos($string, $value) !== false)
                return true;
        return false;
    }
}

function mini_bench_to($arg_t, $arg_ra=false) 
{
  $tttime=round((end($arg_t)-$arg_t['start'])*1000,4);
  if ($arg_ra) $ar_aff['total_time']=$tttime;
  else $aff="total time : ".$tttime."ms\n";
  $prv_cle='start';
  $prv_val=$arg_t['start'];

  foreach ($arg_t as $cle=>$val)
  {
      if($cle!='start')    
      {
          $prcnt_t=round(((round(($val-$prv_val)*1000,4)/$tttime)*100),1);
          if ($arg_ra) $ar_aff[$prv_cle.' -> '.$cle]=$prcnt_t;
          $aff.=$prv_cle.' -> '.$cle.' : '.$prcnt_t."% (".round(($val-$prv_val)*1000,0)."ms)\n";
          $prv_val=$val;
          $prv_cle=$cle;
      }
  }
  if ($arg_ra) return $ar_aff;
  return $aff;
}

?>
