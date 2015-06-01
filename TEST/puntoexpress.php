<?php
require_once ("../config.php");
require_once (__BASE__ARRANQUE."PHP/vital.php");
require_once (__BASE__ARRANQUE."PHP/lib_puntoexpress.php");
?>
<html>
<head>
    <meta charset="utf-8">
    <!--<link rel="stylesheet" href="<?php echo PROY_URL; ?>CSS/pex.css" media="all">!-->
    <link rel="stylesheet" href="http://desa.api-services.puntoxpress.com:8888/PEXTokenServices/css/pexStyle-min.css" media="all">
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
</head>
<body>
</body>
<?php
$pex = new puntoexpress();
$pex->desarrollo = true;
list ($control, $impresion) = $pex->enviar('quepex','1234ABCD','50','c.duran@flor360.com','79859476');

?>
<script type="text/javascript">
    console.log($("button.btn-primary").html());
    console.log($("#quepex_impresion"));
</script>
</html>