<div style="margin: 10px 0;">
<form id="geo_buscar" action="" method="post">
<input id="geo_ubicacion" type="text" style="font-size:3em;width:80%;vertical-align: middle;" value="" />
<input type="submit" style="font-size:3em;width:18%;vertical-align: middle;" value="Búscar" />
</form>
</div>
<div id="resultados">
    
</div>
<script type="text/javascript">
    $(function(){
        $("#geo_buscar").submit(function(event){
            event.preventDefault();
            $("#resultados").html('Buscando...');
            $("#resultados").load('ajax', {pajax: 'ubicacion', ubicacion: $("#geo_ubicacion").val()});
        });
    });
</script>