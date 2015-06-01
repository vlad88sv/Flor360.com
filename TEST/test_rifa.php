<?php
function cmp($a, $b)
{
    return ($a['comentarios'] < $b['comentarios']);
}

$grap_rifa = file_get_contents('https://graph.facebook.com/comments/?ids=https://www.facebook.com/floristeria.flor360/app_262165247201504?1333555200');
$arr_rifa = json_decode($grap_rifa, true);

$pagina = current($arr_rifa);

echo '<p>Número de comentarios: <b>'. count($pagina['comments']['data']).'</b></p>';

foreach($pagina['comments']['data'] as $data )
{
    $participantes[] = array('nombre' => @$data['from']['name'], 'mensaje' => @$data['message'], 'likes' => @$data['likes'], 'comentarios' => (empty($data['comments']['count']) ? 0 : $data['comments']['count']) );
}


usort($participantes,'cmp');

print_r($participantes);

reset($participantes);



echo '<table>';
echo '<tr><th>Nombre</th><th>Nombre de arreglo</th><th>Número de "Likes"</th></tr>';
for($i=0; $i<3;$i++)
    echo sprintf('<tr><td>%s</td><td>%s</td><td>%s</td></tr>', $participantes[$i]['nombre'], $participantes[$i]['mensaje'], $participantes[$i]['likes']);
echo '</table>';
?>