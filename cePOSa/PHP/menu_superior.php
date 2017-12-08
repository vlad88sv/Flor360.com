<?php if ( defined('NO_MENU') ) return; ?>
<?php if (!isset($_GET['fb'])): ?>
    <div style="height:220px;position: relative;text-align:center;">
        <table style="width: 100%;height:100%;table-layout: fixed;">
            <tr>
                <td style="vertical-align: bottom;text-align: left;padding-left: 30px;">
                    <div>
                        <img style="line-height: 32px; vertical-align: middle;" src="<?php echo PROY_URL_ESTATICA; ?>IMG/portada/rosa.png" alt="Rosa"/>
                        <span style="font-size:1.4em;">TEL; (503) 2278-8391 ; (503) 2243-6017</span>
                    </div>
                    <div>
                        <img style="line-height: 32px; vertical-align: middle;" src="<?php echo PROY_URL_ESTATICA; ?>IMG/portada/rosa.png" alt="Rosa"/>
                        <span style="font-size:1.4em;">CONTACTANOS</span>
                    </div>
                    <div>
                        <img style="line-height: 32px; vertical-align: middle;" src="<?php echo PROY_URL_ESTATICA; ?>IMG/portada/rosa.png" alt="Rosa"/>
                        <span style="font-size:1.4em;">POLITICAS DE ENTREGA</span>
                    </div>
                </td>
                <td style="vertical-align: center;">
                    <img src="<?php echo PROY_URL_ESTATICA; ?>IMG/portada/logo2.png" alt="Flor360.com Logo"/>
                </td>
                <td style="vertical-align: bottom;text-align: right;padding-right: 30px;">
                    <div>
                        <img src="<?php echo PROY_URL_ESTATICA; ?>IMG/portada/lgv.png" alt="Rosa"/>
                    </div>
                    <br />
                    <div>
                        <span style="font-size:1.4em;">FORMAS DE PAGO</span>
                        <img style="line-height: 32px; vertical-align: middle;" src="<?php echo PROY_URL_ESTATICA; ?>IMG/portada/rosa.png" alt="Rosa"/>
                    </div>
                    <div>
                        <span style="font-size:1.4em;">NUESTROS SERVICIOS</span>
                        <img style="line-height: 32px; vertical-align: middle;" src="<?php echo PROY_URL_ESTATICA; ?>IMG/portada/rosa.png" alt="Rosa"/>
                    </div>
                    <div>
                        <span style="font-size:1.4em;">COMPRAR</span>
                        <img style="line-height: 32px; vertical-align: middle;" src="<?php echo PROY_URL_ESTATICA; ?>IMG/portada/rosa.png" alt="Rosa"/>
                    </div>
                </td>
            </tr>
        </table>
    </div>
<?php else: ?>
    <a href="<?php echo PROY_URL ?>"><img style="width:810px;height:66px;" src="<?php echo PROY_URL_ESTATICA ?>IMG/portada/superior.fb.png" /></a>
<?php endif; ?>
<div style="background-color: #e9333f;height: 50px;">
<table style="height: 100%;width: 90%; margin: auto; table-layout: fixed;">
    <tr style="color:white; font-size:1.4em;">
        <td style="vertical-align: middle;text-align: left;">POR OCASION &#x25BC;</td>
        <td style="vertical-align: middle;text-align: right;">&#x25BC; POR TIPO DE FLOR</td>
    </tr>
</table>
</div>

<?php
return;
// Menues dinamicos
if (0 && apc_exists(PROY_NOMBRE_CORTO.'menu')){
    $menu = apc_fetch(PROY_NOMBRE_CORTO.'menu');
    echo '<!-- APC MENU !-->';
} else {
    $c = 'SELECT fcat.`codigo_categoria`, fcat.`titulo`, fcat.`descripcion`, fmenu.`codigo_menu`, fmenu.`titulo` AS "menu" FROM `flores_menu` AS fmenu LEFT JOIN `flores_categorias` AS fcat USING(codigo_menu) WHERE activo=1 ORDER BY fmenu.`posicion` ASC, fcat.importancia DESC, fcat.`titulo` ASC';
    $r = db_consultar($c);

    while ($f=mysqli_fetch_assoc($r))
    {
        $menu[$f['menu']][] = array('menu' => $f['menu'], 'codigo_menu' => $f['codigo_menu'], 'codigo_categoria' => $f['codigo_categoria'], 'titulo' => $f['titulo']);
    }

    apc_add (PROY_NOMBRE_CORTO.'menu', $menu, 3600);
}

$bmenu = '';

echo '<ul id="nav" class="dropdown dropdown-horizontal">'."\n";

echo '<li class="dir lidestacado" style="background-color:#F00;"><a href="categoria-canastas2015-especial.html" title="Especial de Navidad"><span class="blink2">Canastas</span><img style="vertical-align: middle;" src="IMG/stock/santa.png" /></a></li>';
//echo '<li class="dir lidestacado" style=""><a href="categoria-valentines-36.html?orden=color&valor=Rojo" title="Especial de San Valentin!"><span class="">Valentines</span><img style="vertical-align: middle;" src="IMG/stock/cupido.png" /></a></li>';

//echo '<li class="dir lidestacado" style="background-color:#A00;"><a style="color:#FFF" href="categoria-dia-de-la-madre-47.html" title="Especial del Día de la Madre!"><span class="blink">Día de la Madre</span></a></li>';
//echo '<li class="dir lidestacado" style="background-color:#A00;"><a style="color:#FFF" href="/categoria-dia-de-la-madre-48.html" title="Especial del Día de la Madre!"><span class="blink">Mom\'s special</span></a></li>';
//echo '<li class="dir lidestacado" style="background-color:#0094FF;"><a style="color:#FFF" href="/categoria-regalos-para-hombre-44.html" title="Especial del Día del Padre!"><span class="blink">Dad\'s special</span></a></li>';


if(isset($menu))
{
    foreach($menu as $clave => $componentes)
    {
            $bmenu .= '<li '.( count($componentes) > 1 ? 'class="flecha menu_padre"' : '' ).' id="menu_'.$componentes[0]['codigo_menu'].'"><a href="categoria-superior-'.SEO($componentes[0]['menu'].'-'.$componentes[0]['codigo_menu']).'" title="'.$clave.'">'.$clave.'</a>'."\n";

	    // Si tiene al menos 2 submenus
	    if ( count($componentes) > 1 )
	    {
		$bmenu .= '<ul>'."\n";
		$bmenu .= '<li><a href="categoria-superior-'.SEO($componentes[0]['menu'].'-'.$componentes[0]['codigo_menu']).'">Mostrar todos</a></li>'."\n";
	    
		foreach($componentes as $item)
		{
		    $bmenu .= '<li><a href="categoria-'.SEO($item['titulo'].'-'.$item['codigo_categoria']).'" title="'.$item['titulo'].'">'.$item['titulo'].'</a></li>'."\n";
		}
		
		$bmenu .= '</ul>'."\n";
	    }

	    $bmenu .= '</li>'."\n";
	    
    }
    echo $bmenu;
}

echo '<li><a style="color:black;font-weight:bold;" href="/categoria-superior-ocasiones-1.html?refinado=solo_ofertas">Ofertas</a></li>';

/************* AYUDA ***************************/
?>

<li class="flecha menu_padre"><a href="<?php echo PROY_URL; ?>ayuda?tema=nosotros" title="Ayuda">Ayuda</a>
<ul>
<li><a href="<?php echo PROY_URL; ?>ayuda?tema=nosotros" title="Quienes somos">Quienes somos</a></li>
<li><a href="<?php echo PROY_URL; ?>ayuda?tema=terminos_y_condiciones" title="Terminos y condiciones">Terminos y condiciones</a></li>
<li><a href="<?php echo PROY_URL; ?>ayuda?tema=PF" title="Preguntas Frecuentes">Preguntas frecuentes</a></li>
<li><a href="<?php echo PROY_URL; ?>ayuda?tema=como_comprar" title="Como comprar">¿Como comprar?</a></li>
<li><a href="<?php echo PROY_URL; ?>ayuda?tema=buenas_direcciones" title="Buenas direcciones">Direcciones de entrega</a></li>
<li><a href="<?php echo PROY_URL_SSL . (S_iniciado() ? 'finalizar' : 'iniciar'); ?>" title="Socios"><?php echo (S_iniciado() ? 'Salida de socios' : 'Ingreso de socios'); ?></a></li>
</ul></li>

<li class="lidestacado flecha menu_padre"><a href="<?php echo PROY_URL; ?>contactanos" title="Contáctanos">Contáctanos</a>
<ul>
<li><a href="<? echo PROY_URL_SSL; ?>contactanos" title="Contáctanos">Contáctanos</a></li>
<li><a href="http://blog.flor360.com/" title="Blog Floristería Flor360">Nuestro Blog!</a></li>
<li><a href="http://twitter.com/flor360" rel="nofollow" target="_blank" title="Flor360 en Twitter">...en Twitter!</a></li>
<li><a href="https://www.facebook.com/floristeria.flor360" rel="nofollow" target="_blank" title="Flor360 en Facebook">...en Facebook!</a></li>
<li><a href="http://digg.com/d31IAuT" target="_blank" rel="nofollow" title="Flor360 en Digg">...en Digg!</a></li>
</ul>
</li>
<li style="float:right;">
<form action="<?php echo PROY_URL; ?>buscar" class="buscar" method="get">
    <input name="busqueda" placeholder="Ingrese número de producto o palabra clave" type="text" id="busqueda" value="" />
    <input type="submit" id="buscar" value="Búscar" />
</form>
</li>
</ul>
<?php
/************* ADMINISTRACION ***************************/
if (in_array(_F_usuario_cache('nivel'),array(_N_administrador,_N_vendedor)))
{
    echo '<ul id="nav" class="dropdown dropdown-horizontal" style="margin-top:5px;z-index:50;">'."\n";
    
    $caja_admin = SI_ADMIN('
	<li><a href="'.PROY_URL_SSL.'+caja?modo=ingresos" title="Inventario">Ingresos</a></li>
	<li><a href="'.PROY_URL_SSL.'+caja?modo=egresos" title="Inventario">Egresos</a></li>
	<li><a href="'.PROY_URL_SSL.'+articulos" title="Inventario">Articulos</a></li>
    ');
    
    echo '
    <li><a href="'.PROY_URL_SSL.'+chat" title="Chat en línea">Chat</a></li>
    <li class="flecha menu_padre"><a target="_blank" href="'.PROY_URL_SSL.'+info" title="Información básica">Info</a>
	<ul>
            <li><a target="_blank" href="'.PROY_URL_SSL.'+info" title="Información básica">Info</a>
	    <li><a target="_blank" href="'.PROY_URL_SSL.'+geo" title="Referencias geográficas">GEO</a></li>
	    <li><a target="_blank" href="'.PROY_URL_SSL.'+barcode" title="Búscar en base a código de barras">BARCODE</a></li>
	</ul>
    </li>
    <li class="flecha menu_padre"><a href="'.PROY_URL_SSL.'+caja" title="Caja Kiosko">Caja</a>
	<ul>
            <li><a href="'.PROY_URL_SSL.'+caja" title="Caja Kiosko">Caja</a>
	    <li><a href="'.PROY_URL_SSL.'cortez" title="Corte Z de Caja">Corte Z</a></li>
	    <li><a href="'.PROY_URL_SSL.'historial_cortez" title="Historial de cortes Z">Historial Z</a></li>
	    <li><a href="'.PROY_URL_SSL.'historial_cortex" title="Historial de cortes X">Historial X</a></li>
	    <li><a href="'.PROY_URL_SSL.'gasto_z" title="Ingreso de gasto por día">Gasto Z</a></li>
	    <li><a href="'.PROY_URL_SSL.'gasto_x" title="Vista de gasto por mes">Gasto X</a></li>
            <li><a href="'.PROY_URL_SSL.'+caja?modo=devoluciones" title="Devoluciones de dinero">Devoluciones</a></li>
            <li><a href="'.PROY_URL_SSL.'+caja?modo=pagos" title="Pagos de arreglos">Pagos</a></li>
	    '.$caja_admin.'
	</ul>
    </li>
    <li><a target="_blank" href="'.PROY_URL_SSL.'+anexo" title="Agregar datos a ordenes existentes">Anexos</a></li>
    ';
    
    if (_F_usuario_cache('nivel') == _N_administrador)
    {
	echo '
	<li class="dir lidestacado"><a href="'.PROY_URL.'ventas?fecha_entrega=now" title="Obtener lista de compra-venta en espera">Ventas</a>
	    <ul style="width:350px;">
	    <li style="padding:0 5px;"><form method="get" target="_blank" action="+impresion"><input type="hidden" name="objetivo" value="FirmaAvanzada" /> <input type="hidden" name="nocache" value="nocache" /><input type="text" name="fecha_entrega" title="En blanco = todos. Caso contrario usar Ej.: 1,2,3,6-11,13" style="width:68px;" maxlength="10" value="'.mysql_date().'" title="Fecha de entrega"/> <input type="text" name="ordenes" onclick="$(this).val(\'\')" style="width:140px;" value="" /> <input type="checkbox" value="1" name="modelo2" title="Estilo #2" /> <input type="checkbox" value="1" name="tarjetas" title="imprimir tarjetas" /> <input type="submit" value="Imprimir" /></form></li>
	    </ul>    
	</li>';
	
	echo '
	<li class="flecha menu_padre"><a href="'.PROY_URL_SSL.'+administracion" title="administración">Admin</a>
	    <ul>
            <li><a href="'.PROY_URL_SSL.'+administracion" title="administración">Admin</a>
	    <li><a href="'.PROY_URL_SSL.'+contenedores?agregar" title="Agregar contenedor">Nuevo contenedor</a></li>
	    <li><a href="'.PROY_URL_SSL.'+contenedores" title="Contenedores">Contenedores</a></li>
	    <li><a href="'.PROY_URL_SSL.'+categorias" title="Categorias">Categorias</a></li>
	    <li><a href="'.PROY_URL_SSL.'+filtros" title="Gestionar filtros">Filtros</a></li>
	    <li><a href="'.PROY_URL_SSL.'+menu" title="Gestionar menú">Menú</a></li>
	    <li><a href="'.PROY_URL_SSL.'+compras" title="Gestionar compras">Compras</a></li>
	    <li><a href="'.PROY_URL_SSL.'+estadisticas" title="Estadísticas">Estadísticas</a></li>
	    <li><a href="'.PROY_URL_SSL.'+novisibles" title="Arreglos sin categoria">Arreglos no visibles</a></li>
	    <li><a href="'.PROY_URL_SSL.'+rifa" title="Rifas">Rifas</a></li>
	    <li><a href="'.PROY_URL_SSL.'+administracion" title="Administracion global">Administración</a></li>
	    <li><a href="'.PROY_URL_SSL.'+presencia" title="Presencia de usuarios">Presencia de usuarios</a></li>
	    </ul>
	</li>
	';

	echo '
	<li style="float:right;">
	<form action="'.PROY_URL.'ventas" class="busqueda_ventas" method="get">
	<input name="buscar" type="text" placeholder="# de compra, correo, direccion de entrega, nombre de arreglo, nombre del remitente/destinatario" id="busqueda_ventas" value="" />
	<input type="submit" id="buscar_ventas" value="Búscar" />
	</form>
	</li>
	';
    }
    echo '</ul>'; // Fin de dropdown admin
}
?>
<script type="text/javascript">
    $(function(){
        $('.menu_padre').attr('aria-haspopup',true);
        
        if (is_touch_device())
        {
            $(document).on('click', '.menu_padre > a', function(event){
		event.stopPropagation();
		event.preventDefault();
                return false;
            });
        }
    });
</script>
<noscript>
<div style="background-color:#fef1b9;font-size:14px;padding:10px;border-radius:10px;margin:10px 0px;text-align: center;">
Advertencia: su navegador no posee <b>JavaScript</b>, por lo que su experiencia de compra no será óptima.<br />
Le recomendamos mejor ingresar su pedido llamandonos al <b><?php echo PROY_TELEFONO_PRINCIPAL; ?></b>, donde uno de nuestros agentes le asistirá en su compra.
</div>
</noscript>
<!--[if lt IE 7]>
<div style='border: 1px solid #F7941D; background: #FEEFDA; text-align: center; clear: both; height: 75px; position: relative;margin-top:5px;'>
  <div style='position: absolute; right: 3px; top: 3px; font-family: courier new; font-weight: bold;'><a href='#' onclick='javascript:this.parentNode.parentNode.style.display="none"; return false;'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-cornerx.jpg' style='border: none;' alt='Cierra este aviso'/></a></div>
  <div style='width: 640px; margin: 0 auto; text-align: left; padding: 0; overflow: hidden; color: black;'>
    <div style='width: 75px; float: left;'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-warning.jpg' alt='¡Aviso!'/></div>
    <div style='width: 275px; float: left; font-family: Arial, sans-serif;'>
      <div style='font-size: 14px; font-weight: bold; margin-top: 12px;'>Usted está usando un navegador obsoleto.</div>
      <div style='font-size: 12px; margin-top: 6px; line-height: 12px;'>Para navegar mejor por este sitio, por favor, actualice su navegador.</div>
    </div>
    <div style='width: 75px; float: left;'><a href='http://www.mozilla-europe.org/es/firefox/' target='_blank'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-firefox.jpg' style='border: none;' alt='Get Firefox 3.5'/></a></div>
    <div style='width: 75px; float: left;'><a href='http://www.microsoft.com/downloads/details.aspx?FamilyID=341c2ad5-8c3d-4347-8c03-08cdecd8852b&DisplayLang=es' target='_blank'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-ie8.jpg' style='border: none;' alt='Get Internet Explorer 8'/></a></div>
    <div style='width: 73px; float: left;'><a href='http://www.apple.com/es/safari/download/' target='_blank'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-safari.jpg' style='border: none;' alt='Get Safari 4'/></a></div>
    <div style='float: left;'><a href='http://www.google.com/chrome?hl=es' target='_blank'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-chrome.jpg' style='border: none;' alt='Get Google Chrome'/></a></div>
  </div>
</div>
<![endif]-->
