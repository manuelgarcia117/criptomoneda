<?php
    $cantidad = $_GET["cantidad"];
    $precio = $_GET["precio"];
    $numeroorden = $_GET["numeroorden"];
    $tipo = $_GET["tipo"];
    $mercado = $_GET["mercado"];
    $idorden = $_GET["idorden"];
    $apikey='123';
    $apisecret='456';
    $nonce=time();
    //comprar
    if($tipo==1){
        $uri="https://bittrex.com/api/v1.1/market/buylimit?apikey=$apikey&market=$mercado&quantity=$cantidad&rate=$precio&nonce=$nonce";    
    }
    else
    //vender
    if($tipo==2){
        $uri="https://bittrex.com/api/v1.1/market/selllimit?apikey=$apikey&market=$mercado&quantity=$cantidad&rate=$precio&nonce=$nonce";    
    }
    else
    //cancelar ordenes
    if($tipo==3){
        $uri = "https://bittrex.com/api/v1.1/market/cancel?apikey=$apikey&uuid=$idorden&nonce=$nonce";    
    }
    else
    //consultar una orden
    if($tipo==4){
        $uri = "https://bittrex.com/api/v1.1/account/getorder?apikey=$apikey&uuid=$idorden&nonce=$nonce";    
    }
    else
    //ver informacion de un mercado
    if($tipo==5){
       $uri = "https://bittrex.com/api/v1.1/public/getmarketsummary?market=$mercado";
    }
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult);   
 ?>