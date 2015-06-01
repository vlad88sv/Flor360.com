<?php if (!isset($_GET['sin_cabeza'])): ?>
<div style="text-align:center;z-index:1000;position:absolute;height: 100px;width: 100%;">
    <div style="width:1000px;margin:auto;position: relative;">
        <a href="http://flor360.com" style="position:absolute;left:20px;top:29px;"><img src="/IMG/portada/pestana_flor360.png" /></a>
        <a href="/">
            <img style="position:absolute;left:195px;top:29px;" src="/IMG/portada/pestana_grande.png" />
            <img style="position:absolute;left:203px;top:43px;" src="/IMG/portada/logo_condolencias.png" alt="Condolencias Logo"/>
        </a>
        
    </div>
</div>
<div style="background-color:#f1f1f1;z-index:2000;border-bottom:1px #979da9 solid;text-align:center;width:100%;height:62px;">
    <div style="width:1000px;height:100%;margin:auto;position: relative;">
        <div style="position: relative;left: 400px;width: 300px;top: 0px;bottom: 0px;font-style: italic;font-size: 1.1em;">"En los momentos mas difíciles de la vida te brindamos nuestro servicio, haciendo llegar a tus seres queridos tus muestras de apoyo con nuestros arreglos funebres. Disponible para todas las funerarias de San Salvador y alrededores"</div>
        <a style="position:absolute;right:0px;top:0px;" href="<?php echo PROY_URL ?>contactanos"><img src="<?php echo PROY_URL_ESTATICA; ?>IMG/portada/info_derecha.png" alt="Telefono de contacto de Flor360.com" /><br /></a>
        <div style="position:absolute;right:180px;bottom:0px;"><span style="color:#525451;font-weight:bold; font-style: italic;">Like Us Today!</span><br /><div class="fb-like" data-href="<?php echo PROY_URL_LIKE; ?>" data-send="false" data-layout="button_count" data-width="90" data-show-faces="false"></div></div>
    </div>
</div>
<?php endif; ?>