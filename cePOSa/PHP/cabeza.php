<?php if (!isset($_GET['sin_cabeza'])): ?>
<div style="text-align:center;z-index:9999;position:absolute;height: 100px;width: 100%;">
    <div style="width:1000px;margin:auto;position: relative;">
        <a href="/">
            <img style="position:absolute;left:20px;top:29px;" src="/IMG/portada/pestana_grande.png" />
            <img style="position:absolute;left:30px;top:40px;" src="IMG/portada/logo.png" alt="Flor360.com Logo"/>
        </a>
        <a href="http://condolencias.com.sv" style="position:absolute;left:200px;top:29px;"><img src="/IMG/portada/pestana_condolencias.png" /></a>
    </div>
</div>
<div style="background-color:#f1f1f1;border-bottom:1px #979da9 solid;text-align:center;width:100%;height:62px;">
    <div style="width:1000px;height:100%;margin:auto;position: relative;">
        <a style="position:absolute;right:0px;" href="<?php echo PROY_URL ?>contactanos"><img src="<?php echo PROY_URL_ESTATICA; ?>IMG/portada/info_derecha.png" alt="Telefono de contacto de Flor360.com" /><br /></a>
        <div style="position:absolute;right:180px;bottom:0px;"><span style="color:#525451;font-weight:bold; font-style: italic;">Like Us Today!</span>&nbsp;&nbsp;&nbsp;<div class="fb-like" data-href="<?php echo PROY_URL_LIKE; ?>" data-send="false" data-layout="button_count" data-width="90" data-show-faces="false"></div></div>
    </div>
</div>
<?php endif; ?>