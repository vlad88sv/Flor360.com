<?php
$tema = preg_replace('/[^\w]/', '', @$_GET['tema']);

if (empty($tema)) return;

$archivo = 'TXT/'.$tema.'.ayuda.editable';

if (!file_exists($archivo))
{
    header('Location: '. PROY_URL);
    return;
}

$GLOBAL_MOSTRAR_PIE = false;
$HEAD_titulo = PROY_NOMBRE . ' - Ayuda del sitio: '.$tema;
echo '<div style="text-align:justify">';
cargar_editable($tema.'.ayuda');
echo '</div>';

?>
