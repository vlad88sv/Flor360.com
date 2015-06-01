<?php if (empty($_GET['canal'])) return; ?>
<style type="text/css">
body {font-size:10px;}
#mensajes {width:245px;background-color:white;}
#mensajes .mensaje {margin-bottom:3px;border-radius: 2px; padding:6px;margin:4px;font-size:10px;color:#000;word-wrap: break-word;text-align:left;}
#mensajes .remoto {background-color:#F7F7F7;}
#mensajes .origen {background-color:#FFF2F2;}
#mensajes .originador {font-weight: bold; display: inline-block; width: 50px;}
</style>
<script type="text/javascript">
var request = new XMLHttpRequest();
var http = new XMLHttpRequest();

function cargarMensajes()
{
    request.open('GET', 'chatajax?&modo=simple&ajax=ver_mensajes&canal=<?php echo $_GET['canal']; ?>', true);
    request.onreadystatechange = function (event) {if (request.readyState == 4) {document.getElementById("mensajes").innerHTML = request.responseText;request.close;}};
    request.send(null);
}
cargarMensajes();
chatID = window.setInterval(function (){cargarMensajes();},2000);

function EnviarMensaje()
{
    var params = 'ajax=ver_mensajes&mensaje=' + document.getElementById('mensaje').value + '&canal=<?php echo $_GET['canal']; ?>';
    http.open('POST', 'chatajax?&modo=simple', true);
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    http.setRequestHeader("Content-length", params.length);
    http.setRequestHeader("Connection", "close");
    http.onreadystatechange = function (event) {if (request.readyState == 4) {document.getElementById("mensajes").innerHTML = request.responseText;request.close;}};
    http.send(params);
    document.getElementById('mensaje').value = '';
}
</script>
<input type="button" onclick="Finalizar" value="Finalizar" />
<div id="mensajes"></div>
<input name="mensaje" id="mensaje" type="text" value="" /><input type="button" onclick="EnviarMensaje()" value="Enviar" />