<?php
 /* Tour
  * El objetivo es realizarle al usuario distintas preguntas
  * para determinar el tipo de arreglo perfecto para el/la
  */
$arrJS[] = 'jquery.ui.core';
$arrJS[] = 'jquery.ui.widget';
$arrJS[] = 'jquery.ui.mouse';
$arrJS[] = 'jquery.ui.slider';
$arrJS[] = 'jquery.ui.accordion';
$arrCSS[] = 'CSS/css/ui-lightness/jquery-ui';
?>
<table id="tour">
    <tr>
	<td>
	    <div id="accordion">
		<h3><a href="#">Amor</a></h3>
		<div>Amor</div>
		<h3><a href="#">Cumpleaños</a></h3>
		<div>Cumpleaños</div>
		<h3><a href="#">Boda</a></h3>
		<div>Boda</div>
		<h3><a href="#">Fiesta rosa (15 años)</a></h3>
		<div>Fiesta rosa (15 años)</div>
		<h3><a href="#">Bautizo / Baby shower</a></h3>
		<div>Bautizo / Baby shower</div>
		<h3><a href="#">Regalo empresarial/corporativo</a></h3>
		<div>Regalo empresarial/corporativo</div>
		<h3><a href="#">Condolencias / Funeral</a></h3>
		<div>Condolencias / Funeral</div>
	    </div>
	    </td>
	<td style="width: 30px;">
	    <p id="amount" style="border:0; color:#f6931f; font-weight:bold;">$50</p>
	    <div id="slider"></div>
	</td>
    </tr>
</table>

<script>
$(function() {
    $( "#slider" ).slider({orientation: 'vertical',value:50, min: 25, max: 250,step: 25,slide: function( event, ui ) {$( "#amount" ).html( "$" + ui.value );}});
    $( "#amount" ).val( "$" + $( "#slider" ).slider( "value" ) );
    $( "#accordion" ).accordion({change: function(event, ui) { alert($(ui.newHeader).html()); }});
});
</script>
