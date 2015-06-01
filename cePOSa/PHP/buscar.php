<?php

$COMUN = 'SELECT CONCAT("$",(IF(MIN(pv.precio_oferta)=MAX(pv.precio_oferta),pv.precio_oferta,CONCAT(MIN(pv.precio_oferta), " - $",MAX(pv.precio_oferta))))) AS "precio_oferta_combinado", AVG(pv.precio_oferta) AS "tiene_oferta", CONCAT("$",(IF(MIN(pv.precio)=MAX(pv.precio),pv.precio,CONCAT(MIN(pv.precio), " - $",MAX(pv.precio))))) AS "precio_combinado", pv.foto AS "variedad_foto", IF(pc.titulo="","sin titulo",pc.titulo) AS "contenedor_titulo", pc.descripcion AS "contenedor_descripcion", pc.codigo_producto, pc.color, pc.creacion, pv.codigo_variedad';

$GROUP = 'GROUP BY variedad_foto';

$ORDER_BY = '';

$busqueda = db_codex(strtolower(trim($_GET['busqueda'])));
if (empty($busqueda))
{
    echo 'Búsqueda inválida';
    return;
}


if (strstr($busqueda,'de:'))
{
    $busqueda = str_replace('de:','',$busqueda);
    header('Location: ' . PROY_URL.'ventas?de='.rawurlencode(trim($busqueda)));
    ob_end_clean();
    exit;
}

if (strstr($busqueda,'para:'))
{
    $busqueda = str_replace('para:','',$busqueda);
    header('Location: ' . PROY_URL.'ventas?para='.rawurlencode(trim($busqueda)));
    ob_end_clean();
    exit;
}

// Será que esta búscando un arreglo por código?
if (is_numeric($busqueda))
{
    // Hacemos el intento de búsqueda directa del código
    $WHERE = 'pc.codigo_producto = "'.$busqueda.'"';
    $r = buscar(false);

    if (mysqli_num_rows($r))
    {
        $f = mysqli_fetch_assoc($r);
        header('Location: ' . URL_SUFIJO_VITRINA.SEO($f['contenedor_titulo'].'-'.$f['codigo_producto']));
        ob_end_clean();
        exit;
    }

    // No encontramos coincidencia directa, no intentar parcial en codigo_producto;

}

// Será que esta búscando un pedido por su Codigo de Compra.Salt?
if (preg_match('/[0-9]{1,9}[a-z]{3,4}/',strtolower($busqueda)))
{
    if (S_iniciado())
        header('Location: '.PROY_URL.'ventas?cc='.sha1(strtolower($busqueda)));
    else
        header('Location: '.PROY_URL.'informacion?tipo=estado&pin='.sha1(strtolower($busqueda)));
    return;
}

echo '<h1>Resultados de búsqueda</h1>';

// Ok, no era un numero ni búsqueda por Código de compra/Facturacion, intento un texto, pero no debe ser menor de 3 letras
if (strlen($busqueda)<3)
{
    echo 'Texto de búsqueda demasiado corto';
    return;
}

$arrJS[] = 'jquery.lazyload';

$cl = new SphinxClient();
$cl->SetServer( "localhost", 9312 );
$cl->SetMatchMode( SPH_MATCH_ANY  );
$cl->SetLimits(0,1000, 1000);

// Probamos la primera busqueda de forma amplia
$result = $cl->Query( preg_replace('/([[:alnum:]]{4,})/','$1*',$busqueda), 'f360_contenedor' );

// No salio nada?, demole oportunidar a Sphinx con el texto completo
$result2 = $cl->Query($busqueda, 'f360_contenedor' );


echo $cl->getLastError();
echo $cl->getLastWarning();

if ( (!is_array($result) || empty($result["matches"])) && (!is_array($result2) || empty($result2["matches"])) ) {
    echo '<p>No se encontraron arreglos que coincidiera con el texto búscado</p>';
} else {
    
    $resultado = join(',', array_unique(array_merge (array_keys($result["matches"]),array_keys($result2["matches"]))) );

    // echo '<p>C.'.count(array_unique(array_merge (array_keys($result["matches"]),array_keys($result2["matches"])))).' - '.$resultado.' </p>';
    
    $WHERE = 'pv.codigo_variedad IN ('.$resultado.')';
    
    $ORDER_BY = 'ORDER BY FIELD (pv.codigo_variedad, '.$resultado.')';
    
    $r = buscar();
    $nResultadosPorTexto = mysqli_num_rows($r);
    if ($nResultadosPorTexto)
    {
        echo '<p>Se encontraron los siguientes productos que concuerdan con su texto de búsqueda ('.$busqueda.'), a continuación se muestran los resultados.</p>';
        echo Rejilla_Resultados($r);
    } else {
        
    }
}
// Registremos la búsqueda y fin!
/* INET_ATON = Adress 2 Number; INET_NTOA = Number 2 Adress */

@$c = sprintf('INSERT INTO %s (codigo_busqueda,ip,texto_buscado,fecha,referencia) VALUES (NULL,INET_ATON("%s"),"%s",NOW(),"%s")',db_prefijo.'busquedas',$_SERVER['REMOTE_ADDR'], $busqueda, db_codex(@$_SERVER['HTTP_REFERER']));
@db_consultar($c);

function buscar($solo_si_mostrar_en_vitrina = true)
{
    global $COMUN, $WHERE, $GROUP, $ORDER_BY;
    
    if ($solo_si_mostrar_en_vitrina)
        $WHERE .= ' AND `mostrar_en_vitrina` = 1';
    
    $c = sprintf('%s FROM '.db_prefijo.'producto_variedad AS pv LEFT JOIN '.db_prefijo.'producto_contenedor AS pc USING(codigo_producto) LEFT JOIN `flores_productos_categoria` AS pcat USING(codigo_producto) LEFT JOIN `flores_categorias` AS cat USING(codigo_categoria) WHERE %s %s %s', $COMUN, $WHERE, $GROUP, $ORDER_BY);
    
    return db_consultar($c);
}
?>
<script>
    $(function () {
        $('img.lazy').lazyload({ threshold : 500, effect : "fadeIn" });
    });
</script>

