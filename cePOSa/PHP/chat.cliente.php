<?php
if (PLATAFORMA_MOBIL)
    return;

//    return;
    
if ( S_iniciado() && (_F_usuario_cache('flag_chat') == 1) )
{
?>
    <audio id="beep">
    <source src="/SND/chirp.ogg">
    <source src="/SND/chirp.mp3">
    </audio>
    
    <script type="text/javascript">    
    function Beep() {
        if(typeof(Storage)=="undefined") return;
        
        if (new Date().getTime() > (localStorage.piopio || 0))
        {
            localStorage.piopio = (new Date().getTime() + 20000);
            $('#beep').get(0).play();
        }
    }
    
    var titulo = false;
    IDChequeoNuevosChats = 0;

    function chequearNuevosChats()
    {
        $.getJSON('chatajax?ref=vnc',{ajax:'verificar_nuevos_chats'}, function(data) {
            if (data.hay_nuevos == "si")
            {
                if (titulo)
                    document.title = "pip! - 911 - S.O.S - HELP";
                else
                    document.title = "¡MIRE EL CHAT!";
                
                titulo = !titulo;
                
                $.jGrowl('Chats esperando respuesta: <ul class="sin_margen">'+data.lista_pendientes+'</ul><hr /><a href="+chat">Verlos todos</a>');
                
                Beep();
            }
        });
    }
    
    $(function(){
        IDChequeoNuevosChats = window.setInterval("chequearNuevosChats()",5000);
        $.cookie("socio", 'socio', { expires: 15, path:  '/' });
    });
    </script>
<?php
}

if (S_iniciado() || isset($_GET['fb'])  || (__DESACTIVAR_CHAT && !isset($_GET['chat'])))
    return;

// Búsquemos si hay personas disponibles para chat, si no para que...
$hayAgentes = (apc_fetch('ultimo_acceso') > (time() - 300));
    
?>
<script type="text/javascript">
var ultimaActualizacion = 0;
var chatAyuda = true;
var chatID = 0;
var actualizando = false;

function cargarMensajes() {
    if ($.cookie("chat_minimizado", {path: '/'}) != 1)
        comando = 'ver_mensajes';
    else
        comando = 'ver_ultimo_mensaje'
        
    $.getJSON(('chatajax?ref='+comando),{ajax:comando,ultima_actualizacion:ultimaActualizacion}, function(data) {
        ultimaActualizacion = Math.round(new Date().getTime() / 1000);
        actualizando = false;
        //if (data.actualizar == '0') return;
        $("#mensajes").html(data[$.cookie("canal", {path: '/'})].html);
        $("#mensajes").scrollTo('max');
    });
}

function iniciarChat() {
    $('#chat #mensaje_chat').val('');
    cargarMensajes();
    chatID = window.setInterval("cargarMensajes()",1000);
    if ($.cookie("chat_minimizado", {path: '/'}) != 1)
    {
        $('#chat').css('height','225px');
        $('#mensajes').css('height','200px');
        $('#chat_minimizar').show();
        $('#chat_maximizar').hide();
        $('#chat_cerrar').show();
        $.cookie("chat_minimizado", 0, {path: '/'});
    }
    $('#chat_info').hide();
}

function MinimizarChat() {    

    /*    
    if ($.cookie("canal") != null)
        $.post('chatajax?ref=mc',{mensaje:'Flor360: cliente minimizó el chat!'});
    */
   
    $('#chat').css('height','auto');
    $('#mensajes').css('height','auto');
    $('#mensajes').empty();
    $('#chat_minimizar').hide();
    $('#chat_maximizar').show();
    $('#chat_cerrar').hide();
    $.cookie("chat_minimizado", 1, {path: '/'});
    $('#chat_info').show();
}

function CerrarChat() {
    
    if ($.cookie("canal") != null)
        $.post('chatajax?ref=cc',{mensaje:'Flor360: cliente cerró el chat!'});
    $.cookie("chat_minimizado", null, {path: '/'});
    $.cookie("canal", null, {path: '/'});
    window.clearInterval(chatID);
    MinimizarChat();
}

$(document).ready(function() {
    if ($.cookie("canal", {path: '/'}) != null)
        iniciarChat();
    
    $('#chat #mensaje_chat').click(function(){
        if (chatAyuda)
            $('#chat #mensaje_chat').val('');
    });
    
    $('#chat #ajax_mensajes').submit(function(event) {
        event.preventDefault();
        $.post('chatajax',{mensaje:$('#mensaje_chat').val()}, function(data){
            if (typeof data.canal != 'undefined')
                $.cookie("canal", data.canal, {path: '/'});
        }, 'json');
        $.cookie("chat_minimizado", 0, {path: '/'});
        $('#chat #mensaje_chat').val('');
        iniciarChat();
    });
    
    $('#chat #enviar').click(function () {$('#ajax_mensajes').submit();$("#mensajes").scrollTo('max');});
    $('#chat #chat_minimizar').click(function () {MinimizarChat();});
    $('#chat #chat_maximizar').click(function () {$.cookie("chat_minimizado", 0, {path: '/'});iniciarChat();});
    $('#chat #chat_cerrar').click(function () {CerrarChat();});
});

$(document).ready(function() {
    if (0 && $(window).width() > 1100)
    {
        $("#ayuda_telefonica").css('display','block').css('left',($("#wrapper").offset().left + $("#wrapper").width() + 15));
        $("#ayuda_telefonica_enviar").click(function(){
            $.post('ajax',{pajax:'ayuda_telefonica',telefono:$("#ayuda_telefonica_telefono").val(),nombre:$("#ayuda_telefonica_nombre").val()});
            $("#ayuda_telefonica").html('<p>Gracias, será contactado brevemente.</p>');
        });
    }
    
    if (<?php echo (PLATAFORMA_MOBIL ? 'false' : 'true'); ?> && $(window).width() > 800)
    {
        $("#chat").show('fast');
    }
});
</script> 
<?php if ($hayAgentes && (date('G') >= 10 && date('G') < 22) ): ?>
<div id="chat" style="display:none;">
    <div id="chat_controles">
    <a class="fb" href="javascript:void(0)" id="chat_minimizar" title="Minimizar"><img src="/IMG/iconos/minimizar.gif" /></a>
    <a class="fb" href="javascript:void(0)" id="chat_maximizar" title="Maximizar"><img src="/IMG/iconos/maximizar.gif" /></a>
    <a class="fb" href="javascript:void(0)" id="chat_cerrar" title="Cerrar"><img src="/IMG/iconos/cerrar.gif" /></a>
    </div>
    <div id="chat_info">
    <table>
    <tr>
        <td><img src="IMG/stock/agente.jpg" /></td>
        <td>¿Necesita ayuda?<br />Puede chatear con uno de nuestros asistentes aquí.</td>
    </tr>
    </table>
    </div>
    <div id="mensajes">
    </div>
    <div id="redaccion-mensajes">
        <form id="ajax_mensajes" method="post" action="<?php echo PROY_URL; ?>chatajax?ref=nm">
            <input type="text" id="mensaje_chat" name="mensaje_chat" value="Envie su mensaje para iniciar el chat" />
            <a class="fb" href="javascript:void(0)" id="enviar"><img src="/IMG/iconos/mensajes.gif" /></a>
        </form>
    </div>
</div>
<?php endif;?>
<?php
// Habilitar soporte telefónico de Lunes a Viernes de 8am a 8pm y Sabado de 8am a 12pm.
// Deshabilitado desde 27 de Sept por Alejandro.
/*
if ((date('N') > 0 && date('N') < 6 && date('G') > 7 && date('G') < 23) || ((date('N') == 7 || date('N') == 6) && date('G') > 11 && date('G') < 23) ):
?>
<div id="ayuda_telefonica" style="display: none;position: fixed;top:0px;background-color:#a9d774;width:112px;padding:5px;text-align:center;">
    <p style="font-weight:bold;font-size: 11px;">Ayuda por teléfono</p>
    <p><b>¿</b>Se encuentra indeciso sobre cúal arreglo escoger<b>?</b>, <b>¿</b>tiene consultas sobre el envío<b>?</b>, <b>¿</b>alguna otra duda en la que le podamos ayudar<b>?</b>, complete y envie este formulario y uno de nuestros agentes le llamará de inmediato para asistirlo.</p>
    <label style="display: block;margin-top: 10px;" for="ayuda_telefonica_nombre">Su nombre:</label>
    <input id="ayuda_telefonica_nombre" style="width:100px;" type="text" value=""/>
    <label style="display: block;margin-top: 10px;" for="ayuda_telefonica_telefono">Teléfono fijo o celular:</label>
    <input id="ayuda_telefonica_telefono" style="width:100px;" type="text" value=""/>
    <input type="button" style="margin-top: 10px;border-radius:10px;background-color:#ff007a;border:1px solid #c7255e;color:white;padding:0px 3px;font-size: 11px;" id="ayuda_telefonica_enviar" value="Enviar" />
</div>
<?php
endif;
*/
?>
