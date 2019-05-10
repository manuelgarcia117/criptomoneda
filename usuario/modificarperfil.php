<?php
    session_start();
    $idusua = $_SESSION["id_usua"];
    $clave = $_GET["clave"];
    $key='ACTIVOSDIGITALES'; 
    $clave = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $clave, MCRYPT_MODE_CBC, md5(md5($key))));
    include("../control/conexion.php");
    $acentos = $conexion->query("SET NAMES 'utf8'");
    $consulta = "update usuario set usua_clave='$clave' where usua_id=$idusua";
    $ejecutar_consulta = $conexion->query(utf8_decode($consulta));
    if($ejecutar_consulta==1){
        echo "1";    
    }
    else{
        echo "Se ha producido un error, intente nuevamente";    
    }
    $conexion->close();
?>