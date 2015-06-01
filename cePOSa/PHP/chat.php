<?php
protegerme(false,array(_N_vendedor));
$HEAD_titulo = 'Centro de Comando de Chats';
ACTIVAR_PAQUETES(array('ui','facebox'));
?>
<p><b>Datos necesarios:</b> 1. Nombre y correo eléctronico de quien envia, 2. Nombre de quien recibe, 3. Telefono de quien envia y de quien recibe, 4. Fecha de entrega, 5. Direccion de entrega y 6. Dedicatoria</p>
<div style="min-height:350px; border: 1px dotted #CCC;" id="contenedor_chats"></div>

<p style="border: 1px dotted #CCC;text-align: left;color: red;font-weight:bold;font-size:13px;padding:10px;">
   Información de precios de envío por zona, productos y adicionales especiales puede encontrarlo aquí: <a target="_blank" href="/+info">Menú de información [Info]</a> 
</p>

<table class="tabla-estandar">
    <tr>
        <td style="width: 200px;">
            <h2>Chats y sus estados de <input id="fecha_chat" class="datepicker" type="text" style="width:70px;" value="<?php echo mysql_date(); ?>" /></h2>
            <div id="chats_hoy"></div>
        </td>
        <td>
            <h2>Pedidos y sus estados de <input id="fecha_pedidos" class="datepicker" type="text" style="width:70px;" value="<?php echo mysql_date(); ?>" /></h2>
            <div id="pedidos_hoy"></div>    
        </td>
    </tr>
</table>


<script type="text/javascript">
    
    qTipObjetivo = null;    
    ChatsActivos = [];
    cargandoMensajes = false;
    
    function actualizarVentana() {
        $.getJSON('chatajax?ref=avc',{ajax:'actualizar_ventana_chat', fecha_chat: $("#fecha_chat").val(), fecha_pedidos: $("#fecha_pedidos").val()}, function(data) {
            $('#chats_hoy').html(data.chats_hoy);
            $('#pedidos_hoy').html(data.pedidos_hoy);
            window.setTimeout("actualizarVentana()",   2000);
        });
    }
    
    function cargarMensajes() {

        var _ids = [];
    
        $(".multichat").each(function(){
            _ids.push($(this).attr('id'));
        });

        $.getJSON('chatajax?ref=vm',{ajax:'ver_mensajes', canal:_ids}, function(datos) {
            
            for (id in datos) {
                
                var data = datos[id];
                var canal = $("#"+id);
                
                if (data.atencion == "si"){
                    document.title = "S.O.S";
                    Beep();                
                    canal.addClass('chat_atencion');
                } else {
                    document.title = "¡MIRE EL CHAT!"
                    canal.removeClass('chat_atencion');
                }
    
                canal.find(".codigo_chat").html(data.codigo_chat);
                canal.find(".atendido_por").html(data.atendido_por);
                canal.find(".ultima_vez").html(data.ultima_vez);
                canal.find(".lugar").html(data.lugar);
                
                if (data.finalizar == '1')
                {
                    canal.remove();
                    alert("Un canal ha sido removido de su vista porque otro agente lo ha finalizado.");
                }
    
                if ( data.actualizar == '1' && canal.find(".mensaje_scroll").is(':checked') )
                {
                  if (JSON.stringify(canal.find(".mensajes").html) != JSON.stringify(data.html))
                    canal.find(".mensajes").html(data.html);
                    
                    canal.find(".mensajes").scrollTo('max');
                    canal.attr('ultima_actualizacion', Math.round(new Date().getTime() / 1000));
                }
            }
            
            window.setTimeout("cargarMensajes()",      1000);
        }, 'json');
    }
    
    function abrirCanal(canal) {
        ChatsActivos.push(canal);
        $("#contenedor_chats").append('<div style="display:none;" class="multichat" id="'+canal+'" ultima_actualizacion="0"><a class="fb finalizar" style="float: right;" href="#">X</a><div style="font-size:12px;color:blue;">#<span class="codigo_chat">0</span> atendido por <span class="atendido_por"></span></div><div style="font-size:10px;color:black;font-weight:bold;">Cliente activo hace: <span class="ultima_vez"></span></div><div style="font-size:10px;color:black;font-weight:bold;">Ubicación: <span class="lugar"></span></div><div class="mensajes"></div><div class="redaccion-mensajes"><form class="ajax_mensajes" method="post" action="ajax"><input type="text" class="mensaje_chat" /><input type="checkbox" class="mensaje_scroll" value="1" checked="checked" /></form></div></div>');
        
        $("#"+canal).fadeIn();
        
        cargarMensajes();
        
        return true;

    }
    
    function cargarPendientes() {
        $.getJSON('chatajax?ref=cp',{ajax:'chats_pendientes'}, function(data) {
            if (data.hay_nuevos == "si")
            {
                $(data.canales_abiertos).each (function(){
                    var nuevo_canal = JSON.stringify(this).replace('"','').replace('"','');
                    //console.log ("canal: " + nuevo_canal + ' index: ' + $.inArray(nuevo_canal,ChatsActivos));
                    
                    if ($.inArray(nuevo_canal,ChatsActivos) == -1)
                    {
                        abrirCanal(nuevo_canal);
                    }
                });
                Beep();
            }
            window.setTimeout("cargarPendientes()",    1000);
        });
    }


    $(function (){
        $("a.abrirCanal").live('click', function(event){
            event.preventDefault();
            
            if ($.inArray($(this).attr('canal'),ChatsActivos) == -1)
            {
                abrirCanal($(this).attr('canal'));
            }
        });
               
        $('.multichat .ajax_mensajes').live('submit',function(event) {
            event.preventDefault();
            var canal = $(this).closest(".multichat");
            var mensaje = canal.find(".mensaje_chat");
            strMensaje = mensaje.val();
            mensaje.val('Enviando...');
            mensaje.attr('disabled','disabled');
            $.post('chatajax?ref=enmmc',{canal:canal.attr('id'), mensaje:strMensaje}, function (){
                mensaje.val('');
                mensaje.removeAttr('disabled');
                document.title = "Enviado";
            });
        });
          
        
        var d = new Date();
        var curr_hour = d.getHours();
        var curr_min = d.getMinutes();
        var curr_sec = d.getSeconds();
          
        var texto = '<div class="quickinfo">';
        texto += '<input style="width:100%;" type="text" value="'+ (curr_hour < 12 ? 'Buenos días, ¿como puedo ayudarle?' : 'Buenas tardes, ¿como puedo ayudarle?') +'" /><br />';
        texto += '<input style="width:100%;" type="text" value="Hola, ¿como puedo ayudarle?" /><br />';
        texto += '<input style="width:100%;" type="text" value="¿Algo más en lo que pueda ayudarle?" /><br />';
        texto += '<input style="width:100%;" type="text" value="Permitame un momento en línea mientras verifico la información" /></li>';
      texto += '<input style="width:100%;" type="text" value="Estamos ubicados en C.C. La Gran Vía, en el Kiosko KGV26, sobre el paso peatonal frente a Beninhana." /></li>';
        texto += '</div>';
        
        $(".mensaje_chat").live('click', function(event){
           $(this).qtip({
               overwrite: false,
               content: { text: texto, title: {text: 'Textos rápidos',button: 'X'} },
               style: { classes: "ui-tooltip-shadow ui-tooltip-cream ui-tooltip-shadow qtip_chat", tip: "bottom center" },
               position: { my: "bottom center",at: "top center" },
               show: { solo: true, event: event.type, ready: true },
               hide: { delay: 2000 }
           }, event);
           
           qTipObjetivo = $(this);
        });
        
        $(".quickinfo input[type='text']").live('click',function(){
            $(this).select();
            qTipObjetivo.val($(this).val());
            qTipObjetivo.focus();
        });
        
        $('.finalizar').live('click',function(event) {
            event.preventDefault();
            var canal = $(this).closest(".multichat");
            $.post('chatajax?ref=fc',{canal:canal.attr('id'), ajax:"finalizar"});
            var idx = ChatsActivos.indexOf(canal.attr('id'));
            if(idx!=-1) ChatsActivos.splice(idx, 1);
            canal.remove();
        });
        
        
        
        actualizarVentana();
        
        $.datepicker.regional["es"];
        $(".datepicker").datepicker({constrainInput: true, dateFormat : "yy-mm-dd", defaultDate: +0});
        
        cargarPendientes();
        actualizarVentana();
    });    
</script>

<script type="text/javascript">
<?php   
if (!empty($_GET['canal']))
{
    // Agregar este canal a ChatsActivos
    echo "\n".'abrirCanal("'.$_GET['canal'].'");'."\n";
}
?>
</script>