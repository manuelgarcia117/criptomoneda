<?php
    include("../control/variables.php");
    session_start();
    $txtbusqueda = $_GET["texto"];
    $idp=$_GET["plataforma"];
    $idu=$_GET["usuario"];
    include("../control/conexion.php");
    $acentos = $conexion->query("SET NAMES 'utf8'");
    $consulta = "select m.*,(select s.simb_abreviacion from simbolo s where s.plat_id=p.plat_id and s.mone_id=m.mone_id) as simbolo from moneda m, plataforma p, monedaplataforma mp 
                where m.mone_id=mp.mone_id and mp.plat_id=p.plat_id and p.plat_id=$idp 
                and (m.mone_nombre like'%$txtbusqueda%' or m.mone_simbolo like'%$txtbusqueda%' or (select s.simb_abreviacion
                from simbolo s where s.plat_id = p.plat_id and s.mone_id = m.mone_id) LIKE '%$txtbusqueda%') and mp.mopl_bandera=1 order by mone_nombre";
    $ejecutar_consulta = $conexion->query($consulta);
    $numReg=$ejecutar_consulta->num_rows;
    if($numReg!=0){
        while ($registro=$ejecutar_consulta->fetch_assoc()) {
            $id["id"][] = $registro["mone_id"];
            if($registro["simbolo"]==""){
                $simboloa=$registro["mone_simbolo"];
            }
            else{
               $simboloa=$registro["simbolo"]; 
            }
            $simbolo["simbolo"][] = $simboloa;
            $icono["icono"][] = $url."logos/".strtolower($simboloa).".png";
            $nombre["nombre"][] = $registro["mone_nombre"];
            $data = $id+$nombre+$icono+$simbolo;
        }
        $status["status"][] = "1";
        $data+= $status;
        print_r(json_encode($data));
    }
    else{
        $status["status"][] = "No hay monedas disponibles para el criterio de busqueda";
        $data = $status;
        print_r(json_encode($data));    
    }
    $conexion->close();
?>