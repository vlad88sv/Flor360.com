<?php

  $cl = new SphinxClient();
  $cl->SetServer( "localhost", 9312 );
  $cl->SetMatchMode( SPH_MATCH_ANY  );

  $result = $cl->Query( 'dia de la madre', 'f360_contenedor' );

  if ( is_array($result) ) {
      if ( ! empty($result["matches"]) ) {
        print_r (join(',', array_keys($result["matches"])));
      }
  }

  exit;
?>
