<?php
    include("../control/variables.php");
    session_start();
    $txtbusqueda = $_GET["texto"];
    include("../control/conexion.php");
    $acentos = $conexion->query("SET NAMES 'utf8'");
    $consulta = "select distinct * from usuario u where u.usua_nombres like '%$txtbusqueda%' or u.usua_apellidos like '%$txtbusqueda%'
                 or u.usua_documento like '%$txtbusqueda%' or u.usua_usuario like '%$txtbusqueda%'";
    $ejecutar_consulta = $conexion->query($consulta);
    $numReg=$ejecutar_consulta->num_rows;
    if($numReg!=0){
        while ($registro=$ejecutar_consulta->fetch_assoc()) {
            $id["id"][] = $registro["usua_id"];
            $nombre["nombre"][] = explode(" ",$registro["usua_nombres"])[0]." ".explode(" ",$registro["usua_apellidos"])[0];
            // $nombres["nombres"][] = $registro["usua_nombres"];
            // $apellidos["apellidos"][] = $registro["usua_apellidos"];
            $documento["documento"][] = $registro["usua_documento"];
            $usuario["usuario"][] = $registro["usua_usuario"];
            // $comision["comision"][] = $registro["usua_comision"];
            // $tipo["tipo"][] = $registro["usua_tipo"];
            // $ganancia["ganancia"][] = $registro["usua_ganancia"];
            // $perdida["perdida"][] = $registro["usua_perdida"];
            // $rol["rol"][] = $registro["rol_id"];
            $data = $id+$nombre+$documento+$usuario;
        }
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