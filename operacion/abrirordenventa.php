<?php 
include("../control/variables.php");
include("../control/conexion.php");
session_start();
date_default_timezone_set("America/Bogota");
$fecha = date("Y-m-d H:i:s");
$idusua = $_SESSION["id_usua"];
$mercado= $_GET["mercado"];
$plataforma= $_GET["plataforma"];
$idmonedacompra= $_GET["monedacompra"];
$idmonedaventa= $_GET["monedaventa"];
$cantidad = $_GET["cantidad"];
$precio = $_GET["precio"];
$tipo = 2;

$consulta = "select distinct m.*,(select s.simb_abreviacion from simbolo s where s.mone_id = m.mone_id) as simbolo, 
            (replace(format((select ifnull(sum(d.depo_cantidad),0) from deposito d where d.usuareci_id=$idusua and d.plat_id=$plataforma and d.mone_id=m.mone_id)
			 -(select ifnull(sum(d.depo_cantidad),0) from deposito d where d.usuadepo_id=$idusua and d.plat_id=$plataforma and d.mone_id=m.mone_id)
			 +(select ifnull(sum(t.tran_cantidad),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$plataforma and o.monecomp_id=m.mone_id)
			 -(select ifnull(sum(o.orde_cantidad),0) from orden o where o.usua_id=$idusua and o.plat_id=$plataforma and (o.esor_id=1 or o.esor_id=3) and o.monebase_id=m.mone_id)
			 -(select ifnull(sum(t.tran_precio),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$plataforma and o.esor_id=2 and o.monebase_id=m.mone_id)
			 -(select ifnull(sum(t.tran_comision),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$plataforma and o.monecomi_id=m.mone_id)
			 -(select ifnull(sum(t.tran_comisionplataforma),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$plataforma and o.monecomi_id=m.mone_id)
			 -(select ifnull(sum(t.tran_cantidad),0) from transaccion t,orden o 
where t.orde_id=o.orde_id and t.tran_procesada=0 and o.orde_seguimiento=1 and o.usua_id=$idusua and o.plat_id=$plataforma and o.monecomp_id=m.mone_id)
			,8),',','')) as saldo
             from moneda m where m.mone_id=$idmonedaventa";

$ejecutar_consulta = $conexion->query(utf8_decode($consulta));
$reg = $ejecutar_consulta->fetch_assoc();
$saldousua = number_format($reg["saldo"],8,".","");
    //traer la comision del usuario
    $consulta = "select usua_comision from usuario where usua_id=$idusua";    
    $ejecutar_consulta = $conexion->query(utf8_decode($consulta));
    $reg = $ejecutar_consulta->fetch_assoc();
    $pcomisionusua = $reg["usua_comision"]; 
if($cantidad<=$saldousua){   
        $con = file_get_contents($url."operacion/api.php?cantidad=$cantidad&precio=$precio&mercado=$mercado&tipo=$tipo&tipoc=$tipoc");
        $arrayresult = json_decode($con,true);
         if($arrayresult["success"]=="1"){         	
            $numerooperacion = $arrayresult["result"]["uuid"];
            $con = file_get_contents($url."operacion/api.php?idorden=$numerooperacion&tipo=4");
            $arrayresult = json_decode($con,true);
            $consulta = "insert into orden (orde_cantidad,orde_precio,orde_numero,orde_fechaapertura,usua_id,monecomp_id,monebase_id,plat_id,orde_tipo,orde_mercado, monecomi_id,orde_bandera) 
                        values('$cantidad','$precio','$numerooperacion','$fecha',$idusua,$idmonedacompra,$idmonedaventa,$plataforma,'Venta','$mercado',$idmonedacompra,1)";           
            $conexion->query(utf8_decode($consulta));
            $ultimoid = $conexion->insert_id;            
            $filled = $arrayresult["result"]["Quantity"]-$arrayresult["result"]["QuantityRemaining"];
            $preciotransaccion = $arrayresult["result"]["Price"];
            $comision = $arrayresult["result"]["CommissionPaid"];
            $comisionplataforma = number_format($preciotransaccion*$pcomisionusua,8,".","");
            if($filled>0)
            {
                $mensaje = "";
                $filledf = number_format($filled,8,".","");
                $preciotransaccionf = number_format($preciotransaccion,8,".","");  
                $comisionf = number_format($comision,8,".","");
                $precio = $arrayresult["result"]["PricePerUnit"];
                $consulta = "insert into transaccion (orde_id,tran_cantidad,tran_precio,tran_comision,tran_tipo,tran_comisionplataforma) 
                            values($ultimoid,'$preciotransaccionf','$filledf','$comisionf','Taker','$comisionplataforma')";
                $conexion->query(utf8_decode($consulta));           
                /*if($filled==$arrayresult["result"]["Quantity"]){
                    $consulta = "update orden set esor_id = 2, orde_fechacierre = '$fecha' where orde_id=$ultimoid";
                    $conexion->query(utf8_decode($consulta));              
                }
                else
                if(($arrayresult["result"]["QuantityRemaining"]!=$arrayresult["result"]["Quantity"])&&($arrayresultv["result"]["QuantityRemaining"]!=0)){
                    $consulta = "update orden set esor_id = 3 where orde_id=$ultimoid";
                    $conexion->query(utf8_decode($consulta));                
                }*/
                $mensaje.="se han Comprado ".$arrayresult["result"]["Price"]." por el valor de ".$filled;
            }
            $consulta = "update orden set orde_bandera=0 where orde_id=$ultimoid";
            $conexion->query(utf8_decode($consulta));
            echo "1|".$mensaje;
        }
        else
        if($arrayresult["message"]=="MIN_TRADE_REQUIREMENT_NOT_MET"){
            echo "Monto demaciado bajo, por favor intente con un monto mas alto|"; 
        }
        else{
            echo "Se ha producido un error, intente nuevamente|";    
        }
}
else{
	echo "Usted no cuenta con suficiente saldo para completar esta operación|";	
}
$conexion->close();
?>