<?php
include("../control/conexion.php");
$acentos = $conexion->query("SET NAMES 'utf8'");
date_default_timezone_set("America/Bogota");
$idu = $_GET["id"];
$fecha = date("Y-m-d H:i:s"); 

$consulta = "delete from usuario where usua_id=$idu";
$ejecutar_consulta=$conexion->query($consulta);
if($ejecutar_consulta){
    $status["status"][] = "1";
	$data = $status;
	print_r(json_encode($data)); 
}
else{
    $status["status"][] = "Se ha presentado un error, intente nuevamente";
	$data = $status;
	print_r(json_encode($data)); 
}
$conexion->close();	
?>