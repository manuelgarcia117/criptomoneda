<?php
    $par = $_GET["par"];
    $idp = $_GET["plataforma"];
    $arrayp = explode(",", $par);
     switch ($idp) {
        case 1:
            for($i=0;$i<count($arrayp);$i++){
                $con=file_get_contents("https://bittrex.com/api/v1.1/public/getorderbook?market=".$arrayp[$i]."&type=both");
                $arraylibrobtx = json_decode($con,TRUE); 
                $subarray = $arraylibrobtx["result"];
                $venta = array_slice($subarray["sell"],0,8);
                $venta = array_reverse($venta);
                $compra = array_slice($subarray["buy"],0,8);
                $cadcantidad="";
                $cadprecio="";
                for($j=0;$j<count($venta);$j++){
                    $cadcantidad.=trim(rtrim(str_replace(",", "", number_format($venta[$j]["Quantity"],8)),"0"),".")."|";
                    $cadprecio.=str_replace(",", "", number_format($venta[$j]["Rate"],8))."|";
                }
                for($j=0;$j<count($compra);$j++){
                    $cadcantidad.=trim(rtrim(str_replace(",", "", number_format($compra[$j]["Quantity"],8)),"0"),".")."|";
                    $cadprecio.=str_replace(",", "", number_format($compra[$j]["Rate"],8))."|";
                }
                $cadcantidad = trim($cadcantidad, '|');
                $cadprecio = trim($cadprecio, '|');
                $mercado["mercado"][]=$arrayp[$i];
                $cantidad["cantidad"][]=$cadcantidad;
                $precio["precio"][]=$cadprecio;
                $data=$mercado+$cantidad+$precio;
            }
            // $compra = array_slice($subarray["buy"],0,10);
            print_r(json_encode($data));
        break;
     }
?>