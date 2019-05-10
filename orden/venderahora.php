<?php
    session_start();
    $idorden = $_GET["idorden"];
    date_default_timezone_set("America/Bogota");
    include("../control/conexion.php");
    include("../control/variables.php");
    $consulta = "select o.*, (select sum(t.tran_cantidad) from transaccion t where t.orde_id=o.orde_id and t.tran_procesada=0) as cantidad  from orden o where o.orde_id=$idorden";
    $ejecutar_consulta = $conexion->query(utf8_decode($consulta));
    $registro=$ejecutar_consulta->fetch_assoc();
    $numReg=$ejecutar_consulta->num_rows;
    if($numReg!=0){
        $idusua = $registro["usua_id"];
        //actualizar estado de las transacciones tomadas        
        $consultaac = "update transaccion set tran_procesada=1 where orde_id=$idorden";
        $ejecutar_consultaac = $conexion->query($consultaac);
        $filas = $conexion->affected_rows;
        if($filas>0){
            $mercado= $registro["orde_mercado"];
            $cantidad= number_format($registro["cantidad"],8,".","");
            // //consultar precio actual con la api
            $con = file_get_contents($url."operacion/api.php?mercado=$mercado&tipo=5&plataforma=".$registro["plat_id"]);            
            $arrayresult = json_decode($con,true);
            $resultado = $arrayresult["result"];
            $precioactual = number_format($resultado["0"]["Bid"],8,".","");
            $preciosubirorden = number_format(($precioactual-($precioactual*(0.30))),8,".","");
            //montarorden        
            $conv = file_get_contents($url."operacion/api.php?cantidad=$cantidad&precio=$preciosubirorden&mercado=$mercado&tipo=2&plataforma=".$registro["plat_id"]);        
            $arrayresultv = json_decode($conv,true);
            if($arrayresultv["success"]=="1"&&$arrayresultv!=null){                        
                $fecha = date("Y-m-d H:i:s");
                $numerooperacion = $arrayresultv["result"]["uuid"];
                //consultar operacion
                $conc = file_get_contents($url."operacion/api.php?idorden=$numerooperacion&tipo=4");
                $arrayresultc = json_decode($conc,true);
                $precio = number_format($arrayresultc["result"]["PricePerUnit"],8,".","");
                $consulta = "insert into orden (orde_cantidad,orde_precio,orde_numero,orde_fechaapertura,usua_id,monecomp_id,monebase_id,plat_id,orde_tipo,orde_mercado, monecomi_id, orde_bandera) 
                            values('$cantidad','$preciosubirorden','$numerooperacion','$fecha',$idusua,".$registro["monebase_id"].",".$registro["monecomp_id"].",".$registro["plat_id"].",'Venta','$mercado',".$registro["monebase_id"].",1)";
                $conexion->query($consulta);
                $ultimoid = $conexion->insert_id;
                $consultadif = "insert into diferencia(ordecomp_id,ordevent_id) values($idorden,$ultimoid)";
                $conexion->query($consultadif);        
                $filled = number_format(($arrayresultc["result"]["Quantity"]-$arrayresultc["result"]["QuantityRemaining"]),8,".","");
                $preciotransaccion = number_format($arrayresultc["result"]["Price"],8,".","");
                $comision = number_format($arrayresultc["result"]["CommissionPaid"],8,".","");
                $comisionplataforma = number_format($preciotransaccion*$registro["usua_comision"],8,".","");
                if($filled>0)
                {                
                    echo "1";  
                    $consulta = "insert into transaccion (orde_id,tran_cantidad,tran_precio,tran_comision,tran_tipo,tran_comisionplataforma) 
                                values($ultimoid,'$preciotransaccion','$filled','$comision','Taker','$comisionplataforma')";
                    $conexion->query($consulta);           
                }
                $consulta = "update orden set orde_bandera=0 where orde_id=$ultimoid";
                $conexion->query($consulta);
            }
            else{
                echo "Error, no se ha podido completar la operación";
                $consultaac = "update transaccion set tran_procesada=0 where orde_id=$idorden";
                $ejecutar_consultaac = $conexion->query($consultaac);
            }
        }
        else{
            echo "Error, no se ha podido completar la operación";    
        }
    }
    else{
        echo "Error al cargar los datos de la orden";
    }
    $conexion->close();	
?>