<?php
    session_start();
    $idorden = $_GET["idorden"];
    $ganancia = $_GET["ganancia"];
    $perdida = $_GET["perdida"];
    include("../control/conexion.php");
    $consulta = "select *,(select ifnull(sum(1),0) from transaccion t where t.tran_procesada=0 
                and t.orde_id=o.orde_id) as estado from orden o where o.orde_id=$idorden";
    $ejecutar_consulta = $conexion->query(utf8_decode($consulta));
    $registro=$ejecutar_consulta->fetch_assoc();
    if($registro["esor_id"]==2&&$registro["estado"]==0){
        echo "Esta orden ya ha sido procesada y no puede ser modificada";            
    }
    else{
        $consulta = "update orden set orde_ganancia=$ganancia, orde_perdida=$perdida where orde_id=$idorden"; 
        $ejecutar_consulta = $conexion->query(utf8_decode($consulta));
        if($ejecutar_consulta==1){
            echo "1";
        }
        else{
            echo "Error al modificar, por favor, intente nuevamente";
        }
    }