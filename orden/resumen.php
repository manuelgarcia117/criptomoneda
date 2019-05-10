<?php
include("../control/variables.php");
include("../control/conexion.php");
$idorden = $_GET["idorden"];
$plataforma = $_GET["plataforma"];
$consulta = "select t.*,o.*,(select m.mone_nombre from moneda m where m.mone_id=o.monecomp_id) as monedacomprada,
            (select m.mone_simbolo from moneda m where m.mone_id=o.monecomp_id) as simbolomonedacomprada,
            (select s.simb_abreviacion from simbolo s where s.mone_id=o.monecomp_id and s.plat_id=$plataforma) as osimbolomonedacomprada,
            (select m.mone_nombre from moneda m where m.mone_id=o.monebase_id) as monedabase,
            (select s.simb_abreviacion from simbolo s where s.mone_id=o.monebase_id and s.plat_id=$plataforma) as osimbolomonedabase,
            (select m.mone_simbolo from moneda m where m.mone_id=o.monebase_id) as simbolomonedabase,
            (select m.mone_nombre from moneda m where m.mone_id=o.monecomi_id) as monedacomision,
            (select m.mone_simbolo from moneda m where m.mone_id=o.monecomi_id) as simbolomonedacomision,
            (select s.simb_abreviacion from simbolo s where s.mone_id=o.monecomi_id and s.plat_id=$plataforma) as osimbolomonedacomision
            from transaccion t, orden o where t.orde_id=o.orde_id and o.orde_id=$idorden";
$ejecutar_consulta = $conexion->query(utf8_decode($consulta));
 $numReg=$ejecutar_consulta->num_rows;
    $auxcant=0;
    $auxcomi=0;
    $auxprec=0;
    if($numReg!=0){
        while ($registro=$ejecutar_consulta->fetch_assoc()){
        	if($registro["osimbolomonedacomprada"]==""){
        	    $simbolomonedacompra["simbolomonedacompra"][] = $registro["simbolomonedacomprada"];        
        	}
        	else{
        	     $simbolomonedacompra["simbolomonedacompra"][] = $registro["osimbolomonedacomprada"];    
        	}
        	
        	if($registro["osimbolomonedabase"]==""){
        	    $simbolomonedaventa["simbolomonedaventa"][] = $registro["simbolomonedabase"];        
        	}
        	else{
        	    $simbolomonedaventa["simbolomonedaventa"][] = $registro["osimbolomonedabase"];    
        	}
        	
        	if($registro["osimbolomonedacomision"]==""){
        	    $simbolomonedacomision["simbolomonedacomision"][] = $registro["simbolomonedacomision"];        
        	}
        	else{
        	    $simbolomonedacomision["simbolomonedacomision"][] = $registro["osimbolomonedacomision"];    
        	}
        	$iconomonedacompra["iconomonedacompra"][] = $url."logos/".strtolower($registro["simbolomonedacomprada"]).".png";
            $iconomonedaventa["iconomonedaventa"][] = $url."logos/".strtolower($registro["simbolomonedabase"]).".png";
            $iconomonedacomision["iconomonedacomision"][] = $url."logos/".strtolower($registro["simbolomonedacomision"]).".png";
            $monedacompra["monedacompra"][] = $registro["monedacomprada"];
            $monedaventa["monedaventa"][] = $registro["monedabase"];
            $monedacomision["monedacomision"][] = $registro["monedacomision"];
            $cantidad["cantidad"][] = $registro["tran_cantidad"];
            $precio["precio"][] = $registro["tran_precio"];            
            $comision["comision"][] = $registro["tran_comision"];
            $tipo["tipo"][] = $registro["orde_tipo"];
            
            if($registro["orde_tipo"]=="Compra"){
				$precioporunidad = number_format(($registro["tran_precio"]/$registro["tran_cantidad"]),8,".",",");
							
			}
			else{
				$precioporunidad = number_format(($registro["tran_cantidad"]/$registro["tran_precio"]),8,".",",");								
			}
			$preciounidad["preciounidad"][]=$precioporunidad;		
            $data = $monedacompra+$monedaventa+$monedacomision+$cantidad+$precio+$comision+$preciounidad+$tipo+$simbolomonedacompra+$simbolomonedaventa+$simbolomonedacomision+$iconomonedacompra+$iconomonedaventa+$iconomonedacomision;
            $auxcant+=$registro["tran_cantidad"];
            $auxcomi+=$registro["tran_comision"];
            $auxprec+=$registro["tran_precio"];
        }
        $status["status"][]="1";
        $totalcantidad["totalcantidad"][]=$auxcant;
        $totalcomision["totalcomision"][]=$auxcomi;
        $totalprecio["totalprecio"][]=$auxprec;
        $data+=$status+$totalcomision+$totalcantidad+$totalprecio;        
        print_r(json_encode($data));
    }
    else{
        $status["status"][]="Esta orden no tiene transacciones para mostrar";
        $data=$status;
        print_r(json_encode($data));    
    }
?>