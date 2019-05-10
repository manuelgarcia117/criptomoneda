<?php
$idorden = $_GET["idorden"];
include("../control/conexion.php");
$consulta = "select t.*,o.*,(select m.mone_nombre from moneda m where m.mone_id=o.monecomp_id) as monedacomprada, 
            (select m.mone_nombre from moneda m where m.mone_id=o.monebase_id) as monedabase,
            (select m.mone_nombre from moneda m where m.mone_id=o.monecomi_id) as monedacomision
            from transaccion t, orden o where t.orde_id=o.orde_id and o.orde_id=$idorden";
$ejecutar_consulta = $conexion->query(utf8_decode($consulta));
 $numReg=$ejecutar_consulta->num_rows;
    if($numReg!=0){
        while ($registro=$ejecutar_consulta->fetch_assoc()){
        	
            $monedacompra["monedacompra"][] = $registro["monedacomprada"];
            $monedaventa["monedaventa"][] = $registro["monedabase"];
            $monedacomision["monedacomision"][] = $registro["monedacomision"];
            $cantidad["cantidad"][] = $registro["tran_cantidad"];
            $precio["precio"][] = $registro["tran_precio"];            
            $comision["comision"][] = $registro["tran_comision"];
        	$tipo["tipo"][] = $registro["orde_tipo"];
            if($registro["orde_tipo"]=="Compra"){
				$precioporunidad = floor(($registro["tran_precio"]/$registro["tran_cantidad"])*100000000)/100000000;
							
			}
			else{
				$precioporunidad = floor(($registro["tran_cantidad"]/$registro["tran_precio"])*100000000)/100000000;								
			}
			$preciounidad["preciounidad"][]=$precioporunidad;		
            $data = $monedacompra+$monedaventa+$monedacomision+$cantidad+$precio+$comision+$preciounidad+$tipo;
            
        }        
        $status["status"][]="1";
        $data+=$status;        
        print_r(json_encode($data));
    }
    else{
        $status["status"][]="Esta orden no tiene transacciones para mostrar";
        $data=$status;
        print_r(json_encode($data));    
    }
?>