<?php
protegerme();
require_once (__BASE__ARRANQUE."PHP/PME/phpMyEdit.class.php");
$GLOBAL_MOSTRAR_PIE = false;
set_time_limit(0);
/*
 * IMPORTANT NOTE: This generated file contains only a subset of huge amount
 * of options that can be used with phpMyEdit. To get information about all
 * features offered by phpMyEdit, check official documentation. It is available
 * online and also for download on phpMyEdit project management page:
 *
 * http://platon.sk/projects/main_page.php?project_id=5
 *
 * This file was generated by:
 *
 *                    phpMyEdit version: 5.7.1
 *       phpMyEdit.class.php core class: 1.204
 *            phpMyEditSetup.php script: 1.50
 *              generating setup script: 1.50
 */

// MySQL host name, user name, password, database, and table
$opts['dbh'] = $db_link;
$opts['page_name'] = PROY_URL_ACTUAL;
$opts['tb'] = db_prefijo.'filtros';

// Name of field which is the unique key
$opts['key'] = 'nombre_filtro';

// Type of key field (int/real/string/date etc.)
$opts['key_type'] = 'string';

// Sorting field(s)
$opts['sort_field'] = array('nombre_filtro');

// Number of records to display on the screen
// Value of -1 lists all records in a table
$opts['inc'] = 15;

// Options you wish to give the users
// A - add,  C - change, P - copy, V - view, D - delete,
// F - filter, I - initial sort suppressed
$opts['options'] = 'ACPVDF';

// Number of lines to display on multiple selection filters
$opts['multiple'] = '4';

// Navigation style: B - buttons (default), T - text links, G - graphic links
// Buttons position: U - up, D - down (default)
$opts['navigation'] = 'DB';

// Display special page elements
$opts['display'] = array(
	'form'  => true,
	'query' => true,
	'sort'  => true,
	'time'  => false,
	'tabs'  => true
);

// Set default prefixes for variables
$opts['js']['prefix']               = 'PME_js_';
$opts['dhtml']['prefix']            = 'PME_dhtml_';
$opts['cgi']['prefix']['operation'] = 'PME_op_';
$opts['cgi']['prefix']['sys']       = 'PME_sys_';
$opts['cgi']['prefix']['data']      = 'PME_data_';

$opts['language'] = 'ES-AR-UTF8';

$opts['fdd']['nombre_filtro'] = array(
  'name'     => 'Nombre filtro',
  'select'   => 'T',
  'maxlen'   => 150,
  'sort'     => true
);
$opts['fdd']['filtro_sql'] = array(
  'name'     => 'Filtro sql',
  'select'   => 'T',
  'maxlen'   => -1,
  'textarea' => array(
    'rows' => 5,
    'cols' => 50),
  'sort'     => true
);
$opts['fdd']['descripcion_filtro'] = array(
  'name'     => 'Descripcion filtro',
  'select'   => 'T',
  'maxlen'   => 300,
  'sort'     => true
);

echo <<< HTML
<p>Advertencia: la creación y modificación de filtros requiere conocimientos de SQL (especificamente MySQL). A continuación se muestra la información necesaria para saber como crear los filtros; como regla general, si no comprende la siguiente información usualmente significa que no debería modificar nada</p>
<p>
<font face="Courier New" size="2">
<font color = "blue">SELECT</font>&nbsp;&nbsp;&nbsp;<font color = "#FF0080"><b>Concat</font></b><font color = "maroon">(</font><font color = "maroon">&quot;$&quot;</font><font color = "silver">,</font><font color = "maroon">(</font><font color = "#FF0080"><b>If</font></b><font color = "maroon">(</font><font color = "#FF0080"><b>Min</font></b><font color = "maroon">(</font><font color = "maroon">pv</font><font color = "silver">.</font><font color = "maroon">precio</font><font color = "maroon">)</font>&nbsp;<font color = "silver">=</font>&nbsp;<font color = "#FF0080"><b>Max</font></b><font color = "maroon">(</font><font color = "maroon">pv</font><font color = "silver">.</font><font color = "maroon">precio</font><font color = "maroon">)</font><font color = "silver">,</font><font color = "maroon">pv</font><font color = "silver">.</font><font color = "maroon">precio</font><font color = "silver">,</font>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color = "#FF0080"><b>Concat</font></b><font color = "maroon">(</font><font color = "#FF0080"><b>Min</font></b><font color = "maroon">(</font><font color = "maroon">pv</font><font color = "silver">.</font><font color = "maroon">precio</font><font color = "maroon">)</font><font color = "silver">,</font><font color = "maroon">&quot;&nbsp;-&nbsp;$&quot;</font><font color = "silver">,</font><font color = "#FF0080"><b>Max</font></b><font color = "maroon">(</font><font color = "maroon">pv</font><font color = "silver">.</font><font color = "maroon">precio</font><font color = "maroon">)</font><font color = "maroon">)</font><font color = "maroon">)</font><font color = "maroon">)</font><font color = "maroon">)</font>&nbsp;<font color = "blue">AS</font>&nbsp;<font color = "maroon">&quot;precio_combinado&quot;</font><font color = "silver">,</font>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color = "maroon">pv</font><font color = "silver">.</font><font color = "maroon">foto</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color = "blue">AS</font>&nbsp;<font color = "maroon">&quot;variedad_foto&quot;</font><font color = "silver">,</font>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color = "#FF0080"><b>If</font></b><font color = "maroon">(</font><font color = "maroon">pc</font><font color = "silver">.</font><font color = "maroon">titulo</font>&nbsp;<font color = "silver">=</font>&nbsp;<font color = "maroon">&quot;&quot;</font><font color = "silver">,</font><font color = "maroon">&quot;sin&nbsp;titulo&quot;</font><font color = "silver">,</font><font color = "maroon">pc</font><font color = "silver">.</font><font color = "maroon">titulo</font><font color = "maroon">)</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color = "blue">AS</font>&nbsp;<font color = "maroon">&quot;contenedor_titulo&quot;</font><font color = "silver">,</font>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color = "maroon">pc</font><font color = "silver">.</font><font color = "maroon">descripcion</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color = "blue">AS</font>&nbsp;<font color = "maroon">&quot;contenedor_descripcion&quot;</font><font color = "silver">,</font>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color = "maroon">pc</font><font color = "silver">.</font><font color = "maroon">codigo_producto</font>
<br><font color = "blue">FROM</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color = "maroon">flores_producto_variedad</font>&nbsp;<font color = "blue">AS</font>&nbsp;<font color = "maroon">pv</font>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color = "blue">LEFT</font>&nbsp;<font color = "blue">JOIN</font>&nbsp;<font color = "maroon">flores_producto_contenedor</font>&nbsp;<font color = "blue">AS</font>&nbsp;<font color = "maroon">pc</font>
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color = "blue">USING</font><font color = "maroon">(</font><font color = "maroon">codigo_producto</font><font color = "maroon">)</font>
<br><font color = "blue">GROUP</font>&nbsp;<font color = "blue">BY</font>&nbsp;<font color = "maroon">pv</font><font color = "silver">.</font><font color = "maroon">codigo_producto</font>
<br><font color = "blue">LIMIT</font>&nbsp;&nbsp;&nbsp;&nbsp;<font color = "black">0</font><font color = "silver">,</font><font color = "black">30</font>
</font>
</p>
HTML;
// Now important call to phpMyEdit
new phpMyEdit($opts);

?>
