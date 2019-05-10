<?php
include("../control/conexion.php");
$acentos = $conexion->query("SET NAMES 'utf8'");
date_default_timezone_set("America/Bogota");
$idu = $_GET["id"];
$nombres = ucwords(strtolower($_GET["nombres"]));
$apellidos = ucwords(strtolower($_GET["apellidos"]));
$documento = strtoupper($_GET["documento"]);
$usuario = $_GET["usuario"];
$comision = $_GET["comision"];
$tipo = $_GET["tipo"];
$ganancia = $_GET["ganancia"];
$perdida = $_GET["perdida"];
$rol = $_GET["rol"];

$fecha = date("Y-m-d H:i:s"); 

$consulta = "SELECT	* FROM
			usuario u
			WHERE  (u.usua_usuario ='$usuario' and u.usua_id<>$idu) 
			or (u.usua_documento='$documento' and u.usua_id<>$idu)";
$ejecutar_consulta=$conexion->query($consulta);
$numReg = $ejecutar_consulta->num_rows;
if($numReg==0){
    $consulta = "update usuario set usua_nombres='$nombres', usua_apellidos='$apellidos',
                usua_documento='$documento',usua_usuario='$usuario',usua_comision='$comision',
                usua_tipo=$tipo,usua_ganancia='$ganancia',usua_perdida='$perdida',rol_id=$rol where usua_id=$idu";
    $ejecutar_consulta=$conexion->query($consulta);
    if($ejecutar_consulta){
        $status["status"][]="1";
        $data=$status;
        print_r(json_encode($data));
    }
    else{
        $status["status"][]="Se ha producido un error al modificar, intente nuevamente";
        $data=$status;
        print_r(json_encode($data));    
    }
}
else{
    $status["status"][] = "Ya existe un usuario con el usuario o documento ingresados";
	$data = $status;
	print_r(json_encode($data)); 
}
$conexion->close();	
?>