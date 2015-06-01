<?php
require_once('../config.php');
require_once (__BASE__ARRANQUE.'PHP/vital.php');

echo 'De: ' . PROY_MAIL_POSTMASTER.' a: '. 'vladimiroski@gmail.com' .'<br />';
echo "<div>". getcwd() . "</div>";

$extra[] = array('ruta' => '/var/www/flor360.com/IMG/portada/logo.png', 'cid' => 'logo', 'alt' => 'logo', 'tipo' => 'image/png');

echo (string) correoSMTP('v.hidalgo@mupi.com.sv',
                         'Test de flor360.com',
                         'Hola, esta es una prueba SMTP desde flor360.com + '.time() . '<br /><img src="cid:logo" />',
                         true,
                         $extra
                        );
?>
