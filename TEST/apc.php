<?php
echo '<hr /><h1>ultimo_acceso [fetch]</h1>';
echo apc_fetch('ultimo_acceso');

echo '<hr /><h1>Store & Fetch</h1>';
apc_store('test_apc','funcionando');
echo apc_fetch('test_apc');
?>