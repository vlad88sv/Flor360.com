<?php
echo addslashes(preg_replace('/(\r\n|\n|\r)/','',file_get_contents('../TXT/buenas_direcciones.ayuda.editable')));
?>