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
    <title>Orden</title>
    <!-- Bootstrap Core CSS -->
    <link rel="shortcut icon" type="image/png" sizes='72x72' href="<?=$url?>img/favicon.png"/>
    <script src="<?=$url?>js/jquery.min.js"></script>
    <script src="<?=$url?>js/bootstrap.js"></script>
    <link href="<?=$url?>css/bootstrap.css" rel="stylesheet">
    <link href="<?=$url?>css/bootstrap-datetimepicker.css" rel="stylesheet" media="screen">
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
    <script src="<?=$url?>js/paginacion.js"></script>
    <script src="<?=$url?>js/validaciones.js"></script>
    <script>
    //id para mantener el filtro de moenda
    var idmonedafiltro="";
    var idsi="";
    
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
	        
	        //limpiar el filtro al cerrar
	        $('.filterable .btn-filter').click(function(){
                $('.filters').slideToggle(0);
                $("#autocompletarmonedas").children().remove();
                $('#txtfiltromoneda').val("");
                $('#txtfiltrofecha1').val("");
                $('#txtfiltrofecha2').val("");
                $('#txtfiltrotipo').val("");
                idmonedafiltro="";
                cargarordenes();
            });
            $('body').click(function(){
                $("#autocompletarmonedas").children().remove();  
            });
            
        });
        
        //se cargan las plataformas en el combo gral
        function cargarplataformas(id){
            $.get("<?=$url?>plataforma/cargarplataforma.php", function(data){
        		var response = jQuery.parseJSON(data);
        		for(var i = 0;i<response.id.length;i++){
        		    $("#selec-merc-gral").append("<option value='"+response.id[i]+"' data-color='"+response.color[i]+"'>"+response.nombre[i]+"</option>");       
        		}
        		tema();
        		cargarordenes();
	        });        
        }
        
        
    
        function tema() {
            var color = $("#selec-merc-gral").find(':selected').attr('data-color');
            $(".navbar-default").css("background-color", color);
            $("body").css('background-color', convertHex(color));
            $(".gmenu").css('background-color', convertHex(color));
            $(".panel-heading").css('background-color', convertHex(color));
            $(".panel-primary").css('border-color', color);
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
        
        function cargarordenes(e){
            var plataforma= $("#selec-merc-gral").val();
            //mantener filtro de moneda o tomar uno nuevo
            if(e!=null)
            {
                if($(e).data("idgp")==null){
                    var moneda = $(e).data("id");
                    $("#txtfiltromoneda").val($(e).data("nombre"));
                    idmonedafiltro = moneda;    
                }
                else{
                    var idordenes = $(e).data("idgp");
                    $('#txtfiltrotipo').val("");
                    clearTimeout(idsi)
                }
            }
            else{
                moneda = idmonedafiltro;
            }
            var tipo = $("#txtfiltrotipo").val();
            var fecha1 = $("#txtfiltrofecha1").val();
            var fecha2 = $("#txtfiltrofecha2").val();
            $.get("cargarorden.php",{plataforma:plataforma,tipo:tipo,fecha1:fecha1,fecha2:fecha2,moneda:moneda,idordenes:idordenes},function(data){  
                var response = jQuery.parseJSON(data);
                if(response.status=="1"){
                    cadena="";
                    for(var i=0;i<response.id.length;i++){
                        cadena+='<tr><td data-title="#">'+(i+1)+'</td>';
                        cadena+='<td data-title="Mercado">'+response.mercado[i]+'</td>';
        				cadena+='<td data-title="Tipo" class="numeric">'+response.tipo[i]+'</td>';
        				cadena+='<td data-title="Cantidad" class="numeric">'+response.cantidad[i]+'</td>';
        				cadena+='<td data-title="Precio" class="numeric">'+response.precio[i];
        				if(response.diferencia[i]>0){
        				    if(idordenes==null){
        				        cadena+='<span data-idgp="'+response.id[i]+','+response.relacion[i]+'" onclick="cargarordenes(this)" class="spn-diferencia" style="color:#008E29"> ('+response.diferencia[i]+' %) <i class="fa fa-eye" style="font-size:10pt"></i></span>';    
        				    }
        				    else{
        				        cadena+='<span data-idgp="'+response.id[i]+','+response.relacion[i]+'" onclick="cargarordenes()" class="spn-diferencia" style="color:#008E29"> ('+response.diferencia[i]+' %) <i class="fa fa-eye-slash" style="font-size:10pt"></i></span>';    
        				    }
        				        
        				}
        				else
        				if(response.diferencia[i]<0){
        				    if(idordenes==null){
        				        cadena+='<span data-idgp="'+response.id[i]+','+response.relacion[i]+'" onclick="cargarordenes(this)" class="spn-diferencia" style="color:#FF0000"> ('+Math.abs(response.diferencia[i])+' %) <i class="fa fa-eye" style="font-size:10pt"></i></span>';    
        				    }
        				    else{
        				        cadena+='<span data-idgp="'+response.id[i]+','+response.relacion[i]+'" onclick="cargarordenes()" class="spn-diferencia" style="color:#FF0000"> ('+Math.abs(response.diferencia[i])+' %) <i class="fa fa-eye-slash" style="font-size:10pt"></i></span>';   
        				    }
        				}
        				cadena+='</td>';
        				cadena+='<td data-title="Fecha" class="numeric">'+response.fecha[i]+'</td>';
        				cadena+='<td data-title="⚙" class="numeric"><a onclick="resumen(this)" data-id="'+response.id[i]+'" class="btn btn-default" title="Ver detalle" style="padding: 3px 8px;"><em class="fa fa-eye"></em></a></td></tr>';
        			   
                    }
                    $("#tblordenes").children().remove();
                    $("#tblordenes").append(cadena);
                    if(idordenes==null){
                        idsi = setTimeout(function(){ cargarordenes(); }, 5000);  
                    }
                    $("#div-loader").css("display","none");
                    $("#wrapper").css("display","unset");
                }
                else{
                    $("#tblordenes").children().remove();
                    $("#tblordenes").append("<td colspan='6'><h4 class='text-center'>No hay órdenes para mostrar<h4></td>");
                    if(idordenes==null){
                        idsi = setTimeout(function(){ cargarordenes(); }, 5000);  
                    }
                    $("#div-loader").css("display","none");
                    $("#wrapper").css("display","unset");
                }
            });
        }
        
        function autocompletar(){
            var plataforma = $("#selec-merc-gral").val();
            var texto = $("#txtfiltromoneda").val().trim();
            if(texto!=""){
                $.get("autocompletar.php",{plataforma:plataforma,texto:texto},function(data){  
                    var response = jQuery.parseJSON(data);
                    if(response.status=="1"){
                        cadena="";
                        for(var i=0;i<response.id.length;i++){
                            cadena+="<div onclick='cargarordenes(this)' data-id='"+response.id[i]+"' data-nombre='"+response.nombre[i]+"'><img src='"+response.icono[i]+"' width=25 height=25>"+response.nombre[i]+"  ("+response.simbolo[i]+")</div>";    
                        }
                        $("#autocompletarmonedas").children().remove();
                        $("#autocompletarmonedas").append(cadena);
                    }
                    else{
                        $("#autocompletarmonedas").children().remove();
                    }
                });
            }
            else{
                $("#autocompletarmonedas").children().remove();    
            }
        }
        
        function resumen(e){
            var plataforma = $("#selec-merc-gral").val();
            var id = $(e).data("id");
            $.get("resumen.php",{plataforma:plataforma,idorden:id},function(data){
                var response = jQuery.parseJSON(data);
                if(response.status==1){     
                    cadena="";
                    cadena+='<div style="width: auto;display: inline-block;margin-right:20px;border: 1px solid #ccc!important;padding:3px;border-radius:10px;margin-bottom:10px;min-width:40%">';
                    cadena+='<label>Moneda adquirida </label><br>';
                    cadena+='<img src="'+response.iconomonedacompra[0]+'" width="20" height="20"> '+response.monedacompra[0]+' ('+response.simbolomonedacompra[0]+')</div>';
                    
                    cadena+='<div style="width: auto;display: inline-block;border: 1px solid #ccc!important;padding:3px;border-radius:10px;margin-bottom:10px;min-width:40%">';
                    cadena+='<label>Moneda base </label><br>';
                    cadena+='<img src="'+response.iconomonedaventa[0]+'" width="20" height="20"> '+response.monedaventa[0]+' ('+response.simbolomonedaventa[0]+')</div>';
                    
                    cadena+='<div id="no-more-tables"><table class="table" style="margin-bottom:0px"><thead><tr><th>#</th><th>Cantidad</th><th>Precio</th><th>Total</th><th>Comisión</th></tr></thead><tbody>';
                    for(var i=0;i<response.tipo.length;i++){
                        cadena+='<tr><td data-title="#">'+(i+1)+'</td>';
                        cadena+='<td data-title="Cantidad">'+response.cantidad[i]+'</td>';   
                        cadena+='<td data-title="Precio">'+response.preciounidad[i]+'</td>';   
                        cadena+='<td data-title="Total">'+response.precio[i]+'</td>';
                        cadena+='<td data-title="Comisión">'+response.comision[i]+'</td></tr>';   
                    }
                    cadena+="</tbody></table><div>";
                    $("#cuerporesumen").children().remove();
                    $("#cuerporesumen").append(cadena);
                    cadena="";
                    cadena+='<div style="float:left;width:75%;text-align:left">';
                    
                    cadena+='<div style="with:150px;display:block">';
                    cadena+='<div style="width: 120px;display:inline;padding-right:30px"><b>Total Cantidad</b></div>';
                    cadena+='<div style="width: 120px;display:inline">'+response.totalcantidad+'</div>';
                    cadena+='</div>';
                    
                    cadena+='<div style="with:150px;display:block">';
                    cadena+='<div style="width: 120px;display:inline;padding-right:50px"><b>Total Precio</b></div>';
                    cadena+='<div style="width: 120px;display:inline">'+response.totalprecio+'</div>';
                    cadena+='</div>';
                    
                    cadena+='<div style="with:150px;display:block">';
                    cadena+='<div style="width: 120px;display:inline;padding-right:29px"><b>Total Comisión</b></div>';
                    cadena+='<div style="width: 120px;display:inline">'+response.totalcomision+'</div>';
                    cadena+='</div>';
                    
                    cadena+='</div>';
                    cadena+='<button style="float:right;margin-top:10px" type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>';
                    $("#pieresumen").children().remove();
                    $("#pieresumen").append(cadena);
                    $("#modalresumen").modal('show');     
                }
                else{
                    alertify.error("Error al cargar el resumen de la orden");    
                }
            });
        }
        
        
    </script>

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
        <!--//tabla-->
        <div class="container" style="width:100%">
        <div class="panel panel-primary filterable">
            <div class="panel-heading">
                <h3 class="panel-title">Órdenes cerradas</h3>
                <div class="pull-right">
                    <button class="btn btn-default btn-xs btn-filter"><span class="glyphicon glyphicon-filter"></span> Filtrar</button>
                </div>
            </div>
            
            <div class="filters" style="display:none">
                    <div class="col-md-3 form-group">
                        <label for="txtfiltromoneda">Moneda</label>
                        <input type="text" class="form-control" id="txtfiltromoneda" onkeyup="idmonedafiltro='';autocompletar();cargarordenes()">
                        <div id="autocompletarmonedas" class="autocomplete-items">
                        </div>
                    </div>
                    
                    <div class="col-md-3 form-group">
                        <label for="txtfiltrofecha1">Fecha Inicial</label>
                        <input type="text" class="form-control" id="txtfiltrofecha1" readonly="" onchange="cargarordenes()">    
                    </div>
                    
                    <div class="col-md-3 form-group">
                        <label for="txtfiltrofecha2">Fecha Final</label>
                        <input type="text" class="form-control" id="txtfiltrofecha2" readonly="" onchange="cargarordenes()">
                    </div>
                    
                    <div class="col-md-3 form-group">
                        <label for="txtfiltrotipo">Tipo</label>
                        <select type="text" class="form-control" id="txtfiltrotipo" onchange="cargarordenes()">
                            <option value="">Todo</option>
                            <option value="Compra">Compra</option>
                            <option value="Venta">Venta</option>
                        </select>
                    </div>
                </div>
                
            <div id="no-more-tables">
            <table class="table" style="margin-bottom:0px">
                <thead>
        			<tr>
        				<th>#</th>
        				<th>Mercado</th>
        				<th>Tipo</th>
        				<th>Cantidad</th>
        				<th>Precio</th>
        				<th>Fecha</th>
        				<th><i class="fa fa-cog"></i></th>
        			</tr>
        		</thead>
        		<tbody id="tblordenes">
        			<!--<tr>-->
        			<!--	<td data-title="#">AAC</td>-->
        			<!--	<td data-title="Mercado">ETC-ETH</td>-->
        			<!--	<td data-title="Tipo" class="numeric">$1.38</td>-->
        			<!--	<td data-title="Cantidad" class="numeric">-0.01</td>-->
        			<!--	<td data-title="Precio" class="numeric">-0.36%</td>-->
        			<!--	<td data-title="Fecha" class="numeric">-0.36%</td>-->
        			<!--	<td data-title="⚙" class="numeric">$1.39</td>-->
        			<!--</tr>-->
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
        
        
        <!-- Modal resumen-->
        <div class="modal fade" id="modalresumen" role="dialog">
            <div class="modal-dialog">    
          <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Resumen</h4>
                    </div>
                <div class="modal-body" id="cuerporesumen">
                    
                     
                    
                    <div style="width: auto;display: inline-block;">
                        <label>Moneda adquirida </label><br>
                        <img src="../logos/btc.png" width="20" height="20"> Bitcoin (BTC)
                    </div>
                    
                </div>
                <div class="modal-footer" id="pieresumen">
                  
                </div>
              </div>      
            </div>
        </div>
    </div>
        <!--//resumen-->
    <div id="div-loader">
        <img src="<?=$url?>/img/loading.gif">
    </div>    
    
    <script type="text/javascript" src="<?=$url?>js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
    <script type="text/javascript" src="<?=$url?>js/bootstrap-datetimepicker.es.js" charset="UTF-8"></script>
    <script type="text/javascript">
    	$("#txtfiltrofecha1").datetimepicker({
    	    format: "yyyy-mm-dd",
    	    weekStart: 1,
            todayBtn:  1,
    		autoclose: 1,
    		todayHighlight: 1,
    		startView: 2,
    		minView: 2,
    		forceParse: 0,
    		endDate:new Date(),
    	    language: "es"
    	});
    	
    	$("#txtfiltrofecha2").datetimepicker({
    	    format: "yyyy-mm-dd",
    	    weekStart: 1,
            todayBtn:  1,
    		autoclose: 1,
    		todayHighlight: 1,
    		startView: 2,
    		minView: 2,
    		forceParse: 0,
    		endDate:new Date(),
    	    language: "es"
    	});
    </script>
    
    
    </body>
</html>