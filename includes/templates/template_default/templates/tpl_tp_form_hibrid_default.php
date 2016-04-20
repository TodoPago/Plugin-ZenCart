<?php
	require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."payment".DIRECTORY_SEPARATOR."todopago".DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."TodopagoTransaccion.php");

	if(isset($_GET['id'])){
		global $customer_id;

		$resultConfig = $db->Execute('SELECT * FROM todo_pago_configuracion');

		//set url form js
		$endpoint = "";
		$ambiente = $resultConfig->fields['ambiente'];

		//set url external form library
		$library = "resources/TPHybridForm-v0.1.js";
		  
		if($ambiente == "test"){
		  // developers	
		  $endpoint = "https://developers.todopago.com.ar/";
		}else{
			// produccion
			$endpoint = "https://forms.todopago.com.ar/";  
		}

		$endpoint .= $library;
		$orderId = $_GET['id'];

		//RequestKey
		$tpTransaccion = new TodopagoTransaccion();
    	$response = $tpTransaccion->getTransaction($orderId);		
		$publicKey= $response['public_request_key'];

		//orderID*/
		$orderId = $_GET['id'];
		
		//merchatid
        $merchantId = $resultConfig->fields['test_merchant'];
        
	    require('includes/classes/order.php');
	    $order = new order($orderId);

	    //name
	    $user = $order->customer['name'];
	    
		//email
		$mail = $order->customer['email_address'];
	}else{

		$url = str_replace("&amp;", "&", zen_href_link('checkout_payment', '', 'SSL'));
        header('Location:'.$url);
        die;
	}

?>
<html>
	<head>
		<title>Formulario Híbrido</title>
		<meta charset="UTF-8">
		<script src="<?php echo $endpoint ?>"></script>
		<link rel="stylesheet" type="text/css" href="includes/modules/payment/todopago/form_todopago.css">
		<script type="text/javascript">

			$(document).ready(function() {
				
				$("#formaDePagoCbx").change(function () {
				    if(this.value == 500 || this.value == 501){
				    	$(".spacer").hide();
				    }else{
				    	$(".spacer").show();
				    }
				})
			});

		</script>
	</head>
	<body class="contentContainer">
		<div id="security" data-securityKey="<?php echo $publicKey ?>"></div>
		<div id="user" data-user="<?php echo $user ?>"></div>
		<div id="mail" data-mail="<?php echo $mail ?>"></div>

		<div id="tp-form-tph">
			<div id="validationMessage"></div>
			<div id="tp-logo"></div>
			<div id="tp-content-form">
				<span class="tp-label">Elegí tu forma de pago </span>
				<div>
					<select id="formaDePagoCbx"></select>	
				</div>
				<div>
					<select id="bancoCbx"></select>
				</div>
				<div>
					<select id="promosCbx" class="left"></select>
					<label id="labelPromotionTextId" class="left tp-label"></label>
					<div class="clear"></div>
				</div>
				<div>
					<input id="numeroTarjetaTxt"/>
				</div>
				<div class="dateFields">
		            <input id="mesTxt" class="left">
		            <span class="left spacer">/</span>
		            <input id="anioTxt" class="left">
		            <div class="clear"></div>
		      	</div>
				<div>
					<input id="codigoSeguridadTxt" class="left"/>
					<label id="labelCodSegTextId" class="left tp-label"></label>
					<div class="clear"></div>
				</div>
				<div>
					<input id="apynTxt"/>
				</div>
				<div>
					<select id="tipoDocCbx"></select>
				</div>
				<div>
					<input id="nroDocTxt"/>	
				</div>
				<div>
					<input id="emailTxt"/><br/>
				</div>
				<div id="tp-bt-wrapper">
					<button id="MY_btnConfirmarPago" />
        			<button id="btnConfirmarPagoValida" class="tp-button">Pagar</button>
				</div>
			</div>	
		</div>

	</body>

	<script>
	    origin = document.location.origin;
	    urlOri = document.location.pathname;
	    index = "?main_page=";
	    page = "checkout_success_todopago";
	    paramOrder = "&Order=";
	    paramOrderResult = <?php echo $orderId ?>;
	    paramAnswer = "&Answer=";
	    url = origin+urlOri+index+page+paramOrder+paramOrderResult+paramAnswer;

		/************* CONFIGURACION DEL API ************************/
		window.TPFORMAPI.hybridForm.initForm({
			callbackValidationErrorFunction: 'validationCollector',
			callbackBilleteraFunction: 'billeteraPaymentResponse',
			botonPagarConBilleteraId: 'MY_btnPagarConBilletera',
			modalCssClass: 'modal-class',
			modalContentCssClass: 'modal-content',
			beforeRequest: 'initLoading',
			afterRequest: 'stopLoading',
			callbackCustomSuccessFunction: 'customPaymentSuccessResponse',
			callbackCustomErrorFunction: 'customPaymentErrorResponse',
			botonPagarId: 'MY_btnConfirmarPago',
			codigoSeguridadTxt: 'Codigo',
		});
		
		window.TPFORMAPI.hybridForm.setItem({
		    publicKey: $('#security').attr("data-securityKey"),
	        defaultNombreApellido: $('#user').attr("data-user"),
	        defaultMail: $('#mail').attr("data-mail"),
	        defaultTipoDoc: 'DNI'
		});
		//callbacks de respuesta del pago
		function validationCollector(response) {
			var errorMessage = "<div class='messageStackError'><img src='includes/templates/template_default/images/icons/error.gif' alt='Error' title='Error'>"+response.error+"</div>";
      		        $("#validationMessage").append(errorMessage);
		}
		function billeteraPaymentResponse(response) {
			window.location.href = url+response.AuthorizationKey;
		}
		function customPaymentSuccessResponse(response) {
			window.location.href = url+response.AuthorizationKey;
		}
		function customPaymentErrorResponse(response) {
			window.location.href = url;
		}
		function initLoading() {
		}
		function stopLoading() {
		}

		$( document ).ready(function() {
	           $("#btnConfirmarPagoValida").on("click", function(){
	           $('.messageStackError').remove();
	           $('#MY_btnConfirmarPago').click();
	      });
	    });
	</script>
</html>