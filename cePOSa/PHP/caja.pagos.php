<?php
protegerme(false,array(_N_vendedor));
?>
<h1>Módulo para realizar pagos en kiosko para pedidos previamente ingresados en el sistema</h1>
<p>Para buscar un pedido ingresa el código de compra completo, Ej. <b>1234abcd</b> y presione el botón búscar.</p>
<p>Si el cliente desconoce su codigo de compra, preguntar su nombre y llamar para consultar el codigo de compra.</p>
<input type="text" name="hash" id="hash" value=""><input type="button" id="buscar_hash" value="Búscar" />
<div id="resultado_busqueda_pagos"></div>
<script>
    $(function(){
        $('#buscar_hash').click(function () {
            $('#resultado_busqueda_pagos').load('ajax',{pajax:'buscar_para_pago',hash:$('#hash').val()});
        });
        
        $('#pagar_arreglo').live('click',function(){
            $('#resultado_busqueda_pagos').load('ajax',{pajax:'procesar_pago',codigo_compra: $(this).attr('rel'),metodo_pago:$('input:radio[name="metodo_pago"]:checked').val()});
        });
    });
</script>
