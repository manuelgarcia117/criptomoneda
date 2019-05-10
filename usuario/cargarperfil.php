<?php
    session_start();
    $idusua = $_SESSION["id_usua"];
    include("../control/conexion.php");
    $consulta = "select distinct * from usuario u, rol r where r.rol_id=u.rol_id and u.usua_id=$idusua";
    $ejecutar_consulta = $conexion->query(utf8_decode($consulta));
    $registro=$ejecutar_consulta->fetch_assoc();
    $numReg=$ejecutar_consulta->num_rows;
    if($numReg!=0){
        $status["status"][]= "1";
        $nombres["nombres"][]= $registro["usua_nombres"];
        $apellidos["apellidos"][]= $registro["usua_apellidos"];
        $rol["rol"][]= $registro["rol_nombre"];
        $data = $status+$nombres+$apellidos+$rol;
        print_r(json_encode($data));
    }
    else{
        $status["status"][]= "Error al cargar sus datos de perfil"; 
        $data = $status;
        print_r(json_encode($data));
    }
    $conexion->close();	
?>