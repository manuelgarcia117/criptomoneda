<?php
    include("../control/conexion.php");
    include("../control/variables.php");
    $texto = $_GET["texto"];
    $plataforma = $_GET["plataforma"];
    $consulta = "select distinct m.*, (select s.simb_abreviacion from simbolo s where s.mone_id=m.mone_id) as simbolo 
                from moneda m, monedaplataforma mp,plataforma p where m.mone_id=mp.mone_id and mp.plat_id=p.plat_id 
                and p.plat_id=$plataforma and mp.mopl_bandera=1 and (m.mone_simbolo like '%$texto%' or m.mone_nombre like '%$texto%' or 
                (select s.simb_abreviacion from simbolo s where s.plat_id=p.plat_id and s.mone_id=m.mone_id) like '%$texto%')
";
     $ejecutar_consulta = $conexion->query(utf8_decode($consulta));
    $numReg=$ejecutar_consulta->num_rows;
    if($numReg!=0){
        while ($registro=$ejecutar_consulta->fetch_assoc()) {
            $id["id"][] = $registro["mone_id"];
            $nombre["nombre"][] = $registro["mone_nombre"];
            $icono["icono"][] = $url."logos/".strtolower($registro["mone_simbolo"]).".png";
            if($registro["simbolo"]==""){
                $simbolo["simbolo"][]=$registro["mone_simbolo"];
            }
            else{
                $simbolo["simbolo"][]=$registro["simbolo"];    
            }
            $data = $id+$nombre+$icono+$simbolo;
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