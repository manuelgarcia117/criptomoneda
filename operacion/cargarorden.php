<?php 
    include("../control/variables.php");
    include("../control/conexion.php");
    session_start();
    date_default_timezone_set("America/Bogota");
    $fecha = date("Y-m-d H:i:s");
    $idusua = $_SESSION["id_usua"];
    $plataforma = $_GET["plataforma"];
    $tipob = $_GET["tipo"];
    $consulta = "select *, (select ifnull(count(t.tran_procesada),1) fROM transaccion t WHERE t.tran_procesada=0 and t.orde_id = o.orde_id and o.orde_seguimiento=1) as estadoseguimiento, 
                (select ifnull(sum(1),0) fROM transaccion t WHERE t.orde_id = o.orde_id) as numerotransacciones,
                (case when o.esor_id=1 then o.orde_precio else (select (t.tran_precio/t.tran_cantidad) from transaccion t where t.orde_id=o.orde_id and t.tran_procesada=0 order by (t.tran_precio/t.tran_cantidad) desc limit 1) end) as preciocompra
                from orden o where o.usua_id=$idusua and o.plat_id=$plataforma having (estadoseguimiento<>0 or o.esor_id=1 or o.esor_id=3)";
    $ejecutar_consulta = $conexion->query(utf8_decode($consulta));
    $numReg=$ejecutar_consulta->num_rows;
    if($numReg!=0){
        switch ($plataforma) {
            case 1:
                $con = file_get_contents("https://bittrex.com/api/v1.1/public/getmarketsummaries");
                $arraybtx = json_decode($con,true);
                $subarray = $arraybtx["result"];
            break;
        }
        while($registro=$ejecutar_consulta->fetch_assoc()) {
            $id["id"][] = $registro["orde_id"];
            $cantidad["cantidad"][] = $registro["orde_cantidad"];
            $precio["precio"][] = number_format($registro["preciocompra"],8,".","");
            $transacciones["transacciones"][] = $registro["numerotransacciones"];
            $mercado["mercado"][] = $registro["orde_mercado"];
            $tipo["tipo"][] = $registro["orde_tipo"];
            $estado["estado"][] = $registro["esor_id"];
            $seguimiento["seguimiento"][] = $registro["orde_seguimiento"];
            $estadoseguimiento["estadoseguimiento"][] = $registro["estadoseguimiento"];
            //se toma el precio actual para calcular la diferencia porcentual
            switch ($plataforma) {
                case 1:
                    $posicion = array_search($registro["orde_mercado"], array_column($arraybtx["result"], 'MarketName'));
                    $pactual = str_replace(",", "",number_format($subarray[$posicion]["Bid"],8));
                break;
            }
            $diferencia["diferencia"][] = number_format(((($pactual/$registro["preciocompra"])-1)*100),2,".","");
            $data = $id+$cantidad+$precio+$mercado+$tipo+$estado+$seguimiento+$estadoseguimiento+$transacciones+$diferencia;
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