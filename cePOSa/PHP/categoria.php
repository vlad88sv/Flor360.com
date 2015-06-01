<?php
    $arrJS[] = 'jquery.lazyload';
    
    $modoHorizontal = false;
    $ocultar_ocasion = true;
    $ocultar_tipo = true;
    
    /* Tablas */
    // pc = _producto_contenedor
    // pv = _producto_variedad
    // pcat = _productos_categoria
    // cat = _categorias

    /* Campos que utilizaremos */
    // Diff. de precios
    $CAMPOS[] = 'IF(MIN(pv.precio)=MAX(pv.precio),CONCAT("$",pv.precio),CONCAT("Desde $" , MIN(pv.precio))) AS "precio_combinado"';
    $CAMPOS[] = 'CONCAT("$",(IF(MIN(pv.precio_oferta)=MAX(pv.precio_oferta),pv.precio_oferta,CONCAT(MIN(pv.precio_oferta), " - $",MAX(pv.precio_oferta))))) AS "precio_oferta_combinado"';
    $CAMPOS[] = 'AVG(pv.precio_oferta) AS "tiene_oferta"';
    // Nombre de archivo de la foto de la variedad
    $CAMPOS[] = 'pv.codigo_variedad';
    $CAMPOS[] = 'pv.foto AS "variedad_foto"';
    $CAMPOS[] = 'pv.receta AS "variedad_receta"';
    $CAMPOS[] = 'IF(pc.titulo="","sin titulo",pc.titulo) AS "contenedor_titulo"';
    $CAMPOS[] = 'pc.descripcion AS "contenedor_descripcion"';
    $CAMPOS[] = 'pc.codigo_producto';
    $CAMPOS[] = 'pc.creacion';
    $CAMPOS[] = 'cat.codigo_categoria';
    $CAMPOS[] = 'cat.titulo AS "titulo_categoria"';

    // Obtenemos el modo de operacion
    // Superior = mostrar todos los tipo/categoria/especial que pertenezcan al menu
    // Normal (por defecto) = mostrar los productos dentro del codigo_categoria seleccionado
    if (isset($_GET['modo']) && $_GET['modo'] == 'superior')
    {
        switch ($_GET['codigo_categoria'])
        {
            case '7':
                $modoHorizontal = false;
                $arrCSS[] = 'CSS/estilo.formal';
                break;
            
            case '6':
                $modoHorizontal = false;
                $arrCSS[] = 'CSS/estilo.alegre';
                break;
            
            case '5':
                $modoHorizontal = true;
                $arrCSS[] = 'CSS/estilo.formal';
                $arrCSS[] = 'CSS/estilo.horizontal';
                break;
            
            case '2':
                $ocultar_ocasion = true;
                $ocultar_tipo = false;
                break;
            
            case '1':
                $ocultar_ocasion = false;
                $ocultar_tipo = true;
                break;
        }

        $variante = '';
        $WHERE = 'WHERE cat.codigo_menu='.db_codex($_GET['codigo_categoria']);

        // Para titulo y refinado
        $titulo = db_obtener(db_prefijo.'menu','descripcion','codigo_menu="'.db_codex($_GET['codigo_categoria']).'"');
        $HEAD_descripcion = PROY_NOMBRE . " - " . $titulo;
    }
    elseif (isset($_GET['modo']) && $_GET['modo'] == 'filtro')
    {
        $c = sprintf('SELECT nombre_filtro, filtro_sql, descripcion_filtro FROM %s WHERE nombre_filtro="%s"',db_prefijo.'filtros',db_codex($_GET['codigo_categoria']));
        $FILTRO = mysqli_fetch_assoc(db_consultar($c));
        if (empty($FILTRO['filtro_sql']))
        {
            echo 'Filtro inválido';
            return;
        }
        $WHERE = 'WHERE ' . $FILTRO['filtro_sql'];

        $titulo = $_GET['codigo_categoria'];
        $HEAD_descripcion = 'Categoría: '.$_GET['codigo_categoria'] .' - Filtro: '.$FILTRO['descripcion_filtro'];
    }
    else
    {
        $variante = 'codigo_categoria='.db_codex($_GET['codigo_categoria']);
        $WHERE = sprintf('WHERE %s',$variante);

        switch ($_GET['codigo_categoria'])
        {

            case '37':
            $modoHorizontal = true;
            $arrCSS[] = 'CSS/estilo.formal';
            $arrCSS[] = 'CSS/estilo.horizontal';
            break;
        }

        // Para titulo y refinado
        $c = 'SELECT tipo, titulo, descripcion FROM '.db_prefijo.'categorias'.' WHERE codigo_categoria="'.db_codex($_GET['codigo_categoria']).'" LIMIT 1';
        $CATEGORIA = mysqli_fetch_assoc(db_consultar($c));
        if ($CATEGORIA['tipo'] != 'especial')
        {
            $ocultar_ocasion = ($CATEGORIA['tipo'] == 'ocasion');
            $ocultar_tipo = !$CATEGORIA['tipo'];
        }
        $titulo = $CATEGORIA['titulo'];
        $HEAD_descripcion = $CATEGORIA['descripcion'];

    }
    
    $WHERE .= ' AND deshabilitado=0';
    
    $HEAD_titulo = PROY_NOMBRE . ' - ' . $titulo;
    
    if (isset($_GET['fb']))
        $ocultarFiltros = true;

    switch (@$_GET['refinado'])
    {
        case 'color':
            $REFINADO = ' AND pc.color="'.$_GET['valor'].'"';
            break;
        case 'precio':
            $pregs = array('/^\-(\d+)/' => ' AND pv.precio<$1','/(\d+)\-(\d+)/' => 'AND pv.precio BETWEEN $1 AND $2','/^[\+\s](\d+)/' => ' AND pv.precio>$1');
            $precio = preg_replace(array_keys($pregs),array_values($pregs),$_GET['valor']);
            $REFINADO = $precio;
            $_GET['orden'] = 'precio';
            break;
        case 'categoria':
            $REFINADO = ' AND pc.codigo_producto IN (SELECT codigo_producto FROM '.db_prefijo.'productos_categoria WHERE codigo_categoria="'.$_GET['valor'].'")';
            break;
        case 'solo_ofertas':
            $REFINADO = ' AND pv.precio_oferta > 0';
            break;
        default:
            $REFINADO = '';
    }
    
    switch (@$_GET['orden'])
    {
        case 'precio':
            $ORDER_BY = "IF(precio_oferta > 0, precio_oferta, precio) ASC";
            break;
        case 'precio_asc':
            $ORDER_BY = "IF(precio_oferta > 0, precio_oferta, precio) ASC";
            break;
        case 'precio_desc':
            $ORDER_BY = "IF(precio_oferta > 0, precio_oferta, precio) DESC";
            break;
        case 'color':
            $ORDER_BY = "FIELD (pc.color,'Rojo','Multicolor','Amarillo','Naranja','Azul','Blanco','Gris','Negro','Rosa','Verde','Violeta','Púrpura','Fucsia','Lavanda','Lila','Turquesa','Oro','Ladrillo','Plateado','Primaveral')";
            break;
        case 'lineal':
            $ORDER_BY = "codigo_producto ASC";
            break;
        case 'titulo':
            $ORDER_BY = "contenedor_titulo ASC";
            break;
        case 'variedad':
            $ORDER_BY = "(codigo_variedad  + 0 ) ASC";
            break;
        case 'orden_color':
            if (!empty($_GET['orden_color']))
            {
                $ORDER_BY = "FIELD (pc.color,'".db_codex($_GET['orden_color'])."','Rojo','Multicolor','Amarillo','Naranja','Azul','Blanco','Gris','Negro','Rosa','Verde','Violeta','Púrpura','Fucsia','Lavanda','Lila','Turquesa','Oro','Ladrillo','Plateado','Primaveral')";
                break 1;
            }
        default:
            $ORDER_BY = '(COS(codigo_producto*(curdate()+0))) ASC';
            //$ORDER_BY = 'RAND(curdate()+0)';
    }
    
    $bELEMENTOS = '';

    $FROM = sprintf('FROM '.db_prefijo.'producto_contenedor AS pc LEFT JOIN '.db_prefijo.'producto_variedad AS pv USING(codigo_producto) LEFT JOIN '.db_prefijo.'productos_categoria AS pcat USING(codigo_producto) LEFT JOIN '.db_prefijo.'categorias AS cat USING(codigo_categoria) %s',$WHERE);
    $GROUP_BY = 'GROUP BY foto';
    
    $descontinuado = (isset($_GET['descontinuados']) ? 'si' : 'no');
    $c = 'SELECT '. join(', ',$CAMPOS) . ' ' . $FROM . ' AND descontinuado="'.$descontinuado.'" '. $REFINADO .' ' . $GROUP_BY . ' ORDER BY ' . $ORDER_BY;
    
    // error_log($c);
    
    if (0 && apc_exists(__CONTEXTO__.'+menu'.  crc32($c))) {
    
            echo '<!-- APC Categoria !-->';
            $bELEMENTOS .= apc_fetch (PROY_NOMBRE_CORTO.'menu'.  crc32($c));
    } else {
        $r = db_consultar($c);

        if (!mysqli_num_rows($r))
        {
            $bELEMENTOS .=  '<div style="display:block">Lo sentimos, por el momento se nos han agotado las existencias de estos productos.</div>';
            return;
        }

        /* Workhorse */
        $bELEMENTOS .= Rejilla_Resultados($r,$modoHorizontal);
        
        apc_store(__CONTEXTO__.'+menu'.  crc32($c), $bELEMENTOS, 3600);
    }
    
    $opciones_color = array('' => 'Ninguno', 'Multicolor' => 'Multicolor primero', 'Amarillo ' => 'Amarillo primero', 'Naranja' => 'Naranja primero', 'Azul' => 'Azul primero', 'Blanco' => 'Blanco primero', 'Negro' => 'Negro primero', 'Rojo' => 'Rojo primero', 'Rosa' => 'Rosa primero', 'Verde' => 'Verde primero', 'Fucsia' => 'Fucsia primero', 'Primaveral' => 'Primaveral primero');

/* SALIDA */

echo '<h1 style="padding:0px;font-size:11px;">'.$titulo.'</h1><hr style="margin:0px;" />';
echo '<div id="refinado-categoria">
<form style="display:inline;" action="" method="GET"><span class="li" style="font-size:13px;font-style:italic;font-weight:bold;">Ordenar por precio</span>&nbsp;
<input class="ordenamiento" name="orden" type="radio" value="precio_asc" id="orden_asc" '.(@$_GET['orden'] == 'precio_asc' ? 'checked="checked"' : '').'>&nbsp;<label for="orden_asc">De menor a mayor</label> <input class="ordenamiento" name="orden" type="radio" value="precio_desc" id="orden_desc" '.(@$_GET['orden'] == 'precio_desc' ? 'checked="checked"' : '').'> <label for="orden_asc">De mayor a menor</label><span style="display:inline-block;width:100px;">&nbsp;</span></form>
<form style="display:inline;" action="" method="GET"><input type="hidden" name="orden" value="orden_color" />
<span class="li" style="font-size:13px;font-style:italic;font-weight:bold;">Ordenar por color</span>&nbsp;&nbsp;'.ui_combobox('orden_color',ui_array_a_opciones( $opciones_color),@$_GET['orden_color'] , 'ordenamiento').'</select>
</form></div>';
echo $bELEMENTOS;
?>
<p style="background-color:#e18522;color:white;width:930px;padding:4px; margin: 2px auto;font-size: 11px;text-align:justify;">
Flor360.com es la mas destacada entre las <span style="font-weight:bold;">Floristerias El Salvador</span> ya que contamos con diseños florales exclusivos para enviar <span style="font-weight:bold;">Flores a El Salvador</span> y <span style="font-weight:bold;">Regalos a El Salvador</span>. Are you an international costumer looking to <span style="font-weight:bold;">send present for birthday, valentine, christmas or just some roses or other beautiful flowers</span>?, don't worry, we accept international orders so you can send flowers to El Salvador. Call us now to <b>+503 <?php echo PROY_TELEFONO_PRINCIPAL; ?></b> or reach us by e-mail at <a href="mailto:info@flor360.com">info@flor360.com</a>.
</p>
<script>
    $(function () {
        if ($('#refinado-categoria').length > 0)
        {
            $(window).scroll(function (event) {
                var winTop = $(this).scrollTop();
                var divTop = $('#secc_general').offset().top;
                if ( divTop <= winTop) {
                    $('#refinado-categoria').css({position: 'fixed', top: '0', left: '0', right: '0'});
                    //console.log('winTop: ' + winTop + " > divTop: " + divTop);
                } else {
                    $('#refinado-categoria').css({position: '', top: '', left: '', right: ''});
                    //console.log('winTop: ' + winTop + " < divTop: " + divTop);
                }
            });
        }
    
        $('a.enlace-elemento[tooltip]').each(function()
        {
           $(this).qtip({content: $(this).attr('tooltip'), style: {classes: "ui-tooltip-shadow ui-tooltip-cream"}, position: {my: 'top center',at: 'bottom center'}});
        });
        
        $('img.lazy').lazyload({ threshold : 500, effect : "fadeIn" });
        
        $('#refinado-categoria input:radio').click(function(e){
            if($(this).hasClass('on')){
               $(this).removeAttr('checked');
               $(this).parents('form').submit();
            }
            $(this).toggleClass('on');
        }).filter(':checked').addClass('on');
        
        $('#refinado-categoria .ordenamiento').change(function(){
            $(this).parents('form').submit();
        });
        
    });
</script>
