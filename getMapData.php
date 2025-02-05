<?php
	error_reporting(0);
	include_once "db.php";
	
    $idTerritorio=$_GET["idTerritorio"];
    if($_GET["alrededores"]!=0)
      $respuesta=getTerritory($idTerritorio);
    else
      $respuesta=getDatosLugarBase($idTerritorio);
    
// For the case of districts, whose surface normally is only partially covered by the neighbourhoods within, zoom is adjusted 
// to the surface covered by the neighbourhood polygons 
    if ($respuesta["nivel"]==9 && $respuesta["idDescendiente"]!=0) 
    {
      $coordenadasInteriores=getCoordenadasInteriores($respuesta["id"]);
      $respuesta["xmax"]=$coordenadasInteriores["xmax"];
      $respuesta["ymax"]=$coordenadasInteriores["ymax"];
      $respuesta["xmin"]=$coordenadasInteriores["xmin"];
      $respuesta["ymin"]=$coordenadasInteriores["ymin"];      
    }
// For the case of navigation with surroundings, no uncles are shown, only territories with the same level as the one displayed
    if ($_GET["alrededores"]!=0) 
    {
      //$coordenadasColindantes=getCoordenadasColindantes($respuesta["nivel"],$respuesta["xmin"],$respuesta["xmax"],$respuesta["ymin"],$respuesta["ymax"]); 
      //$coordenadasColindantes=getCoordenadasCentroidesColindantes($respuesta["nivel"],$respuesta["xmin"],$respuesta["xmax"],$respuesta["ymin"],$respuesta["ymax"]);
      $vecindad=$respuesta["vecinos"];
      if ($vecindad<>'') {
        $vecindad.=",".$idTerritorio;
      }
      else {
        $vecindad=$idTerritorio;
      }
        
      $coordenadasColindantes=getCoordenadasVecinos($respuesta["nivel"],$vecindad);      

      // In case of having a very big neighbour coordinates are too wide and territory loses relevance and central position. 
      // Check for it and correct, using a 2x margin 
        $width=$respuesta["xmax"]-$respuesta["xmin"];
        $height=$respuesta["ymax"]-$respuesta["ymin"];
        if ($coordenadasColindantes["xmax"]>1.5*$width+$respuesta["xmax"])
          $coordenadasColindantes["xmax"]=1.5*$width+$respuesta["xmax"];
        if ((float) $coordenadasColindantes["ymax"]>1.5*$height+(float)$respuesta["ymax"])
          $coordenadasColindantes["ymax"]=1.5*$height+$respuesta["ymax"];
        if ((float) $coordenadasColindantes["xmin"]<-1.5*$width+(float)$respuesta["xmin"])
          $coordenadasColindantes["xmin"]=-1.5*$width+$respuesta["xmin"];
        if ((float) $coordenadasColindantes["ymin"]<-1.5*$height+(float)$respuesta["ymin"])
          $coordenadasColindantes["ymin"]=-1.5*$height+$respuesta["ymin"];  
      
      // Coordinates might not be satisfactory for a peripheric territory, as they get cut. Check for it and correct.
//      $respuesta["xmax"]=($respuesta["xmax"]>$coordenadasColindantes["xmax"]?$respuesta["xmax"]:$coordenadasColindantes["xmax"]);
//      $respuesta["ymax"]=($respuesta["ymax"]>$coordenadasColindantes["ymax"]?$respuesta["ymax"]:$coordenadasColindantes["ymax"]);
//      $respuesta["xmin"]=($respuesta["xmin"]<$coordenadasColindantes["xmin"]?$respuesta["xmin"]:$coordenadasColindantes["xmin"]);
//      $respuesta["ymin"]=($respuesta["ymin"]<$coordenadasColindantes["ymin"]?$respuesta["ymin"]:$coordenadasColindantes["ymin"]);
      
      $respuesta["xmax"]=$coordenadasColindantes["xmax"];
      $respuesta["ymax"]=$coordenadasColindantes["ymax"];
      $respuesta["xmin"]=$coordenadasColindantes["xmin"];
      $respuesta["ymin"]=$coordenadasColindantes["ymin"];
    }
    
    //Data for the Breadcrumbs
	$lugares=getFertileAncestors($respuesta["id"]);
	$cantidad=0;
	$breadcrumbs=array();
	for($i=1;$i<=10;$i++)
	{
		if(isset($lugares[$i]))
		{
          $cantidad++;
          if($cantidad<count($lugares))	//Todos menos el último                         
          {
            if($lugares[$i]["nombreCorto"]!="")
				$nombreBreadcrumb=$lugares[$i]["nombreCorto"];
            else
				$nombreBreadcrumb=$lugares[$i]["nombre"];

			if(strlen($nombreBreadcrumb)>9)		//Si es de más de 9 caracteres lo acortamos a 6 y puntos suspensivos
				$nombreBreadcrumb=mb_substr($lugares[$i]["nombre"],0,6)."...";					
          }
          else {
            if($lugares[$i]["nombre"]!="")
				$nombreBreadcrumb=$lugares[$i]["nombre"];
            else
				$nombreBreadcrumb=$lugares[$i]["nombreCorto"];              
          }
        array_push($breadcrumbs,array($lugares[$i]["id"],$nombreBreadcrumb,$lugares[$i]["nombre"]));
		}
	}
	$respuesta["breadcrumbs"]=$breadcrumbs;

	echo json_encode($respuesta);
?>