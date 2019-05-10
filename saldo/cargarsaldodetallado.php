<?php
$tinicio = microtime(true);
header('Access-Control-Allow-Origin: *');
if(!function_exists("array_column"))
{

    function array_column($array,$column_name)
    {

        return array_map(function($element) use($column_name){return $element[$column_name];}, $array);

    }

}    
    include("../control/variables.php");
    session_start();
    $idusua = 20;
    $idp = $_GET["plataforma"];
    include("../control/conexion.php");
    $acentos = $conexion->query("SET NAMES 'utf8'");
    //traer precio del dolar
    $consulta = "select * from dolar order by dola_fecha desc limit 1";
    $ejecutar_consulta = $conexion->query(utf8_decode($consulta));
    $registro=$ejecutar_consulta->fetch_assoc();
    $preciodolar = $registro["dola_precio"];
    $con = file_get_contents("../json/bittrex.json");
    $arraybtx=json_decode($con,TRUE);
    $subarray = $arraybtx["result"];                        
    //precio de btc en dolar
    $posicion = array_search("USDT-BTC", array_column($arraybtx["result"], 'MarketName'));                         	
    $btcdolar = number_format($subarray[$posicion]["Bid"],8,".","");
    //precio actual del dolar
    $consulta = "select distinct m.*,(select s.simb_abreviacion from simbolo s where s.mone_id = m.mone_id) as simbolo, 
            (format((select ifnull(sum(d.depo_cantidad),0) from deposito d where d.usuareci_id=$idusua and d.plat_id=$idp and d.mone_id=m.mone_id)
			 -(select ifnull(sum(d.depo_cantidad),0) from deposito d where d.usuadepo_id=$idusua and d.plat_id=$idp and d.mone_id=m.mone_id)
			 +(select ifnull(sum(t.tran_cantidad),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$idp and o.monecomp_id=m.mone_id)			
			 -(select ifnull(sum(t.tran_precio),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$idp and o.esor_id=2 and o.monebase_id=m.mone_id)
			 -(select ifnull(sum(t.tran_comision),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$idp and o.monecomi_id=m.mone_id)
			 -(select ifnull(sum(t.tran_comisionplataforma),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$idp and o.monecomi_id=m.mone_id),8)) as saldototal,
			
            ((select ifnull(sum(t.tran_cantidad),0) from transaccion t,orden o 
            where t.orde_id=o.orde_id and t.tran_procesada=0 and o.orde_seguimiento=1 and o.usua_id=$idusua and o.plat_id=$idp and o.monecomp_id=m.mone_id)
            +(select ifnull(sum(o.orde_cantidad),0) from orden o where o.usua_id=$idusua and o.plat_id=$idp and (o.esor_id=1 or o.esor_id=3) and o.orde_tipo='Venta' and o.monebase_id=m.mone_id)
            +(select ifnull(sum(o.orde_cantidad*o.orde_precio),0) from orden o where o.usua_id=$idusua and o.plat_id=$idp and (o.esor_id=1 or o.esor_id=3) and o.orde_tipo='Compra' and o.monebase_id=m.mone_id)
            ) as saldoordenes

             from moneda m having(saldototal>0)";
             
    echo $consulta;
    // $ejecutar_consulta = $conexion->query(utf8_decode($consulta));
    // $numReg=$ejecutar_consulta->num_rows;
    // if($numReg!=0){
    //     $sumabtc=0;
    //     while ($registro=$ejecutar_consulta->fetch_assoc()) {
    //         if($registro["simbolo"]==""){
    //             $simboloa=$registro["mone_simbolo"];
    //         }
    //         else{
    //             $simboloa=$registro["simbolo"];
    //         }
    //         $simbolo["simbolo"][] = "(".$simboloa.")";
    //         $nombre["nombre"][] = $registro["mone_nombre"];
    //         $saldototal["saldototal"][] = number_format($registro["saldototal"],8);
    //         $saldoordenes["saldoordenes"][] = number_format($registro["saldoordenes"],8);
    //         $saldodisponible["saldodisponible"][] = number_format((number_format($registro["saldototal"],8)-number_format($registro["saldoordenes"],8)),8);
    //         switch ($idp) {
    //             case 1:
    //                 //echo "simbolo:".$simboloa."<br>";
    //                 if($simboloa!="BTC"){
    //                     $posicion = array_search("BTC-".$simboloa, array_column($arraybtx["result"], 'MarketName'));                           
    //                     if($simboloa=="USDT"){
    //                     	$posicion = array_search("USDT-BTC", array_column($arraybtx["result"], 'MarketName'));                         	
    //                         $preciobtca = number_format((1/$subarray[$posicion]["Bid"]),8,".","");                            
    //                         $preciobtca = $preciobtca*$registro["saldototal"];
    //                         $sumabtc+= $preciobtca;
    //                     }
    //                     else{
    //                         $preciobtca = number_format($subarray[$posicion]["Bid"],8,".","");                            
    //                         $preciobtca = $preciobtca*$registro["saldototal"];                            
    //                         $sumabtc+= $preciobtca;
    //                     }
    //                 }
    //                 else{
    //                     $preciobtca = $registro["saldototal"];
    //                     $sumabtc+=number_format((number_format($registro["saldototal"],8)-number_format($registro["saldoordenes"],8)),8);
    //                 }
    //             break;
    //         }
    //         $preciobtc["preciobtc"][]=number_format($preciobtca,8,".","");
    //         $icono["icono"][]=$url."logos/".strtolower($registro["mone_simbolo"]).".png";
    //         $data=$simbolo+$nombre+$saldototal+$saldoordenes+$saldodisponible+$preciobtc+$icono;
    //     }
    //     $totalbtc["totalbtc"][]=number_format($sumabtc,8,".","");
    //     $totalpesos["totalpesos"][]=number_format(($sumabtc*$preciodolar*$btcdolar),4,".",",");
    //     $status["status"][]="1";
    //     $nombreusuario["nombreusuario"][] = $_SESSION["nombre_usua"];
    //     $tfinal = microtime(true);
    //     $tiempo["tiempo"][] = $tfinal-$tinicio;
    //     $data+=$status+$totalbtc+$totalpesos+$nombreusuario+$tiempo;        
    //     print_r(json_encode($data));
    // }
    // else{
    //     $nombreusuario["nombreusuario"][] = $_SESSION["nombre_usua"];
    //     $status["status"][]="0";
    //     $tiempo["tiempo"][] = $tfinal-$tinicio;
    //     $data=$status+$nombreusuario+$tiempo;
    //     print_r(json_encode($data));
    // }
    $conexion->close();
?>