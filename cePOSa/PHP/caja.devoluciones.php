<?php
protegerme(false,array(_N_vendedor));
?>
<h1>Módulo para realizar devoluciones de dinero a clientes</h1>
<p>Únicamente pueden realizarse las devoluciones de dinero autorizadas en esta sección</p>
<p>Para buscar una devolución autorizada, ingrese el código de compra completo, Ej. <b>4321zxyw</b> y presione el botón búscar.</p>
<p>Si el cliente desconoce su codigo de compra, preguntar su nombre y llamar para consultar el codigo de compra.</p>
<input type="text" name="hash" id="hash" value=""><input type="button" id="buscar_hash" value="Búscar" />
<div id="resultado_busqueda"></div>
<script>
    $(function(){
        $('#buscar_hash').click(function () {
            $('#resultado_busqueda').load('ajax',{pajax:'buscar_para_devolucion',hash:$('#hash').val()});
        });
        
        $('#hacer_devolucion').live('click',function(){
            $('#resultado_busqueda').load('ajax',{pajax:'procesar_devolucion',codigo_devolucion: $(this).attr('codigo_devolucion'),codigo_compra: $(this).attr('codigo_compra'),recibido_por:$('#recibido_por').val()});
        });
    });
</script>
