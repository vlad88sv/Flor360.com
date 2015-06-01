<html>
    <head>
	<meta http-equiv="refresh" content="600" >
	<style type="text/css">
	    body {font-family: Arial, sans-serif}
	    .logotipo{font-size: 4em; color: #FFBA44;text-align: center; font-weight: bold;}
	    .img_kiosko{height: 450px; width: 300px;}
	    .txt_kiosko .codigo_producto{color: #7C7CFF; font-weight: bold; font-size:3.5em;}
	    .txt_kiosko .titulo{text-align: center;color: #e18522; font-weight: bold; font-size: 2.5em;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;}
	</style>
	<script type="text/javascript" src="/JS/jquery-1.7.2.js"></script>
    </head>
    <body>
	<table style="width:100%;height:100%;table-layout: fixed;">
	    <tr>
		<td colspan="3" class="logotipo">FLORISTERIA FLOR360</td>
	    </tr>
	    <tr>
		<td style="vertical-align: middle;text-align: center;">

		    <img id="img_kiosko_1" class="img_kiosko" src="/imagen_300_450_356a192b7913b04c54574d18c28d46e6395428ab.jpg" />
		</td>
		<td style="vertical-align: middle;text-align: center;">
		    <img id="img_kiosko_2" class="img_kiosko" src="/imagen_300_450_356a192b7913b04c54574d18c28d46e6395428ab.jpg" />
		</td>
		<td style="vertical-align: middle;text-align: center;">
		    <img id="img_kiosko_3" class="img_kiosko" src="/imagen_300_450_356a192b7913b04c54574d18c28d46e6395428ab.jpg" />
		</td>
	    </tr>
	    <tr>
		<td style="vertical-align: middle;text-align: center;">
		    <div id="txt_kiosko_1" class="txt_kiosko"></div>
		</td>
		<td style="vertical-align: middle;text-align: center;">
		    <div id="txt_kiosko_2" class="txt_kiosko"></div>
		</td>
		<td style="vertical-align: middle;text-align: center;">
		    <div id="txt_kiosko_3" class="txt_kiosko"></div>
		</td>
	    </tr>
	</table>
	<script type="text/javascript">
	    
	    img_pld = $('<img />').appendTo('body').css('display','none');
	    nueva_imagen = '';
	    nuevo_titulo = '';
	    nuevo_codigo_producto = '';
	    
	    cantidad = 3;
	    actual = 1;
	    
	    image = $('#img_kiosko_1');
	    texto = $('#txt_kiosko_1');
	    
	    function rotar_imagenes()
	    {
		// Establecemos la imagen actual
		image = $('#img_kiosko_' + actual);
		texto = $('#txt_kiosko_' + actual);
		
		// Obtener imagen al azar
		$.get('ajax',{pajax: 'img_kiosko'}, function(data) {
		    nueva_imagen = '/imagen_300_450_'+data.foto+'.jpg';
		    nuevo_titulo = data.titulo;
		    nuevo_codigo_producto = data.codigo_producto;
		    img_pld.attr('src', nueva_imagen)		    
		}, 'json');
		
		actual++;
		
		if (actual > cantidad) actual = 1;
	    }
	    
	    $(document).ready(function(){
		img_pld.load(function(){
		    image.fadeOut('fast', function () {
			image.attr('src', nueva_imagen);
			image.fadeIn('fast');
			texto.html('<div class="titulo">' + nuevo_titulo + '</div><div class="codigo_producto">#' + nuevo_codigo_producto + '</div>');
		    });		    
		});
		
		setInterval(rotar_imagenes,1000);
	    });
	</script>
    </body>
</html>
