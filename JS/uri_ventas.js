copia_original = "";
copia_modificada = "";
objetivo = "";
codigo_compra = "";
salt = "";
ultimo_cambio = 0;
ignorar_cambios = false;

function obtenerLista() {
    lista_ordenes = [];
    $('.sel_pedido:checked').each(function(){
        lista_ordenes.push ($(this).val());
    });
    
    $("#lista_compra").load('ajax',{pajax:'obtenerLista',ordenes:lista_ordenes});
}

function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

function obtenerTimeline() {
    $.get('ajax',{pajax:'timeline_estados'}, function (data){
                $("#timeline_estados").html(data.datos);
    }, 'json');
}

function mostrar_notas_y_estados()
{
    $('#notas_y_estados').show();
    $('#mostrar_notas_y_estados').remove();
    
    window.setInterval(obtenerTimeline,5000);
    obtenerTimeline();    
    obtenerLista();
}

function obtenerCambios()
{
    
    var ordenes = [];
    $('.sel_pedido:checked').each(function(){
        ordenes.push ($(this).val());
    });

    $.post('ajax', {pajax:'obtener_cambios', ordenes: ordenes}, function(datos){        
        // Si los datos llegaran a ser mas viejos, alertar
        if ( !ignorar_cambios && (ultimo_cambio < parseInt(datos)) ) {
            var recargar = confirm("Alguien actualizó datos de los pedidos que esta viendo.\n¿Desea recargar la página?.");
            
            if (recargar) {
                window.location = window.location;
                return;
            } else {
                ignorar_cambios = true;
            }
        }
    });
}

setInterval(obtenerCambios, 10000);
ultimo_cambio = Math.round(+new Date()/1000);

$(function(){        
    
    $(document).on('click','#quitar_acceso_rapido', function(){
        $("#acceso_rapido_ordenes").remove();
    });

    $(document).on('contextmenu', '.img_tc', function(){
        var codigo_compra = $(this).parents('div.contenedor-compra').attr('codigo_compra');
        var respuesta = confirm('Esta seguro de (des)marcar como fraudulenta esta compra?');
        
        if (true === respuesta) {
            $.post('ajax',{pajax:'marcar_fraudulenta', codigo_compra: codigo_compra}, function () {
                $.jGrowl("Fraude registrado.<br />CC #"+codigo_compra);
            });
        }
    });
    
    if ($('#acceso_rapido_ordenes').length > 0)
    {
        $(window).scroll(function (event) {
            var winTop = $(this).scrollTop();
            var divTop = $('#tabla-ventas').offset().top;
            if ( divTop <= winTop) {
                $('#acceso_rapido_ordenes').css({position: 'fixed', top: '0', left: '0', right: '0'});
            } else {
                $('#acceso_rapido_ordenes').css({position: 'relative', top: '', left: '', right: ''});
            }
        });
    }

    $('#construir_filtro').click(function(){
        var html = '';
        html += '<button rel="-c" class="construir">no cobrados</button> <button rel="+c" class="construir">cobrados</button><br />';
        html += '<button rel="-e" class="construir">no elaborados</button> <button rel="+e" class="construir">elaborados</button><br />';
        html += '<button rel="-E" class="construir">no enviados</button> <button rel="+E" class="construir">enviados</button><br />';
        html += '<button rel="+kiosko_credito" class="construir">Solo crédito kiosko</button> <button rel="+C" class="construir">Solo crédito</button><br />';
        html += '<button rel="+kiosko" class="construir">Solo pagará en kiosko</button> <button rel="+multi" class="construir">Solo asociados</button><br />';
        
       $.facebox(html); 
    });
    
    
    $('.construir').live('click', function(){
        if ( $("#flags").val().indexOf($(this).attr('rel')) == -1 )
            $("#flags").val($("#flags").val() + $(this).attr('rel'));
    });
    
    $.datepicker.regional["es"];
    $(".datepicker").datepicker({constrainInput: true, dateFormat : "yy-mm-dd", defaultDate: +0});
    $(".ruta").autocomplete({source: "ajax?pajax=ruta"});
    
    $(".ruta").blur(function(){
        var codigo_compra = $(this).parents('div.contenedor-compra').attr('codigo_compra');
        $.post('ajax',{pajax:'modificar_estado_orden', codigo_compra: codigo_compra, objetivo:'ruta', valor:$(this).val()}, function () {
            $.jGrowl("Ruta guardada.<br />CC #"+codigo_compra);
        });
    });
    
    $(".autoguardar").focus(function(){
        copia_original = $(this).val();
    });
    
    $(".autoguardar").blur(function(){
        var objeto = $(this);
        var control = $(this).closest('div.contenedor-compra');
        
        copia_modificada = objeto.val();
        
        if (copia_original != copia_modificada)
        {
            if (confirm("¿Desea guardar los cambios en el código de compra " + control.attr('codigo_compra') + control.attr('salt') + "?\nRespecto a: " + objeto.attr("objetivo")))
            {
                $.post('ajax',{pajax:'modificar_estado_orden', codigo_compra: control.attr('codigo_compra'), objetivo:objeto.attr("objetivo"), valor:objeto.val()}, function () {
                    $.jGrowl("Cambio realizado: "+objeto.attr("objetivo")+"<br />CC #"+control.attr('codigo_compra') + control.attr('salt'));
                });
            }
            else
                objeto.val(copia_original);
        }
    });
    
    $(".guardar").click(function(){
        var codigo_compra = $(this).closest('div.contenedor-compra').attr('codigo_compra');
        var estado_notas = $(this).closest('div.ajax_estado').find('.estado_notas');

        if (estado_notas.val() == "" && !confirm('No ha escrito ningún estado. Presione OK si su intención era borrar esta nota'))
        {
            return;
        }
        
        $.post('ajax',{pajax:'modificar_estado_orden', codigo_compra: codigo_compra, objetivo:'estado_notas', valor:estado_notas.val()}, function () {
            $.jGrowl("Cambio guardado.<br />CC #"+codigo_compra);
        });
    });
    
    $(".revelar_fecha_entrega").click(function(){
        $("#fecha_entrega_"+$(this).attr('rel')).show();
        $("#fecha_entrega_formato_"+$(this).attr('rel')).hide();
        $(this).hide();
    });
    
    // Obtener el codigo_compra mas cercano y trabajar con el.
    $(".flag").click(function(){
        var flag = $(this);
        var contenedor_compra = flag.closest('div.contenedor-compra');
        var codigo_compra = contenedor_compra.attr('codigo_compra');
        var transaccion = contenedor_compra.attr('transaccion');
        
        $.post('ajax',{pajax:'modificar_estado_orden', codigo_compra: codigo_compra, objetivo:$(this).attr('name'), valor:($(this).is(':checked') ? 1 : 0)}, function (retorno) {
            
            if (retorno !== 'ok') {
                alert ('ERROR: la modificación concluyo en error. Posiblemente no se marco.');
                return;
            }
            
            if (flag.attr('name') == 'flag_cobrado')
            {
                var prefijo_img = (flag.is(':checked') ? 'imagen_SSLC_' : 'imagen_SSL_' );
                contenedor_compra.find('img.img_tc').attr('src','/' + prefijo_img + transaccion + '.png');
            }
            
            if (flag.attr('name') == 'flag_enviado' && flag.is(':checked'))
            {
                flag.closest('div.ajax_estado').find('[name="flag_elaborado"]').attr('checked','checked');
            }
            
            $.jGrowl("Cambio guardado.<br />CC #"+codigo_compra);
        
        }, 'html').fail(function() { alert ('ERROR: la modificación concluyo en error. Posiblemente no se marco.'); });
        
    }); // .flag::click
    
    $(".enviar_notificacion").click(function(){
        var transaccion = $(this).closest('div.contenedor-compra');
        var plantilla = transaccion.find('select.plantilla');
        
        if (plantilla.val() == '')
        {
            alert('Olvidó seleccionar una plantilla');
            return;
        }
        
        jQuery.facebox('<iframe width="960px" height="700px" frameBorder="0" src="+notificacion?nochat=silencio&sin_cabeza=descabezar&desactivar_fb=desactivar&transaccion=' + transaccion.attr('transaccion') + '&plantilla=' + plantilla.val() + '"></iframe>');
    });
    
    $(".enviar_notificacion_rapida").click(function(){
        var transaccion = $(this).closest('div.contenedor-compra');
        var plantilla = transaccion.find('select.plantilla');
        
        if (plantilla.val() == '')
        {
            alert('Olvidó seleccionar una plantilla');
            return;
        }
        
        $.get('+notificacion',{envio_rapido:'super_rapido', transaccion:transaccion.attr('transaccion'), plantilla:plantilla.val()}, function () {
            $.jGrowl("<b>Correo enviado</b><hr />Plantilla: " + plantilla.val()+"<hr />Código de compra: "+transaccion.attr('codigo_compra')+transaccion.attr('salt'));
        }).fail(function() { alert ('ERROR: el envio concluyo en error. Posiblemente no se envió.'); });
    });
    
    $(".hacer_envio").click(function(){
        var transaccion = $(this).closest('div.contenedor-compra');
        
        transaccion.find('div.ajax_estado').find('[name="flag_enviado"]').attr('checked','checked');
        transaccion.find('div.ajax_estado').find('[name="flag_elaborado"]').attr('checked','checked');
        
        $.post('ajax',{pajax:'modificar_estado_orden', codigo_compra: transaccion.attr('codigo_compra'), objetivo:'flag_enviado', valor:1});
        
        $.get('+notificacion',{envio_rapido:'super_rapido', transaccion:transaccion.attr('transaccion'), plantilla:'enviado'}, function () {
            $.jGrowl("<b>Arreglo enviado</b><hr />Código de compra: "+transaccion.attr('codigo_compra')+transaccion.attr('salt'));
        });
    });

    $(".marcar_cobrado").click(function(){
        var transaccion = $(this).closest('div.contenedor-compra');
        
        transaccion.find('div.ajax_estado').find('[name="flag_cobrado"]').attr('checked', 'checked').triggerHandler('click');
        
        $.get('+notificacion',{envio_rapido:'super_rapido', transaccion:transaccion.attr('transaccion'), plantilla:'facturacion_correcta'}, function () {
            $.jGrowl("<b>Arreglo cobrado</b><hr />Código de compra: "+transaccion.attr('codigo_compra')+transaccion.attr('salt'));
        });
    });

    
    $('.ver_historial').click(function (event) {
        event.preventDefault();
        jQuery.facebox('<iframe width="960px" height="700px" frameBorder="0" src="' + $(this).attr('href') + '"></iframe>');
    });
    
    $('.eliminar').click(function () {
        var control = $(this).closest('div.contenedor-compra');
        if (confirm("Desea eliminar el pedido " + control.attr('codigo_compra') + control.attr('salt') + "?"))
        {
            $.post('ajax',{pajax:'eliminar_orden',codigo_compra:control.attr('codigo_compra'), salt:control.attr('salt')}, function (){
                $.jGrowl("<b>Pedido eliminado</b><hr />Código: " + control.attr('codigo_compra') + control.attr('salt'));
            });
        }
    });
    
    $('.suspender').click(function (event){
        var control = $(this).closest('div.contenedor-compra');
        if (confirm("Desea suspender el pedido " + control.attr('codigo_compra') + control.attr('salt') + "?"))
        {
            $.post('ajax',{pajax:'suspender_orden',codigo_compra:control.attr('codigo_compra'), salt:control.attr('salt')}, function (){
                $.jGrowl("<b>Pedido suspendido</b><hr />Código: " + control.attr('codigo_compra') + control.attr('salt'));
            });
        }
    });

    $('.reactivar').click(function (event){
        var control = $(this).closest('div.contenedor-compra');
        if (confirm("Desea reactivar el pedido " + control.attr('codigo_compra') + control.attr('salt') + "?"))
        {
            $.post('ajax',{pajax:'reactivar_orden',codigo_compra:control.attr('codigo_compra'), salt:control.attr('salt')}, function (){
                $.jGrowl("<b>Pedido reactivado</b><hr />Código: " + control.attr('codigo_compra') + control.attr('salt'));
            });
        }
    });
    
    $('.editar_extras').click(function (event){
        event.preventDefault();
        jQuery.facebox('<iframe width="960px" height="700px" frameBorder="0" src="+extras?nochat=silencio&sin_cabeza=descabezar&desactivar_fb=desactivar&codigo_compra=' + $(this).attr('rel') + '"></iframe>');
    });
    
    $('.realizar_devolucion').click(function (event){
        var control = $(this).closest('div.contenedor-compra');
        var cantidadADevolver = 0.00;
        
        while (true) {
            cantidadADevolver =  prompt("¿Que cantidad de dinero desea autorizar como devolución para este arreglo?",control.attr('total'));
            
            if (isNumber(cantidadADevolver))
            {
                $.post('ajax',{pajax:'crear_devolucion',codigo_compra:control.attr('codigo_compra'), salt:control.attr('salt'), monto:cantidadADevolver}, function (){
                    alert("Devolución creada\nCódigo: " + control.attr('codigo_compra') + control.attr('salt'));
                });
                break;
            } else {
                if (!cantidadADevolver) return;
                alert('Favor ingrese una cantidad númerica.');
            }
        }
    });
    
    $('#lista_compra_animar').click(function (event) {
        $('#lista_compra').css('height','500px');
        $(this).remove();
    });
    
    $('.overlay_nocobrados').dblclick(function (event){
        $(this).remove();
    });
    
    $('#desel').click(function(){
        $('.sel_pedido').removeAttr('checked');
        obtenerLista();
    });
    
    $('.imgzoom').contextmenu(function(event){
        event.preventDefault();
        $.facebox('<img src="/imagen_266_400_'+$(this).attr('foto')+'.jpg" style="width:399px;height:600px;"></img>');
    });
    
    $('img.lazy').lazyload({ threshold : 500, effect : "fadeIn" });

}); // jquery.ready