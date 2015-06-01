<?php
$matriz="";
$val=array();

if(isset($_POST["Accion"]))
{
   for($x=0;$x<$_POST["Filas"];$x++)
   {
      for($y=0;$y<$_POST["Columnas"];$y++)
      {
         $val[$x][$y] = @$_POST["val"][$x][$y];
      }
   }
   switch($_POST["Accion"])
   {
   case "crear":
      $matriz .= "<table>";
         for($x=0;$x<$_POST["Filas"];$x++)
         {
            $matriz .= "<tr>";
            for($y=0;$y<$_POST["Columnas"];$y++)
            {
               $matriz .= "<td><input type=\"text\" name=\"val[".$x."][".$y."]\" /></td>";
            }
            $matriz .= "</tr>";
         }
      $matriz .= "</table><br />
               <input type=\"submit\" name=\"Accion\" value=\"calcular\"";
   break;
   case "calcular":
	print_r($_POST);
      $matriz .= "<table>";
         for($x=0;$x<$_POST["Filas"];$x++)
         {
            $sumcol="";
            $matriz .= "<tr>";
            for($y=0;$y<$_POST["Columnas"];$y++)
            {
               $sumcol+=$val[$x][$y];
               $matriz .= "<td><input type=\"text\" name=\"val[".$x."][".$y."]\" value=\"".$val[$x][$y]."\"]\"/></td>";
            }
            $matriz .= "<td>$sumcol</td></tr>";
         }
      $matriz .= "</table><br />
               <input type=\"submit\" name=\"Accion\" value=\"calcular\"";
   break;
   }
   
}
      
   echo"
   <!DOCTYPE html PUBLIC \"~//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\" >
   <html xmlns:\"http://www.w3.org/1999/xhtml\">
      <head>
         <title>P4P3 [Escriba aquí su nombre completo]</title>
      </head>
      <body>
         <form method=\"POST\" action=\"$_SERVER[PHP_SELF]\">
            <fieldset>
               <legend>Pr&aacute;ctica 4 - Ejercicio 3</legend>
                  <table>
                     <tr>
                        <td>
                           Filas
                        </td>
                        <td>
                           <input type=\"text\" name=\"Filas\" value=\"".$_POST["Filas"]."\" />
                        </td>
                     </tr>
                     <tr>
                        <td>
                           Columnas
                        </td>
                        <td>
                           <input type=\"text\" name=\"Columnas\" value=\"".$_POST["Columnas"]."\" />
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <input type=\"submit\" name=\"Accion\" value=\"crear\" />
                        </td>
                     </tr>
                  </table>
            </fieldset>
            <fieldset>
               <legend>MATRIZ</legend>
               $matriz
            </fieldset>
         </form>
      </body>
   </html>";
?>
