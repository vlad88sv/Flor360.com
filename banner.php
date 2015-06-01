<?php
    function DEPURAR(){}

    require_once ("config.php");
    require_once (__BASE__ARRANQUE."PHP/stubs.php");
    require_once (__BASE__ARRANQUE."PHP/const.php");
    require_once (__BASE__ARRANQUE."PHP/db.php");

    define('_B_FORZAR_SERVIDOR_IMG_NULO',true);

    if (empty($_GET['img_alto']))
        $_GET['img_alto'] = 200;

    $CAMPOS[] = 'CONCAT("$",(IF(MIN(pv.precio)=MAX(pv.precio),pv.precio,CONCAT(MIN(pv.precio), " - $",MAX(pv.precio))))) AS "precio_combinado"';
    $CAMPOS[] = 'pv.foto AS "variedad_foto"';
    $CAMPOS[] = 'pv.receta AS "variedad_receta"';
    $CAMPOS[] = 'IF(pc.titulo="","sin titulo",pc.titulo) AS "contenedor_titulo"';
    $CAMPOS[] = 'pc.descripcion AS "contenedor_descripcion"';
    $CAMPOS[] = 'pc.codigo_producto';

    $FROM = sprintf('FROM %s AS pc LEFT JOIN %s AS pv USING(codigo_producto)',db_prefijo.'producto_contenedor',db_prefijo.'producto_variedad');

    $c = sprintf('SELECT %s %s WHERE descontinuado="no" GROUP BY pv.codigo_producto ORDER BY RAND(curdate()+0) LIMIT 5',join(', ',$CAMPOS),$FROM);
    $r = db_consultar($c);

    while ($f = mysqli_fetch_assoc($r))
    {
        $link = PROY_URL.URL_SUFIJO_VITRINA.SEO(strip_tags($f['contenedor_titulo']).'-'.$f['codigo_producto']);
        $src = imagen_URL($f['variedad_foto'],0,$_GET['img_alto']);
        $title = htmlentities(strip_tags($f['contenedor_descripcion']).' - Precio: '.$f['precio_combinado'],ENT_QUOTES,'UTF-8');

        $buffer[] = sprintf('<a href="%s" rel="nofollow" target="_blank" /><img src="%s" title="%s" /></a>',$link,$src,$title);
    }
    echo '<style>img{border:none;}</style>';
    echo join(' ',$buffer);
?>
