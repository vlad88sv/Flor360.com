<?php
protegerme(false,array(_N_vendedor));
?>
<h1>Módulo para ingresar anexos a pedidos</h1>
<p>Para buscar un pedido ingresa el código de compra completo, Ej. <b>1234abcd</b> y presione el botón búscar.</p>
<p>Si el cliente desconoce su codigo de compra, preguntar su nombre y llamar para consultar el codigo de compra.</p>
<input type="text" name="hash" id="hash" value=""><input type="button" id="buscar_hash" value="Búscar" />
<div id="resultado_busqueda_pedidos"></div>
<script>
    $(function(){
        $('#buscar_hash').click(function () {
            $('#resultado_busqueda_pedidos').load('ajax',{pajax:'buscar_para_anexo',hash:$('#hash').val()});
        });
        
        $('#anexar_datos').live('click',function(){
            datos = {pajax:'procesar_anexo',codigo_compra: $(this).attr('rel'),descripcion:$("#descripcion").val(), paquete:($("#paquete").is(":checked") ? 1 : 0)};
            $('#resultado_busqueda_pedidos').load('ajax',datos);
        });
    });
</script>
