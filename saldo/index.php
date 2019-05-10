<?php
    include("../control/variables.php");
    session_start();
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portafolio</title>
    <!-- Bootstrap Core CSS -->
    <link rel="shortcut icon" type="image/png" sizes='72x72' href="<?=$url?>img/favicon.png"/>
    <script src="<?=$url?>js/jquery.min.js"></script>
    <script src="<?=$url?>js/bootstrap.js"></script>
    <link href="<?=$url?>css/bootstrap.css" rel="stylesheet">
    <link href="<?=$url?>css/font-awesome.css" rel="stylesheet" type="text/css">
    <link href="<?=$url?>css/metisMenu.css" rel="stylesheet">
    <link href="<?=$url?>css/sb-admin-2.css" rel="stylesheet">
    <link href="<?=$url?>css/estilo.css" rel="stylesheet">
    <link href="<?=$url?>css/morris.css" rel="stylesheet">
    <link href="<?=$url?>css/alertify.css" rel="stylesheet">
    <script src="<?=$url?>js/morris.js"></script>
    <script src="<?=$url?>js/alertify.js"></script>
    <script src="<?=$url?>js/metisMenu.js"></script>
    <script src="<?=$url?>js/sb-admin-2.js"></script>
    <script src="<?=$url?>js/paginathing.js"></script>
    <script src="<?=$url?>js/validaciones.js"></script>
    <script>
    //variables de control de recarga del order buy
    var idrecargaorderbuy;
    var tipocargaroderbuy;
    var strmercorderbuy;
    //variable para evitar que el order buy modifique un precio que el usuario ya haya cambiado
    var strprecioestatico;
    //id de la orden a modificar
    var idordenglobal;
    
        $(document).ready(function(){
            //carga de menú
            $.get("<?=$url?>control/cargarmenu.php", function(data){
        		var response = jQuery.parseJSON(data);
        		for(var i = 0;i<response.id.length;i++){
        			if(response.nombre[i]!="Mi perfil"){
						$("#limenu").append('<a href="<?=$url?>'+response.ruta[i]+'"><i class="'+response.icono[i]+'"></i> '+response.nombre[i]+'</a>');
					}
					else{					
						$("#limenu").append('<a href="'+response.ruta[i]+'"><i class="'+response.icono[i]+'"></i> '+response.nombre[i]+'</a>');
					}        		         
        		}
        		$("#limenu").append('<a href="<?=$url?>control/logout.php"><i class="glyphicon glyphicon-log-out"></i> Cerrar sesión</a>');        		
	        })
	        
        });
        
        
        //se cargan las plataformas en el combo gral
        function cargarplataformas(id){
            $.get("<?=$url?>plataforma/cargarplataforma.php", function(data){
        		var response = jQuery.parseJSON(data);
        		for(var i = 0;i<response.id.length;i++){
        		    $("#selec-merc-gral").append("<option value='"+response.id[i]+"' data-color='"+response.color[i]+"'>"+response.nombre[i]+"</option>");       
        		}
        		tema();
        		cargarsaldodetallado();
	        });        
        }
    
        function tema() {
            var color = $("#selec-merc-gral").find(':selected').attr('data-color');
            $(".navbar-def  ault").css("background-color", color);
            $("body").css('background-color', convertHex(color));
            $(".gmenu").css('background-color', convertHex(color));
        }
        
        //esta funcion convierte el color de hex a rgb para controlar la tomalidad del tema segun la plataforma elegida
        function convertHex(hex) {
            hex = hex.replace('#', '');
            r = parseInt(hex.substring(0, 2), 16);
            g = parseInt(hex.substring(2, 4), 16);
            b = parseInt(hex.substring(4, 6), 16);
            result = 'rgba(' + r + ',' + g + ',' + b + ',' + 0.8 + ')';
            return result;
        }
        
        
        function cargarperfil(){
            $.get("../usuario/cargarperfil.php",{},function(data){  
                var response = jQuery.parseJSON(data);
                if(response.status=="1"){
                    $("#txtnombresperfil").val(response.nombres);
                    $("#txtapellidosperfil").val(response.apellidos); 
                    $("#txtrolperfil").val(response.rol); 
                    $("#modalperfil").modal('show');        
                }
                else{
                    alertify.error(response.status);
                }
            });
        }
        
        function modificarperfil(){
            var clave1 = $("#txtclaveperfil1").val().trim();
            var clave2 = $("#txtclaveperfil2").val().trim();
            if(clave1!=""&&clave2!=""){
                if(clave1==clave2){
                    $.get("../usuario/modificarperfil.php",{clave:clave1},function(data){  
                        if(data==1){
                            alertify.success("Su perfil ha sido modificado con éxito");        
                        }
                        else{
                            alertify.error(data);
                        }
                    });
                }
                else{
                    alertify.error("La nueva clave y la confirmación no coinciden");    
                }
            }
            else{
                alertify.error("Por favor escriba la nueva contraseña y su confirmación");
            }
        }
        
        function cargarsaldodetallado(){
            var plataforma= $("#selec-merc-gral").val();
            $.get("cargarsaldodetallado.php",{plataforma:plataforma},function(data){  
                var response = jQuery.parseJSON(data);
                if(response.status=="1"){
                    cadena="";
                    for(var i=0;i<response.nombre.length;i++){
                        cadena+="<tr><td><img src='"+response.icono[i]+"' width='20' height='20'/>"+" "+response.nombre[i]+" "+response.simbolo[i]+"</td><td style='text-align:right'>"+response.saldodisponible[i]+"</td><td style='text-align:right'>"+response.saldoordenes[i]+"</td><td style='text-align:right'>"+response.saldototal[i]+"</td><td style='text-align:center'>"+response.preciobtc[i]+"</td></tr>"    
                    }
                    cadena+="<tr><td colspan='4'><b><p class='text-center' style='margin:0px'>Total Bitcoin</p></b></td><td style='text-align:center'>"+response.totalbtc+"</td></tr>";
                    cadena+="<tr><td colspan='4'><b><p class='text-center' style='margin:0px'>Total Pesos</p></b></td><td style='text-align:center'>"+response.totalpesos+"</td></tr>";
                    $("#tblsaldo").children().remove();
                    $("#tblsaldo").append(cadena);
                    setTimeout(function(){ cargarsaldodetallado(); }, 5000);
                    $("#div-loader").css("display","none");
                    $("#wrapper").css("display","unset");
                }
                else{
                    $("#tblsaldo").children().remove();
                    $("#tblsaldo").append("<td colspan='6'><h4 class='text-center'>No hay saldos disponibles para mostrar<h4></td>");
                    setTimeout(function(){ cargarsaldodetallado(); }, 5000);
                    $("#div-loader").css("display","none");
                    $("#wrapper").css("display","unset");
                }
            });
        }
        
        
    </script>


    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body onload="cargarplataformas()">
    <div id="wrapper" style="display:none">
        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <img src="<?=$url?>img/logotransparente.png" style="height:40px;margin:5px 0px 0px 20px" />
            </div>
            
            <div class="navbar-default sidebar gmenu" role="navigation">
                <div class="sidebar-nav navbar-collapse gmenu">
                    <ul class="nav gmenu" id="side-menu">
                        <li class="sidebar-search">
                            <div class="input-group custom-search-form">
                                <select class="form-control" id="selec-merc-gral" onchange="tema();cargarmonedas();cargarsaldo();cargarmonedasventa();" style="padding-bottom:0px"></select>
                                <span class="input-group-addon">$</span>
                                </span>
                            </div>
                             <!--/input-group -->
                        </li>
                        <li class="gmenu" id="limenu">
                            <a href="<?=$url?>panel.php"><i class="fa fa-home fa-fw"></i> Inicio</a>
                        </li>
                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>

        <div id="page-wrapper">
            <br>
            <table class="table table-hover">
                <thead>
                    <tr>
                      <th class="text-center" colspan="6">Mis saldos</th>
                    </tr>
                    <tr>
                      <th></th>
                      <th style="text-align:right">Disponible</th>
                      <th style="text-align:right">En órdenes</th>
                      <th style="text-align:right">Total</th>
                      <th style="text-align:center">Precio BTC</th>
                    </tr>
                </thead>
                <tbody id="tblsaldo">
                    
                </tbody>
            </table>
            
            
        </div>
        
                <!-- Modal perfil-->
    <div class="modal fade" id="modalperfil" role="dialog">
        <div class="modal-dialog modal-sm">    
      <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Mi perfil</h4>
            </div>
            <div class="modal-body">
                
                <div class="form-group">
                    <label for="txtnombresperfil">Nombres</label>
                    <input type="text" class="form-control" id="txtnombresperfil" name="txtnombresperfil" disabled>
                </div>
                
                <div class="form-group">
                    <label for="txtapellidosperfil">Apellidos</label>
                    <input type="text" class="form-control" id="txtapellidosperfil" name="txtapellidosperfil" disabled>
                </div>
                
                <div class="form-group">
                    <label for="txtclaveperfil1">Contraseña</label>
                    <input type="password" class="form-control" id="txtclaveperfil1" name="txtclaveperfil1">
                </div>
                
                <div class="form-group">
                    <label for="txtclaveperfil2">Confirmar contraseña</label>
                    <input type="password" class="form-control" id="txtclaveperfil2" name="txtclaveperfil2">
                </div>
                
                <div class="form-group">
                    <label for="txtrolperfil">Rol</label>
                    <input type="text" class="form-control" id="txtrolperfil" name="txtrolperfil" disabled>
                </div>
                
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-success" onclick="modificarperfil()">Modificar</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
          </div>      
        </div>
    </div>
  <!--//modal perfil-->
   <!--//resumen-->
   </div>
    <div id="div-loader">
        <img src="<?=$url?>/img/loading.gif">
    </div>     
    </body>
</html>