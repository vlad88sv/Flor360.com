<?php
$memcache = new Memcached();
$memcache -> addServer('127.0.0.1', 11211);

// Usar solamente en scripts que no procesen post
function memcache_iniciar($contexto,$discriminador='')
{
    global $memcache;
    
    if (!MEMCACHE_ACTIVO || isset($_GET['nocache']) || !empty($_POST))
        return;

    $hash = sha1($contexto.serialize(array($_SERVER['SERVER_PORT'],@$_SERVER["SERVER_NAME"],$discriminador)));
    $buffer = $memcache->get($hash);

    if ($buffer)
    {        
        echo $buffer;
        echo '<!-- memcache.hash :: '.$hash.' !-->';
        return true;
    }
    
    ob_start(); // Nota: usar memcache_finalizar() o no se veran los datos!
    
    return false;
}

function memcache_finalizar($contexto, $discriminador='', $duracion = '+1 hour')
{
    global $memcache;
    
    if (!MEMCACHE_ACTIVO || isset($_GET['nocache']) || !empty($_POST))
        return;
    
    $contenido = ob_get_clean();
    
    $hash = sha1($contexto.serialize(array($_SERVER['SERVER_PORT'],@$_SERVER["SERVER_NAME"],$discriminador)));
    $memcache -> set($hash, $contenido, strtotime($duracion));
    
    return $contenido;
}
?>