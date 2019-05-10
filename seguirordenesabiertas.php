<?php
    header('Access-Control-Allow-Origin: *');   
    date_default_timezone_set("America/Bogota");
    include("control/conexion.php");
    include("control/variables.php");        
    //traer parametros
    $consulta = "select * from parametros";
    $ejecutar_consulta = $conexion->query($consulta);
    $registro=$ejecutar_consulta->fetch_assoc();
    $stoploss = $registro["para_stoploss"];
    $subirorden = $registro["para_montarorden"];    
    $slp = ($_GET["slp"]);
    $aux =0;  
    while($aux<56){
        $tinicio = microtime(true);   
        //traer ordenes abiertas y parcialmente ejecutadas       
        $consulta = "select distinct o.*, u.usua_comision,ifnull((select sum(t.tran_cantidad) from transaccion t where t.orde_id=o.orde_id),0) as filled,
        ifnull((select sum(t.tran_precio) from transaccion t where t.orde_id=o.orde_id),0) as precioparcial,
        ifnull((select sum(t.tran_comision) from transaccion t where t.orde_id=o.orde_id),0) as comisionparcial from orden o,usuario u where o.usua_id=u.usua_id and (esor_id=1 or esor_id=3) and o.orde_bandera=0";
        $ejecutar_consulta = $conexion->query($consulta);
        $numReg=$ejecutar_consulta->num_rows;
        if($numReg!=0){
            while ($registro=$ejecutar_consulta->fetch_assoc()){
                $tiniciop = microtime(true);
                $consultaab = "update orden set orde_bandera=1 where orde_id=".$registro["orde_id"];
                $ejecutarconsultaab = $conexion->query($consultaab);            
                if($ejecutarconsultaab){    
                    switch ($registro["plat_id"]) {
                        case 1:
                            $con = file_get_contents("http://2x3.co/operacion/api.php?idorden=".$registro["orde_numero"]."&tipo=4");            
                            $arrayresult = json_decode($con,true);
                            if($arrayresult!=null&&$arrayresult["success"]!=""&&(($arrayresult["result"]["IsOpen"]==""&&$arrayresult["result"]["Closed"]!="")||($registro["esor_id"]==1&&$arrayresult["result"]["QuantityRemaining"]!=$arrayresult["result"]["Quantity"])||$registro["esor_id"]==3)){                   
                                if($registro["orde_tipo"]=="Compra"){
                                    $fecha = date("Y-m-d H:i:s");
                                    $cantidad = number_format((($arrayresult["result"]["Quantity"]-$arrayresult["result"]["QuantityRemaining"])-$registro["filled"]),8,".","");
                                    $precio = number_format(($arrayresult["result"]["Price"]-$registro["precioparcial"]),8,".","");
                                    $comision = number_format(($arrayresult["result"]["CommissionPaid"]-$registro["comisionparcial"]),8,".","");                       
                                    $comisionplataforma = number_format($precio*$registro["usua_comision"],8,".","");                      
                                                  
                                    if($cantidad>0){
                                        $consultac = "insert into transaccion(tran_cantidad,tran_precio,tran_comision,tran_comisionplataforma, orde_id)
                                                        values('$cantidad','$precio','$comision','$comisionplataforma',".$registro["orde_id"].")";
                                        $ejecutarconsultac = $conexion->query($consultac);                                              
                                        if($ejecutarconsultac){                                
                                            if(number_format($arrayresult["result"]["QuantityRemaining"],8,".","")==0){
                                                $consultac = "update orden set esor_id=2,orde_entrada=1,orde_fechacierre='$fecha' where orde_id=".$registro["orde_id"];
                                                $conexion->query($consultac);
                                            }
                                            else{
                                                $consultac = "update orden set esor_id=3,orde_fechacierre='$fecha' where orde_id=".$registro["orde_id"];
                                                $conexion->query($consultac);   
                                            }
                                        }
                                    }
                                    else{
                                        if($arrayresult["result"]["QuantityRemaining"]!=0&&$arrayresult["result"]["Closed"]!=""){
                                            $consultac = "update orden set esor_id=2,orde_entrada=2,orde_fechacierre='$fecha' where orde_id=".$registro["orde_id"];
                                            $conexion->query(utf8_decode($consultac));  
                                        }
                                        else                                        
                                        if($arrayresult["result"]["QuantityRemaining"]==0&&$arrayresult["result"]["Closed"]!=""){
                                            $consultac = "update orden set esor_id=2,orde_entrada=3,orde_fechacierre='$fecha' where orde_id=".$registro["orde_id"];
                                            $conexion->query(utf8_decode($consultac));    
                                        }
                                        else
                                        if(($arrayresult["result"]["Quantity"]-$arrayresult["result"]["QuantityRemaining"])!=0&&$arrayresult["result"]["Closed"]==""&&$registro["esor_id"]!=3){
                                                $consultac = "update orden set esor_id=3,orde_entrada=3,orde_fechacierre='$fecha' where orde_id=".$registro["orde_id"];
                                                $conexion->query($consultac);    
                                        }
                                        if(($arrayresult["result"]["Quantity"]==$arrayresult["result"]["QuantityRemaining"])&&$arrayresult["result"]["Closed"]!=""){
                                            $consultac = "delete from orden where orde_id=".$registro["orde_id"];
                                            $conexion->query($consultac);  
                                        }
                                    }                                     
                                }
                                else{
                                    $fecha = date("Y-m-d H:i:s");
                                    $precio = number_format((($arrayresult["result"]["Quantity"]-$arrayresult["result"]["QuantityRemaining"])-$registro["precioparcial"]),8,".","");
                                    $cantidad = number_format($arrayresult["result"]["Price"] - $registro["filled"],8,".","");
                                    $comision = number_format($arrayresult["result"]["CommissionPaid"] - $registro["comisionparcial"],8,".","");
                                    $comisionplataforma = number_format($precio*$registro["usua_comision"],8,".","");                        
                                                        
                                    if($cantidad>0){
                                        $consultac = "insert into transaccion(tran_cantidad,tran_precio,tran_comision,tran_comisionplataforma, orde_id)
                                                        values('$cantidad','$precio','$comision','$comisionplataforma',".$registro["orde_id"].")";
                                        $ejecutarconsultac = $conexion->query($consultac);
                                        if($ejecutarconsultac){                             
                                            if(number_format($arrayresult["result"]["QuantityRemaining"],8,".","")==0){
                                                $consultav = "update orden set esor_id=2,orde_entrada=1,orde_fechacierre='$fecha' where orde_id=".$registro["orde_id"];
                                                $conexion->query($consultav);
                                            }
                                            else{
                                                $consultav = "update orden set esor_id=3,orde_fechacierre='$fecha' where orde_id=".$registro["orde_id"];
                                                $conexion->query($consultav);    
                                            }
                                        }
                                    }
                                    else{
                                        if($arrayresult["result"]["QuantityRemaining"]!=0&&$arrayresult["result"]["Closed"]!=""){
                                            $consultav = "update orden set esor_id=2,orde_entrada=2,orde_fechacierre='$fecha' where orde_id=".$registro["orde_id"];
                                            $conexion->query($consultav);  
                                        }
                                        else                                        
                                        if($arrayresult["result"]["QuantityRemaining"]==0&&$arrayresult["result"]["Closed"]!=""){
                                            $consultav = "update orden set esor_id=2,orde_entrada=3,orde_fechacierre='$fecha' where orde_id=".$registro["orde_id"];
                                            $conexion->query($consultav);
                                        }
                                        else
                                        if(($arrayresult["result"]["Quantity"]-$arrayresult["result"]["QuantityRemaining"])!=0&&$arrayresult["result"]["Closed"]==""&&$registro["esor_id"]!=3){
                                                $consultav = "update orden set esor_id=3,orde_fechacierre='$fecha' where orde_id=".$registro["orde_id"];
                                                $conexion->query($consultav);    
                                        }
                                        if(($arrayresult["result"]["Quantity"]==$arrayresult["result"]["QuantityRemaining"])&&$arrayresult["result"]["Closed"]!=""){
                                            $consultav = "delete from orden where orde_id=".$registro["orde_id"];
                                            $conexion->query($consultav);  
                                        }                                     
                                    }                                 
                                }
                            }
                        break;
                    }                    
                }
                $consultaab = "update orden set orde_bandera=0 where orde_id=".$registro["orde_id"];
                $conexion->query($consultaab);                
                $tfinalp = microtime(true);
                $ttotalp = ($tfinalp - $tiniciop)+1;
                if(($aux+$ttotalp)>=56){
                    break;
                }
            }
        }  
        
                  
        //seguirgananciaperdida
        
        $consultap = "select distinct o.*,u.usua_comision, (select sum(t.tran_cantidad) from transaccion t where t.orde_id=o.orde_id and t.tran_procesada=0)as cantidad,
        (select (t.tran_precio/t.tran_cantidad) from transaccion t where t.orde_id=o.orde_id and t.tran_procesada=0 limit 1) as preciocompra
        from orden o,usuario u where o.orde_seguimiento = 1 and o.orde_tipo='Compra' and o.usua_id=u.usua_id having(cantidad>0 and preciocompra>0)";
        $ejecutar_consultap = $conexion->query($consultap);
        $numReg=$ejecutar_consultap->num_rows;
        if($numReg!=0){
            
            while($registro=$ejecutar_consultap->fetch_assoc()){
                $tiniciop = microtime(true);
                $consultaab = "update orden set orde_bandera=1 where orde_id=".$registro["orde_id"];
                $ejecutarconsultaab = $conexion->query($consultaab);
                if($ejecutarconsultaab){    
                    switch ($registro["plat_id"]) {
                        case 1:
                            //verificar si se cumple el porcentaje de ganancia perdida establecido
                            $gananciabd = number_format($registro["orde_ganancia"],8,".","");
                            $perdidabd = number_format($registro["orde_perdida"],8,".","");
                            $mercado = $registro["orde_mercado"];
                            $preciocompra = number_format($registro["orde_precio"],8,".","");
                            $cantidad = number_format($registro["cantidad"],8,".","");
                            //echo "cantidad a vender: ".$cantidad;                            
                            //consultar precio actual con la api
                            $con = file_get_contents($url."operacion/api.php?mercado=".$registro["orde_mercado"]."&tipo=5&plataforma=".$registro["plat_id"]);                                       
                            $arrayresult = json_decode($con,true);
                            $resultado = $arrayresult["result"];
                            //se valida que la api retorne un error correcto
                            if($arrayresult!=null&&number_format($resultado["0"]["Bid"]>0)){    
                                
                                $precioactual = number_format($resultado["0"]["Bid"],8,".","");
                                $pdiferencia = number_format(((($precioactual/$preciocompra)-1)*100),2,".","");                        
                                
                                //echo "precio compra".$preciocompra."<br>";
                                //echo "precio actual".$precioactual."<br>";
                                //echo "diferencia".$pdiferencia."<br>";
                                //echo "ganancia".$gananciabd."<br>";                            
                                //se calcula el porcentaje para el stop loss
                                $auxsl = (($perdidabd)*($stoploss))/100;
                                if($perdidabd>=0){
                                    $disparador = $auxsl+$perdidabd;    
                                }else{
                                    $disparador = (($auxsl-$perdidabd)*-1);     
                                }
                                //echo "disparador fijado a".$disparador."<br>";
                                
                                //echo $registro["usua_comision"];  
                                
                                if($pdiferencia>=$gananciabd){
                                    //actualizar las ordenes a procesadas
                                    $consultaac = "update transaccion set tran_procesada=1 where orde_id=".$registro["orde_id"]; 
                                    $ejecutar_consultac = $conexion->query($consultaac);
                                    //echo "ENTRO EN GANANCIA<br>";
                                    if($ejecutar_consultac){
                                        $preciosubirorden = number_format($preciocompra+($preciocompra*($gananciabd/100)),8,".",""); 
                                        //$precioprueba = "30";
                                        $conv = file_get_contents($url."operacion/api.php?cantidad=$cantidad&precio=$preciosubirorden&mercado=$mercado&tipo=2&plataforma=".$registro["plat_id"]);
                                        //echo "Se monta una orden con las siguientes caracteristicas<br>";
                                        //echo $url."operacion/api.php?cantidad=$cantidad&precio=$preciosubirorden&mercado=$mercado&tipo=2&plataforma=".$registro["plat_id"]."<br><br> ";
                                        $arrayresultv = json_decode($conv,true);
                                       // print_r($arrayresultv);
                                    }
                                    
                                }
                                else
                                if($pdiferencia<=$disparador){
                                    $consultaac = "update transaccion set tran_procesada=1 where orde_id=".$registro["orde_id"]; 
                                    $ejecutar_consultac = $conexion->query($consultaac);
                                    if($ejecutar_consultac){
                                        ///echo "ENTRO EN PERDIDA<br>";
                                        $auxorden = (($perdidabd)*($subirorden))/100;
                                        if($perdidabd>=0){
                                            $psubirorden = $perdidabd-$auxorden; 
                                            $preciosubirorden = number_format($precioactual-(($precioactual*$psubirorden)/100),8,".","");
                                        }
                                        else{
                                            $psubirorden = $perdidabd+$auxorden;
                                            $preciosubirorden = number_format($precioactual+(($precioactual*$psubirorden)/100),8,".","");
                                        }
                                        //echo "Se monta una orden con las siguientes caracteristicas<br>";
                                        $conv = file_get_contents($url."operacion/api.php?cantidad=$cantidad&precio=$preciosubirorden&mercado=$mercado&tipo=2&plataforma=".$registro["plat_id"]);
                                        //echo $url."operacion/api.php?cantidad=$cantidad&precio=$preciosubirorden&mercado=$mercado&tipo=2&plataforma=".$registro["plat_id"]."<br>";
                                        $arrayresultv = json_decode($conv,true);
                                    }
                                    //print_r($arrayresultv);
                                }
                                // se ejecuta orden de venta cuando cumple las condiciones ganancia/perdida
                                // ejecutar orden de venta normal
                                if($arrayresultv["success"]=="1"&&$arrayresultv!=null){
                                    $numerooperacion = $arrayresultv["result"]["uuid"];
                                    //consulta de la operacion para tomar el saldo de taker
                                    $conc = file_get_contents($url."operacion/api.php?idorden=$numerooperacion&tipo=4");
                                    $fecha = date("Y-m-d H:i:s");                               
                                    $arrayresultc = json_decode($conc,true);
                                    $consulta = "insert into orden (orde_cantidad,orde_precio,orde_numero,orde_fechaapertura,usua_id,monecomp_id,monebase_id,plat_id,orde_tipo,orde_mercado, monecomi_id) 
                                                values('$cantidad','$preciosubirorden','$numerooperacion','$fecha',".$registro["usua_id"].",".$registro["monebase_id"].",".$registro["monecomp_id"].",".$registro["plat_id"].",'Venta','$mercado',".$registro["monebase_id"].")";
                                    $conexion->query($consulta);
                                    $ultimoid = $conexion->insert_id;
                                    $consultadif = "insert into diferencia(ordecomp_id,ordevent_id) values(".$registro["orde_id"].",$ultimoid)";
                                    $conexion->query($consultadif);                          
                                    $filled = number_format($arrayresultc["result"]["Quantity"]-$arrayresultc["result"]["QuantityRemaining"],8,".","");
                                    $preciotransaccion = number_format($arrayresultc["result"]["Price"],8,".","");
                                    $comision = number_format($arrayresultc["result"]["CommissionPaid"],8,".","");
                                    //echo "el filled de esta operacion es:".$filled."<br>";
                                    //echo "el precio de esta transaccion es:".$preciotransaccion."<br>";
                                    //echo "la comision es:".$comision."<br>";
                                    $comisionplataforma = number_format($preciotransaccion*$registro["usua_comision"],8,".","");
                                    if($filled>0)
                                    {                                  
                                        $precio = $arrayresultv["result"]["PricePerUnit"];
                                        $consulta = "insert into transaccion (orde_id,tran_cantidad,tran_precio,tran_comision,tran_tipo,tran_comisionplataforma) 
                                                    values($ultimoid,'$preciotransaccion','$filled','$comision','Taker','$comisionplataforma')";
                                        $conexion->query($consulta);
                                    }                               
                                }
                                else{
                                    $consultaac = "update transaccion set tran_procesada=0 where orde_id=".$registro["orde_id"]; 
                                    $ejecutar_consultac = $conexion->query($consultaac);
                                }
                                $arrayresultv=null;
                            }
                        break;
                    }
                }
                $consultaab = "update orden set orde_bandera=0 where orde_id=".$registro["orde_id"];
                $conexion->query($consultaab);
                $tfinalp = microtime(true);
                $ttotalp = ($tfinalp - $tiniciop)+1;
                if(($aux+$ttotalp)>=56){
                    break;
                }
            }
        }               
        sleep(2);
        $tfinal = microtime(true);
        $ttotal = $tfinal - $tinicio;
        $aux+=$ttotal;
    }  
   $conexion->close(); 
?>