<?php
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . "payment" . DIRECTORY_SEPARATOR . "todopago" . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "TodopagoTransaccion.php");
define('LIBRARY_PROD', "https://forms.todopago.com.ar/resources/v2/TPBSAForm.min.js");
define('LIBRARY_TEST', "https://developers.todopago.com.ar/resources/v2/TPBSAForm.min.js");
global $messageStack;
if (isset($_GET['id'])) {
    global $customer_id;

    $resultConfig = $db->Execute('SELECT * FROM todo_pago_configuracion');

    //set url form js
    $endpoint = "";
    $ambiente = $resultConfig->fields['ambiente'];

    //set url external form library


    if ($ambiente == "test") {
        // developers
        $endpoint = LIBRARY_TEST;
    } else {
        // produccion
        $endpoint = LIBRARY_PROD;
    }

    $orderId = $_GET['id'];

    //RequestKey
    $tpTransaccion = new TodopagoTransaccion();
    $response = $tpTransaccion->getTransaction($orderId);
    $publicKey = $response['public_request_key'];

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
} else {

    $url = str_replace("&amp;", "&", zen_href_link('checkout_payment', '', 'SSL'));
    header('Location:' . $url);
    die;
}

?> 
<html> 
<head>
    <title>Formulario Híbrido</title>
    <meta charset="UTF-8">
    <script src="<?php echo $endpoint ?>"></script>
    <link rel="stylesheet" type="text/css" href="includes/modules/payment/todopago/formulario_tp.css">

</head>
<body class="contentContainer">
<?php if ($messageStack->size > 0)
?>
<div class="messageStack-header noprint">
    <?php
    echo $messageStack->output('header', $error, 'error');
    ?>
</div>
<div class="progress">
    <div class="progress-bar progress-bar-striped active" id="loading-hibrid">
    </div>
</div>
<div class="formuHibrido container-fluid" id="tpForm">
    <!-- row 0 -->
    <div class="static-row">
        <div class="bloque bloque-medium">
            <img src="https://portal.todopago.com.ar/app/images/logo.png" alt="todopago" id="todopago_logo">
        </div>
    </div>
    <!-- row 1 -->
    <div class="row">
        <div class="left-col">
            <div class="bloque bloque-medium float-left">
                <select id="formaPagoCbx" class="input button-medium"></select>
            </div>
            <div class="bloque bloque-big float-left loaded-form">
                <input id="numeroTarjetaTxt" class="input">
                <label id="numeroTarjetaLbl" for="numeroTarjetaTxt" class="advertencia"></label>
            </div>
        </div>
        <div class="right-col float-right loaded-form">
            <div class="bloque bloque-full float-right">
                <input id="nombreTxt" class="input button-big">
                <label id="nombreLbl" for="nombreTxt" class="advertencia"></label>
            </div>
        </div>
    </div>
    <div class="loaded-form">
        <!-- row 2 -->
        <div class="row" id="row-pei">
            <div class="left-col">
                <div class="bloque bloque-small float-left pei-cbx">
                    <input id="peiCbx" class="input">
                </div>
                <div class="bloque bloque-medium float-left">
                    <label id="peiLbl" for="peiCbx"></label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="left-col">
                <div class="bloque bloque-medium float-left">
                    <select id="medioPagoCbx" class="input"></select>
                </div>
                <div class="bloque bloque-medium float-left">
                    <select id="bancoCbx" class="input "></select>
                </div>
            </div>
            <div class="right-col">
                <div class="bloque bloque-small float-right">
                    <select id="tipoDocCbx" class="input "></select>
                    <label id="tipoDocLbl" for="tipoDocCbx" class="advertencia"></label>
                </div>
                <div class="bloque bloque-big float-right">
                    <input id="nroDocTxt" class="input button-big">
                    <label id="nroDocLbl" for="nroDocTxt" class="advertencia"></label>
                </div>
            </div>
        </div>
        <!-- row 3 -->
        <div class="row">
            <div class="left-col" id="codigo-col">
                <div class="bloque bloque-small float-left">
                    <select id="mesCbx" class="input "></select>
                    <label id="mesLbl" class="advertencia" for="mesCbx"></label>
                </div>
                <div class="bloque bloque-small float-left">
                    <select id="anioCbx" class="input"></select>
                    <label id="fechaLbl" class="advertencia" for="anioCbx"></label>
                </div>
                <div class="bloque bloque-small float-left">
                    <input id="codigoSeguridadTxt" class="input">
                    <label id="codigoSeguridadLbl" for="codigoSeguridadTxt" class="advertencia"></label>
                </div>
            </div>
            <div class="right-col">
                <div class="bloque bloque-full float-right">
                    <input id="emailTxt" class="input">
                    <label id="emailLbl" class="advertencia" for="emailTxt"></label>
                </div>
            </div>
        </div>
        <!-- row 4 -->
        <div class="row">
            <div class="left-col">
                <div class="bloque bloque-big float-left">
                    <select id="promosCbx" class="input "></select>
                </div>
            </div>
            <div class="right-col" id="tokenpei-row">
                <div class="bloque bloque-small float-right" id="tokenpei-bloque">
                    <input id="tokenPeiTxt" class="input ">
                    <label id="tokenPeiLbl" for="tokenPeiTxt" class="advertencia"></label>
                </div>
            </div>
        </div>
        <!-- row 5 -->
        <div class="static-row" id="promos-row">
            <div class="bloque bloque-medium float-left">
                <label id="promosLbl" for="promosCbx button-medium"></label>
            </div>
        </div>
    </div>
    <!-- row 6 -->
    <div class="static-row" id="botonera-row">
        <button id="MY_buttonPagarConBilletera"
                class="button button-payment-method button-primary float-right"></button>
        <button id="MY_buttonConfirmarPago"
                class="button button-payment-method button-primary float-right"></button>
    </div>
</div>
<script src="includes/modules/payment/todopago/jquery-3.2.1.min.js"></script>

<script type="text/javascript">

    var tpformJquery = $.noConflict();

    /************* SMALL SCREENS DETECTOR (?) *************/


    function detector() {
        console.log("Width: " + tpformJquery("#tpForm").width());
        if (tpformJquery("#tpForm").width() < 600) {
            console.log("inside");
            tpformJquery(".left-col").width('100%');
            tpformJquery(".right-col").width('100%');
            tpformJquery(".advertencia").css("height", "50px");
            tpformJquery(".row").css({
                "height": "120px",
                "width": "95%",
                "margin-bottom": "30px"
            });
            tpformJquery("#codigo-col").css("margin-bottom", "10px");
            tpformJquery("#row-pei").css("height", "100px");
        }
    }

    /************* CONFIGURACION DEL ROUTEO DE ZENCART *************/
    origin = document.location.origin;
    urlOri = document.location.pathname;
    index = "?main_page=";
    page = "checkout_success_todopago";
    paramOrder = "&Order=";
    paramOrderResult = <?php echo $orderId ?>;
    paramAnswer = "&Answer=";
    paramError = "&Error=";
    url = origin + urlOri + index + page + paramOrder + paramOrderResult + paramAnswer;
    urlError = origin + urlOri + index + page + paramOrder + paramOrderResult + paramError;

    /************* CONFIGURACION DEL API *********************/
    function loadScript(url, callback) {
        var script = document.createElement("script");
        script.type = "text/javascript";
        if (script.readyState) {  //IE
            script.onreadystatechange = function () {
                if (script.readyState === "loaded" || script.readyState === "complete") {
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {  //et al.
            script.onload = function () {
                callback();
            };
        }
        script.src = url;
        document.getElementsByTagName("head")[0].appendChild(script);
    }

    loadScript('<?php echo $endpoint ?>', function () {
        loader();
    });

    function loader() {
        tpformJquery("#loading-hibrid").css("width", "50%");
        setTimeout(function () {
            ignite();
        }, 100);
        setTimeout(function () {
            tpformJquery("#loading-hibrid").css("width", "100%");
        }, 1000);
        setTimeout(function () {
            initialFormaDePago();
            tpformJquery(".progress").hide('fast');
        }, 2000);
        setTimeout(function () {
            tpformJquery("#tpForm").fadeTo('fast', 1);
        }, 2200);
    }

    function ignite() {
        window.TPFORMAPI.hybridForm.initForm({
            callbackValidationErrorFunction: 'validationCollector',
            callbackBilleteraFunction: 'billeteraPaymentResponse',
            callbackCustomSuccessFunction: 'customPaymentSuccessResponse',
            callbackCustomErrorFunction: 'customPaymentErrorResponse',
            botonPagarId: 'MY_buttonConfirmarPago',
            botonPagarConBilleteraId: 'MY_buttonPagarConBilletera',
            modalCssClass: 'modal-class',
            modalContentCssClass: 'modal-content',
            beforeRequest: 'initLoading',
            afterRequest: 'stopLoading'
        });

        /************* SETEO UN ITEM PARA COMPRAR ******************/
        window.TPFORMAPI.hybridForm.setItem({
            publicKey: '<?php echo $publicKey; ?>',
            defaultNombreApellido: '<?php echo $user; ?>',
            defaultNumeroDoc: '',
            defaultMail: '<?php echo $mail; ?>',
            defaultTipoDoc: 'DNI'
        });
    }

    /************ FUNCIONES CALLBACKS ************/

    function validationCollector(parametros) {
        console.log("Validando los datos");
        console.log(parametros.field + " -> " + parametros.error);
        var input = parametros.field;
        if (input.search("Txt") !== -1) {
            label = input.replace("Txt", "Lbl");
        } else {
            label = input.replace("Cbx", "Lbl");
        }
        if (document.getElementById(label) !== null)
            document.getElementById(label).innerHTML = parametros.error;
    }

    function billeteraPaymentResponse(response) {
        console.log("Iniciando billetera");
        console.log(response.ResultCode + " -> " + response.ResultMessage);
        if (response.AuthorizationKey) {
            window.location.href = url + response.AuthorizationKey;
        } else {
            window.location.href = urlError + response.ResultMessage;
        }
    }

    function customPaymentSuccessResponse(response) {
        console.log("Success");
        console.log(response.ResultCode + " -> " + response.ResultMessage);
        window.location.href = url + response.AuthorizationKey;
    }

    function customPaymentErrorResponse(response) {
        console.log(response.ResultCode + " -> " + response.ResultMessage);
        if (response.AuthorizationKey) {
            window.location.href = url + response.AuthorizationKey;
        } else {
            window.location.href = urlError + response.ResultMessage;
        }
    }

    var formaDePago = document.getElementById("formaPagoCbx");
    formaDePago.addEventListener("click", function () {
        if (formaDePago.value === "1") {
            desplegar();
        } else {
            tpformJquery(".loaded-form").hide('fast');
        }
    });
    
    function initialFormaDePago() {
        if (formaDePago.value === "1") {
            desplegar();
        }
    }
    
    function desplegar(){
        detector();
        tpformJquery(".loaded-form").show('fast');
    }

    function initLoading() {
        console.log('Loading...');
    }

    function stopLoading() {
        console.log('Stop loading...');
        var peiCbx = tpformJquery("#peiCbx");
        var peiRow = tpformJquery("#row-pei");
        var tokenPeiRow = tpformJquery("#tokenpei-row");
        var tokenPeiTxt = tpformJquery("#tokenPeiTxt");
        var tokenPeiBloque = tpformJquery("#tokenpei-bloque");
        if (peiCbx.css('display') !== 'none') {
            peiRow.show('fast');
        } else {
            peiRow.hide('fast');
        }
        if (tokenPeiTxt.css('display') !== 'none') {
            tokenPeiBloque.css('height', "%100");
            tokenPeiRow.css('height', "%100");
        } else {
            tokenPeiBloque.css('height', "%0");
            tokenPeiRow.css('height', "%0");
        }
    }

</script>
</html>
