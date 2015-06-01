<?php
    require_once ("../config.php");
    require_once (__BASE__ARRANQUE."PHP/vital.php");

    echo '<h1>stubs -> genRandomString()';
    echo '<pre>'.genRandomString(4).'</pre>';
    
    echo '<h1>Tiquete</h1>';
    echo '<a href="'.PROY_URL.'+impresion?nocache=nocache&objetivo=Tiquete&transaccion=e4388945ae9868a0dff1108a574e3647f8b43e94">Ver</a>';
    
    echo '<h1>Boucher</h1>';
    echo '<a href="'.PROY_URL.'+impresion?nocache=nocache&objetivo=Boucher&transaccion=aca775933f20a59b487ebef26e415cd99f81d89f">Ver</a>';

    echo '<h1>Corte Z</h1>';
    echo '<a href="https://flor360.com/cortez?fecha=2012-02-29">Ver</a>';
    
?>