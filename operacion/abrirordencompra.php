<?php 
 include("../control/variables.php");
 include("../control/conexion.php");
 session_start();
 date_default_timezone_set("America/Bogota");
 
 $idusua = $_SESSION["id_usua"];
 $mercado= $_GET["mercado"];
 $plataforma= $_GET["plataforma"];
 $seguimiento= $_GET["seguimiento"]; 
 $ganancia= $_GET["ganancia"];
 $perdida= $_GET["perdida"]; 
 $idmonedacompra= $_GET["monedacompra"];
 $idmonedaventa= $_GET["monedaventa"];
 $cantidad = $_GET["cantidad"];
 $precio = $_GET["precio"];
 $tipo = 1;
	//traer el saldo del usuario
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
 $saldousua = $reg["saldo"];
 //traer la comision de la plataforma
 $consulta = "select plat_comisiontaker from plataforma where plat_id=$plataforma";
 $ejecutar_consulta = $conexion->query(utf8_decode($consulta));
 $reg = $ejecutar_consulta->fetch_assoc();
 $pcomisiontaker = $reg["plat_comisiontaker"];
 //traer la comision del usuario
 $consulta = "select usua_comision from usuario where usua_id=$idusua";
 $ejecutar_consulta = $conexion->query(utf8_decode($consulta));
 $reg = $ejecutar_consulta->fetch_assoc();
 $pcomisionusua = $reg["usua_comision"];
 $comisiontaker = $cantidad*$precio*$pcomisiontaker;
 $comisionusua = $cantidad*$precio*$pcomisionusua;
 $total = ($cantidad*$precio)+$comisiontaker+$comisionusua;
 // echo $total."<br>";
 // echo $saldousua."<br>";
 if($total<=$saldousua){
    //llamada a la api
    $precio = number_format($precio,8,".","");
    $consulta = "insert into orden (orde_cantidad,orde_precio,usua_id,monecomp_id,monebase_id,plat_id,orde_tipo, orde_mercado, monecomi_id, orde_seguimiento, orde_ganancia, orde_perdida,orde_bandera) 
                     values('$cantidad','$precio',$idusua,$idmonedacompra,$idmonedaventa,$plataforma,'Compra','$mercado',$idmonedaventa,$seguimiento,'$ganancia','$perdida',1)";
    $ejecutar_consulta = $conexion->query($consulta);
    if($ejecutar_consulta){
       $ultimoid = $conexion->insert_id;
       $con = file_get_contents($url."operacion/api.php?cantidad=$cantidad&precio=$precio&mercado=$mercado&tipo=$tipo");
       $arrayresult = json_decode($con,true);
          if($arrayresult["success"]=="1"&&$arrayresult!=null){
             $numerooperacion = $arrayresult["result"]["uuid"];
             $fecha = date("Y-m-d H:i:s");
             //consultar la orden
             $con = file_get_contents($url."operacion/api.php?idorden=$numerooperacion&tipo=4");
             $arrayresult = json_decode($con,true);
             $consulta = "update orden set orde_numero='$numerooperacion',orde_fecha='$fecha' where orde_id=$ultimoid";                                     	
             $conexion->query($consulta);
             $filled = number_format(($arrayresult["result"]["Quantity"]-$arrayresult["result"]["QuantityRemaining"]),8,".","");
             $preciotransaccion = number_format($arrayresult["result"]["Price"],8,".","");
             $comision = number_format($arrayresult["result"]["CommissionPaid"],8,".","");
             //se calcula la comision que cobrara la plataforma propia
             $comisionplataforma = number_format($preciotransaccion*$pcomisionusua,8,".","");
             if($filled>0){
                $mensaje = "";             
                $consulta = "insert into transaccion (orde_id,tran_cantidad,tran_precio,tran_comision,tran_tipo,tran_comisionplataforma) 
                            values($ultimoid,'$filled','$preciotransaccion','$comision','Taker','$comisionplataforma')";
                $conexion->query(utf8_decode($consulta)); 
                $mensaje.="se han Comprado ".$filled." por el valor de ".$arrayresult["result"]["Price"];            
             }
             $consulta = "update orden set orde_bandera=0 where orde_id=$ultimoid";
             $conexion->query($consulta);
             echo "1|".$mensaje;
         }
         else
         if($arrayresult["message"]=="MIN_TRADE_REQUIREMENT_NOT_MET"){
             $consulta = "delete from consulta where id=$ultimoid";
             $conexion->query($consulta);
             echo "Monto demaciado bajo, por favor intente con un monto mas grande|";
         }
         else{
             echo "Se ha producido un error, intente nuevamente|".$arrayresult["message"];
             $consulta = "delete from consulta where id=$ultimoid";
             $conexion->query($consulta);
         }
     }
     else{
        echo "Se ha producido un error, intente nuevamente|".$arrayresult["message"];  
     }
 }
 else{
      echo "Usted no tiene saldo para realizar esta operación|";    
 }
$conexion->close();
?>