<?php
    include("../control/variables.php");
    session_start();
    $idu = $_GET["id"];
    include("../control/conexion.php");
    $acentos = $conexion->query("SET NAMES 'utf8'");
    $consulta = "select distinct * from usuario u where u.usua_id=$idu";
    $ejecutar_consulta = $conexion->query($consulta);
    $numReg=$ejecutar_consulta->num_rows;
    if($numReg!=0){
        $registro=$ejecutar_consulta->fetch_assoc();
        $id["id"][] = $registro["usua_id"];
        $nombres["nombres"][] = $registro["usua_nombres"];
        $apellidos["apellidos"][] = $registro["usua_apellidos"];
        $documento["documento"][] = $registro["usua_documento"];
        $usuario["usuario"][] = $registro["usua_usuario"];
        $comision["comision"][] = $registro["usua_comision"];
        $tipo["tipo"][] = $registro["usua_tipo"];
        $ganancia["ganancia"][] = $registro["usua_ganancia"];
        $perdida["perdida"][] = $registro["usua_perdida"];
        $rol["rol"][] = $registro["rol_id"];
        $data = $id+$nombres+$apellidos+$documento+$usuario+$comision+$tipo+$ganancia+$perdida+$rol;
        $status["status"][] = "1";
        $data+= $status;
        print_r(json_encode($data));
    }
    else{
        $status["status"][] = "0";
        $data = $status;
        print_r(json_encode($data));    
    }
    $conexion->close();
?>