<?php
if(!defined('__BASE__')) die('Error, no puede arrancar el sistema');
if(!defined('__BASE_cePOSa__')) define('__BASE_cePOSa__',str_replace('//','/',dirname(__FILE__).'/'));

/* todos los stubs vitales */
require_once (__BASE_cePOSa__."PHP/vital.php");

/* CAPTURAR <body> */
require_once (__BASE_cePOSa__."PHP/body.php");

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
    <link href="<?php echo __FAVICON__; ?>.ico" rel="icon" type="image/x-icon" />
    <meta name="viewport" content="width=device-width,initial-scale=0.5,minimum-scale=0.5" />
    <link rel="canonical" href="<?php echo PROY_URL_ACTUAL; ?>" />
<?php HEAD_CSS(); HEAD_JS(); HEAD_EXTRA(); ?>
</head>
<?php
$HEAD = ob_get_clean();

/* MOSTRAR TODO */
echo $HEAD.$BODY;
?>
