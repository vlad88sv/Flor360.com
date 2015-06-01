<?php
ini_set('memory_limit', '128M');
set_time_limit(0);

require_once ("config.php");
require_once(__BASE__ARRANQUE.'PHP/vital.php');

$contenedores = array();

if (isset($_FILES['archivo']) || isset($_GET['subido']))
{
    if (!isset($_GET['subido']))
        move_uploaded_file($_FILES['archivo']['tmp_name'],__BASE__.'importacion/archivos.zip');
    
    echo shell_exec("unzip ".__BASE__."importacion/archivos.zip -d ".__BASE__."importacion/ 2>&1");
    
    foreach (glob('importacion/*.{jpg,jpeg,JPG,JPEG}',GLOB_BRACE) as $file)
    {
        $contenedores[preg_replace('/[^\d]/','',$file)][] = $file;
    }
    
    print_r($contenedores);
    
    foreach ($contenedores as $contenedor => $variedades)
    {
        unset ($datos);
        $datos['titulo'] = 'Arreglo Abr12 #'.$contenedor;
        $datos['descripcion'] = 'Este es el contenedor para el arreglo #'.$contenedor;
        $datos['color'] = 'Multicolor';
        $datos['descontinuado'] = 'si';
        
        print_r($datos);
        $codigo_producto = db_agregar_datos('flores_producto_contenedor',$datos);
        
        //$codigo_producto = microtime(true);
        
        foreach ($variedades as $variedad)
        {
            $foto = sha1(microtime());
            rename(__BASE__.$variedad, __BASE__.'IMG/i/'.$foto);
            
            unset ($datos);
            $datos['codigo_producto'] = $codigo_producto;
            $datos['foto'] = $foto;
            $datos['descripcion'] = 'Variedad del archivo: '.$variedad;
            
            print_r($datos);
            db_agregar_datos('flores_producto_variedad',$datos);
        }
    }
    
    echo shell_exec("rm -vr ".__BASE__."importacion/* 2>&1");
}
?>
<html>
    <body>
        <form method="post" enctype="multipart/form-data" action="/importar.php">
            <input type="file" name="archivo" />
            <input type="submit" value="Cargar" />
        </form>
    </body>
</html>