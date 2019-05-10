<?php
    include("control/variables.php");
    session_start();
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel</title>
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
    //variables de control de recarga del order book
    var idrecargaorderbuy;
    var tipocargaroderbuy;
    var strmercorderbuy;
    //variable para evitar que el order buy modifique un precio que el usuario ya haya cambiado
    var strprecioestatico;
    //id de la orden a modificar
    var idordenglobal;
    //porcentaje global del profit
    var profitglobal;
    //idsettime del modificar orden
    var idsettimeglobal;
    
        $(document).ready(function(){
            $("#div-contenedor").slideUp(0);
            //carga de menú
            $.get("<?=$url?>control/cargarmenu.php", function(data){
                var response = jQuery.parseJSON(data);
                for(var i = 0;i<response.id.length;i++){
                    $("#limenu").append('<a href="'+response.ruta[i]+'"><i class="'+response.icono[i]+'"></i> '+response.nombre[i]+'</a>');       
                }
                $("#limenu").append('<a href="<?=$url?>control/logout.php"><i class="glyphicon glyphicon-log-out"></i> Cerrar sesión</a>');             
            })
        });
        
        // function seguirorden(){
        //     $.get("seguirordenesabiertas.php", function(data){
              
        //     });    
        // }
        
        //funcion para poner ajustar campos a dar click en espacio del order book
        function preciocelda(e){
            var precio = parseFloat($(e).text());
            var mercado = $(e).closest('table').data("mercado");
            var tipo = $(e).closest('table').data("tipo");
            strprecioestatico = mercado;
            if(tipo==1){
                $("#txtprecioc"+mercado).val(precio);
                $("#txtcantidadc"+mercado).val(parseFloat($("#txttotalc"+mercado).val()/$("#txtprecioc"+mercado).val()).toFixed(8));
                $("#txtcomisionc"+mercado).text(parseFloat($("#txttotalc"+mercado).val()*$("#comisionmaker").val()).toFixed(8));    
                $("#txtpreciogananciac"+mercado).val(parseFloat((precio)+(parseFloat(precio)*(parseFloat($("#txtgananciac"+mercado).val()/100).toFixed(4)))).toFixed(8));
                $("#txtprecioperdidac"+mercado).val(parseFloat((precio)+(parseFloat(precio)*(parseFloat($("#txtperdidac"+mercado).val()/100).toFixed(4)))).toFixed(8));
            }
            else{
                $("#txtpreciov"+mercado).val(precio);
                $("#txtcantidadv"+mercado).val(parseFloat($("#txttotalv"+mercado).val()*$("#txtpreciov"+mercado).val()).toFixed(8));
                $("#txtcomisionv"+mercado).text(parseFloat($("#txtcantidadv"+mercado).val()*$("#comisionmaker").val()).toFixed(8));   
            }
        }
        
        //se cargan las plataformas en el combo gral
        function cargarplataformas(id){
            $.get("<?=$url?>plataforma/cargarplataforma.php", function(data){
                var response = jQuery.parseJSON(data);
                for(var i = 0;i<response.id.length;i++){
                    $("#selec-merc-gral").append("<option value='"+response.id[i]+"' data-color='"+response.color[i]+"'>"+response.nombre[i]+"</option>");       
                }
                tema();
                cargarmonedas();
                cargarmonedasventa();
                cargarordenes();
            });        
        }
        
        
        //carga todas las monedas segun la plataforma seleccionada
        function cargarmonedas(){           
            var plataforma = $("#selec-merc-gral").val();
            var txt = $("#filtro-moneda").val();           
            $.get("moneda/cargarmoneda.php",{plataforma:plataforma,textobusqueda:txt,tipo:1},function(data){
                var response = jQuery.parseJSON(data);
                $(".pagmonedaplataforma").remove();
                if(response.status==1){
                    var cadena= "";
                    for(var i = 0;i<response.id.length;i++){
                        if(response.precios[i]!=""){
                            cadena+='<a class="list-group-item focus-compra"><div class="divnombremoneda" display:inline-block"><img src="'+response.icono[i]+'" width="20" height="20"/>'+response.nombre[i]+" ("+response.simbolo[i]+")"+'</div>'+response.precios[i]+'<span class="text-muted small"><button onclick="cargarmercados(this);" data-idm="'+response.id[i]+'" class="btn sample btn-xs btn-sample btncomprarmodal" data-toggle="modal" >Comprar</button></span></a>';
                        }
                        $("#listadomonedas").children().remove();
                        $("#listadomonedas").append(cadena);
                    }
                    
                    var nitems = response.id.length;
                    if(nitems>10){
                        if(nitems>=30){
                            nitems = 3;       
                        }
                        else{
                            nitems = Math.ceil(nitems/10);
                        }
                        $('#listadomonedas').paginathing({
                            perPage: 10,
                            limitPagination: parseInt(nitems),
                            containerClass: 'pagmonedaplataforma'                        
                         })   
                    } 
                    
                }
                else{
                    $("#listadomonedas").children('a').remove();
                    $("#listadomonedas").append('<a class="list-group-item focus-compra text-center">No hay registros que cumplan el criterio</a>');    
                }
            }); 
        }
        
        //cargar las monedas compradas por el usuario
        function cargarmonedasventa(){
            var plataforma = $("#selec-merc-gral").val();
            var txt = $("#filtro-moneda-venta").val();
            $.get("moneda/cargarmoneda.php",{plataforma:plataforma,textobusqueda:txt,tipo:2},function(data){
                var response = jQuery.parseJSON(data);
                $(".pagmonedaplataformav").remove();
                if(response.status==1){
                    var cadena= "";
                    for(var i = 0;i<response.id.length;i++){
                        if(response.precios[i]!=""){
                            cadena+='<a class="list-group-item focus-compra"><div class="divnombremoneda" display:inline-block"><img src="'+response.icono[i]+'" width="20" height="20"/>'+response.nombre[i]+" ("+response.simbolo[i]+")"+'</div>'+response.precios[i]+'<span class="text-muted small"><button onclick="cargarmercadosventa(this);" data-idm="'+response.id[i]+'" class="btn sample btn-xs btn-sample btncomprarmodal" data-toggle="modal" >Vender</button></span></a>';
                        }
                        $("#listadomonedasventa").children().remove();
                        $("#listadomonedasventa").append(cadena);                       
                    }
                    
                    var nitems = response.id.length;
                    if(nitems>10){
                        if(nitems>=30){
                            nitems = 3;       
                        }
                        else{
                            nitems = Math.ceil(nitems/10);
                        }
                        $('#listadomonedasventa').paginathing({
                            perPage: 10,
                            limitPagination: parseInt(nitems),
                            containerClass: 'pagmonedaplataformav'                        
                         })   
                    }                   
                }
                else{
                    $("#listadomonedasventa").children('a').remove();
                    $("#listadomonedasventa").append('<a class="list-group-item focus-venta text-center">No hay registros que cumplan el criterio</a>');    
                }
            }); 
        }
    
        // //cargar los saldos de los mercados
        // function cargarsaldo(){
        //     var plataforma = $("#selec-merc-gral").val();
        //     $.get("<?=$url?>saldo/cargarsaldo.php",{plataforma:plataforma},function(data){
        //      var response = jQuery.parseJSON(data);
        //      $("#itemscarousel").children().remove();
        //      if(response.status==1){
        //          for(var i = 0;i<response.id.length;i++){
        //              $("#itemscarousel").append('<div class="item"><div style="width: initial;" class="col-md-3 col-sm-6 col-xs-12"><div style="width: 200px;margin-top: 10px" class="panel panel-default"><div class="panel-heading" style="background-color:'+response.color[i]+';padding:5px 0px 5px 0px"><img src="<?php $url?>'+response.icono[i]+'"  height="30px"/>'+response.nombre[i]+" ("+response.simbolo[i]+")"+'</div><div class="panel-body">Saldo: '+response.cantidad[i]+'</div></div></div></div></div>');       
        //          }
        //          $("#itemscarousel").children(":first").addClass("active");
        //          slider();
        //          if(response.id.length>4){
        //              $(".carousel-control").slideToggle(0);  
        //          }
        //      }
           // });
        // }
        
    
        function tema() {
            var color = $("#selec-merc-gral").find(':selected').attr('data-color');
            $(".navbar-default").css("background-color", color);
            $("body").css('background-color', convertHex(color));
            $(".gmenu").css('background-color', convertHex(color));
            $("#heading-panel-ordenes").css('background-color', convertHex(color));
            $("#panel-ordenes").css('border-color', color);
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
        
        
        
        
        //cambiar sliders al escribir en inputs del form de modificarorden
        function cambiarslider(e){
            if($(e).val()!=""){
                var precio = parseFloat($("#txtpreciom").val());
                if($(e).data("tipo")=="sg"){
                    var max = $("#slidergananciam").attr('max');
                    var min = $("#slidergananciam").attr('min');
                    if(parseFloat($(e).val())<=parseFloat(max)&&parseFloat($(e).val())>=parseFloat(min)){
                        $("#slidergananciam").val($(e).val().replace(/^\.+|\.+$/g,""));
                        $("#txtpreciogananciam").val(parseFloat(precio+(precio*($(e).val()/100).toFixed(4))).toFixed(8));
                    }
                    else
                    if(parseFloat($(e).val())>parseFloat(max)){
                        $("#slidergananciam").val(max); 
                        $(e).val(max);
                        $("#txtpreciogananciam").val(parseFloat(precio+(precio*(max/100).toFixed(4))).toFixed(8));
                    }
                    else
                    if(parseFloat($(e).val())<parseFloat(min)){
                        $("#slidergananciam").val(min);
                    }
                }
                else{
                    var max = $("#sliderperdidam").attr('max');
                    var min = $("#sliderperdidam").attr('min');
                    if(parseFloat($(e).val())>=parseFloat(min)&&parseFloat($(e).val())<=parseFloat(max)){
                        $("#sliderperdidam").val($(e).val().replace(/^\.+|\.+$/g,""));
                        $("#txtprecioperdidam").val(parseFloat(precio+(precio*($(e).val()/100).toFixed(4))).toFixed(8));
                    }
                    else
                    if(parseFloat($(e).val())<parseFloat(min)){
                        $("#sliderperdidam").val(min); 
                    } 
                    else
                    if(parseFloat($(e).val())>parseFloat(max)){
                        $("#sliderperdidam").val(max);
                        $("#txtperdidam").val(max);
                    } 
                }
            }
        }
        
        
        
        
        //cambiar valores al mover barslider
        function cambiarvalores(e){
            var tipo = $(e).data("type");
            var mercado = $(e).data("mercado");
            strprecioestatico = mercado;
            var ve = $(e).val().replace(/^\.+|\.+$/g,"");
            if(tipo=="c"){
                $("#txtcantidad"+tipo+mercado).val(parseFloat((ve/$("#txtprecio"+tipo+mercado).val())).toFixed(8).replace(/\.?0+$/,""));    
            }
            else{
                $("#txtcantidad"+tipo+mercado).val(parseFloat((ve*$("#txtprecio"+tipo+mercado).val())).toFixed(8).replace(/\.?0+$/,""));    
            }
            $("#txttotal"+tipo+mercado).val(Number(ve).toFixed(8).replace(/\.?0+$/,""));
            if(tipo=="c"){
                $("#txtcomision"+$(e).data("type")+$(e).data("mercado")).text(parseFloat($("#txttotal"+$(e).data("type")+$(e).data("mercado")).val()*$("#comisionmaker").val()).toFixed(8).replace(/^\.+|\.+$/g,""));    
            }
            else{
                $("#txtcomision"+$(e).data("type")+$(e).data("mercado")).text(parseFloat($("#txtcantidad"+$(e).data("type")+$(e).data("mercado")).val()*$("#comisionmaker").val()).toFixed(8).replace(/^\.+|\.+$/g,""));    
            }
        }
        
        function cambiarinputs(e){
            var tipo = $(e).data("type");
            strprecioestatico = $(e).data("mercado");
            var aux = $(e).attr("id").replace($(e).data("type")+$(e).data("mercado"), "");
            if(aux=="txttotal"){
                if(parseFloat($(e).val().trim()).toFixed(8).replace(/^\.+|\.+$/g,"")!=""||parseFloat($(e).val().trim()).toFixed(8).replace(/^\.+|\.+$/g,"")>0){
                    var vtotal = $(e).val().trim().replace(/^\.+|\.+$/g,"");
                    var precio = $("#txtprecio"+$(e).data("type")+$(e).data("mercado")).val().trim().replace(/^\.+|\.+$/g,"");
                    if(parseFloat(vtotal)<parseFloat($("#txtsaldo"+$(e).data("type")+$(e).data("mercado")).val())){
                        $("#slidertotal"+$(e).data("type")+$(e).data("mercado")).val(vtotal);
                        if(tipo=="c"){
                            $("#txtcantidad"+$(e).data("type")+$(e).data("mercado")).val(parseFloat(vtotal/precio).toFixed(8));
                        }
                        else{
                            $("#txtcantidad"+$(e).data("type")+$(e).data("mercado")).val(parseFloat(vtotal*precio).toFixed(8));    
                        }
                    }
                    else{
                        var saldo = $("#txtsaldo"+$(e).data("type")+$(e).data("mercado")).val().trim().replace(/^\.+|\.+$/g,"");
                        var precio = $("#txtprecio"+$(e).data("type")+$(e).data("mercado")).val().trim().replace(/^\.+|\.+$/g,"");
                        $("#txttotal"+$(e).data("type")+$(e).data("mercado")).val(parseFloat(saldo).toFixed(8).replace(/^\.+|\.+$/g,""));
                        $("#slidertotal"+$(e).data("type")+$(e).data("mercado")).val(parseFloat(saldo).toFixed(8).replace(/^\.+|\.+$/g,""));
                        if(tipo=="c"){
                            $("#txtcantidad"+$(e).data("type")+$(e).data("mercado")).val(parseFloat(saldo/precio).toFixed(8).replace(/^\.+|\.+$/g,""));    
                        }
                        else{
                            $("#txtcantidad"+$(e).data("type")+$(e).data("mercado")).val(parseFloat(saldo*precio).toFixed(8).replace(/^\.+|\.+$/g,""));    
                        }
                    }
                    if(tipo=="c"){
                        $("#txtcomision"+$(e).data("type")+$(e).data("mercado")).text(parseFloat($("#txttotal"+$(e).data("type")+$(e).data("mercado")).val()*$("#comisionmaker").val()).toFixed(8).replace(/^\.+|\.+$/g,""));    
                    }else{
                        $("#txtcomision"+$(e).data("type")+$(e).data("mercado")).text(parseFloat($("#txtcantidad"+$(e).data("type")+$(e).data("mercado")).val()*$("#comisionmaker").val()).toFixed(8).replace(/^\.+|\.+$/g,""));    
                    }
                    
                }
            }
            else
            if(aux=="txtprecio"){
                var precio = parseFloat($(e).val().trim()).toFixed(8).replace(/^\.+|\.+$/g,"");
                var mercado = $(e).data("mercado");
                if(precio!=""&&precio>0){
                    var saldo = $("#txtsaldo"+$(e).data("type")+$(e).data("mercado")).val().trim().replace(/^\.+|\.+$/g,"");
                    if(tipo=="c"){
                        $("#txtcantidad"+$(e).data("type")+$(e).data("mercado")).val(parseFloat($("#txttotal"+$(e).data("type")+$(e).data("mercado")).val()/precio).toFixed(8).replace(/^\.+|\.+$/g,""));    
                        $("#txtprecioganancia"+$(e).data("type")+$(e).data("mercado")).val(parseFloat(parseFloat(precio)+parseFloat(precio*($("#txtganancia"+tipo+mercado).val()/100))).toFixed(8).replace(/^\.+|\.+$/g,""));
                        $("#txtprecioperdida"+$(e).data("type")+$(e).data("mercado")).val(parseFloat(parseFloat(precio)-parseFloat(precio*($("#txtperdida"+tipo+mercado).val()/100))).toFixed(8).replace(/^\.+|\.+$/g,""));
                    }
                    else{
                        $("#txtcantidad"+$(e).data("type")+$(e).data("mercado")).val(parseFloat($("#txttotal"+$(e).data("type")+$(e).data("mercado")).val()*precio).toFixed(8).replace(/^\.+|\.+$/g,""));   
                    }
                    //$("#txttotal"+$(e).data("type")+$(e).data("mercado")).val(parseFloat($("#slidertotal"+$(e).data("type")+$(e).data("mercado")).val()).toFixed(8).replace(/^\.+|\.+$/g,""));
                   
                  if(tipo=="c"){
                        $("#txtcomision"+$(e).data("type")+$(e).data("mercado")).text(parseFloat($("#txttotal"+$(e).data("type")+$(e).data("mercado")).val()*$("#comisionmaker").val()).toFixed(8).replace(/^\.+|\.+$/g,""));    
                    }else{
                        $("#txtcomision"+$(e).data("type")+$(e).data("mercado")).text(parseFloat($("#txtcantidad"+$(e).data("type")+$(e).data("mercado")).val()*$("#comisionmaker").val()).toFixed(8).replace(/^\.+|\.+$/g,""));    
                    }
                }
            }
            else{
                var cantidad = parseFloat($(e).val().trim()).toFixed(8).replace(/^\.+|\.+$/g,"");
                if(cantidad!=""&&cantidad>0){
                    var saldo = $("#txtsaldo"+$(e).data("type")+$(e).data("mercado")).val().trim().replace(/^\.+|\.+$/g,"");
                    var precio = $("#txtprecio"+$(e).data("type")+$(e).data("mercado")).val().trim().replace(/^\.+|\.+$/g,"");
                    if(tipo=="c"){                  
                        if(cantidad<saldo/precio){
                            $("#txttotal"+$(e).data("type")+$(e).data("mercado")).val(parseFloat(cantidad*precio).toFixed(8).replace(/^\.+|\.+$/g,""));        
                        }
                        else{
                            $("#txttotal"+$(e).data("type")+$(e).data("mercado")).val(saldo);
                            $("#slidertotal"+$(e).data("type")+$(e).data("mercado")).val(saldo);
                            $("#txtcantidad"+$(e).data("type")+$(e).data("mercado")).val(parseFloat(saldo/precio).toFixed(8).replace(/^\.+|\.+$/g,""));                               
                        }   
                    }
                    else{                       
                        if(cantidad<saldo*precio){
                            $("#txttotal"+$(e).data("type")+$(e).data("mercado")).val(parseFloat(cantidad*precio).toFixed(8).replace(/^\.+|\.+$/g,""));
                            
                        }
                        else{
                            $("#txttotal"+$(e).data("type")+$(e).data("mercado")).val(saldo);
                            $("#slidertotal"+$(e).data("type")+$(e).data("mercado")).val(saldo);
                            $("#txtcantidad"+$(e).data("type")+$(e).data("mercado")).val(parseFloat($("#txttotal"+$(e).data("type")+$(e).data("mercado")).val()*precio).toFixed(8).replace(/^\.+|\.+$/g,""));   
                            
                        }
                    }
                    
                    if(tipo=="c"){
                        $("#txtcomision"+$(e).data("type")+$(e).data("mercado")).text(parseFloat($("#txttotal"+$(e).data("type")+$(e).data("mercado")).val()*$("#comisionmaker").val()).toFixed(8).replace(/^\.+|\.+$/g,""));    
                    }else{
                        $("#txtcomision"+$(e).data("type")+$(e).data("mercado")).text(parseFloat($("#txtcantidad"+$(e).data("type")+$(e).data("mercado")).val()*$("#comisionmaker").val()).toFixed(8).replace(/^\.+|\.+$/g,""));    
                    }                   
                }
            }
        }
        
        //carga los mercados disponibles para comprar la moneda seleccionada
        function cargarmercados(e){
            strprecioestatico="";
            var moneda=$(e).data("idm");
            var plataforma = $("#selec-merc-gral").val();
            $.get("operacion/cargarmercado.php",{plataforma:plataforma,moneda:moneda,accion:1},function(data){
                var response = jQuery.parseJSON(data);
                $("#pest-mercv").children().remove();
                $("#cuer-mercv").children().remove();
                $("#pest-merc").children().remove();
                $("#cuer-merc").children().remove();
                var cadena="";
                var cadmercados="";
                if(response.status==1){
                    for(var i = 0;i<response.mercado.length;i++){
                        if(parseFloat(response.saldo[i])>0){
                            cadena="";
                            $("#pest-merc").append('<li><a style="color:black" data-toggle="tab" href="#'+response.moneda[i]+'"><img src='+response.icono[i]+' width=20 height=20/>'+response.moneda[i]+'</a></li>');   
                            $("#cuer-merc").append('<div id="'+response.moneda[i]+'" class="tab-pane fade"></div>');
                            cadena+='<div class="divinfmerc">\
                                        <div class="form-group" style="margin-top:8px;margin-bottom:8px;">\
                                            <label class="lblinfmerc" for="name" class="control-label">Saldo</label>'+parseFloat(response.saldo[i]).toFixed(8)+' '+response.monedaventa[i]+'\
                                        </div>\
                                        <div class="form-group" style="margin-top:8px;margin-bottom:8px;">\
                                            <label class="lblinfmerc" for="name"  class="control-label">Precio</label>\
                                            <div class="input-group input-group-sm">\
                                                <input class="form-control" oninput="solodecimal(this);cambiarinputs(this)" type="text" data-mercado="'+response.mercado[i]+'" data-type="c" id="txtprecioc'+response.mercado[i]+'" autocomplete="off"><span class="input-group-addon" id="sizing-addon3">'+response.monedaventa[i]+'</span>\
                                            </div>\
                                        </div>\
                                        <div class="form-group" style="margin-top:8px;margin-bottom:8px;">\
                                            <label class="lblinfmerc" for="name" class="control-label">Cantidad</label>\
                                            <div class="input-group input-group-sm">\
                                                <input class="form-control" data-type="c" data-mercado="'+response.mercado[i]+'" oninput="solodecimal(this);cambiarinputs(this)" type="text" id="txtcantidadc'+response.mercado[i]+'" autocomplete="off"><span class="input-group-addon" id="sizing-addon3">'+response.monedacompra[i]+'</span>\
                                            </div>\
                                        </div>\
                                        <div class="form-group" style="margin-top:5px;">\
                                            <div class="input-group input-group-sm">\
                                                <label class="lblinfmerc" for="name" class="control-label">Comisión</label><p style="display:inline;margin-left:5px" id="txtcomisionc'+response.mercado[i]+'"></p> '+response.monedaventa[i]+'\
                                            </div>\
                                        </div>\
                                        <div class="form-group">\
                                            <label class="lblinfmerc" for="name" class="control-label">Total</label>\
                                            <input class="inpinfmerc" step="0.00000001" min="0" value="0" max="'+response.saldo[i]+'" type="range" data-type="c" data-mercado="'+response.mercado[i]+'" id="slidertotalc'+response.mercado[i]+'" class="form-control" oninput="cambiarvalores(this)" style="padding:0">\
                                            <div class="input-group input-group-sm">\
                                                <input type="text" data-type="c" data-mercado="'+response.mercado[i]+'" oninput="solodecimal(this);cambiarinputs(this)"  class="form-control" id="txttotalc'+response.mercado[i]+'"><span class="input-group-addon" id="sizing-addon3">'+response.monedaventa[i]+'</span>\
                                            </div>\
                                        </div>';
                            //opciones ganancia perdida
                            if(response.tipousuario==1){
                                cadena+='<label for="op-av-compra'+response.mercado[i]+'" style="width: 90%; height: 15px;margin-bottom:10px;cursor:pointer">\
                                            <p><span class="glyphicon glyphicon-cog"></span>Opciones avanzadas\
                                            <span class="glyphicon glyphicon-chevron-down">\
                                                <input name="op-av-compra'+response.mercado[i]+'" id="op-av-compra'+response.mercado[i]+'" type="checkbox" onclick="desplegaravanzadas(this)" data-type="c" data-mercado="'+response.mercado[i]+'" data-tipousuario="'+response.tipousuario+'">\
                                            </span></p>\
                                        </label>\
                                        <div style="display:none" id="divslidergpc'+response.mercado[i]+'">\
                                            <label class="lblinfmerc">Ganancia</label>\
                                            <input class="inpinfmerc" step="0.01" min="0.5" value="'+response.ganancia+'" max="'+response.ganancia+'" type="range" data-type="c" data-mercado="'+response.mercado[i]+'" id="slidergananciac'+response.mercado[i]+'" class="form-control" oninput="cambiarporcentajegp(this);" style="padding:0px">\
                                            <input type="text" class="form-control" data-type="c" data-mercado="'+response.mercado[i]+'" data-tipousuario="'+response.tipousuario+'" data-ganancia="'+response.ganancia+'" id="txtgananciac'+response.mercado[i]+'" oninput="solodecimal(this);cambiarinputsgp(this)" value="'+response.ganancia+'" autocomplete="off">\
                                            <input type="text" class="inp-prec-gp" data-type="c" data-mercado="'+response.mercado[i]+'" id="txtpreciogananciac'+response.mercado[i]+'" oninput="solodecimal(this);cambiarpreciogp(this)">\
                                            <label class="lblinfmerc">Perdida</label>\
                                            <input class="inpinfmerc" step="0.01" min="'+response.perdida+'" value="'+response.perdida+'" max="-0.5" type="range" data-type="c" data-mercado="'+response.mercado[i]+'" id="sliderperdidac'+response.mercado[i]+'" class="form-control" oninput="cambiarporcentajegp(this);" style="padding:0px">\
                                            <input type="text" class="form-control" data-type="c" data-mercado="'+response.mercado[i]+'" data-tipousuario="'+response.tipousuario+'" data-perdida="'+response.perdida+'" id="txtperdidac'+response.mercado[i]+'" oninput="solodecimalnegativo(this);cambiarinputsgp(this)"  value="'+response.perdida+'" autocomplete="off">\
                                            <input type="text" class="inp-prec-gp" data-type="c" data-mercado="'+response.mercado[i]+'" id="txtprecioperdidac'+response.mercado[i]+'" oninput="solodecimal(this);cambiarpreciogp(this)">\
                                        </div>';
                            }
                            else{
                                cadena+='<div class="form-group" id="divslidergananciac'+response.mercado[i]+'">\
                                            <label class="lblinfmerc">Ganancia</label>\
                                            <input class="inpinfmerc" step="0.01" min="0.5" value="'+response.ganancia+'" max="'+response.ganancia+'" type="range" data-type="c" data-mercado="'+response.mercado[i]+'" id="slidergananciac'+response.mercado[i]+'" class="form-control" oninput="cambiarporcentajegp(this);" style="padding:0px">\
                                            <input type="text" class="form-control" data-type="c" data-mercado="'+response.mercado[i]+'" data-tipousuario="'+response.tipousuario+'" data-ganancia="'+response.ganancia+'" id="txtgananciac'+response.mercado[i]+'" oninput="solodecimal(this);cambiarinputsgp(this)" value="'+response.ganancia+'" autocomplete="off">\
                                            <input type="text" class="inp-prec-gp" data-type="c" data-mercado="'+response.mercado[i]+'" id="txtpreciogananciac'+response.mercado[i]+'" oninput="solodecimal(this);cambiarpreciogp(this)" autocomplete="off">\
                                        </div>\
                                        <div class="form-group" id="divsliderperdidac'+response.mercado[i]+'">\
                                            <label class="lblinfmerc">Perdida</label>\
                                            <input class="inpinfmerc" step="0.01" min="'+response.perdida+'" value="'+response.perdida+'" max="-0.5" type="range" data-type="c" data-mercado="'+response.mercado[i]+'" id="sliderperdidac'+response.mercado[i]+'" class="form-control" oninput="cambiarporcentajegp(this)" style="padding:0px">\
                                            <input type="text" class="form-control" data-type="c" data-mercado="'+response.mercado[i]+'" data-tipousuario="'+response.tipousuario+'" data-perdida="'+response.perdida+'" id="txtperdidac'+response.mercado[i]+'" oninput="solodecimalnegativo(this);cambiarinputsgp(this)" value="'+response.perdida+'" autocomplete="off">\
                                            <input type="text" class="inp-prec-gp" data-type="c" data-mercado="'+response.mercado[i]+'" id="txtprecioperdidac'+response.mercado[i]+'" oninput="solodecimal(this);cambiarpreciogp(this)" autocomplete="off">\
                                        </div>';   
                            }
                               //opciones avanzadas
                                cadena+='<input type="hidden" id="txtsaldoc'+response.mercado[i]+'" data-idmv="'+response.idmonedaventa[i]+'" data-idmc="'+response.idmonedacompra[i]+'" disabled value="'+response.saldo[i]+'">\
                                        <br><button data-mercado="'+response.mercado[i]+'" data-tipousuario="'+response.tipousuario+'" type="button" class="btn btn-primary" onclick="clearInterval(idrecargaorderbuy);abrirordencompra(this);">Comprar</button>\
                                        <button style="margin-left:8px" type="button" class="btn btn-secondary" data-dismiss="modal" onclick="javascript:clearInterval(idrecargaorderbuy);">Cerrar</button>\
                                    </div>';
                                    
                            $("#"+response.moneda[i]).append(cadena);
                            $("#"+response.moneda[i]).append('<div class="panel panel-default divordbk"><div id="dobc'+response.mercado[i]+'" class="panel-body cont-order-book"></div></div>');
                            cadmercados+=response.mercado[i]+",";                   
                        }
                    }
                    $("#cuer-merc").append("<input type='hidden' id='comisionmaker' value='"+response.comisionmaker+"'>");
                    //mostrar simbolo e icono de la moneda a comprar
                    $("#pest-merc").append("<div class='divdemomoneda'><img class='imgdemomoneda' src='"+response.iconooperacion+"' width='30' height='30'/><p class='imgdemomoneda'> "+response.monedacompra[0]+"</p></div>");
                    cadmercados=cadmercados.slice(0,-1);
                    orderbuy(cadmercados,1);
                    recargarorderbuy();
                    $("#pest-merc").children(":first").addClass("active");
                    $("#cuer-merc").children(":first").addClass("active in");
                    $("#modal-transaccion").modal("show");
                    $('[data-toggle="tooltip"]').tooltip();
                }
                else{
                    var mensaje = response.status.toString();
                    alertify.error(mensaje);
                }
                
            }); 
        }
        
        function cargarmercadosventa(e){
            strprecioestatico="";
            var moneda=$(e).data("idm");
            var plataforma = $("#selec-merc-gral").val();
            $.get("operacion/cargarmercado.php",{plataforma:plataforma,moneda:moneda,accion:2},function(data){
                var response = jQuery.parseJSON(data);
                $("#pest-merc").children().remove();
                $("#cuer-merc").children().remove();
                $("#pest-mercv").children().remove();
                $("#cuer-mercv").children().remove();
                var cadena="";
                var cadmercados="";             
                if(response.status==1){
                    for(var i = 0;i<response.mercado.length;i++){
                            cadena="";
                            $("#pest-mercv").append('<li><a style="color:black" data-toggle="tab" href="#'+response.moneda[i]+'"><img src='+response.icono[i]+' width=20 height=20/>'+response.moneda[i]+'</a></li>');   
                            $("#cuer-mercv").append('<div id="'+response.moneda[i]+'" class="tab-pane fade"></div>');
                          //  cadena+='<div class="divinfmerc"><div class="form-group" style="margin-top:20px;"><label class="lblinfmerc" for="name" class="control-label">Mercado</label><input class="inpinfmerc" type="text" name="txtmercado" class="form-control" style="border-bottom: none;" id="txtmercado" disabled value="'+response.mercado[i]+'"></div>';
                            cadena+='<div class="divinfmerc">\
                                        <div class="form-group" style="margin-top:8px;margin-bottom:8px;">\
                                                <label class="lblinfmerc" for="name" class="control-label">Saldo</label>'+parseFloat(response.saldo[i]).toFixed(8)+' '+response.monedaventa[i]+'\
                                        </div>\
                                        <div class="form-group" style="margin-top:8px;margin-bottom:8px;">\
                                            <label class="lblinfmerc" for="name"  class="control-label">Precio</label>\
                                            <div class="input-group input-group-sm">\
                                                <input class="form-control" oninput="solodecimal(this);cambiarinputs(this)" type="text" data-mercado="'+response.mercado[i]+'" data-type="v" id="txtpreciov'+response.mercado[i]+'" autocomplete="off"><span class="input-group-addon" id="sizing-addon3">'+response.monedacompra[i]+'</span>\
                                            </div>\
                                        </div>\
                                        <div class="form-group" style="margin-top:8px;margin-bottom:8px;">\
                                            <label class="lblinfmerc" for="name" class="control-label">Cantidad</label>\
                                            <div class="input-group input-group-sm">\
                                                <input class="form-control" data-type="v" data-mercado="'+response.mercado[i]+'" oninput="solodecimal(this);cambiarinputs(this)" type="text" id="txtcantidadv'+response.mercado[i]+'" autocomplete="off"><span class="input-group-addon" id="sizing-addon3">'+response.monedacompra[i]+'</span>\
                                            </div>\
                                        </div>\
                                        <div class="form-group" style="margin-top:5px;">\
                                            <div class="input-group input-group-sm">\
                                                <label class="lblinfmerc" for="name" class="control-label">Comisión </label><p style="display:inline;margin-left:5px" id="txtcomisionv'+response.mercado[i]+'"></p> '+response.monedacompra[i]+'\
                                            </div>\
                                        </div>\
                                        <div class="form-group">\
                                            <label class="lblinfmerc" for="name" class="control-label">Total</label>\
                                            <input class="inpinfmerc" step="0.00000001" min="0" value="0" max="'+response.saldo[i]+'" type="range" data-type="v" data-mercado="'+response.mercado[i]+'" id="slidertotalv'+response.mercado[i]+'" class="form-control" oninput="cambiarvalores(this)" style="padding:0px">\
                                            <div class="input-group input-group-sm">\
                                                <input type="text" data-type="v" data-mercado="'+response.mercado[i]+'" oninput="solodecimal(this);cambiarinputs(this)"  class="form-control" id="txttotalv'+response.mercado[i]+'" autocomplete="off"><span class="input-group-addon" id="sizing-addon3">'+response.monedaventa[i]+'</span>\
                                            </div>\
                                        </div>\
                                        <input type="hidden" id="txtsaldov'+response.mercado[i]+'" data-idmv="'+response.idmonedaventa[i]+'" data-idmc="'+response.idmonedacompra[i]+'" disabled value="'+response.saldo[i]+'">\
                                        <br><button data-mercado='+response.mercado[i]+' type="button" class="btn btn-primary" onclick="clearInterval(idrecargaorderbuy);abrirordenventa(this);">Vender</button>\
                                        <button style="margin-left:8px" type="button" class="btn btn-secondary" data-dismiss="modal" onclick="javascript:clearInterval(idrecargaorderbuy);">Cerrar</button>\
                                    </div>';
                            $("#"+response.moneda[i]).append(cadena);
                            $("#"+response.moneda[i]).append('<div class="panel panel-default divordbk"><div id="dobv'+response.mercado[i]+'" class="panel-body cont-order-book"></div></div>');
                            cadmercados+=response.mercado[i]+","; 
                    }
                    $("#cuer-mercv").append("<input type='hidden' id='comisionmaker' value='"+response.comisionmaker+"'>");
                    //mostrar simbolo e icono de la moneda a vender
                    $("#pest-mercv").append("<div class='divdemomoneda'><img class='imgdemomoneda' src='"+response.iconooperacion+"' width='30' height='30'/><p class='imgdemomoneda'> "+response.monedaventa[0]+"</p></div>");
                    cadmercados=cadmercados.slice(0,-1);
                    orderbuy(cadmercados,2);
                    recargarorderbuy();
                    $("#pest-mercv").children(":first").addClass("active");
                    $("#cuer-mercv").children(":first").addClass("active in");
                    $("#modal-venta").modal("show");
                }
                else{
                    var mensaje = response.status.toString();
                    alertify.error(mensaje);
                }
            });
        }
        
        
        function slider(){
            $('.carousel[data-type="multi"] .item').each(function(){
              var next = $(this).next();
              if (!next.length) {
                next = $(this).siblings(':first');
              }
              next.children(':first-child').clone().appendTo($(this));
            });
        }
        
        
        //cargar los order book de la moneda seleccionada
        function orderbuy(str,tipo){
            //tipo de proceso (compra(1)/venta(2))
            tipocargaroderbuy=tipo;
            //cadena de mercados
            strmercorderbuy=str;
            var arraylimpiar = str.split(",");
            var plataforma = $("#selec-merc-gral").val();   
            $.get("operacion/cargarlibroorden.php",{par:str,plataforma:plataforma},function(data){
                var response = jQuery.parseJSON(data);
                var arcantidad;
                var arprecio;
                var cadena;
                for(var i=0;i<response.mercado.length;i++){
                       arprecio=response.precio[i].split("|");
                       arcantidad=response.cantidad[i].split("|");
                       cadena = "";
                       cadena = "<center><table data-tipo='"+tipo+"' data-mercado='"+response.mercado[i]+"' class='table text-center table-striped tblorderbook'>";    
                       for(var j=0;j<arprecio.length;j++){
                            if(j==((arprecio.length/2)-1)&&tipocargaroderbuy==1&&response.mercado[i]!=strprecioestatico){
                                $("#txtprecioc"+response.mercado[i]).val(arprecio[j]);
                                $("#txtpreciogananciac"+response.mercado[i]).val(parseFloat(parseFloat(arprecio[j])+(parseFloat(arprecio[j]*($("#txtgananciac"+response.mercado[i]).val()/100)))).toFixed(8).replace(/^\.+|\.+$/g,""));
                                $("#txtprecioperdidac"+response.mercado[i]).val(parseFloat(parseFloat(arprecio[j])+(parseFloat(arprecio[j]*($("#txtperdidac"+response.mercado[i]).val()/100)))).toFixed(8).replace(/^\.+|\.+$/g,""));
                                $("#txtcantidadc"+response.mercado[i]).val(parseFloat($("#slidertotalc"+response.mercado[i]).val()/arprecio[j]).toFixed(8));
                                $("#txttotalc"+response.mercado[i]).val($("#slidertotalc"+response.mercado[i]).val());
                                $("#txtcomisionc"+response.mercado[i]).text(parseFloat($("#txtcantidadc"+response.mercado[i]).val()*$("#comisionmaker").val()).toFixed(8));
                            }
                            else
                            if(j==(arprecio.length/2)&&tipocargaroderbuy==2&&response.mercado[i]!=strprecioestatico){
                                $("#txtpreciov"+response.mercado[i]).val(arprecio[j]);
                                $("#txtcantidadv"+response.mercado[i]).val(parseFloat($("#slidertotalv"+response.mercado[i]).val()*arprecio[j]).toFixed(8));
                                $("#txttotalv"+response.mercado[i]).val($("#slidertotalv"+response.mercado[i]).val());
                                $("#txtcomisionv"+response.mercado[i]).text(parseFloat($("#txtcantidadv"+response.mercado[i]).val()*$("#comisionmaker").val()).toFixed(8));
                            }
                            if(j<(arprecio.length/2)){
                                if(j!=0){
                                    cadena+="<tr><td onclick='preciocelda(this)' class='tblorderbookprec td-ord-book'>"+arprecio[j]+"</td><td style='background-color:#FF6565' class='tblorderbookcant'>"+arcantidad[j]+"</td></tr>";    
                                }
                                else{
                                    cadena+="<tr><td rowspan='8'></td><td onclick='preciocelda(this)' class='tblorderbookprec td-ord-book'>"+arprecio[j]+"</td><td style='background-color:#FF6565' class='tblorderbookcant'>"+arcantidad[j]+"</td></tr>";    
                                }
                            }
                            else{
                                if(j!=(arprecio.length/2)){
                                    cadena+="<tr><td style='background-color:#26CA4F' class='tblorderbookcant'>"+arcantidad[j]+"</td><td onclick='preciocelda(this)' class='tblorderbookprec td-ord-book'>"+arprecio[j]+"</td></tr>";    
                                }
                                else{
                                    cadena+="<tr style='border-top: 4px solid black;'><td style='background-color:#26CA4F' class='tblorderbookcant'>"+arcantidad[j]+"</td><td onclick='preciocelda(this)' class='tblorderbookprec td-ord-book'>"+arprecio[j]+"</td><td rowspan='8'></td></tr>";    
                                }    
                            }
                           
                       }
                       
                       cadena+="</table></center>";
                        if(tipocargaroderbuy==1){
                            $("#dobc"+response.mercado[i]).children().remove();
                            $("#dobc"+response.mercado[i]).append(cadena);
                        }else{
                            $("#dobv"+response.mercado[i]).children().remove();
                            $("#dobv"+response.mercado[i]).append(cadena);
                        }
                }
            });
        }
        
        function cargarordenes(tipo){
            var plataforma = $("#selec-merc-gral").val();
            $.get("operacion/cargarorden.php",{plataforma:plataforma,tipo:tipo},function(data){
                var response = jQuery.parseJSON(data);
                var cadena = "";
                if(response.status==1){
                    for(var i=0;i<response.id.length;i++){
                        //condicional para mostrar la orden con o sin diferencia porcentual
                        if(response.seguimiento[i]==1){
                            if(parseFloat(response.diferencia[i])>0){
                                cadena+="<tr><td data-title='#'>"+(i+1)+"</td><td data-title='Tipo'>"+response.tipo[i]+"</td><td data-title='Mercado'>"+response.mercado[i]+"</td><td data-title='Cantidad'>"+response.cantidad[i]+"</td><td data-title='Precio'>"+response.precio[i]+"<span style='font-size:8pt;color:#008E29;margin-left:4px;font-weight:600'>("+response.diferencia[i]+"%)</span></td>";    
                            }
                            else{
                                cadena+="<tr><td data-title='#'>"+(i+1)+"</td><td data-title='Tipo'>"+response.tipo[i]+"</td><td data-title='Mercado'>"+response.mercado[i]+"</td><td data-title='Cantidad'>"+response.cantidad[i]+"</td><td data-title='Precio'>"+response.precio[i]+"<span style='font-size:8pt;color:#FF0000;margin-left:4px;font-weight:600'>("+Math.abs(response.diferencia[i])+"%)</span></td>";    
                            }
                            
                        }
                        else{
                            cadena+="<tr><td data-title='#'>"+(i+1)+"</td><td data-title='Tipo'>"+response.tipo[i]+"</td><td data-title='Mercado'>"+response.mercado[i]+"</td><td data-title='Cantidad'>"+response.cantidad[i]+"</td><td data-title='Precio'>"+response.precio[i]+"</td>";    
                        }
                        cadena+="<td data-title='⚙'>";
                        if(response.estado[i]==3||response.estado[i]==2){
                            cadena+="<a onclick='resumen(this)' data-id='"+response.id[i]+"' class='btn btn-default' title='Ver orden' style='padding: 3px 8px;'><em class='fa fa-eye'></em></a>";
                        }
                        if(response.estado[i]==1||response.estado[i]==3){
                            cadena+="<a onclick='cancelarorden(this)' data-id='"+response.id[i]+"' class='btn btn-danger' title='Cancelar orden' style='padding: 3px 8px;'><em class='fa fa-times'></em></a>";
                        }
                        if(response.seguimiento[i]==1){
                            if(response.estadoseguimiento[i]!=0){
                                cadena+="<a onclick='buscarorden(this)' data-id='"+response.id[i]+"' class='btn btn-default' title='Modificar orden' style='padding: 3px 8px;'><em class='fa fa-pencil'></em></a><a onclick='venderahora(this)' data-id='"+response.id[i]+"' class='btn btn-default' title='Vender ahora' style='padding: 3px 8px;'><em class='fa fa-hand-o-down'></em></a>";           
                            }
                            else
                            if(response.estadoseguimiento[i]==0&&response.estado[i]==3){
                                cadena+="<a onclick='buscarorden(this)' data-id='"+response.id[i]+"' class='btn btn-default' title='Modificar orden' style='padding: 3px 8px;'><em class='fa fa-pencil'></em></a>";    
                            }
                        }
                        cadena+="</td>";
                        cadena+="</tr>";
                    }
                   
                    $("#tblorden").children().remove();
                    $("#tblorden").append(cadena);
                    setTimeout(function(){ cargarordenes(); }, 5000);
                    $("#div-loader").css("display","none");
                    $("#wrapper").css("display","unset");
                }
                else{
                    $("#tblorden").children().remove();
                    $("#tblorden").append("<tr><td class='text-center' colspan='5'>No hay registros para mostrar</td></tr>");
                    setTimeout(function(){ cargarordenes(); }, 4000);
                    $("#div-loader").css("display","none");
                    $("#wrapper").css("display","unset");
                }
            });
        }
        
        //interval para recargar el order buy
        function recargarorderbuy(){
            idrecargaorderbuy = setInterval(function(){ orderbuy(strmercorderbuy,tipocargaroderbuy,0); }, 10000);
        }
        
        function abrirordencompra(e){
            var plataforma = $("#selec-merc-gral").val();
            var mercado = $(e).data("mercado");
            var cantidad = $("#txtcantidadc"+mercado).val().trim();
            var precio = $("#txtprecioc"+mercado).val().trim();
            var idmc = $("#txtsaldoc"+mercado).data('idmc');
            var idmv = $("#txtsaldoc"+mercado).data('idmv');            
            var tipousua = $(e).data("tipousuario");
            if(tipousua==2||$("#op-av-compra"+mercado).prop("checked")==true){
                var seguimiento = 1;
                var ganancia = $("#txtgananciac"+mercado).val();
                var perdida = $("#txtperdidac"+mercado).val();
                if(ganancia==""||perdida==""||parseFloat(ganancia)<=parseFloat(0.5)||parseFloat(perdida)>=parseFloat(-0.5)){
                    alertify.error("la ganancia debe ser mayor que 0.5 y las perdida menor que -0.5");
                    return false;
                }
            }
            else{
                seguimiento = 0;
            }
            if(cantidad!=""&&precio!=""&&cantidad!=0&&precio!=0){
                alertify.confirm("¿Esta seguro que desea crear esta orden de compra?",
                  function(){
                    $.get("operacion/abrirordencompra.php",{mercado:mercado,cantidad:cantidad,precio:precio,monedacompra:idmc,monedaventa:idmv,plataforma:plataforma,seguimiento:seguimiento,ganancia:ganancia,perdida:perdida},function(data){
                        mensaje = data.split("|")[1];
                        data = data.split("|")[0];
                        if(data==1){
                            alertify.success("Orden creada con exito");
                            if(mensaje!=""){
                                alertify.warning(mensaje);    
                            }
                            cargarordenes();
                            cargarmonedasventa();
                            $("#modal-transaccion").modal("hide");
                        }
                        else{
                            alertify.error(data);
                        }
                    });
                });
            }
            else{
                alertify.error("Asegúrese de que los campos de precio y cantidad no esten vacios y sean diferentes a cero");
            }
        }
        
       
        
        function abrirordenventa(e){
            var plataforma = $("#selec-merc-gral").val();
            var mercado = $(e).data("mercado");
            //cuando se presenten cruces de mercado en compras se ejecuta una venta
            var cantidad = $("#txttotalv"+mercado).val().trim();
            var precio = $("#txtpreciov"+mercado).val().trim();
            var idmc = $("#txtsaldov"+mercado).data('idmc');
            var idmv = $("#txtsaldov"+mercado).data('idmv');      
            if(cantidad!=""&&precio!=""&&cantidad!=0&&precio!=0){                     
            alertify.confirm("¿Esta seguro que desea abrir esta orden de venta?",
              function(){
                $.get("operacion/abrirordenventa.php",{mercado:mercado,cantidad:cantidad,precio:precio,monedacompra:idmc,monedaventa:idmv,plataforma:plataforma},function(data){
                    mensaje = data.split("|")[1];
                    data = data.split("|")[0];
                    if(data==1){
                        alertify.success("Orden creada con exito");
                        if(mensaje!=""){
                            alertify.warning(mensaje);    
                        }
                        cargarordenes();
                        cargarmonedasventa();
                        $("#modal-venta").modal("hide");
                    }
                    else{
                        alertify.error(data);
                    }
                });
            });
          }
          else{
            alertify.error("Asegúrese de que los campos de precio y cantidad no esten vacios y sean diferentes a cero");    
          }
        }
        
        function cancelarorden(e){
            var idorden = $(e).data("id");
            alertify.confirm("¿Esta seguro que desea cancelar esta orden?",
              function(){
                $.get("operacion/cancelarorden.php",{idorden:idorden},function(data){
                    if(data==1){
                        alertify.success("La orden se ha cancelado con exito");
                    }
                    else{
                        alertify.error(data);    
                    }
                });
                cargarmonedas();
                cargarmonedasventa();
              });
        }
        
        
        function verorden(e){
            var idorden = $(e).data("id");
            $.get("operacion/verorden.php",{idorden:idorden},function(data){
                var response = jQuery.parseJSON(data);
                if(response.status==1){ 
                    cadena='<div class="form-group"><label for="email">Moneda base</label><input type="text" disabled class="form-control" id="email" value="'+response.monedaventa[0]+'"></div><div class="form-group"><label for="pwd">Moneda comprada</label><input type="text" disabled class="form-control" value="'+response.monedacompra[0]+'"></div>';
                    cadena+='<table class="table"><thead><tr><th>N°</th><th>Cantidad</th><th>Precio</th><th>Precio por unidad</th><th>Comisión</th></tr></thead><tbody>';
                    for(var i=0;i<response.monedacompra.length;i++){
                    cadena+="<tr><td>"+(i+1)+"</td><td>"+response.cantidad[i]+" <b>"+response.monedacompra[i]+"</b></td><td>"+response.precio[i]+"<b>"+response.monedaventa[i]+"</b></td><td>"+response.preciounidad[i]+" ";
                        if(response.tipo[i]=="Compra"){
                            cadena+="<b>"+response.monedaventa[i]+"</b></td><td>"+response.comision[i]+" <b>"+response.monedacomision[i]+"</b></td></tr>";    
                        }
                        else{
                            cadena+="<b>"+response.monedacompra[i]+"</b></td><td>"+response.comision[i]+" <b>"+response.monedacomision[i]+"</b></td></tr>";    
                        }
                    }
                    cadena+='</tbody></table>'; 
                    $("#idverorden").children().remove();
                    $("#idverorden").append(cadena);
                    $("#modal-orden").modal("show"); 
                }
                else{
                    alertify.error(response.status.toString());
                }
            })
        }
        
        //funciones de los sliders de ganancia y perdida
        
        //cambiar valores gananciaperdida al mover slider range del formulario de compra
        function cambiarporcentajegp(e){
            var tipo = $(e).data("type");
            var mercado = $(e).data("mercado");
            var aux = $(e).attr("id").replace($(e).data("type")+$(e).data("mercado"), "");
            var precio = parseFloat($("#txtprecio"+tipo+mercado).val().replace(/^\.+|\.+$/g,""));
            strprecioestatico = mercado;
            if(aux=="sliderganancia"){
                $("#txtganancia"+tipo+mercado).val($(e).val());
                $("#txtprecioganancia"+tipo+mercado).val(parseFloat(parseFloat(precio)+parseFloat(precio*($("#txtganancia"+tipo+mercado).val()/100).toFixed(4))).toFixed(8).replace(/^\.+|\.+$/g,""));
            }else{
                $("#txtperdida"+tipo+mercado).val($(e).val());
                $("#txtprecioperdida"+tipo+mercado).val(parseFloat(parseFloat(precio)+parseFloat(precio*($("#txtperdida"+tipo+mercado).val()/100).toFixed(4))).toFixed(8).replace(/^\.+|\.+$/g,""));
            }      
        }
        
        function cambiarpreciogp(e){
            var valor = parseFloat($(e).val()).toFixed(8).replace(/^\.+|\.+$/g,"");
            if(valor!=""&&valor>0){
                var tipo = $(e).data("type");
                if(tipo=="c"){
                    var aux = $(e).attr("id").replace($(e).data("type")+$(e).data("mercado"), "");
                    var mercado = $(e).data("mercado");
                    if(aux=="txtprecioganancia"){
                        var ganancia = parseFloat(((valor/$("#txtprecio"+tipo+mercado).val())-1)*100).toFixed(2);
                        var maximo = $("#sliderganancia"+tipo+mercado).attr("max");
                        var minimo = $("#sliderganancia"+tipo+mercado).attr("min");
                        var precio = $("#txtprecio"+tipo+mercado).val();
                        if(parseFloat(ganancia)<=parseFloat(maximo)&&parseFloat(ganancia)>=parseFloat(minimo)){
                            $("#txtganancia"+tipo+mercado).val(ganancia);
                            $("#sliderganancia"+tipo+mercado).val(ganancia);
                        }
                        else
                        if(parseFloat(ganancia)>parseFloat(maximo)){
                            $("#txtganancia"+tipo+mercado).val(maximo);
                            $("#sliderganancia"+tipo+mercado).val(maximo);
                            $(e).val(parseFloat(parseFloat(precio)+parseFloat(precio*(maximo/100).toFixed(4))).toFixed(8).replace(/^\.+|\.+$/g,""));
                        }
                        else
                        if(parseFloat(ganancia)<parseFloat(minimo)){
                            $("#txtganancia"+tipo+mercado).val(minimo);
                            $("#sliderganancia"+tipo+mercado).val(minimo);
                        }
                        
                    }
                    else{
                        var perdida = parseFloat(((valor/$("#txtprecio"+tipo+mercado).val())-1)*100).toFixed(2);
                        var maximo = $("#sliderperdida"+tipo+mercado).attr("max");
                        var minimo = $("#sliderperdida"+tipo+mercado).attr("min");
                        var precio = $("#txtprecio"+tipo+mercado).val();
                        if(parseFloat(perdida)<=parseFloat(maximo)&&parseFloat(perdida)>=parseFloat(minimo)){
                            $("#txtperdida"+tipo+mercado).val(perdida);
                            $("#sliderperdida"+tipo+mercado).val(perdida);
                        }
                        else
                        if(parseFloat(perdida)<parseFloat(minimo)){
                            $("#txtperdida"+tipo+mercado).val(minimo);
                            $("#sliderperdida"+tipo+mercado).val(minimo);
                        }
                        else
                        if(parseFloat(perdida)>parseFloat(maximo)){
                            $("#txtperdida"+tipo+mercado).val(maximo);
                            $("#sliderperdida"+tipo+mercado).val(maximo);
                            $(e).val(parseFloat(parseFloat(precio)+parseFloat(precio*(maximo/100).toFixed(4))).toFixed(8).replace(/^\.+|\.+$/g,""));
                        }
                        
                    }
                }
                else
                if(tipo=="m"){
                    var aux = $(e).attr("id");
                    var mercado = $(e).data("mercado");
                    if(aux=="txtpreciogananciam"){
                        var ganancia = parseFloat(((valor/$("#txtpreciom").val())-1)*100).toFixed(2);
                        var maximo = $("#slidergananciam").attr("max");
                        var minimo = $("#slidergananciam").attr("min");
                        var precio = $("#txtpreciom").val();
                        if(parseFloat(ganancia)<=parseFloat(maximo)&&parseFloat(ganancia)>=parseFloat(minimo)){
                            $("#txtgananciam").val(ganancia);
                            $("#slidergananciam").val(ganancia);
                        }
                        else
                        if(parseFloat(ganancia)>parseFloat(maximo)){
                            $("#txtgananciam").val(maximo);
                            $("#slidergananciam").val(maximo);
                            $(e).val(parseFloat(parseFloat(precio)+parseFloat(precio*(maximo/100).toFixed(4))).toFixed(8).replace(/^\.+|\.+$/g,""));
                        }
                        else
                        if(parseFloat(ganancia)<parseFloat(minimo)){
                            $("#txtgananciam").val(minimo);
                            $("#slidergananciam").val(minimo);
                        }
                        
                    }
                    else{
                        var perdida = parseFloat(((valor/$("#txtpreciom").val())-1)*100).toFixed(2);
                        var maximo = $("#sliderperdidam").attr("max");
                        var minimo = $("#sliderperdidam").attr("min");
                        var precio = $("#txtpreciom").val();
                        if(parseFloat(perdida)<=parseFloat(maximo)&&parseFloat(perdida)>=parseFloat(minimo)){
                            $("#txtperdidam").val(perdida);
                            $("#sliderperdidam").val(perdida);
                        }
                        else
                        if(parseFloat(perdida)<parseFloat(minimo)){
                            $("#txtperdidam").val(minimo);
                            $("#sliderperdidam").val(minimo);
                        }
                        else
                        if(parseFloat(perdida)>parseFloat(maximo)){
                            $("#txtperdidam").val(maximo);
                            $("#sliderperdidam").val(maximo);
                             $(e).val(parseFloat(parseFloat(precio)+parseFloat(precio*(maximo/100).toFixed(4))).toFixed(8).replace(/^\.+|\.+$/g,""));
                        }
                        
                    }
                }
            }
        }
        
        //mostrar opciones avanzadas a usuarios de tipo 1 al checkear
        function desplegaravanzadas(e){
            var tipo = $(e).data("type");
            var mercado = $(e).data("mercado");
            var tipousua = $(e).data("tipousuario");
            $("#divslidergp"+tipo+mercado).slideToggle("slow");
        }
        
        
        //cambiar inputs gananciaperdida al mover los input range
        function cambiarinputsgp(e){
            if($(e).data("tipo")=="sg"||$(e).data("tipo")=="sp"){
                var precio = $("#txtpreciom").val();
                if($(e).data("tipo")=="sg"){
                    $("#txtgananciam").val($(e).val());
                    $("#txtpreciogananciam").val(parseFloat(parseFloat(precio)+parseFloat(precio*(parseFloat($("#txtgananciam").val())/100).toFixed(4))).toFixed(8).replace(/^\.+|\.+$/g,""));
                }
                else{
                    $("#txtperdidam").val($(e).val());
                    $("#txtprecioperdidam").val(parseFloat(parseFloat(precio)+parseFloat(precio*(parseFloat($("#txtperdidam").val())/100).toFixed(4))).toFixed(8).replace(/^\.+|\.+$/g,""));
                }
            }
            else{
                var tipo = $(e).data("type");
                var mercado = $(e).data("mercado");
                strprecioestatico=mercado;
                var aux = $(e).attr("id").replace($(e).data("type")+$(e).data("mercado"),"");
                var valor = parseFloat($(e).val().replace(/^\.+|\.+$/g,""));
                var precio = parseFloat($("#txtprecio"+tipo+mercado).val().replace(/^\.+|\.+$/g,""));
                var tipousua= $(e).data("tipousuario");
                if(valor!=""){
                    if(aux=="txtganancia"&&valor>0){
                        var maximo = $(e).data("ganancia");
                        var minimo =  $("#sliderganancia"+tipo+mercado).attr("min");
                        if(valor<=maximo&&valor>=minimo){
                            $("#sliderganancia"+tipo+mercado).val(valor);
                            $("#txtprecioganancia"+tipo+mercado).val(parseFloat(parseFloat(precio)+parseFloat(precio*($(e).val().replace(/^\.+|\.+$/g,"")/100).toFixed(4))).toFixed(8).replace(/^\.+|\.+$/g,""));
                        }
                        else
                        if(valor>maximo){
                            $("#sliderganancia"+tipo+mercado).val(maximo);
                            $(e).val(maximo);
                            $("#txtprecioganancia"+tipo+mercado).val(parseFloat(parseFloat(precio)+parseFloat(precio*(maximo/100).toFixed(4))).toFixed(8).replace(/^\.+|\.+$/g,""));
                        }
                        else
                        if(valor<minimo){
                            $("#sliderganancia"+tipo+mercado).val(minimo);
                            $(e).val(minimo);
                            $("#txtprecioganancia"+tipo+mercado).val(parseFloat(parseFloat(precio)+parseFloat(precio*(minimo/100).toFixed(4))).toFixed(8).replace(/^\.+|\.+$/g,""));
                        }
                    }else
                    if(aux=="txtperdida"&&valor<0){
                        var maximo =  parseFloat($("#sliderperdida"+tipo+mercado).attr("max"));
                        var minimo = parseFloat($(e).data("perdida"));
                        if(valor<=maximo&&valor>=minimo){
                            $("#sliderperdida"+tipo+mercado).val(valor);
                            $("#txtprecioperdida"+tipo+mercado).val(parseFloat(parseFloat(precio)+parseFloat(precio*(valor/100).toFixed(4))).toFixed(8).replace(/^\.+|\.+$/g,""));
                        }
                        else
                        if(valor<minimo){
                            $("#sliderperdida"+tipo+mercado).val(minimo);
                            $(e).val(minimo);
                            $("#txtprecioperdida"+tipo+mercado).val(parseFloat(parseFloat(precio)+parseFloat(precio*(minimo/100).toFixed(4))).toFixed(8).replace(/^\.+|\.+$/g,""));
                        }
                        else
                        if(valor>maximo){
                            $("#sliderperdida"+tipo+mercado).val(maximo);
                            $(e).val(maximo);
                            $("#txtprecioperdida"+tipo+mercado).val(parseFloat(parseFloat(precio)+parseFloat(precio*(maximo/100).toFixed(4))).toFixed(8).replace(/^\.+|\.+$/g,""));
                        }
                    }
                }
            }
        }
        
        
        function cargarperfil(){
            $("#txtclaveperfil1").val("");
            $("#txtclaveperfil2").val("");
            $.get("usuario/cargarperfil.php",{},function(data){  
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
                    $.get("usuario/modificarperfil.php",{clave:clave1},function(data){  
                        if(data==1){
                            alertify.success("Su perfil ha sido modificado con éxito");
                            $("#modalperfil").modal('hide');         
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
        
        
        function buscarorden(e){
            profitglobal = "";
            var id = $(e).data("id");
            idordenglobal = id;
            $.get("orden/buscarorden.php",{idorden:id},function(data){  
                var response = jQuery.parseJSON(data);
                if(response.status=="1"){
                    $("#txtmercadom").val(response.mercado);
                    $("#txtcantidadm").val(response.cantidad); 
                    $("#txtpreciom").val(response.precio);
                    $("#slidergananciam").val(response.ganancia);
                    $("#slidergananciam").prop("max", response.usuaganancia);
                    $("#txtgananciam").val(response.ganancia);
                    $("#sliderperdidam").prop("min", response.usuaperdida);
                    if(response.profit!=""){
                        $("#sliderperdidam").prop("max", response.profit);
                        $("#txtperdidam").removeAttr("oninput");
                        $("#txtperdidam").attr("oninput","solodecimalreal(this);cambiarslider(this)");
                        profitglobal  = response.profit;
                    }
                    else{
                        $("#sliderperdidam").prop("max", "-0.5"); 
                        $("#txtperdidam").removeAttr("oninput");
                        $("#txtperdidam").attr("oninput","solodecimalnegativo(this);cambiarslider(this)");
                    }
                    $("#sliderperdidam").val(response.perdida);
                    $("#txtperdidam").val(response.perdida);
                    $("#txtpreciogananciam").val(parseFloat(parseFloat(response.precio)+(parseFloat(response.precio)*(parseFloat(response.ganancia)/100).toFixed(4))).toFixed(8));
                    $("#txtprecioperdidam").val(parseFloat(parseFloat(response.precio)+(parseFloat(response.precio)*(parseFloat(response.perdida)/100).toFixed(4))).toFixed(8));
                    $("#modal-modi-orden").modal('show');
                    idsettimeglobal = setTimeout(function(){ $("#modal-modi-orden").modal('hide');alertify.error("Se ha agotado el tiempo para realizar esta operación"); }, 20000);
                }
                else{
                    alertify.error(response.status);
                }
            }); 
        }
        
        function modificarorden(){
            alertify.confirm("¿Esta seguro de que desea modificar esta orden?",
                function(){
                    var ganancia = $("#txtgananciam").val().trim();
                    var perdida = $("#txtperdidam").val().trim();
                    if(ganancia==""||perdida==""||(profitglobal==""&&(parseFloat(ganancia)<parseFloat(0.5)||parseFloat(perdida)>parseFloat(-0.5)))||(profitglobal!=""&&(parseFloat(ganancia)<parseFloat(0.5)||parseFloat(perdida)>parseFloat(profitglobal)))){
                        if(profitglobal==""){
                            alertify.error("la ganancia debe ser mayor que 0.5 y la perdida menor que -0.5");       
                        }
                        else{
                            alertify.error("la ganancia debe ser mayor que 0.5 y la perdida menor que "+profitglobal);
                        }
                                        
                    }
                    else
                    if(parseFloat(perdida)>parseFloat(ganancia)){
                        alertify.error("La perdida no debe ser mayor que la ganancia");    
                    }
                    else{               
                        $.get("orden/modificarorden.php",{idorden:idordenglobal,ganancia:ganancia,perdida:perdida},function(data){
                            if(data==1){
                                alertify.success("Orden modificada con exito");
                                $("#modal-modi-orden").modal('hide');
                            }
                            else{
                                alertify.error(data);
                            }
                        });
                    }        
                }
            );    
        }
        
        function venderahora(e){
            var idorden = $(e).data("id");
            alertify.confirm("¿Esta seguro de que desea abrir esta orden de venta en este instante?",
                function(){ 
                    $.get("orden/venderahora.php",{idorden:idorden},function(data){
                        if(data==1){
                            alertify.success("La operación se ha completado con éxito");
                            cargarordenes();
                        }
                        else{
                            alertify.error(data);
                        }
                    });                     
                }
            );
        }
        
        
        function resumen(e){
            var plataforma = $("#selec-merc-gral").val();
            var id = $(e).data("id");
            $.get("orden/resumen.php",{plataforma:plataforma,idorden:id},function(data){
                var response = jQuery.parseJSON(data);
                if(response.status==1){
                    console.log(response);
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


    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body onload="cargarplataformas()">
    <div id="wrapper" style="display: none;">
        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <img src="img/logotransparente.png" style="height:40px;margin:5px 0px 0px 20px" />
            </div>
            
            <!-- /.navbar-header -->

            <!--<ul class="nav navbar-top-links navbar-right">-->
            <!-- /.dropdown -->
            <!--    <li class="dropdown">-->
            <!--        <a class="dropdown-toggle" data-toggle="dropdown" href="#">-->
            <!--            <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>-->
            <!--        </a>-->
            <!--        <ul class="dropdown-menu dropdown-user">-->
            <!--            <li><a href="#"><i class="fa fa-user fa-fw"></i> Perfil</a>-->
            <!--            </li>-->
            <!--            <li><a href="#"><i class="fa fa-gear fa-fw"></i> Preferencias</a>-->
            <!--            </li>-->
            <!--            <li class="divider"></li>-->
            <!--            <li><a href="#"><i class="fa fa-sign-out fa-fw"></i> Cerrar sesión</a>-->
            <!--            </li>-->
            <!--        </ul>-->
            <!-- /.dropdown-user -->
            <!--    </li>-->
            <!-- /.dropdown -->
            <!--</ul>-->
            <!-- /.navbar-top-links -->

            <div class="navbar-default sidebar gmenu" role="navigation">
                <div class="sidebar-nav navbar-collapse gmenu">
                    <ul class="nav gmenu" id="side-menu">
                        <li class="sidebar-search">
                            <div class="input-group custom-search-form">
                                <select class="form-control" id="selec-merc-gral" onchange="tema();cargarmonedas();cargarmonedasventa();" style="padding-bottom:0px"></select>
                                <span class="input-group-addon">$</span>
                                </span>
                            </div>
                             <!--/input-group -->
                        </li>
                        <li class="gmenu" id="limenu">
                            <a href="panel.php"><i class="fa fa-home fa-fw"></i> Inicio</a>
                            
                        </li>
                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>

        <div id="page-wrapper">
            <!-- /carousel -->
            
            <!--<div id="contcarousel" class="carousel slide text-center" data-ride="carousel" data-type="multi" data-interval="false">         
            <!-- Wrapper for slides -->
            <!--    <div class="carousel-inner text-center" id="itemscarousel">-->
                   
            <!--    </div>-->
                <!-- Controls -->
            <!--    <a class="left carousel-control" href="#contcarousel" data-slide="prev">-->
            <!--        <span class="glyphicon glyphicon-chevron-left"></span>-->
            <!--    </a>-->
            <!--    <a class="right carousel-control" href="#contcarousel" data-slide="next">-->
            <!--        <span class="glyphicon glyphicon-chevron-right"></span>-->
            <!--    </a>-->
                
            <!--</div>-->
            
            <!-- /carousel -->
            <!--//CONTSALDO-->
            <div class="row" style="padding-top: 20px;">
                <div class="col-lg-7">
                    <div class="panel panel-primary" id="panel-ordenes">
                        <div class="panel-heading" id="heading-panel-ordenes">
                            <i class="fa fa-bar-chart-o fa-fw"></i>Mis Ordenes
                            <div class="pull-right">
                            </div>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div id="no-more-tables">                            
                                <table class="table" style="margin-bottom: 0px;">
                                    <thead>
                                      <tr>
                                        <th>#</th>
                                        <th>Tipo</th>
                                        <th>Mercado</th>
                                        <th>Cantidad</th>
                                        <th>Precio</th>
                                        <th><em class="glyphicon glyphicon-cog"></em></th>
                                      </tr>
                                    </thead>
                                    <tbody id="tblorden">                                           
                                    </tbody>
                                </table>
                               
                            </div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                <!-- /.col-lg-8 -->
                <div class="col-lg-5">
                    <ul class="nav nav-tabs" id="pest-mone">
                            <li class="active">
                              <a style="color:black" data-toggle="tab" href="#mone-comp" onclick="$('#mone-vent').slideUp(0);$('#mone-comp').slideDown(0);">Compra</a>
                            </li>
                            <li>
                                <a style="color:black" data-toggle="tab" href="#mone-vent" onclick="$('#mone-comp').slideUp(0);$('#mone-vent').slideDown(0);">Venta</a>
                            </li>
                    </ul>
                    <div id="mone-comp" class="tab-pane fade active in">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-search">  Buscar moneda</i>
                                <input class="form-control input-sm" type="text"  onkeyup="cargarmonedas()" id="filtro-moneda">
                                <!--<select style="width:47%;display:inline"  class="form-control input-sm" type="text">-->
                                <!--    <option>Bittrex</option>-->
                                <!--    <option>Bitfinex</option>-->
                                <!--    <option>Poloniex</option>-->
                                <!--</select>-->
                            </div>
                            <!-- /.panel-heading -->
                            <div class="panel-body">
                                <b><p>* El precio en pesos es unicamente informativo</p></b>
                                <div class="list-group" id="listadomonedas">
                                    
                                </div>
                            </div>
                            <!-- /.panel-body -->
                        </div>
                    </div>
                    <div id="mone-vent" class="tab-pane fade">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-search">  Buscar moneda</i>
                                <input class="form-control input-sm" type="text" onkeyup="cargarmonedasventa()" id="filtro-moneda-venta">
                            </div>
                            <!-- /.panel-heading -->
                            <div class="panel-body">
                                <b><p>* El precio en pesos es unicamente informativo</p></b>
                                <div class="list-group" id="listadomonedasventa">
                                    
                                </div>
                            </div>
                            <!-- /.panel-body -->
                        </div>
                    </div>
                <!-- /.col-lg-4 -->
            </div>
            <!-- /.row -->
        </div>        <!-- /#page-wrapper -->

    </div>
        <div class="modal fade" id="modal-transaccion" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <!--    <div class="modal-header">-->
                <!--    </div>-->
                    <div class="modal-body">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true"><p onclick="javascript:clearInterval(idrecargaorderbuy);">X</p></span>
                        </button>
                            <ul class="nav nav-tabs" id="pest-merc"></ul>
                            <div class="tab-content" id="cuer-merc"></div>
                    </div>
                    <div class="modal-footer">
                        <!--<button type="button" class="btn btn-primary" onclick="javascript:clearInterval(idrecargaorderbuy);">Comprar</button>-->
                        <!--<button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="javascript:clearInterval(idrecargaorderbuy);">Cerrar</button>-->
                    </div>
                </div>
            </div>
        </div>
    
    
    
        <div class="modal fade" id="modal-venta" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <!--<div class="modal-header">-->
                        
                    <!--</div>-->
                    <div class="modal-body">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true"><p onclick="javascript:clearInterval(idrecargaorderbuy);">X</p></span>
                </button>
                        <ul class="nav nav-tabs" id="pest-mercv"></ul>
                        <div class="tab-content" id="cuer-mercv"></div>
                    </div>
                    <div class="modal-footer">
                        <!--<button type="button" class="btn btn-primary" onclick="javascript:clearInterval(idrecargaorderbuy);">Vender</button>-->
                        <!--<button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="javascript:clearInterval(idrecargaorderbuy);">Cerrar</button>-->
                    </div>
                </div>
            </div>
        </div>
    
    
        <div class="modal fade" id="modal-orden">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <!--<div class="modal-header">-->
                        
                    <!--</div>-->
                    <div class="modal-body" >
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true"><p>X</p></span>
                        </button>
                        
                        <div id="idverorden">
                            
                      </div>
                      
                    </div>
                    <div class="modal-footer">
                        
                    </div>
                </div>
            </div>
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
  
      <!-- Modal modificarorden-->
        <div class="modal fade" id="modal-modi-orden" role="dialog">
            <div class="modal-dialog modal-sm">    
          <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" onclick="clearTimeout(idsettimeglobal);">&times;</button>
                        <h4 class="modal-title">Modificar</h4>
                </div>
                <div class="modal-body">
                    
                    <div class="form-group">
                        <label for="txtmercadom">Mercado</label>
                        <input type="text" class="form-control" id="txtmercadom" name="txtmercadom" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="txtcantidadm">Cantidad</label>
                        <input type="text" class="form-control" id="txtcantidadm" name="txtcantidadm" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="txtpreciom">Precio</label>
                        <input type="text" class="form-control" id="txtpreciom" name="txtpreciom" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="txtgananciam">Ganancia</label>
                        <input type="range" min="0.5" step="0.01" class="inpinfmerc" id="slidergananciam" data-tipo="sg" oninput="cambiarinputsgp(this)" style="padding:0">
                        <input type="text" class="form-control" id="txtgananciam" data-tipo="sg" name="txtgananciam" oninput="solodecimal(this);cambiarslider(this)">
                        <input type="text" class="inp-prec-gp" data-type="m" id="txtpreciogananciam" oninput="solodecimal(this);cambiarpreciogp(this)">
                    </div>
                    
                    <div class="form-group">
                        <label for="txtperdidam">Perdida</label>
                        <input type="range" max="-0.5" step="0.01" class="inpinfmerc" id="sliderperdidam" data-tipo="sp" oninput="cambiarinputsgp(this)" style="padding:0">
                        <input type="text" class="form-control" id="txtperdidam" data-tipo="sp" name="txtperdidaperdidam" oninput="solodecimalnegativo(this);cambiarslider(this)">
                        <input type="text" class="inp-prec-gp" data-type="m" id="txtprecioperdidam" oninput="solodecimal(this);cambiarpreciogp(this)">
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-success" onclick="modificarorden();clearTimeout(idsettimeglobal);">Modificar</button>
                  <button type="button" class="btn btn-default" data-dismiss="modal" onclick="clearTimeout(idsettimeglobal);">Cerrar</button>
                </div>
              </div>      
            </div>
        </div>
      <!--//modal modificarorden-->
  
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
</body>
</html>