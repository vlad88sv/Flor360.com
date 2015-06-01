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
	<script type="text/javascript" src="http://flor360.com/JS/jquery-1.7.2.js"></script>
    </head>
    <body>
	<table style="width:100%;height:100%;table-layout: fixed;">
            <tr>
		<td style="vertical-align: middle;text-align: center;">
                    <input rel="1" class="txt_kiosko" type="text" />
		</td>
		<td style="vertical-align: middle;text-align: center;">
		    <input rel="2" class="txt_kiosko" type="text" />
		</td>
		<td style="vertical-align: middle;text-align: center;">
		    <input rel="3" class="txt_kiosko" type="text" />
		</td>
	    </tr>
	    <tr>
		<td style="vertical-align: middle;text-align: center;">
		    <img id="img_kiosko_1" class="img_kiosko" src="http://flor360.com/imagen_300_450_356a192b7913b04c54574d18c28d46e6395428ab.jpg" />
		</td>
		<td style="vertical-align: middle;text-align: center;">
		    <img id="img_kiosko_2" class="img_kiosko" src="http://flor360.com/imagen_300_450_356a192b7913b04c54574d18c28d46e6395428ab.jpg" />
		</td>
		<td style="vertical-align: middle;text-align: center;">
		    <img id="img_kiosko_3" class="img_kiosko" src="http://flor360.com/imagen_300_450_356a192b7913b04c54574d18c28d46e6395428ab.jpg" />
		</td>
	    </tr>
	</table>
	<script type="text/javascript">
	    $('.txt_kiosko').change(function(){
                var objetivo = $(this).attr('rel');
                $.get('ajax',{pajax: 'img_por_codigo'}, function(data) {
		    nueva_imagen = 'http://flor360.com/imagen_300_450_'+data.foto+'.jpg';
		    nuevo_titulo = data.titulo;
		    nuevo_codigo_producto = data.codigo_producto;
		    img_pld.attr('src', nueva_imagen)		    
		}, 'json');
            });
	</script>
    </body>
</html>
