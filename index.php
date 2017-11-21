<?php
$__BENCH__['start'] = microtime(true);

require_once('vendor/autoload.php');
$firephp = FirePHP::getInstance(true);
$firephp->registerErrorHandler($throwErrorExceptions=false);
$firephp->registerExceptionHandler();
$firephp->registerAssertionHandler($convertAssertionErrorsToExceptions=true,$throwAssertionExceptions=false);

switch (@$_SERVER["SERVER_NAME"]) {
    case 'condolencias.tk':
    case 'condolencias.com.sv':
        if (empty($_GET['peticion'])) $_GET['peticion'] = 'portada.condolencias';
        require_once('config.condolencias.php');
        break;
    default:
        if (empty($_GET['peticion'])) $_GET['peticion'] = 'portada';
        require_once('config.php');
        break;
}

if (empty($_GET['peticion'])) $_GET['peticion'] = __PORTADA__;

require_once(__BASE__ARRANQUE.'arranque.php');
$__BENCH__['end'] = microtime(true);
?>
