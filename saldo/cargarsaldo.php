<?php
    session_start();
    $idusua = $_SESSION["id_usua"];
    $idp = $_GET["plataforma"];
    include("../control/conexion.php");
    $acentos = $conexion->query("SET NAMES 'utf8'");
    $consulta = "select distinct m.*,(select s.simb_abreviacion from simbolo s where s.mone_id = m.mone_id) as simbolo,
			((select ifnull(sum(d.depo_cantidad),0) from deposito d where d.usuareci_id=$idusua and d.plat_id=$idp and d.mone_id=m.mone_id)
			 -(select ifnull(sum(d.depo_cantidad),0) from deposito d where d.usuadepo_id=$idusua and d.plat_id=$idp and d.mone_id=m.mone_id)
			 +(select ifnull(sum(t.tran_cantidad),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$idp and o.monecomp_id=m.mone_id)
			 -(select ifnull(sum(o.orde_cantidad*o.orde_precio),0) from orden o where o.usua_id=$idusua and o.plat_id=$idp and (o.esor_id=1 or o.esor_id=3) and o.monebase_id=m.mone_id)
			 -(select ifnull(sum(t.tran_precio),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$idp and o.esor_id=2 and o.monebase_id=m.mone_id)
			 -(select ifnull(sum(t.tran_comision),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$idp and o.monecomi_id=m.mone_id)
			 -(select ifnull(sum(t.tran_comisionplataforma),0) from orden o, transaccion t where o.orde_id=t.orde_id and o.usua_id=$idusua and o.plat_id=$idp and o.monecomi_id=m.mone_id)
			)as saldo from moneda m, plataforma p, monedaplataforma mp where m.mone_id=mp.mone_id and mp.plat_id=p.plat_id and p.plat_id=$idp and m.mone_mercado=1 having saldo>0";
    $ejecutar_consulta = $conexion->query(utf8_decode($consulta)); 
    $numReg=$ejecutar_consulta->num_rows;
    if($numReg!=0){
        while ($registro=$ejecutar_consulta->fetch_assoc()) {
            $id["id"][] = $registro["sald_id"];
            $nombre["nombre"][] = $registro["mone_nombre"];
            $cantidad["cantidad"][] = number_format($registro["saldo"],8,".","");
            $estado["estado"][] = $registro["sald_estado"];
            $simbolo["simbolo"][] = $registro["mone_simbolo"];
            $icono["icono"][] = $registro["mone_icono"];
            $color["color"][] = $registro["mone_color"];
            $data = $id+$nombre+$cantidad+$estado+$simbolo+$color+$icono;
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