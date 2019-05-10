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
    <link href="<?=$url?>css/bootstrap-select.css" rel="stylesheet">
    <link href="<?=$url?>css/font-awesome.css" rel="stylesheet" type="text/css">
    <link href="<?=$url?>css/metisMenu.css" rel="stylesheet">
    <link href="<?=$url?>css/sb-admin-2.css" rel="stylesheet">
    <link href="<?=$url?>css/estilo.css" rel="stylesheet">
    <link href="<?=$url?>css/morris.css" rel="stylesheet">
    <link href="<?=$url?>css/alertify.css" rel="stylesheet">
    <script src="<?=$url?>js/morris.js"></script>
    <script src="<?=$url?>js/bootstrap-select.js"></script>
    <script src="<?=$url?>js/alertify.js"></script>
    <script src="<?=$url?>js/metisMenu.js"></script>
    <script src="<?=$url?>js/sb-admin-2.js"></script>
    <script src="<?=$url?>js/paginathing.js"></script>
    <script src="<?=$url?>js/validaciones.js"></script>
    <script>
        var idusuarioglobal;
    
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
        		cargarusuario();
	        });        
        }
    
        function tema() {
            var color = $("#selec-merc-gral").find(':selected').attr('data-color');
            $(".navbar-default").css("background-color", color);
            $("body").css('background-color', convertHex(color));
            $(".gmenu").css('background-color', convertHex(color));
            $("#heading-panel-usuarios").css('background-color', convertHex(color));
            $("#panel-usuarios").css('border-color', color);
        }
        
        //esta funcion convierte el color de hex a rgb para controlar la tonalidad del tema segun la plataforma elegida
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
        
        function cargarusuario(){
            var texto = $("#txtbusqueda").val().trim();
            $(".pagusuario").remove();
            $.get("cargarusuario.php",{texto:texto},function(data){  
                var response = jQuery.parseJSON(data);
                if(response.status==1){
                    var cadena= "";
                    for(var i = 0;i<response.id.length;i++){
                            cadena+='<tr>\
                                        <td>'+(i+1)+'</td>\
                                        <td>'+response.nombre[i]+'</td>\
                                        <td>'+response.documento[i]+'</td>\
                                        <td>'+response.usuario[i]+'</td>\
                                        <td>\
                                            <a class="btn btn-warning" onclick="cargarmodal(2,'+response.id[i]+')" title="modificar"><em class="fa fa-pencil"></em></a>\
                                            <a class="btn btn-danger" onclick="cargarmodal(3,'+response.id[i]+')" title="eliminar"><em class="fa fa-trash"></em></a>\
                                            <a class="btn btn-default" onclick="cargarmodal(4,'+response.id[i]+')" title="transferir"><em class="fa fa-exchange"></em></a>\
                                        </td>\
                                    </tr>';
                    } 
                    $("#tblusuarios").children().remove();
                    $("#tblusuarios").append(cadena);
                    
                    var nitems = response.id.length;
                    if(nitems>10){
                        if(nitems>=30){
                            nitems = 3;       
                        }
                        else{
                            nitems = Math.ceil(nitems/10);
                        }
                        $('#tblusuarios').paginathing({
                            perPage: 10,
                            limitPagination: parseInt(nitems),
                            containerClass: 'pagusuario',
                            insertAfter: '.table'
                         })   
                    }
                }
                else{
                    cadena+='<tr class="text-center"><td colspan="5">No se han encontrado datos de usuario</td></tr>';    
                    $("#tblusuarios").children().remove(); 
                    $("#tblusuarios").append(cadena);
                }
            });
            $("#div-loader").css("display","none");
            $("#wrapper").css("display","unset");
        }
        
        function cargarrol(id){
            $.get("../rol/cargarrol.php",{},function(data){      
                var response = jQuery.parseJSON(data);
                if(response.status==1){
                    cadena="<option value='' selected hidden>Seleccione</option>";
                    for(var i=0; i<response.id.length;i++){
                        if(response.id[i]!=id){
                            cadena+="<option value='"+response.id[i]+"'>"+response.nombre[i]+"</option>";    
                        }
                        else
                        {
                            cadena+="<option value='"+response.id[i]+"' selected>"+response.nombre[i]+"</option>";    
                        }
                        $("#txtrol").children().remove();
                        $("#txtrol").append(cadena);
                    }
                }
                else{
                    alertify.error("No han logrado cargarse los datos del rol");
                    $("#modalcrud").modal("hide");
                }
            });
        }
        
        
        function cargarmodal(n,i){
            if(n==1){
                $("#lbl-regi").slideDown(0);
                $("#lbl-modi").slideUp(0);
                $("#lbl-elim").slideUp(0);
                $("#btn-regi").slideDown(0);
                $("#btn-modi").slideUp(0);
                $("#btn-elim").slideUp(0);
                
                $("#txtnombres").val("");
                $("#txtapellidos").val("");
                $("#txtdocumento").val("");
                $("#txtusuario").val("");
                $("#txtcomision").val("0");
                $("#txttipo").val("");
                $("#txtganancia").val("200");
                $("#txtperdida").val("-99");
                cargarrol();
                
                $("#txtnombres").prop("disabled",false);
                $("#txtapellidos").prop("disabled",false);
                $("#txtdocumento").prop("disabled",false);
                $("#txtusuario").prop("disabled",false);
                $("#txtcomision").prop("disabled",false);
                $("#txttipo").prop("disabled",false);
                $("#txtganancia").prop("disabled",false);
                $("#txtperdida").prop("disabled",false);
                $("#txtrol").prop("disabled",false);
                
                $("#modalcrud").modal("show");
            } 
            else
            if(n==2){
                idusuarioglobal=i;
                $("#lbl-regi").slideUp(0);
                $("#lbl-modi").slideDown(0);
                $("#lbl-elim").slideUp(0);
                $("#btn-regi").slideUp(0);
                $("#btn-modi").slideDown(0);
                $("#btn-elim").slideUp(0);
                
                $("#txtnombres").prop("disabled",false);
                $("#txtapellidos").prop("disabled",false);
                $("#txtdocumento").prop("disabled",false);
                $("#txtusuario").prop("disabled",false);
                $("#txtcomision").prop("disabled",false);
                $("#txttipo").prop("disabled",false);
                $("#txtganancia").prop("disabled",false);
                $("#txtperdida").prop("disabled",false);
                $("#txtrol").prop("disabled",false);
                
                $.get("buscar.php",{id:idusuarioglobal},function(data){      
                    var response = jQuery.parseJSON(data);
                    if(response.status==1){
                        $("#txtnombres").val(response.nombres);
                        $("#txtapellidos").val(response.apellidos);
                        $("#txtdocumento").val(response.documento);
                        $("#txtusuario").val(response.usuario);
                        $("#txtcomision").val(response.comision);
                        $("#txttipo").val(response.tipo);
                        $("#txtganancia").val(response.ganancia);
                        $("#txtperdida").val(response.perdida);
                        cargarrol(response.rol);
                    }
                    else{
                        alertify.error("No han logrado cargarse los datos del usuario");
                        $("#modalcrud").modal("hide");
                    }
                });
                
                $("#modalcrud").modal("show");
            }
            else
            if(n==3){
                idusuarioglobal=i;
                $("#lbl-regi").slideUp(0);
                $("#lbl-modi").slideUp(0);
                $("#lbl-elim").slideDown(0);
                $("#btn-regi").slideUp(0);
                $("#btn-modi").slideUp(0);
                $("#btn-elim").slideDown(0);
                
                $("#txtnombres").prop("disabled",true);
                $("#txtapellidos").prop("disabled",true);
                $("#txtdocumento").prop("disabled",true);
                $("#txtusuario").prop("disabled",true);
                $("#txtcomision").prop("disabled",true);
                $("#txttipo").prop("disabled",true);
                $("#txtganancia").prop("disabled",true);
                $("#txtperdida").prop("disabled",true);
                $("#txtrol").prop("disabled",true);
                
                $.get("buscar.php",{id:idusuarioglobal},function(data){      
                    var response = jQuery.parseJSON(data);
                    if(response.status==1){
                        $("#txtnombres").val(response.nombres);
                        $("#txtapellidos").val(response.apellidos);
                        $("#txtdocumento").val(response.documento);
                        $("#txtusuario").val(response.usuario);
                        $("#txtcomision").val(response.comision);
                        $("#txttipo").val(response.tipo);
                        $("#txtganancia").val(response.ganancia);
                        $("#txtperdida").val(response.perdida);
                        cargarrol(response.rol);
                    }
                    else{
                        alertify.error("No han logrado cargarse los datos del usuario");
                        $("#modalcrud").modal("hide");
                    }
                });
                $("#modalcrud").modal("show");
            }
            else
            if(n==4){
                idusuarioglobal=i;
                plataformas();
                $("#txtfiltromoneda").val("");
                $("#txtcantidad").val("");
                $("#div-moneda").slideUp(0);
                $("#modaldepo").modal("show");
            }
        }
        
        function plataformas(){
            $.get("<?=$url?>plataforma/cargarplataforma.php",{usuario:idusuarioglobal}, function(data){
                var response = jQuery.parseJSON(data);
                if(response.status==1){
                    $("#txtusuariod").val(response.usuario);
                    cadena="<option selected hidden value=''>Seleccione</option>";
                    for(var i = 0;i<response.id.length;i++){
                        cadena+="<option value='"+response.id[i]+"' data-color='"+response.color[i]+"'>"+response.nombre[i]+"</option>";
                    }
                    $("#txtplataforma").children().remove();
                    $("#txtplataforma").append(cadena);
                }
                else{
                    alertify.error("se ha presentado un error al cargarlos datos de las plataformas");
                    $("#txtcantidad").val("");
                    $("#modaldepo").modal("hide");
                }
            });        
        }
        
        function cargarmoneda(){
            var plataforma = $("#txtplataforma").val();
            var texto = $("#txtfiltromoneda").val();
            $.get("cargarmoneda.php",{plataforma:plataforma,texto:texto}, function(data){
                var response = jQuery.parseJSON(data);
                if(response.status==1){
                    cadena="";
                    for(var i = 0;i<response.id.length;i++){
                        cadena+="<option value='"+response.id[i]+"' data-thumbnail='"+response.icono[i]+"'>"+response.nombre[i]+" ("+response.simbolo[i]+")"+"</option>";
                    }
                    $("#txtmoneda").children().remove();
                    $("#txtmoneda").append(cadena);
                    $("#div-moneda").slideDown(0);
                    $('#txtmoneda').selectpicker({});
                    $('#txtmoneda').selectpicker('refresh');
                }
                else{
                    alertify.error(String(response.status));
                    $("#div-moneda").slideUp(0);
                    $("#txtfiltromoneda").val("");
                    plataformas();
                }
            }); 
        }
        
        
        function registrar(){
            var nombres = $("#txtnombres").val().trim();
            var apellidos = $("#txtapellidos").val().trim();
            var documento = $("#txtdocumento").val().trim();
            var usuario = $("#txtusuario").val().trim();
            var comision = $("#txtcomision").val().trim();
            var tipo = $("#txttipo").val().trim();
            var ganancia = $("#txtganancia").val().trim();
            var perdida = $("#txtperdida").val().trim();
            var rol = $("#txtrol").val().trim();
            if(usuario!=""&&tipo!=""&&ganancia!=""&&perdida!=""&&comision!=""&&rol!=""){
                $.get("registrar.php",{nombres:nombres,apellidos:apellidos,documento:documento,usuario:usuario,comision:comision,tipo:tipo,ganancia:ganancia,perdida:perdida,rol:rol},function(data){      
                    var response = jQuery.parseJSON(data);
                    if(response.status==1){
                        alertify.success("Usuario registrado con éxito");
                        cargarusuario();
                        $("#modalcrud").modal("hide");
                    }
                    else{
                        alertify.error(String(response.status));
                    }
                });        
            }
            else{
                alertify.error("Complete los campos obligatorios");
            }
        }
        
        function modificar(){
            alertify.confirm("¿Esta seguro que desea modificar este usuario?",
                function(){
                    var nombres = $("#txtnombres").val().trim();
                    var apellidos = $("#txtapellidos").val().trim();
                    var documento = $("#txtdocumento").val().trim();
                    var usuario = $("#txtusuario").val().trim();
                    var comision = $("#txtcomision").val().trim();
                    var tipo = $("#txttipo").val().trim();
                    var ganancia = $("#txtganancia").val().trim();
                    var perdida = $("#txtperdida").val().trim();
                    var rol = $("#txtrol").val().trim();
                    if(usuario!=""&&tipo!=""&&ganancia!=""&&perdida!=""&&comision!=""&&rol!=""){
                        $.get("modificar.php",{id:idusuarioglobal,nombres:nombres,apellidos:apellidos,documento:documento,usuario:usuario,comision:comision,tipo:tipo,ganancia:ganancia,perdida:perdida,rol:rol},function(data){      
                            var response = jQuery.parseJSON(data);
                            if(response.status==1){
                                alertify.success("Usuario modificado con éxito");
                                cargarusuario();
                                $("#modalcrud").modal("hide");
                            }
                            else{
                                alertify.error(String(response.status));
                            }
                        });        
                    }
                    else{
                        alertify.error("Complete los campos obligatorios");
                    }
                }
            )
        }
        
        
        function eliminar(){
            alertify.confirm("¿Esta seguro que desea eliminar este usuario?",
                function(){
                    $.get("eliminar.php",{id:idusuarioglobal},function(data){      
                        var response = jQuery.parseJSON(data);
                        if(response.status==1){
                            alertify.success("Usuario eliminado con éxito");
                            cargarusuario();
                            $("#modalcrud").modal("hide");
                        }
                        else{
                            alertify.error(String(response.status));
                        }
                    });
                }
            )
        }
        
        function depositar(){
            alertify.confirm("¿Esta seguro que desea registrar este depósito?",
                function(){
                var cantidad=$("#txtcantidad").val();
                var moneda=$("#txtmoneda").val();
                var plataforma=$("#txtplataforma").val();
                if(cantidad!=""&&moneda!=""&&plataforma!=""){
                    $.get("../deposito/registrar.php",{usuario:idusuarioglobal,cantidad:cantidad,moneda:moneda,plataforma:plataforma},function(data){ 
                        var response = jQuery.parseJSON(data);
                        if(response.status==1){
                            alertify.success("Depósito realizado con éxito");
                            cargarusuario();
                            $("#modaldepo").modal("hide");
                        }
                        else{
                            alertify.error(String(response.status));
                        }    
                    })
                }
                else{
                    alertify.error("Ingrese todos lso campos obligatorios");
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
                                <select class="form-control" id="selec-merc-gral" onchange="tema();" style="padding-bottom:0px"></select>
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
        <!--//tabla-->
        <div class="container" style="width:100%;height:auto">
        <div class="panel panel-primary" id="panel-usuarios" style="margin-top:20px">
            <div class="panel-heading" id="heading-panel-usuarios">
                <div class="col col-xs-6">
                    <h3 class="panel-title">Usuarios</h3>
                  </div>
                  <div class="col col-xs-6 text-right" style="margin-bottom:5px">
                    <button type="button" class="btn btn-sm btn-success btn-create" onclick="cargarmodal(1,null)">Nuevo</button>
                  </div>
                <input type="text" class="form-control" placeholder="Escriba un nombre, usuario o un documento a buscar" id="txtbusqueda" name="txtbusqueda" onkeyup="cargarusuario()">
            </div>
            <div id="no-more-tables">
            <table class="table" style="margin-bottom:0px" id="tblusuariosg">
                <thead>
        			<tr>
        				<th>#</th>
        				<th>Nombre</th>
        				<th>Documento</th>
        				<th>Usuario</th>
        				<th style="text-align: center;"><i class="fa fa-cog"></i></th>
        			</tr>
        		</thead>
        		<tbody id="tblusuarios">
        			
        		</tbody>
            </table>
            </div>
        </div>
    </div>
            <!--tabla-->
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
  
    <!-- Modal crud-->
    <div class="modal fade" id="modalcrud" role="dialog">
        <div class="modal-dialog">    
      <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="lbl-regi">Registrar</h4>
                    <h4 class="modal-title" id="lbl-modi">Modificar</h4>
                    <h4 class="modal-title" id="lbl-elim">Eliminar</h4>
                </div>
            <div class="modal-body">
                
                <div class="form-group">
                    <label for="txtnombres">Nombres</label>
                    <input type="text" class="form-control" id="txtnombres" name="txtnombres">
                </div>
                
                <div class="form-group">
                    <label for="txtapellidos">Apellidos</label>
                    <input type="text" class="form-control" id="txtapellidos" name="txtapellidos">
                </div>
                
                <div class="form-group">
                    <label for="txtdocumento">Documento</label>
                    <input type="text" class="form-control" id="txtdocumento" name="txtdocumento">
                </div>
                
                <div class="form-group">
                    <label for="txtusuario">Usuario</label>
                    <input type="text" class="form-control" id="txtusuario" name="txtusuario">
                </div>
                
                <div class="form-group">
                    <label for="txtdocomision">Comisión</label>
                    <input type="text" class="form-control" id="txtcomision" name="txtcomision" oninput="solodecimal(this)">
                </div>
                
                <div class="form-group">
                    <label for="txttipo">Tipo</label>
                    <select type="text" class="form-control" id="txttipo" name="txttipo">
                        <option value="" selected hidden>Seleccione</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="txtganancia">Ganancia</label>
                    <input type="text" class="form-control" id="txtganancia" name="txtganancia" oninput="solodecimal(this)">
                </div>
                
                <div class="form-group">
                    <label for="txtperdida">Pérdida</label>
                    <input type="text" class="form-control" id="txtperdida" name="txtperdida" oninput="solodecimalnegativo(this)">
                </div>
                
                <div class="form-group">
                    <label for="txttipo">Rol</label>
                    <select type="text" class="form-control" id="txtrol" name="txtrol"></select>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btn-regi" onclick="registrar()">Registrar</button>
                <button type="button" class="btn btn-warning" id="btn-modi" onclick="modificar()">Modificar</button>
                <button type="button" class="btn btn-danger" id="btn-elim" onclick="eliminar()">Eliminar</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
          </div>      
        </div>
    </div>
  <!--//modal crud-->
  
  <!-- Modal deposito-->
    <div class="modal fade" id="modaldepo" role="dialog">
        <div class="modal-dialog">    
      <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="lbl-depo">Depositar</h4>
                </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="txtusuariod">Usuario</label>
                    <input type="text" class="form-control" id="txtusuariod" name="txtusuariod" onchange="cargarmoneda()" readonly>
                </div>
                
                <div class="form-group">
                    <label for="txtplataforma">Plataforma</label>
                    <select type="text" class="form-control" id="txtplataforma" name="txtplataforma" onchange="$('txtfiltromoneda').val('');cargarmoneda()"></select>
                </div>
                <div id="div-moneda">
                

                <div class="row">
                    <div class="col">
                        <div class="form-group col-md-6">
                            <label for="txtmoneda">Moneda</label>
                            <select type="text" class="form-control" id="txtmoneda" name="txtmoneda"></select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group col-md-6">
                            <label for="txtfiltromoneda">Filtro moneda</label>
                            <input type="text" class="form-control" id="txtfiltromoneda" name="txtfiltromoneda" placeholder="Escriba el nombre o simbolo de la moneda" onkeyup="cargarmoneda()">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="txtganancia">Cantidad</label>
                    <input type="text" class="form-control" id="txtcantidad" name="txtcantidad" oninput="solodecimal(this)" placeholder="Ingrese la cantidad a depositar">
                </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btn-depo" onclick="depositar()">Depositar</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
          </div>      
        </div>
    </div>
  <!--//modal deposito-->
  
   <!--//resumen-->
   </div>
    <div id="div-loader">
        <img src="<?=$url?>/img/loading.gif">
    </div>     
    </body>
</html>