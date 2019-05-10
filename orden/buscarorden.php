<?php
    session_start();
    $idorden = $_GET["idorden"];
    include("../control/conexion.php");
    include("../control/variables.php");
    $consulta = "select distinct *,(case when o.esor_id=1 then o.orde_precio else (select (t.tran_precio/t.tran_cantidad) from transaccion t where t.orde_id=o.orde_id and t.tran_procesada=0 order by (t.tran_precio/t.tran_cantidad) desc limit 1) end) as preciocompra 
    from orden o,usuario u where o.usua_id=u.usua_id and o.orde_id=$idorden";
    $ejecutar_consulta = $conexion->query(utf8_decode($consulta));
    $registro=$ejecutar_consulta->fetch_assoc();
    $numReg=$ejecutar_consulta->num_rows;
    if($numReg!=0){
        $con = file_get_contents($url."operacion/api.php?mercado=".$registro["orde_mercado"]."&tipo=5");
        $arrayresult = json_decode($con,true);
        $arrayresult= $arrayresult["result"];
        $precioactual = number_format($arrayresult[0]["Bid"],8,".","");
        $preciocompra = $registro["preciocompra"];
        $diferencia = number_format(((($precioactual/$preciocompra)-1)*100),8,".","");
        //si la diferencia es menor a 2 se retorna el profit vacio de lo contrario se retorna el nuevo limite maximo de perdida
        if($diferencia<2){
            $perdidaaux = "";    
        }
        else{
            $perdidaaux = $diferencia-($diferencia*0.15);  
        }
        $status["status"][]= "1";
        $mercado["mercado"][]= $registro["orde_mercado"];
        $cantidad["cantidad"][]= $registro["orde_cantidad"];
        $precio["precio"][]= number_format($registro["preciocompra"],8,".","");
        $ganancia["ganancia"][]= $registro["orde_ganancia"];
        $perdida["perdida"][]= $registro["orde_perdida"];
        $usuaganancia["usuaganancia"][]= $registro["usua_ganancia"];
        $usuaperdida["usuaperdida"][]= $registro["usua_perdida"];
        $profit["profit"][] = $perdidaaux;
        $data = $status+$cantidad+$precio+$ganancia+$perdida+$profit+$usuaganancia+$usuaperdida+$mercado;
        print_r(json_encode($data));
    }
    else{
        $status["status"][]= "Error al cargar los datos de la orden"; 
        $data = $status;
        print_r(json_encode($data));
    }
    $conexion->close();	
?>