<?php
$traduccion = '';
$_GET['peticion'] = preg_replace('/\s/','+',$_GET['peticion']);

switch ($_GET['peticion'])
{
    case 'portada':
        $traduccion = 'portada';
        break;
    case 'portada.condolencias':
        $traduccion = 'portada.condolencias';
        break;
    case 'iniciar':
        $traduccion = 'inicio';
        break;
    case 'finalizar':
        _F_sesion_cerrar();
        break;
    case 'tour':
        $traduccion = 'tour';
        break;
    case 'buscar':
        $traduccion = 'buscar';
        break;
    case 'categoria':
        $traduccion = 'categoria';
        break;
    case '+registro':
        $traduccion = 'registro';
        break;
    case '+contenedores':
        $traduccion = 'gestor_contenedores';
        break;
    case '+variedades':
        $traduccion = 'gestor_variedades';
        break;
    case '+categorias':
        $traduccion = 'gestor_categorias';
        break;
    case '+accesorios':
        $traduccion = 'gestor_accesorios';
        break;
    case '+productos_categoria':
        $traduccion = 'gestor_productos_categoria';
        break;
    case '+novisibles':
        $traduccion = 'arreglos_sin_categoria';
        break;
    case '+filtros':
        $traduccion = 'gestor_filtros';
        break;
    case '+menu':
        $traduccion = 'gestor_menu';
        break;
    case '+administracion':
        $traduccion = 'administracion';
        break;
    case '+compras':
        $traduccion = 'ssl.gestor_compras';
        break;
    case '+cupones':
        $traduccion = 'gestor_cupones';
        break;
    case '+cupones_producto':
        $traduccion = 'gestor_cupones_producto';
        break;
    case 'comprar':
        $traduccion = 'ssl.compras';
        break;
    case 'ventas':
        $traduccion = 'ssl.ventas';
        break;
    case 'vitrina':
        $traduccion = 'vitrina';
        break;
    case 'ayuda':
        $traduccion = 'ayuda';
        break;
    case 'editar':
        $traduccion = 'carchivo';
        break;
    case 'verificar':
        $traduccion = 'verificar';
        break;
    case 'contactanos':
        $traduccion = 'contactanos';
        break;
    case '+estadisticas':
        $traduccion = 'estadisticas';
        break;
    case '+massmail':
        $traduccion = 'massmail';
        break;
    case '+impresion':
        $traduccion = 'impresion';
        break;
    case '+notificacion':
        $traduccion = 'notificacion';
        break;
    case 'informacion':
        $traduccion = 'ssl.compras';
        break;
    case '+caja':
        $traduccion = 'caja';
        break;
    case '+articulos':
        $traduccion = 'gestor_articulos';
        break;
    case 'cortez':
        $traduccion = 'cortez';
        break;
    case 'historial_cortez':
        $traduccion = 'historial_cortez';
        break;
    case 'historial_cortex':
        $traduccion = 'historial_cortex';
        break;
    case '+stock':
        $traduccion = 'stock';
        break;
    case '+chat':
        $traduccion = 'chat';
        break;
    case '+rifa':
        $traduccion = 'gestor_rifas';
        break;
    case 'fb':
        $traduccion = 'fb';
        break;
    case '+presencia':
        $traduccion = 'presencia';
        break;
    case '+info':
        $traduccion = 'informacion';
        break;
    case 'lista':
        $traduccion = 'lista';
        break;
    case '+geo':
        $traduccion = 'geo';
        break;
    case '+barcode':
        $traduccion = 'barcode';
        break;
    case '+anexo':
        $traduccion = 'caja.anexo';
        break;
    case '+util.tabla':
        $traduccion = 'util.tabla';
        break;
    default:
        $traduccion = '404';
}

if (!file_exists(__BASE_cePOSa__.'PHP/'.$traduccion.'.php'))
    $traduccion = '404';

require_once(__BASE_cePOSa__.'PHP/'.$traduccion.'.php');
?>
