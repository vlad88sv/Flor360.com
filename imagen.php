<?php
ini_set('memory_limit', '128M');
set_time_limit(60);

if (!isset($_GET['tipo'])) $tipo = 'normal';

switch($_GET['tipo'])
{
    case 'normal':
        IMAGEN_tipo_normal();
        break;
    case 'tcredito':
        IMAGEN_tipo_tcredito();
        break;
    case 'random':
        IMAGEN_tipo_random();
        break;
}

function IMAGEN_tipo_tcredito()
{
    require_once ("config.php");
    require_once(__BASE__ARRANQUE.'PHP/vital.php');
    protegerme();
    
    // No se puede usar cache a nivel de SQL porque el numero puede cambiar pero la transaccion es igual
    $c = sprintf('SELECT AES_DECRYPT(`n_credito`,"%s") AS n_credito_DAES, pin_4_reverso_t_credito, fecha_exp_t_credito, correo_contacto FROM `flores_SSL_compra_contenedor` WHERE transaccion="%s"',db__key_str,db_codex($_GET['pin']));
    $r = db_consultar($c);
    $f = mysqli_fetch_assoc($r);
    $string = preg_replace('/(\d{4})(\d{4})(\d{4})(\d{3,4})/','$1-$2-$3-$4',$f['n_credito_DAES']);
       
    $_GET['cobrado'] = isset($_GET['cobrado']) ? 1 : 0;
    
    if (db_contar('blacklist', 'tarjeta="'.$f['n_credito_DAES'].'"') > 0)
      $_GET['cobrado'] = 2; // Calavera - es robada

    if (db_contar('blacklist_correo', 'correo="'.$f['correo_contacto'].'"') > 0)
      $_GET['cobrado'] = 2; // Calavera - es robada
    
    $cachefile = 'IMG/cache/tc.'.md5($_GET['cobrado'].$string.$f['fecha_exp_t_credito']);
    
    if(!file_exists($cachefile)) {
        $im = ImageCreate(max((int)(strlen($string)) * 9,171), 32);
        switch ($_GET['cobrado'])
        {
            case 2:
                $background_color = ImageColorAllocate ($im, 0, 255, 0);
                $f['fecha_exp_t_credito'] = 'FRAUDE - ESTAFA';
                break;
                
            case 1:
                $background_color = ImageColorAllocate ($im, 255, 204, 204);
                break;
          
            case 0:
                $background_color = ImageColorAllocate ($im, 224, 230, 255);
                break;
        }       

        $text_color = ImageColorAllocate ($im, 0, 0, 0);
        ImageString ($im, 5, 0, 0, $string, $text_color);
        ImageString ($im, 5, 0, 16, $f['fecha_exp_t_credito'], $text_color);
        
        imagetruecolortopalette($im, false, 2);
        ImagePNG($im, $cachefile, 9,  PNG_ALL_FILTERS);
        ImageDestroy($im);
    }
    
    header("Accept-Ranges: bytes",true);
    header("Content-Length: ".filesize($cachefile),true);
    header("Keep-Alive: timeout=15, max=100",true);
    header("Connection: Keep-Alive",true);
    header("Content-type: image/png",true);
    
    $fp = fopen($cachefile, 'rb');
    fpassthru($fp);
    
}
function IMAGEN_tipo_normal()
{
    $escalado = ('IMG/i/m/'.$_GET['ancho'].'_'.$_GET['alto'].'_'.$_GET['sha1']);
    $origen = 'IMG/i/'.$_GET['sha1'];
    $ancho = $_GET['ancho'];
    $alto = $_GET['alto'];

    if(!file_exists($origen))
    {
        error_log('No existe tal imagen ['.$origen.']');
        return;
    }

    if(@($ancho*$alto) > 562500)
    {
        error_log("La imagen solicitada excede el límite de este servicio. $ancho x $alto");
        return;
    }

    if($ancho > 800 || $alto > 800)
    {
        error_log("La imagen solicitada excede el límite de este servicio. $ancho x $alto");
        return;
    }
    
    if (!file_exists($escalado))
    {
        require_once ("config.php");
        require_once(__BASE__ARRANQUE.'PHP/phmagick/phmagick.php');
        $phMagick = new phMagick ($origen, $escalado);
        
        if(@($ancho*$alto) <= 122500)
        {
            // Es mas un thumbnail
            $phMagick->superthumbnail($ancho,$alto);
        } else {
            // Es una imagen grande
            $phMagick->resize($ancho,$alto,true);
            //$phMagick->watermark('IMG/stock/marca.png', phMagickGravity::Center, 100);
        }
    }

    header("Accept-Ranges: bytes",true);
    header("Content-Length: ".filesize($escalado),true);
    header("Keep-Alive: timeout=15, max=100",true);
    header("Connection: Keep-Alive",true);
    header("Content-Type: image/jpeg",true);

    readfile($escalado);
}

function IMAGEN_tipo_random()
{
    require_once ("config.php");
    require_once (__BASE__ARRANQUE."PHP/vital.php");

    $archivo = 'estatico/imagen_sms.todosv.com.jpg';

    $c = 'SELECT DISTINCT codigo_producto, foto FROM flores_producto_variedad ORDER BY RAND() LIMIT 3';
    $r = db_consultar($c);

    $canvas = imagecreatetruecolor(336,168);
    $x = 0;
    while($f = mysqli_fetch_assoc($r))
    {
        $foto  = imagecreatefromjpeg('IMG/i/'.$f['foto']);
        imagecopyresampled($canvas,$foto,$x,0,0,0,112,168,imagesx($foto),imagesy($foto));
        imagedestroy($foto);
        $x += 112;
    }
    $logo = imagecreatefrompng("estatico/logo_difuso.png");
    imagecopy($canvas,$logo,0,0,0,0,336,168);
    imagejpeg($canvas,$archivo,65);
    imagedestroy($canvas);
}
exit;
?>