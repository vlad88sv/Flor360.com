<?php
protegerme();
require_once ("$base/PME/phpMyEdit.class.php");
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
$opts['tb'] = db_prefijo.'SSL_compra_contenedor';

// Name of field which is the unique key
$opts['key'] = 'codigo_compra';

// Type of key field (int/real/string/date etc.)
$opts['key_type'] = 'int';

// Sorting field(s)
$opts['sort_field'] = array('codigo_compra');

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

$opts['fdd']['codigo_compra'] = array(
  'name'     => 'Codigo compra',
  'select'   => 'T',
  'options'  => 'AVCPDR', // auto increment
  'maxlen'   => 11,
  'default'  => '0',
  'sort'     => true
);
$opts['fdd']['codigo_variedad'] = array(
  'name'     => 'Codigo variedad',
  'select'   => 'T',
  'maxlen'   => 11,
  'sort'     => true
);
$opts['fdd']['metodo_pago'] = array(
  'name'     => 'Método de pago',
  'select'   => 'T',
  'maxlen'   => -1,
  'sort'     => false,
  'values'   => array('tarjeta','domicilio','abono','kiosko','kiosko_efectivo','kiosko_credito')
);
$opts['fdd']['cobrar_a'] = array(
  'name'     => 'Cobro con atención a',
  'select'   => 'T',
  'maxlen'   => 300,
  'sort'     => true
);
$opts['fdd']['cobrar_en'] = array(
  'name'     => 'Cobrar en la dirección',
  'select'   => 'T',
  'maxlen'   => 300,
  'sort'     => true
);
$opts['fdd']['nombre_t_credito'] = array(
  'name'     => 'Nombre del titular de tarjeta',
  'select'   => 'T',
  'maxlen'   => 300,
  'sort'     => true
);
$opts['fdd']['n_credito'] = array(
  'name'     => '# en tarjeta',
  'select'   => 'T',
  'maxlen'   => -1,
  'sort'     => true
);
$opts['fdd']['n_credito']['sql'] = 'AES_DECRYPT(n_credito,"'.db__key_str.'")';
$opts['fdd']['n_credito']['sqlw'] = 'AES_ENCRYPT($val_qas,"'.db__key_str.'")';
$opts['fdd']['fecha_exp_t_credito'] = array(
  'name'     => 'Fecha de exp. de tarjeta',
  'select'   => 'T',
  'maxlen'   => 15,
  'sort'     => true
);

if (isset($_POST['PME_sys_savechange']))
    registrar($_POST['PME_sys_rec'],'edición','Orden modificada');

// Now important call to phpMyEdit
new phpMyEdit($opts);

?>
