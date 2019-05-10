<?php

if(!function_exists("array_column"))
{

    function array_column($array,$column_name)
    {

        return array_map(function($element) use($column_name){return $element[$column_name];}, $array);

    }

}

session_start();
$idusua = $_SESSION["id_usua"];
$idp = $_GET["plataforma"];
$idmoneda = $_GET["moneda"];
$acc = $_GET["accion"];
include("../control/conexion.php");
include("../control/variables.php");
//traer las comisiones cobradas por la plataforma
$consulta = "select distinct * from plataforma
            where plat_id=$idp";
$ejecutar_consulta = $conexion->query(utf8_decode($consulta));
$reg=$ejecutar_consulta->fetch_assoc();
$comtaker = $reg["plat_comisiontaker"];
$commaker = $reg["plat_comisionmaker"];

//traer las datos de usuario
$consulta = "select distinct * from usuario
            where usua_id=$idusua";
$ejecutar_consulta = $conexion->query(utf8_decode($consulta));
$reg=$ejecutar_consulta->fetch_assoc();
$usuaganacia = $reg["usua_ganancia"];
$usuaperdida = $reg["usua_perdida"];
$tipousua = $reg["usua_tipo"];

//compra
if($acc==1){
    $consulta = "select distinct *,(select s.simb_abreviacion from simbolo s where s.mone_id = m.mone_id) as simbolo from moneda m
            where m.mone_id=$idmoneda";
    $ejecutar_consulta = $conexion->query(utf8_decode($consulta));
    $reg=$ejecutar_consulta->fetch_assoc();
    if($reg["simbolo"]==""){
        $simbolo = $reg["mone_simbolo"];    
    }
    else{
        $simbolo = $reg["simbolo"];   
    }
    //para establecer el icono
    $iconooperacion["iconooperacion"][] = $url."logos/".strtolower($reg["mone_simbolo"]).".png";
}
//venta
else{
    $consulta = "select distinct m.*,(select s.simb_abreviacion from simbolo s where s.mone_id = m.mone_id) as simbolo, 
            ((select ifnull(sum(d.depo_cantidad),0) from deposito d where d.usuareci_id=$idusua and d.plat_id=$idp and d.mone_id=m.mone_id)
			 -(select ifnull(sum(d.depo_cantidad),0) from deposito d where d.usuadepo_id=$idusua and d.plat_id=$idp and d.mone_id=m.mone_id)
			 +(select ifnull(sum(t.tran_cantidad),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$idp and o.monecomp_id=m.mone_id)
			 -(select ifnull(sum(o.orde_cantidad),0) from orden o where o.usua_id=$idusua and o.plat_id=$idp and (o.esor_id=1 or o.esor_id=3) and o.monebase_id=m.mone_id)
			 -(select ifnull(sum(t.tran_precio),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$idp and o.esor_id=2 and o.monebase_id=m.mone_id)
			 -(select ifnull(sum(t.tran_comision),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$idp and o.monecomi_id=m.mone_id)
			 -(select ifnull(sum(t.tran_comisionplataforma),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$idp and o.monecomi_id=m.mone_id)
			 -(select ifnull(sum(t.tran_cantidad),0) from transaccion t,orden o 
where t.orde_id=o.orde_id and t.tran_procesada=0 and o.orde_seguimiento=1 and o.usua_id=$idusua and o.plat_id=$idp and o.monecomp_id=m.mone_id)
			) as saldo
             from moneda m where m.mone_id=$idmoneda having(saldo>0)";      
                 
        $ejecutar_consulta = $conexion->query(utf8_decode($consulta));
        $reg=$ejecutar_consulta->fetch_assoc();
        if($reg["simbolo"]==""){
            $simbolo = $reg["mone_simbolo"]; 
        }
        else{
            $simbolo = $reg["simbolo"];    
        }
        $saldoms = $reg["saldo"];
        //para establecer el icono
        $iconooperacion["iconooperacion"][] = $url."logos/".strtolower($reg["mone_simbolo"]).".png";
}
//traer los mercados para los q el usuario tiene saldo en la plataforma seleccionada actualmente
if($acc==1){
$consulta = "select distinct m.*,(select s.simb_abreviacion from simbolo s where s.mone_id = m.mone_id) as simbolo,
			((select ifnull(sum(d.depo_cantidad),0) from deposito d where d.usuareci_id=$idusua and d.plat_id=$idp and d.mone_id=m.mone_id)
			  -(select ifnull(sum(d.depo_cantidad),0) from deposito d where d.usuadepo_id=$idusua and d.plat_id=$idp and d.mone_id=m.mone_id)
			  +(select ifnull(sum(t.tran_cantidad),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$idp and o.monecomp_id=m.mone_id)
			 -(select ifnull(sum(o.orde_cantidad*o.orde_precio),0) from orden o where o.usua_id=$idusua and o.plat_id=$idp and (o.esor_id=1 or o.esor_id=3) and o.monebase_id=m.mone_id)
			 -(select ifnull(sum(t.tran_precio),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$idp and o.esor_id=2 and o.monebase_id=m.mone_id)
			 -(select ifnull(sum(t.tran_comision),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$idp and o.monecomi_id=m.mone_id)
			 -(select ifnull(sum(t.tran_comisionplataforma),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$idp and o.monecomi_id=m.mone_id) 
			 )  as saldo from moneda m, plataforma p, monedaplataforma mp 
			where m.mone_id=mp.mone_id and mp.plat_id=p.plat_id and p.plat_id=$idp and m.mone_mercado=1 having (saldo>0)";
}
else{
    $consulta ="select * from moneda m, monedaplataforma mp 
    			where m.mone_id = mp.mone_id and mp.plat_id =$idp and m.mone_mercado =1";
}

$ejecutar_consulta = $conexion->query(utf8_decode($consulta));
$numReg=$ejecutar_consulta->num_rows;
if($numReg!=0){
    switch ($idp) {
        case 1:
            $con = file_get_contents("../json/bittrex.json");
            $arraybtx=json_decode($con,TRUE);
            $subarray = $arraybtx["result"];
            while($registro=$ejecutar_consulta->fetch_assoc()){
                if($simbolo!=$registro["mone_simbolo"]){
                    $posicion = array_search($registro["mone_simbolo"]."-".$simbolo, array_column($arraybtx["result"], 'MarketName'));
                    $par = $registro["mone_simbolo"]."-".$simbolo;
                    if($posicion==""){
                        $posicion = array_search($simbolo."-".$registro["mone_simbolo"], array_column($arraybtx["result"], 'MarketName'));     
                        $par = $simbolo."-".$registro["mone_simbolo"];
                    }
                    if($posicion!=""){
                        $moneda["moneda"][]=$registro["mone_simbolo"];
                        $mercado["mercado"][] = $par;
                        $icono["icono"][] = "logos/".strtolower($registro["mone_simbolo"]).".png";    
                        if($acc==2){
                            $saldo["saldo"][] = number_format($saldoms,8,".","");
                            $idmonedaventa["idmonedaventa"][] = $idmoneda;
                            $idmonedacompra["idmonedacompra"][] = $registro["mone_id"];
                            $monedaventa["monedaventa"][] = $simbolo;
                            $monedacompra["monedacompra"][] = $registro["mone_simbolo"];
                            $crucemercados["crucemercados"][] = 0;
                        }
                        else{
                            $saldo["saldo"][] = number_format($registro["saldo"],8,".","");
                            $idmonedaventa["idmonedaventa"][] = $registro["mone_id"];
                            $idmonedacompra["idmonedacompra"][] = $idmoneda;
                            $monedaventa["monedaventa"][] = $registro["mone_simbolo"];
                            $monedacompra["monedacompra"][] = $simbolo;
                            if($simbolo."-".$registro["mone_simbolo"]==$par){
                                $crucemercados["crucemercados"][] = 1;    
                            }
                            else{
                                $crucemercados["crucemercados"][] = 0;    
                            }
                        }
                        $data = $moneda+$mercado+$saldo+$monedacompra+$monedaventa+$idmonedacompra+$idmonedaventa+$icono+$crucemercados;
                    }
                }
            }
        break;
        
    }
    if($data!=null){
        $status["status"][]="1";
        $tipousuario["tipousuario"][]=$tipousua;
        $ganancia["ganancia"][]=$usuaganacia;
        $perdida["perdida"][]=$usuaperdida;
        $comisiontaker["comisiontaker"][]= $comtaker;
        $comisionmaker["comisionmaker"][]= $commaker;
        $data+=$status+$comisionmaker+$comisiontaker+$tipousuario+$ganancia+$perdida+$iconooperacion;
        print_r(json_encode($data));
    }
    else{
        $status["status"][]="Usted no cuenta con saldos disponibles para completar esta operación";	
        $data=$status;
        print_r(json_encode($data));       
    }
}
else{
    $status["status"][]="Usted no cuenta con saldos disponibles para realizar esta operación";
    $data=$status;
    print_r(json_encode($data));
}
$conexion->close();
?>















