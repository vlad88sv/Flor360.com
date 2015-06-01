<?php
define('NO_MENU',true);
$_GET['sin_cabeza'] = true;
?>
<style type="text/css">
.clock {
    width: 100%;
    margin: 0 auto;
    border: none;
    color: yellow;
}

.clock ul {
    margin: 0 auto;
    padding: 0px;
    list-style: none;
    text-align: right;
    display: inline;
}

.clock ul li {
    display: inline;
    font-size: 18em;
    text-align: center;
    font-family: "Lucida Console", Monaco, monospace
    font-weight: bold;
    text-shadow: -2px -2px 0 #000, 2px -2px 0 #000, -2px 2px 0 #000, 2px 2px 0 #000; 
}

#sec {
    margin-left: 10px;
    font-size: 10em;
}

.point {
    font-family: "Courier New", monospace;
    position: relative;
    -moz-animation: mymove 1s ease infinite;
    -webkit-animation: mymove 1s ease infinite;
    padding-left: 10px;
    padding-right: 10px;
}

/* Simple Animation */
@-webkit-keyframes mymove {
    0% {
	opacity: 1.0;
	text-shadow: 0 0 50px black;
    }
    
    50% {
	opacity: 0;
	text-shadow: none;
    }
    
    100% {
	opacity: 1.0;
	text-shadow: 0 0 50px black;
    }	
}

@-moz-keyframes mymove {
    0% {
        opacity: 1.0;
        text-shadow: 0 0 50px black;
    }

    50% {
        opacity: 0;
        text-shadow: none;
    }

    100% {
        opacity: 1.0;
        text-shadow: 0 0 50px black;
    };
}

html, body {
    color: white;
    background-color: black;
}
</style>
<script type="text/javascript">
$(document).ready(function() {
// Create two variable with the names of the months and days in an array
var monthNames = [ "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre" ]; 
var dayNames= ["Sábado","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado"]

setInterval( function() {
	// Create a newDate() object and extract the seconds of the current time on the visitor's
	var seconds = new Date().getSeconds();
        var suffex = (new Date().getHours() >= 12)? 'PM' : 'AM';
	// Add a leading zero to seconds value
	//$("#sec").html(( seconds < 10 ? "0" : "" ) + seconds + " " + suffex);
	$("#sec").html(" " + suffex);
	},1000);
	
setInterval( function() {
	// Create a newDate() object and extract the minutes of the current time on the visitor's
	var minutes = new Date().getMinutes();
	// Add a leading zero to the minutes value
	$("#min").html(( minutes < 10 ? "0" : "" ) + minutes);
    },1000);
	
setInterval( function() {
	// Create a newDate() object and extract the hours of the current time on the visitor's
	var hours = new Date().getHours();
        hours = parseInt(((hours + 11) % 12 + 1));        
        
	// Add a leading zero to the hours value
	$("#hours").html(( hours < 10 ? "0" : "" ) + hours);
    }, 1000);	
});
</script>
<script type="text/javascript">
    function cargarCompra(codigo_compra) {
        $("#resultados").load('ajax', {pajax: 'info_pedido', codigo_compra: codigo_compra});
    }
    
    $(function(){
        $("#pedido_buscar").submit(function(event){
            event.preventDefault();
            $("#resultados").html('Buscando...');
            cargarCompra($("#codigo_compra").val());
            $("#codigo_compra").val('');
        });
        
        $("#pedido_buscar").focus();
         
        $(document).on('click',"#barcode__marcar_como_elaborado",function(){
            var codigo_compra = $(this).attr('rel');
            $.post('ajax',{pajax:'modificar_estado_orden', codigo_compra: codigo_compra, objetivo:'flag_elaborado', valor: '1'}, function () {
                cargarCompra(codigo_compra + '0');
            });
        }); 
    });
    
    function enfocar(){$("#codigo_compra").focus();};
    
    window.setInterval(enfocar, 500);
</script>
<div style="margin: 5px 0;">
<form id="pedido_buscar" style="display:inline;" action="" method="post" autocomplete='off'>
<input id="codigo_compra" type="text" style="font-size:2em;vertical-align: middle;width:80%" value="" />
<input type="submit" style="font-size:2em;width:18%;vertical-align: middle;" value="Búscar" />
</form>
</div>
<br /><hr /><br />
<div id="resultados">
    
</div>
<div style="position:fixed;right:0;left:0;bottom:0;text-align:center;z-index:99;" class="clock">
<ul>
    <li id="hours"></li>
    <li class="point">:</li>
    <li id="min"></li>
    <!--<li class="point">:</li>!-->
    &nbsp;&nbsp;&nbsp;
    <li id="sec"></li>
</ul>
</div>