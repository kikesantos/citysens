<?php
include "../db.php";
error_reporting(E_ERROR);

// script deactivated unless needed
exit();

// Script to get from Google GeoCode API the lat and lng of the directions of a city

ini_set('default_charset', 'utf-8');

$idCiudad="888004284";  //Id of the city addresses to update. In this case, Alcalá de Henares
$places=array();
$link=connect();
mysqli_query($link,"SET NAMES 'utf8'");
$sql="SELECT * FROM lugares_shp WHERE id='$idCiudad'";
$result=mysqli_query($link,$sql);
$area=mysqli_fetch_assoc($result);
$nombreCiudad=$area["nombre"];
    
$sql="SELECT * FROM places WHERE idCiudad='$idCiudad' AND lat='0'";
$result=mysqli_query($link,$sql);
while($fila=mysqli_fetch_assoc($result))
{
	array_push($places,$fila);
}

foreach($places as $place)
{
    $idPlace=$place['idPlace'];
    $strDireccion = $place['direccion'];
    $strDireccion = $strDireccion.", ".$nombreCiudad;
    echo PHP_EOL, $strDireccion, PHP_EOL;
    
    $strDireccionClean = str_replace (" ", "+", $strDireccion);
    //Seems you could use urlEncode($strDirection);
    $respuesta=json_decode(file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?address=".$strDireccionClean."&sensor=false"),true);
    
    if ($respuesta['status']=='OK') {
        $lat=$respuesta['results'][0]['geometry']['location']['lat']; // get lat for json
        $lng=$respuesta['results'][0]['geometry']['location']['lng']; // get lng for json
        $queryStr = "UPDATE places SET lat='$lat', lng='$lng' WHERE idPlace='$idPlace'";
        echo $queryStr, PHP_EOL;
        usleep(1500000);//google free 2.500 searchs with speed 5 pers sec.
        mysqli_query($link,$queryStr);
    }    
}

?>