<?php
    session_start();
    include("../control/conexion.php");
    include("../control/variables.php");
    date_default_timezone_set("America/Bogota");
    $plataforma = $_GET["plataforma"];
    $moneda = $_GET["moneda"];
    $fecha1 = $_GET["fecha1"];
    $fecha2 = $_GET["fecha2"];
    $tipob = $_GET["tipo"];
    $idordenes = $_GET["idordenes"];
    $idusua = $_SESSION["id_usua"];
    $consulta = "select o.*,
    (select case when o.orde_tipo = 'Compra' then (t.tran_precio/t.tran_cantidad) else (t.tran_cantidad/t.tran_precio) end 
    from transaccion t where t.orde_id=o.orde_id limit 1) as precio,
    (select ifnull(d.ordecomp_id,0) from diferencia d where d.ordevent_id=o.orde_id) as idrelacion,
    ifnull((((select case when o.orde_tipo = 'Compra' then (t.tran_precio/t.tran_cantidad) else (t.tran_cantidad/t.tran_precio) end 
    from transaccion t where t.orde_id=o.orde_id limit 1)/(select ifnull(od.orde_precio,0) from orden od,diferencia di where od.orde_id=di.ordecomp_id and di.ordevent_id=o.orde_id)-1)*100),0) as diferencia
    from orden o where o.orde_tipo like '%$tipob%' and o.usua_id=$idusua and (o.esor_id=2 or o.esor_id=3) and o.plat_id=$plataforma ";
    if($idordenes!=""){
        $idordenes = explode(",",$idordenes);
        $consulta.="and (o.orde_id=".$idordenes[0]." or o.orde_id=".$idordenes[1].")";
    }
    else{
        if($moneda!=""){
            $consulta.="and (o.monecomp_id = $moneda or o.monebase_id = $moneda) ";
        }
        //consulta si las fechas no vienen vacias
        if(($fecha1!=""&&$fecha2=="")||($fecha1==""&&$fecha2!="")){
            if($fecha1==""){
                $consulta.="and date(o.orde_fechaapertura) between '$fecha2' and '$fecha2' ";    
            }
            else{
                $consulta.="and date(o.orde_fechaapertura) between '$fecha1' and '$fecha1' ";     
            }
        }
        else
        if($fecha1!=""&&$fecha2!=""){
            $consulta.="and date(o.orde_fechaapertura) between '$fecha1' and '$fecha2' ";
        }
    }
    $consulta.=" order by orde_fechaapertura desc";
    $ejecutar_consulta = $conexion->query(utf8_decode($consulta));
    $numReg=$ejecutar_consulta->num_rows;
    if($numReg!=0){
        while ($registro=$ejecutar_consulta->fetch_assoc()) {
            $id["id"][] = $registro["orde_id"];
            $mercado["mercado"][] = $registro["orde_mercado"];
            $fecha["fecha"][] = date_format(new DateTime($registro["orde_fechaapertura"]),'d/M/Y H:i');
            $cantidad["cantidad"][] = $registro["orde_cantidad"];
            $precio["precio"][] = number_format($registro["precio"],8,".","");
            $tipo["tipo"][] = $registro["orde_tipo"];
            $relacion["relacion"][] = $registro["idrelacion"];
            $diferencia["diferencia"][] = number_format($registro["diferencia"],2,".","");
            $data = $id+$mercado+$fecha+$cantidad+$precio+$tipo+$relacion+$diferencia;
        }
        $status["status"][]="1";
        $data+=$status;
        print_r(json_encode($data));
    }
    else{
        $status["status"][]="0";
        $data=$status;
        print_r(json_encode($data));    
    }
    $conexion->close();
?>