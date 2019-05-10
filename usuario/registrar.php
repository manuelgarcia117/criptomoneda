<?php
include("../control/conexion.php");
$acentos = $conexion->query("SET NAMES 'utf8'");
date_default_timezone_set("America/Bogota");
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
			WHERE  u.usua_usuario ='$usuario' or u.usua_documento='$documento'";
$ejecutar_consulta=$conexion->query($consulta);
$numReg = $ejecutar_consulta->num_rows;
if($numReg==0){
    $consulta = "insert into usuario(usua_nombres, usua_apellidos, usua_documento, usua_usuario,
                usua_comision,usua_tipo,usua_ganancia,usua_perdida,rol_id) values('$nombres','$apellidos',
                '$documento','$usuario','$comision',$tipo,'$ganancia','$perdida',$rol)";
    $ejecutar_consulta=$conexion->query($consulta);
    if($ejecutar_consulta){
        $status["status"][]="1";
        $data=$status;
        print_r(json_encode($data));
    }
    else{
        $status["status"][]="Se ha producido un error al registrar, intente nuevamente";
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