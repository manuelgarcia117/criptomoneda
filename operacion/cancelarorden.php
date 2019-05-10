<?php 
include("../control/variables.php");
include("../control/conexion.php");
session_start();
date_default_timezone_set("America/Bogota");
$fecha = date("Y-m-d H:i:s");
$idusua = $_SESSION["id_usua"];
$idorden = $_GET["idorden"];
$tipo = 3;
$consulta = "select * from orden where orde_id=$idorden";
$ejecutar_consulta = $conexion->query(utf8_decode($consulta));
$reg = $ejecutar_consulta->fetch_assoc();
$numeroorden = $reg["orde_numero"];
$con = file_get_contents($url."operacion/api.php?idorden=$numeroorden&tipo=$tipo");
$arrayresult = json_decode($con,true);

if($arrayresult["success"]=="1"){
    echo 1;     
}
else
if($arrayresult["message"]=="ORDER_NOT_OPEN"){
    echo "Esta orden se encuentra cerrada.";
}
else{
    echo "Se ha producido un error, intente nuevamente";    
}
$conexion->close();
?>