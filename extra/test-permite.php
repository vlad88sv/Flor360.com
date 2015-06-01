<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>P5P2 [Escriba su nombre completo]</title>
  <script>
  function permite(elEvento, permitidos)
  {
  var numeros = ".0123456789";
  permitidos = numeros;
  var evento = elEvento || window.event;
  var codigoCaracter = evento.charCode || evento.keyCode;
  var caracter = String.fromCharCode(codigoCaracter);
  return permitidos.indexOf(caracter) != -1;
  }
</script>
</head>

<?php
$codigo="";
if($_POST)
{
$mask=$_POST["mascara"];
function evalmask($mask)
{
  $octetos = explode(".",$mask);
  $counter = 0;
  foreach($octetos as $a)
  {
    $counter+=1;
  }
  if ($counter==4)
  {
    foreach($octetos as $a => $b)
    {
      if(($b<0||$b>255)||($b>=0&&$b<10&&strlen($b)>1)||($b>=10&&$b<100&&strlen($b)>2)||($b>=100&&$b<256&&strlen($b)>3))
      {
        $a+=1;
        $error[]="Valor erroneo en el octeto $a";
      }
      else
      {
        $binoct[]=str_pad(decbin($b),8,"0",STR_PAD_LEFT);
      }
    }
    //print_r($binoct);
    foreach($binoct as $a => $b)
    {
      if($b=="00000000"||$b=="10000000"||$b=="11000000"||$b=="11100000"||$b=="11110000"||$b=="11111000"||$b=="11111100"||$b=="11111110"||$b=="11111111")
      {

      }else
      {
        $a+=1;
        $error[]="Valor erroneo en el octeto $a";
      }
    }
    if($binoct[0]=="00000000")
    {
      $error[]="El primer octeto debe ser diferente de 0";
    }
    foreach($binoct as $a => $b)
    {
      if($a>0)
      {
        if($b!="00000000"&&$binoct[($a-1)]!="11111111")
        {
          $error[]="Mascara no valida";
        }
      }
    }
    if(@$error)
    {
      return $error;
    }
    elseif($binoct)
    {
      $binoct = implode(".",$binoct);
      return $binoct;
    }
  }
  else
  {
    return "Mascara no valida";
  }
}

$resultado = evalmask($mask);
if(is_string($resultado))
{
  $codigo = $resultado;
}
else
{
  foreach($resultado as $a)
  {
    $codigo = "$a <br />";
  }
}

}

echo"
<body>
<form method=\"POST\" action=\"".$_SERVER["PHP_SELF"]."\">
    <fieldset>
        <legend>Ingrese una mascara de subred</legend>
        Mascara: <input type=\"text\" name=\"mascara\" value=\"".@$mask."\" onkeypress=\"return permite(event, 'num')\"/>
        <input type=\"submit\" value=\"PROBAR\" />
    </fieldset>
    <fieldset>
    $codigo
    </fieldset>
</form>
</body>

</html>
";
?>