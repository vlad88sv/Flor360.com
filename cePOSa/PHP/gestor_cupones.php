<?php
protegerme();
require_once (__BASE__ARRANQUE."PHP/PME/phpMyEdit.class.php");
// MySQL host name, user name, password, database, and table
$opts['dbh'] = $db_link;
$opts['page_name'] = PROY_URL_ACTUAL;
$opts['tb'] = db_prefijo.'cupones';

// Name of field which is the unique key
$opts['key'] = 'codigo_cupon';

// Type of key field (int/real/string/date etc.)
$opts['key_type'] = 'int';

// Sorting field(s)
$opts['sort_field'] = array('codigo_cupon');

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
	'time'  => true,
	'tabs'  => true
);

// Set default prefixes for variables
$opts['js']['prefix']               = 'PME_js_';
$opts['dhtml']['prefix']            = 'PME_dhtml_';
$opts['cgi']['prefix']['operation'] = 'PME_op_';
$opts['cgi']['prefix']['sys']       = 'PME_sys_';
$opts['cgi']['prefix']['data']      = 'PME_data_';

/* Get the user's default language and use it if possible or you can
   specify particular one you want to use. Refer to official documentation
   for list of available languages. */
$opts['language'] = 'ES-AR-UTF8';

$opts['fdd']['codigo_cupon'] = array(
  'name'     => 'Codigo cupon',
  'select'   => 'T',
  'maxlen'   => 11,
  'default'  => '0',
  'options'  => 'AVCPDR', // auto increment
  'sort'     => true
);

$opts['fdd']['codigo'] = array(
  'name'     => 'Codigo',
  'select'   => 'T',
  'maxlen'   => 150,
  'sort'     => true
);

$opts['fdd']['valor'] = array(
  'name'     => 'Valor',
  'select'   => 'T',
  'maxlen'   => 12,
  'sort'     => true
);

// Now important call to phpMyEdit
new phpMyEdit($opts);

?>
