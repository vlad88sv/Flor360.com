<?php
if (isset($_COOKIE[ini_get('session.name')]))
{
   session_start();
   $_SESSION['rnd'] = time();
   // Extend cookie life time by an amount of your liking
   $cookieLifetime = 365 * 24 * 60 * 60; // A year in seconds
   setcookie(session_name(),session_id(),time()+$cookieLifetime);
}

function _F_sesion_cerrar(){
   db_agregar_datos('latidos',array('ID_usuario' => _F_usuario_cache('codigo_usuario'), 'fechatiempo' => mysql_datetime(), 'categoria' => 'salida'));
   setcookie(session_name(), session_id(), 1, '/');
   unset($_SESSION);
   session_destroy ();
   header('location: '.PROY_URL);
   return;
}

function S_iniciado(){
   return isset($_SESSION['autenticado']);
}
?>
