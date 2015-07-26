<?php
/*
  $Id$
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2013 osCommerce
  Released under the GNU General Public License
*/

   
    require('includes/application_top.php');
    
    ?>
    <!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?> </title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script language="javascript" src="includes/menu.js"></script>
    <script language="javascript" src="includes/general.js"></script>
    <script type="text/javascript">
        <!--
        function init()
        {
        cssjsmenu('navbar');
        if (document.getElementById)
        {
        var kill = document.getElementById('hoverJS');
        kill.disabled = true;
        }
        }
        // -->
    </script>
</head>
<body onLoad="init()">
    
    <?php
    global $db;
 
    require(DIR_WS_INCLUDES . 'header.php');

    $mensaje =""; 
    
    if (isset($_POST["submit"])){
    
        unset($_POST["submit"]);
        
        $query = "update todo_pago_configuracion set ";
        
        foreach($_POST as $key=>$value){
        $query .= $key. "='".$value."',";
        
        }
        
        $query = trim($query,",");
   
        $db->Execute($query);
        
        $mensaje = "La configuracion se guardo correctamente";
    }
    
    $sql = "select * from todo_pago_configuracion";
    $res = $db->Execute($sql);
    $row = $res->fields;
   
?>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_SERVER.'/'.DIR_WS_CATALOG?>/includes/modules/payment/todopago/todopago.css"/>
<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_SERVER.'/'.DIR_WS_CATALOG?>/includes/modules/payment/todopago/includes/datatables/jquery.dataTables.css"/>
<!-- jQuery -->
<script type="text/javascript" charset="utf8" src="<?php echo HTTP_SERVER.'/'.DIR_WS_CATALOG?>/includes/modules/payment/todopago/includes/datatables/jquery-1.10.2.min.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<!-- DataTables -->
<script type="text/javascript" charset="utf8" src="<?php echo HTTP_SERVER.'/'.DIR_WS_CATALOG?>/includes/modules/payment/todopago/includes/datatables/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8" src="<?php echo HTTP_SERVER.'/'.DIR_WS_CATALOG?>/includes/modules/payment/todopago/includes/datatables/dataTables.tableTools.min.js"></script>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td>
        <table style="margin-left: 35px;" border="0" width="100%" cellspacing="0" cellpadding="2" height="40">
          <tr>
            <td class="pageHeading">TodoPago v1.0 | Configuraci&oacute;n </td>
            <td class="pageHeading">
             
            </td>
            <td class="smallText" align="right"></td>
          </tr> 
          <tr>
            <td>
            <img src="<?php echo HTTP_SERVER.'/'.DIR_WS_CATALOG?>/includes/modules/payment/todopago/includes/todopago.jpg" />
            </td>
          </tr>
        </table>
        </td>
      </tr>
      <tr>
      <td>  <?php echo($mensaje);?></td></tr>
      <tr>
        <td>
        
<div id="todopago">
  <ul class="secciones-todopago-config">
    <li><a class="tabs-todopago" todopago="#config">Configuracion</a></li>
    <li><a class="tabs-todopago" todopago="#prod">Productos</a></li>
    <li><a class="tabs-todopago" todopago="#orden">Ordenes</a></li>
  </ul>
  <div id="config">  
        <form action="" method="post">
        <div class="input-todopago">
        <label>Authorization HTTP</label>
        <input type="text" value='<?php echo(isset($row["authorization"])?$row["authorization"]:"")?>' placeholder="Authorization HTTP" name="authorization"/>
        </div>

<?php
$segmento = (isset($row["segmento"])?$row["segmento"]:"");
?>
<div class="input-todopago">
 <label>Segmento del Comercio</label>
<select name="segmento">
<option value="">Seleccione</option>
<option value="retail" <?php echo($segmento=="retail"?"selected":"")?>>Retail</option>
</select>
</div>
<?php
$canal = (isset($row["canal"])?$row["canal"]:"");
?>

<div class="input-todopago">
 <label>Canal de Ingreso del Pedido</label>
<select name="canal">
<option value="">Seleccione</option>
<option value="web" <?php echo($canal=="web"?"selected":"")?>>Web</option>
<option value="mobile" <?php echo($canal=="mobile"?"selected":"")?>>Mobile</option>
<option value="telefonica" <?php echo($canal=="telefonica"?"selected":"")?>>Telefonica</option>
</select>
</div>
<?php
$ambiente = (isset($row["ambiente"])?$row["ambiente"]:"");
?>

<div class="input-todopago">
<label>Modo Test o Producci&oacute;n</label>
<select name="ambiente">
<option value="">Seleccione</option>
<option value="test" <?php echo($ambiente=="test"?"selected":"")?>>Developers</option>
<option value="production" <?php echo($ambiente=="production"?"selected":"")?>>Produccion</option>
</select>
</div>


<div class="input-todopago">
<label>Dead Line</label>
<input type="text" value="<?php echo(isset($row["deadline"])?$row["deadline"]:"")?>" placeholder="Dead Line" name="deadline"/>
</div>

<div class="subtitulo-todopago">AMBIENTE TEST</div>

<div class="input-todopago">
<label>ID Site Todo Pago</label>
<input type="text" value="<?php echo(isset($row["test_merchant"])?$row["test_merchant"]:"")?>" placeholder="ID Site Todo Pago" name="test_merchant"/>
</div>

<div class="input-todopago">
<label>Security Code</label>
<input type="text" value="<?php echo(isset($row["test_security"])?$row["test_security"]:"")?>" placeholder="Security Code" name="test_security"/>
</div>
<div class="subtitulo-todopago">AMBIENTE PRODUCCION</div>

<div class="input-todopago">
<label>ID Site Todo Pago</label>
<input type="text" value="<?php echo(isset($row["production_merchant"])?$row["production_merchant"]:"")?>" placeholder="ID Site Todo Pago" name="production_merchant"/>
</div>

<div class="input-todopago">
<label>Security Code</label>
<input type="text" value="<?php echo(isset($row["production_security"])?$row["production_security"]:"")?>" placeholder="Security Code" name="production_security"/>
</div> 
<div class="subtitulo-todopago">ESTADOS DE LA ORDEN</div>
<div class="input-todopago">
<?php

$sql = "select  orders_status_id,orders_status_name from ".TABLE_ORDERS_STATUS. " where language_id = 1";
$res = $db->Execute($sql);
 
while (!$res->EOF){ 
    
    $opciones[$res->fields["orders_status_id"]] = $res->fields['orders_status_name'];
    $res->MoveNext();
}

?>
<label>Estado cuando la transaccion ha sido iniciada</label>
<select name="estado_inicio">
<?php
foreach($opciones as $key=>$value){
    $selected = "";
    if ($key == $row["estado_inicio"]) $selected ="selected"
?>
    
    <option <?php echo($selected)?> value="<?php echo($key)?>"><?php echo($value)?></option>    
<?php
}
?>

</select>
</div>

<div class="input-todopago">
<label>Estado cuando la transaccion ha sido aprobada</label>
<select name="estado_aprobada">
<?php
foreach($opciones as $key=>$value){
     $selected = "";
    if ($key == $row["estado_aprobada"]) $selected ="selected"
    
?>

    <option <?php echo($selected)?> value="<?php echo($key)?>"><?php echo($value)?></option>    
<?php
}
?>

</select>

</div>

<div class="input-todopago">
<label>Estado cuando la transaccion ha sido rechazada</label>
<select name="estado_rechazada">
<?php
foreach($opciones as $key=>$value){
     $selected = "";
    if ($key == $row["estado_rechazada"]) $selected ="selected"
?>
    <option <?php echo($selected)?> value="<?php echo($key)?>"><?php echo($value)?></option>    
<?php
}
?>

</select>
</div>

<div class="input-todopago">
<label>Estado cuando la transaccion ha sido offline</label>
<select name="estado_offline">
<?php
foreach($opciones as $key=>$value){
     $selected = "";
    if ($key == $row["estado_offline"]) $selected ="selected"
?>
    <option <?php echo($selected)?> value="<?php echo($key)?>"><?php echo($value)?></option>    
<?php
}
?>

</select>
</div>
<input  type="submit" name="submit" value="Guardar Datos"/>
</form>
</div>
<div id="prod">
<form action="" method="POST">



<table id="data-table"  style="width:100%">

<thead>

<tr>

<td>ID</td><td>Nombre</td><td>Codigo de Producto</td><td>Fecha Evento</td><td>Tipo de Envio</td><td>Tipo de Servicio</td><td>Tipo de Delivery</td><td>Editar</td>

</tr> 

</thead>

<tbody>
<?php

$sql = "select p.products_id,pd.products_name,p.products_model from ". TABLE_PRODUCTS. " as p inner join ".TABLE_PRODUCTS_DESCRIPTION." as pd on p.products_id = pd.products_id where language_id=1";
$res = $db->Execute($sql);
// echo $sql;
$i =0;
while (!$res->EOF){ 
    
    $sql = "select * from todo_pago_atributos where product_id=".$res->fields["products_id"];
    $res2 = $db->Execute($sql);
    $tipoDelivery = "";
    $tipoEnvio = "";
    $tipoServicio = "";
    $codigoProducto = "";
    $diasEvento = "";
    if (!$res2->EOF){
        if ($res2->fields["CSITPRODUCTCODE"] != "") $codigoProducto = $res2->fields["CSITPRODUCTCODE"];
        if ($res2->fields["CSMDD33"] != "") $diasEvento = $res2->fields["CSMDD33"];
        if ($res2->fields["CSMDD34"] != "") $tipoEnvio = $res2->fields["CSMDD34"];
        if ($res2->fields["CSMDD28"] != "") $tipoServicio = $res2->fields["CSMDD28"];
        if ($res2->fields["CSMDD31"] != "") $tipoDelivery = $res2->fields["CSMDD31"];
        
    }
    $i=$res->fields["products_id"];
 echo "<tr><td>".$res->fields["products_id"]."</td><td id='nombre".$i."'>".$res->fields["products_name"]."</td><td id='codigo".$i."'>".$codigoProducto."</td><td id='evento".$i."'>".$diasEvento."</td><td id='envio".$i."'>".$tipoEnvio."</td><td id='servicio".$i."'>".$tipoServicio."</td><td id='delivery".$i."'>".$tipoDelivery."</td><td class='editar' id='".$i."'>Editar</td></tr>";
 
 $res->MoveNext();
}
    
?>
</tbody>
</table> 
<div id="config-producto-todopago">
<div class="close-todopago">x</div>
    <table>
    <tr>
        <td align="center" id="titulo">Titulo del producto</td>
    </tr>
    <tr>
        <td>
        <table>
        <tr><td>Codigo de Producto</td><td><select id="codigo_producto"><option value="">- None -</option><option value="adult_content">adult_content</option><option value="coupon">coupon</option><option value="default">default</option><option value="electronic_good" selected="selected">electronic_good</option><option value="electronic_software">electronic_software</option><option value="gift_certificate">gift_certificate</option><option value="handling_only">handling_only</option><option value="service">service</option><option value="shipping_and_handling">shipping_and_handling</option><option value="shipping_only">shipping_only</option><option value="subscription">subscription</option></select></td></tr>
        <tr><td>Dias para el Evento</td><td><input id="dias_evento" type="text" value=""/></td></tr>
        <tr><td>Tipo de Envio</td><td><select id="envio_producto"><option value="">- None -</option><option value="Pickup">Pickup</option><option value="Email">Email</option><option value="Smartphone">Smartphone</option><option value="Other">Other</option></select></td></tr>
        <tr><td>Tipo de Delivery</td><td><select id="delivery_producto"><option value="">- None -</option><option value="WEB Session">WEB Session</option><option value="Email">Email</option><option value="SmartPhone">SmartPhone</option></select></td></tr>
        <tr><td>Tipo de Servicio</td><td><select id="servicio_producto"><option value="">- None -</option><option value="Luz">Luz</option><option value="Gas">Gas</option><option value="Agua">Agua</option><option value="TV">TV</option><option value="Cable">Cable</option><option value="Internet">Internet</option><option value="Impuestos">Impuestos</option></select></td></tr>
        <tr><td colspan="2"><input type="hidden" value="" id="id_producto"/><input id="guardar" type="button" value="Guardar"/></td></tr>
        </table>
        </td>
    </tr>
    </table>
</div>
<script type="text/javascript">
$(document).ready(function(){
    
    $('.close-todopago').click(function(){
        $('#config-producto-todopago').hide();
    });
    
    $("#guardar").click(function(){
        
        $('#config-producto-todopago').hide();
        $.post( "<?php echo HTTP_SERVER.'/'.DIR_WS_CATALOG?>/todo_pago_config_ajax.php", 
            { CSITPRODUCTCODE:$("#codigo_producto").val(), 
              CSMDD33: $("#dias_evento").val(),  
              CSMDD34: $("#envio_producto").val() ,
              CSMDD28: $("#servicio_producto").val() ,
              CSMDD31: $("#delivery_producto").val() ,
              product_id: $("#id_producto").val()
            }
      ).done(function(){
              
                $("#codigo"+$("#id_producto").val()).html($("#codigo_producto").val());
                $("#evento"+$("#id_producto").val()).html($("#dias_evento").val());
                $("#envio"+$("#id_producto").val()).html($("#envio_producto").val());
                $("#delivery"+$("#id_producto").val()).html($("#delivery_producto").val());
                $("#servicio"+$("#id_producto").val()).html($("#servicio_producto").val());            
            }).fail(function(){
              
                $("#codigo"+$("#id_producto").val()).html($("#codigo_producto").val());
                $("#evento"+$("#id_producto").val()).html($("#dias_evento").val());
                $("#envio"+$("#id_producto").val()).html($("#envio_producto").val());
                $("#delivery"+$("#id_producto").val()).html($("#delivery_producto").val());
                $("#servicio"+$("#id_producto").val()).html($("#servicio_producto").val());            
            }); 
        
    });
    
    
    $(".editar").click(function(){
        
        $('#config-producto-todopago').hide();
        $('#config-producto-todopago').show();
        $("#id_producto").val($(this).attr("id"));
        $("#titulo").html($("#nombre"+$(this).attr("id")).html());
        $("#codigo_producto").val($("#codigo"+$(this).attr("id")).html());
        $("#dias_evento").val($("#evento"+$(this).attr("id")).html());
        $("#envio_producto").val($("#envio"+$(this).attr("id")).html());
        $("#delivery_producto").val($("#delivery"+$(this).attr("id")).html());
        $("#servicio_producto").val($("#servicio"+$(this).attr("id")).html());

});

$('#data-table').dataTable(
                {bFilter: true, 
                bInfo: true,
                bPaginate :true,
                
                });
  })  

</script>


</form>

</div>
<div id="orden">
<table id="orders-table"  style="width:100%">

<thead>

<tr>

<td>ID</td><td>Nombre</td><td>Telefono</td><td>Email</td><td>Fecha</td><td>Status</td><td>Editar</td>

</tr> 

</thead>

<tbody>
<?php

$sql = "select orders_id,customers_name,customers_telephone,customers_email_address,date_purchased,orders_status_name from ". TABLE_ORDERS. " as o inner join ". TABLE_ORDERS_STATUS. " as os on os.orders_status_id = o.orders_status order by date_purchased desc";
//echo $sql;
$res = $db->Execute($sql);
// echo $sql;
$i =0;
while (!$res->EOF){ 
    
 echo "<tr><td>".$res->fields["orders_id"]."</td><td>".$res->fields["customers_name"]."</td><td>".$res->fields["customers_telephone"]."</td><td>".$res->fields["customers_email_address"]."</td><td>".$res->fields["date_purchased"]."</td><td>".$res->fields["orders_status_name"]."</td><td class='status' id='".$res->fields["orders_id"]."' style='cursor:pointer'>Ver Status</td></tr>";
 
 
 $res->MoveNext();   
}
    

?>
</tbody>
</table> 
<div id="status-orders">
<div class="close-status-todopago">x</div>
<div id="status">

</div>
</div>

</div>
</div>

<script>
$(document).ready(function(){

$('.close-status-todopago').click(function(){
        $('#status-orders').hide();
    });
    
$(".status").click(function(){
        
        $('#status-orders').hide();
        $.post( "<?php echo HTTP_SERVER.'/'.DIR_WS_CATALOG?>/todo_pago_status_ajax.php", 
            { order_id:$(this).attr("id"), 
              
            }, function( data ) {
                
                $("#status").html(data);
                $('#status-orders').show();
            })});

   
$('#orders-table').dataTable(
                {bFilter: true, 
                bInfo: true,
                bPaginate :true,
                });
  })  

</script>
</td>
          </tr>
        </table></td>
      </tr>
    </table>
  <script>
  $(document).ready(function(){
 
    $("#prod").hide();
    $("#orden").hide();

  $(".tabs-todopago").each(function(){
    $(this).css("cursor","pointer");
    
  })
  $(".tabs-todopago").click(function(){
    $("#config").hide();
       $("#prod").hide();
    $("#orden").hide();  
  
    $(""+$(this).attr("todopago")+"").show();
  })
  
    })  
  </script>
<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');