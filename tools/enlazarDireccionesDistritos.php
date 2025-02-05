<?php
include "../db.php";
include_once('../vendor/phayes/geophp/geoPHP.inc');
error_reporting(E_ERROR);

exit();
// Links an address to the district it lies within (lugares_shp with level 9).

$nivel=9;  // Districts are level 9
$distritos=array();
$link=connect();
$sql="SELECT * FROM territorios WHERE nivel='$nivel' AND provincia='$provincia'";
//$sql="SELECT * FROM lugares_shp WHERE nivel='$nivel' AND idPadre like '80128%'";
$result=mysqli_query($link,$sql);
while($fila=mysqli_fetch_assoc($result))
{
	array_push($distritos,$fila["id"]);
}

$places=array();
$link=connect();
$sql="SELECT * FROM places WHERE idDistrito='0'";
$result=mysqli_query($link,$sql);
while($fila=mysqli_fetch_assoc($result))
{
	array_push($places,$fila);
	$asociados[$fila["idPlace"]]="";
}

foreach($distritos as $distrito)
{
	//echo $distrito.PHP_EOL;
	$poligono = geoPHP::load(file_get_contents("../shp/geoJSON/$nivel/$distrito.geojson"),'json');	

	//print_r($poligono->asArray());//.PHP_EOL;

	foreach($places as $place)
	{
		$punto = geoPHP::load("POINT({$place['lng']} {$place['lat']})","wkt");
		if($poligono->contains($punto))
		{
			$asociados[$place["idPlace"]]=$distrito;
		}
	}
}

foreach($asociados as $id=>$distrito)
{
	mysqli_query($link,"UPDATE places SET idDistrito='$distrito' WHERE idPlace='$id'");
	echo $distrito."\t".$id.PHP_EOL;
}



?>