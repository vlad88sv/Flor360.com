<?php
//return;

$t['start'] = microtime(true);
ini_set('memory_limit', '256M');
set_time_limit(0);

require_once ("config.php");
require_once (__BASE__ARRANQUE."PHP/vital.php");
require_once (__BASE__ARRANQUE.'PHP/phmagick/phmagick.php');

function mini_bench_to($arg_t, $arg_ra=false) 
{
  $tttime=round((end($arg_t)-$arg_t['start'])*1000,4);
  if ($arg_ra) $ar_aff['total_time']=$tttime;
  else $aff="total time : ".$tttime."ms\n";
  $prv_cle='start';
  $prv_val=$arg_t['start'];

  foreach ($arg_t as $cle=>$val)
  {
      if($cle!='start')    
      {
          $prcnt_t=round(((round(($val-$prv_val)*1000,4)/$tttime)*100),1);
          if ($arg_ra) $ar_aff[$prv_cle.' -> '.$cle]=$prcnt_t;
          $aff.=$prv_cle.' -> '.$cle.' : '.$prcnt_t." %\n";
          $prv_val=$val;
          $prv_cle=$cle;
      }
  }
  if ($arg_ra) return $ar_aff;
  return $aff;
}

function calculateTextWidth($text, $font_size) { 
$draw = new ImagickDraw();
$draw->setFont( "/usr/share/fonts/truetype/msttcorefonts/arial.ttf" );
$draw->setFontSize( $font_size );
$im = new Imagick();
$ret = $im->queryFontMetrics( $draw, $text );
return $ret['textWidth'];
}

// Genera un libro de arreglos en formato tabloide. Ver ejemplo en Docs/libro.tabloide.jpg
// Ej. Para página 1:
/*
  **********
 * Logo
 * [1] [2]
 * Logo 
 * [5] [7]
 *
  **********
 */
// Ej. Para página 2:
/*
  **********
 * Logo
 * [3] [4]
 * Logo
 * [6] [8]
 *
  **********
 */
 $t['init_values'] = microtime(true);
 $c = 'SELECT fpc.codigo_producto, codigo_variedad, fpc.titulo, FORMAT(FLOOR(precio*0.9),2) AS precio, FORMAT(FLOOR(MIN(precio)*0.9),2) AS min_precio, FORMAT(FLOOR(MAX(precio)*0.9),2) AS max_precio, fpv.foto FROM flores_producto_variedad AS fpv LEFT JOIN flores_producto_contenedor AS fpc USING ( codigo_producto ) LEFT JOIN flores_productos_categoria AS fpcat USING (codigo_producto)  WHERE fpcat.codigo_categoria=37 AND fpc.codigo_producto IS NOT NULL GROUP BY codigo_producto ORDER BY codigo_producto ASC;';
 $r = db_consultar($c);
 $t['mysql'] = microtime(true); 
 
 // Romperemos el loop cuando ya no podamos hacer fetch
 $terminarLoop = false;
 $numeroJuego = 1;
 for ($i = 0; $i < mysqli_num_rows($r); $i = $i+8)
 {
   $pLote = array();
   
   // Vamos por lotes de 8 paginas
   for ($iLote = 1; $iLote < 9; $iLote++)
   {
       // Construyamos un array con 8 paginas, 4 anverso y 4 reverso.
       if ($f = mysqli_fetch_assoc($r))
       {
           $pLote[$iLote]['codigo_producto'] = $f['codigo_producto'];
           $pLote[$iLote]['titulo'] = $f['titulo'];
           
           //if ($f['min_precio'] == $f['max_precio'])
            $pLote[$iLote]['precio'] = '$'.$f['min_precio'];
           //else
            //$pLote[$iLote]['precio'] = '$'.$f['min_precio'].' - $'.$f['max_precio'];
           
           $pLote[$iLote]['foto'] = 'IMG/i/'.$f['foto'];
       } else {
           $pLote[$iLote]['codigo_producto'] = '';
           $pLote[$iLote]['titulo'] = 'Flor360.com';
           $pLote[$iLote]['precio'] = '$0.00';
           $pLote[$iLote]['foto'] = 'IMG/stock/sin_imagen.jpg';
       }
       
   }

   $t['array_construido'] = microtime(true);
   
   // Construyamos el anverso
   $phMagick = new phMagick ('IMG/libro/fondo.png', 'libro/'.$numeroJuego.'a.jpg');
   
   $phMagick->overlay($pLote[1]['foto'],238,458,1250,1870);
   $phMagick->overlay($pLote[2]['foto'],1789,458,1250,1870);
   $phMagick->overlay($pLote[5]['foto'],238,3008,1250,1870);
   $phMagick->overlay($pLote[6]['foto'],1789,3008,1250,1870);

   $phMagick->overlay('IMG/libro/capa.png',0,0,3300,5100);

   $phMagick->annotate($pLote[1]['codigo_producto'], 80, '0x0+'.(170+(80-(calculateTextWidth($pLote[1]['codigo_producto'],100)/2))).'+550','black');
   $phMagick->annotate($pLote[2]['codigo_producto'], 80, '0x0+'.(1740+(80-(calculateTextWidth($pLote[2]['codigo_producto'],100)/2))).'+550','black');
   $phMagick->annotate($pLote[5]['codigo_producto'], 80, '0x0+'.(170+(80-(calculateTextWidth($pLote[5]['codigo_producto'],100)/2))).'+3100','black');
   $phMagick->annotate($pLote[6]['codigo_producto'], 80, '0x0+'.(1740+(80-(calculateTextWidth($pLote[6]['codigo_producto'],100)/2))).'+3100','black');
   
   $phMagick->annotate($pLote[1]['titulo'], 80, '0x0+238+2472','#d21c73');
   $phMagick->annotate($pLote[2]['titulo'], 80, '0x0+1790+2472','#d21c73');
   $phMagick->annotate($pLote[5]['titulo'], 80, '0x0+238+5030','#d21c73');
   $phMagick->annotate($pLote[6]['titulo'], 80, '0x0+1790+5030','#d21c73');

   $phMagick->annotate($pLote[1]['precio'], 80, '0x0+'.((238+1251)-calculateTextWidth($pLote[1]['precio'],80)).'+2472','#747273');
   $phMagick->annotate($pLote[2]['precio'], 80, '0x0+'.((1790+1251)-calculateTextWidth($pLote[2]['precio'],80)).'+2472','#747273');
   $phMagick->annotate($pLote[5]['precio'], 80, '0x0+'.((238+1251)-calculateTextWidth($pLote[5]['precio'],80)).'+5030','#747273');
   $phMagick->annotate($pLote[6]['precio'], 80, '0x0+'.((1790+1251)-calculateTextWidth($pLote[6]['precio'],80)).'+5030','#747273');
   
   $t['Lado A'] = microtime(true);

   // Construyamos el reverso
   $phMagick = new phMagick ('IMG/libro/fondo.png', 'libro/'.$numeroJuego.'b.jpg');
   
   $phMagick->overlay($pLote[3]['foto'],238,458,1250,1870);
   $phMagick->overlay($pLote[4]['foto'],1789,458,1250,1870);
   $phMagick->overlay($pLote[7]['foto'],238,3008,1250,1870);
   $phMagick->overlay($pLote[8]['foto'],1789,3008,1250,1870);

   $phMagick->overlay('IMG/libro/capa.png',0,0,3300,5100);

   $phMagick->annotate($pLote[3]['codigo_producto'], 80, '0x0+'.(170+(80-(calculateTextWidth($pLote[3]['codigo_producto'],100)/2))).'+550','black');
   $phMagick->annotate($pLote[4]['codigo_producto'], 80, '0x0+'.(1740+(80-(calculateTextWidth($pLote[4]['codigo_producto'],100)/2))).'+550','black');
   $phMagick->annotate($pLote[7]['codigo_producto'], 80, '0x0+'.(170+(80-(calculateTextWidth($pLote[7]['codigo_producto'],100)/2))).'+3100','black');
   $phMagick->annotate($pLote[8]['codigo_producto'], 80, '0x0+'.(1740+(80-(calculateTextWidth($pLote[8]['codigo_producto'],100)/2))).'+3100','black');
   
   $phMagick->annotate($pLote[3]['titulo'], 80, '0x0+238+2472','#d21c73');
   $phMagick->annotate($pLote[4]['titulo'], 80, '0x0+1790+2472','#d21c73');
   $phMagick->annotate($pLote[7]['titulo'], 80, '0x0+238+5030','#d21c73');
   $phMagick->annotate($pLote[8]['titulo'], 80, '0x0+1790+5030','#d21c73');

   $phMagick->annotate($pLote[3]['precio'], 80, '0x0+'.((238+1251)-calculateTextWidth($pLote[3]['precio'],80)).'+2472','#747273');
   $phMagick->annotate($pLote[4]['precio'], 80, '0x0+'.((1790+1251)-calculateTextWidth($pLote[4]['precio'],80)).'+2472','#747273');
   $phMagick->annotate($pLote[7]['precio'], 80, '0x0+'.((238+1251)-calculateTextWidth($pLote[7]['precio'],80)).'+5030','#747273');
   $phMagick->annotate($pLote[8]['precio'], 80, '0x0+'.((1790+1251)-calculateTextWidth($pLote[8]['precio'],80)).'+5030','#747273');
   
   $t['Lado B'] = microtime(true);

   $numeroJuego++;
}
$t['fin'] = microtime(true); 
$str_result_bench=mini_bench_to($t);
echo '<pre>'.$str_result_bench.'</pre>'; // string return
?>