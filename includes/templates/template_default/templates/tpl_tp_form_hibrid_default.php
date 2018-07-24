<?php
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . "payment" . DIRECTORY_SEPARATOR . "todopago" . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "TodopagoTransaccion.php");
define('LIBRARY_PROD', "https://forms.todopago.com.ar/resources/v2/TPBSAForm.min.js");
define('LIBRARY_TEST', "https://developers.todopago.com.ar/resources/v2/TPBSAForm.min.js");

global $messageStack;

if (isset($_GET['id'])) {
    global $customer_id;
    global $_SESSION;
    $method_payment = $_SESSION["payment"];
    
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

    //merchatid
    $merchantId = $resultConfig->fields['test_merchant'];
    require('includes/classes/order.php');
    $order = new order($orderId);

    //total amount
    $total_clean = str_replace("$","",$order->totals[2]['text']);
    $total_amount = number_format($total_clean, 2, ',', ' ');

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
    <link rel="stylesheet" type="text/css" href="includes/modules/payment/todopago/css/grid.css">
    <link rel="stylesheet" type="text/css" href="includes/modules/payment/todopago/css/queries.css">
    <link rel="stylesheet" type="text/css" href="includes/modules/payment/todopago/css/form_todopago.css">
    <script src="includes/modules/payment/todopago/jquery-3.2.1.min.js"></script>
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
<div class="header_info">
    <div class="bold">Total a pagar $<?php echo($total_amount) ?></div>
    <div>Elegí tu forma de pago</div>
</div>

<div class="tp_wrapper" id="tpForm">
    <section class="billetera_virtual_tp">
        <div class="tp_row">
            <div class="tp_col tp_span_1_of_2 texto_billetera_virtual">
                <p>Pag&aacute; con tu <strong>Billetera Virtual Todo Pago</strong></p>
                <p>y evit&aacute; cargar los datos de tu tarjeta</p>
            </div>
            <div class="tp_col tp_span_1_of_2">
                <button id="btn_Billetera" title="Pagar con Billetera" class="tp_btn tp_btn_sm">Pagar con Billetera</button>
            </div>
        </div>
    </section>

    <section class="billeterafm_tp">
        <div class="field field-payment-method">
            <label for="formaPagoCbx" class="text_small">Forma de Pago</label>
            <div class="input-box">
                <select id="formaPagoCbx" class="tp_form_control"></select>
                <span class="error" id="formaPagoCbxError"></span>
            </div>
        </div>
    </section>

    <section class="billetera_tp" >
        <div class="tp_row">
            <h3>
                Con tu tarjeta de cr&eacute;dito o d&eacute;bito
            </h3>
        </div>
        <div class="tp_row">
            <div class="tp_col tp_span_1_of_2 cardbox">
                <div class="tp_col tp_span_1_of_1 inputcc">
                    <label for="numeroTarjetaTxt" class="text_small">NÚMERO DE TARJETA</label>     
                    <input id="numeroTarjetaTxt" class="tp_form_control" maxlength="25" title="Número de Tarjeta" min-length="" autocomplete="off" >
                    <!--span class="error" id="numeroTarjetaTxtError"></span-->
                    <label id="numeroTarjetaLbl" class="error"></label>
                </div>
                <div class="logocc">
                    <img src="includes/modules/payment/todopago/img/empty.png" id="tp-tarjeta-logo" alt=""/>
                </div>                
            </div>
            <div class="tp_col tp_span_1_of_2">
                <label for="bancoCbx" class="text_small">BANCO</label>     
                <select id="bancoCbx" class="tp_form_control" placeholder="Selecciona banco"></select>
                <span class="error" id="bancoCbxError"></span>
            </div>
            <div class="tp_col tp_span_1_of_2 payment-method">
                <label for="medioPagoCbx" class="text_small">MEDIO DE PAGO</label>
                <select id="medioPagoCbx" class="tp_form_control" placeholder="Mediopago"></select>
                <span class="error" id="medioPagoCbxError"></span>
            </div>
        </div>
                
        <section class="tp_row" id="peibox">
            <div class="tp_row">
                <div class="tp_col tp_span_1_of_2 pei_wrapper">
                    <label id="peiLbl" for="peiCbx" class="text_small right">Pago con PEI</label>
                </div>
                <label class="switch" id="switch-pei">
                    <input type="checkbox" id="peiCbx">
                    <span class="slider round"></span>
                    <span id="slider-text"></span>
                </label>
            </div>
        </section>        
        
        <div class="tp_row">
            <div class="tp_col tp_span_1_of_2">
                <div class="tp_col tp_span_1_of_2">
                    <label for="mesCbx" class="text_small">VENCIMIENTO</label>

                    <div class="tp_row">
                        <div class="tp_col tp_span_1_of_2">
                            <select id="mesCbx" maxlength="2" class="tp_form_control" placeholder="Mes"></select>
                        </div>
                        <div class="tp_col tp_span_1_of_2">
                            <select id="anioCbx" maxlength="2" class="tp_form_control"></select>
                        </div>
                    </div>     
                    <label id="fechaLbl" class="left error"></label>     
                </div>
                
                <div class="tp_col tp_span_1_of_2">
                    <label for="codigoSeguridadTxt" class="text_small">CÓDIGO DE SEGURIDAD</label>
                    <input id="codigoSeguridadTxt" class="tp_form_control" maxlength="4" autocomplete="off"/>
                    <span class="error" id="codigoSeguridadTxtError"></span>
                    <label id="codigoSeguridadLbl" class="left tp-label spacer"></label>
                </div>
            </div>
            
            <div class="tp_col tp_span_1_of_2">
                <div class="tp_col tp_span_1_of_1 tp_col_dni">
                    <label for="tipoDocCbx" class="text_small">TIPO</label>
                    <select id="tipoDocCbx" class="tp_form_control"></select>
                </div>
                <div class="tp_col tp_span_1_of_2 tp_col_num">
                    <label for="NumeroDocCbx" class="text_small">NÚMERO</label>
                    <input id="nroDocTxt" maxlength="10" type="text" class="tp_form_control" placeholder="Número" autocomplete="off" />
                    <span class="error" id="nroDocLbl"></span>
                </div>
            </div>
        </div>
        
        <div class="tp_row">
            <div class="tp_col tp_span_1_of_2">
                <label for="nombreTxt" class="text_small">NOMBRE Y APELLIDO</label>
                <input id="nombreTxt" class="tp_form_control" autocomplete="off" placeholder="" maxlength="50">
                <span class="error" id="nombreLbl"></span>

            </div>
            <div class="tp_col tp_span_1_of_2">
                <label for="emailTxt" class="text_small">EMAIL</label>
                <input id="emailTxt" type="email" class="tp_form_control" placeholder="nombre@mail.com" data-mail="" autocomplete="off"/><br/>
                <span class="error" id="emailLbl"></span>
            </div>
        </div>    
        
        <div class="tp_row">
            <div class="tp_col tp_span_1_of_2">
                <label for="promosCbx" class="text_small">SELECCIONÁ LA CANTIDAD DE CUOTAS</label>
                <select id="promosCbx" class="tp_form_control"></select>
                <span class="error" id="promosCbxError"></span>
            </div>
             <div class="tp_col tp_span_1_of_2">
                <div class="clear"><label id="promosLbl" class="left"></label></div>
            </div>
        </div>

        <div class="tp_row">
            <div class="tp_col tp_span_1_of_2">
                <label id="tokenPeiLbl" for="tokenPeiTxt" class="info_pei"></label>
                <input id="tokenPeiTxt"/>
                <span class="error" id="peiTokenTxtError"></span>
            </div>    
        </div>
        
        <div class="tp_row">
            <div class="tp_col tp_span_2_of_2">
                <button id="btn_ConfirmarPago" class="tp_btn" title="Pagar" class="button"><span>Pagar</span></button>
            </div>
            <div class="tp_col tp_span_2_of_2">
                <div class="tp_col tp_span_2_of_2">
                    <div class="text_legals"> 
                        AL CONFIRMAR EL PAGO ACEPTO LOS <a href="https://www.todopago.com.ar/terminos-y-condiciones-comprador" target="_blank" title="Términos y Condiciones">TÉRMINOS Y CONDICIONES</a> DE TODO PAGO.
                    </div>
                </div>

            </div>
        </div>
    </section>
    
    <div class="tp_row">
        <div id="tp-powered">
            <span>Powered by</span><img id="tp-powered-img" src="includes/modules/payment/todopago/img/tp_logo_prod.png"/>
        </div>
    </div>

</div>

<script language="javascript">
    var tpformJquery = $.noConflict();
    var method_payment = '<?php echo $method_payment; ?>';
    var formaDePago = document.getElementById("formaPagoCbx");
    var medioDePago = document.getElementById('medioPagoCbx');
    var tarjetaLogo = document.getElementById('tp-tarjeta-logo');
    var poweredLogo = document.getElementById('tp-powered-img');
    var emptyImg = "includes/modules/payment/todopago/img/empty.png";
    var poweredLogoUrl = "includes/modules/payment/todopago/img/";
    var switchPei = tpformJquery("#switch-pei");
    var sliderText = tpformJquery("#slider-text");
    var peiCbx = tpformJquery("#peiCbx");

    //logo tp
    var idTarjetas = {
        'VISA': 'VISA',
        'VISA DEBITO': 'VISAD',
        'AMEX': 'AMEX',
        'DINERS': 'DINERS',
        'CABAL': 'CABAL',
        'CABAL DEBITO': 'CABALD',
        'MASTERCARD': 'MC',
        'MASTERCARD DEBITO': 'MCD',
        'NARANJA': 'NARANJA'         
    };   
    numeroTarjetaTxt.onblur = clearImage;

    function clearImage() {
        tarjetaLogo.src = emptyImg;
    }

    function cardImage(select) {
        var tarjeta = idTarjetas[select.text];

        if (tarjeta === undefined) {
            tarjeta = idTarjetas[select.textContent];
        }

        if (tarjeta !== undefined) {
            tarjetaLogo.src = "https://forms.todopago.com.ar/formulario/resources/images/" + tarjeta + '.png';
            tarjetaLogo.style.display = 'block';
            tarjetaLogo.width = 45;
            tarjetaLogo.height = 45;
        }
    }

    formaDePago.addEventListener('blur', function () {
        setTimeout(function () {
            peiLabelLoader();
        }, 200);
    });

    function peiLabelLoader() {
        //console.log(tpformJquery("#peiCbx").css('display'));
    }

    loadScript('<?php echo $endpoint ?>', function () {
        loader();
    });

    function loadScript(url, callback) {
        var script = document.createElement("script");
        var entorno = (url.indexOf('developers') === -1) ? 'prod' : 'dev';

        poweredLogo.src = poweredLogoUrl + 'tp_logo_' + entorno + '.png';

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
    
    function loader() {
        tpformJquery("#loading-hibrid").css("width", "50%");
        setTimeout(function () {
            ignite();
        }, 100);
        
        setTimeout(function () {
            tpformJquery("#loading-hibrid").css("width", "100%");
        }, 1000);
        
        setTimeout(function () {
            tpformJquery(".progress").hide('fast');
        }, 2000);
        
        setTimeout(function () {
            tpformJquery("#tpForm").fadeTo('fast', 1);
            if(method_payment == "todopagobilletera"){
                $("#btn_Billetera").click();
                $(".billetera_tp").hide();
            }
        }, 2200);
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

    //callbacks de respuesta del pago
    window.validationCollector = function (parametros) {
        console.log("Validando los datos");
        console.log(parametros.field + " -> " + parametros.error);
        var input = parametros.field;

        if (input.search("Txt") !== -1) {
            label = input.replace("Txt", "Lbl");
        } else {
            label = input.replace("Cbx", "Lbl");
        }
        if (document.getElementById(label) !== null){
            document.getElementById(label).innerHTML = parametros.error;
        }

        tpformJquery("#codigoSeguridadLbl").css("color","red");
    }

    window.billeteraPaymentResponse = function (response){
        console.log("Iniciando billetera");
        console.log(response.ResultCode + " -> " + response.ResultMessage);
        if (response.AuthorizationKey) {
            window.location.href = url + response.AuthorizationKey;
        } else {
            window.location.href = urlError + response.ResultMessage;
        }
    }

    window.customPaymentSuccessResponse = function (response){
        console.log("Success");
        console.log(response.ResultCode + " -> " + response.ResultMessage);
        if (response.AuthorizationKey) {
            window.location.href = url + response.AuthorizationKey;
        } else {
            window.location.href = urlError + response.ResultMessage;
        }
    }

    window.customPaymentErrorResponse = function (response) {
        console.log(response.ResultCode + " -> " + response.ResultMessage);
        if (response.AuthorizationKey) {
            window.location.href = url + response.AuthorizationKey;
        } else {
            window.location.href = urlError + response.ResultMessage;
        }
    }

    window.initLoading = function () {
      console.log("init");
      //tpformJquery("#codigoSeguridadLbl").html("");
      tpformJquery("#peibox").hide();
      cardImage(medioDePago);
    }

    window.stopLoading = function () {
        console.log('Stop loading...');
        tpformJquery("#peibox").hide();

        if(tpformJquery('#peiLbl').is(':empty')){
            tpformJquery("#peibox").hide("fast");
        }else{
            tpformJquery("label > p").each(function() {
                var clean_strip = tpformJquery(this).text().replace("<br>","");
                tpformJquery(this).html(clean_strip);
            });

            tpformJquery("#peibox").show("slow");
            activateSwitch(getInitialPEIState());
            switchPei.css("display", "block");
        }

        tpformJquery("#codigoSeguridadLbl").css("color","#909090");
    }
    
    // Verifica que el usuario no haya puesto para solo pagar con PEI y actúa en consecuencia
    function activateSwitch(soloPEI) {
        readPeiCbx();

        if (!soloPEI) {
            tpformJquery("#switch-pei").click(function () {
                 console.log("CHECKED", peiCbx.prop("checked"));

                if (peiCbx.prop("checked") === false) {
                    peiCbx.prop("checked", true);
                    switchPei.prop("checked", false);
                    peiCbx.prop("checked", false); 
                    sliderText.text("NO");
                    sliderText.css('transform', 'translateX(24px)');

                } else {
                    peiCbx.prop("checked", false);
                    switchPei.prop("checked", true);
                    peiCbx.prop("checked", true);  
                    sliderText.text("SÍ");
                    sliderText.css('transform', 'translateX(3px)');
                }

            });
        }

    }
    
    function readPeiCbx() {
        if (peiCbx.prop("checked", true)) {
            switchPei.prop("checked", true);
            sliderText.text("SÍ");
            sliderText.css('transform', 'translateX(3px)');
        } else {
            switchPei.prop("checked", true);
            sliderText.text("NO");
            sliderText.css('transform', 'translateX(24px)');
        }
    }

    function getInitialPEIState() {
        return (tpformJquery("#peiCbx").prop("disabled"));
    }

    tpformJquery('#peiLbl').bind("DOMSubtreeModified",function(){
        tpformJquery("#peibox").hide();
    });

    function ignite(){
        /************* CONFIGURACION DEL API ************************/
        window.TPFORMAPI.hybridForm.initForm({
            callbackValidationErrorFunction: 'validationCollector',
            callbackBilleteraFunction: 'billeteraPaymentResponse',
            callbackCustomSuccessFunction: 'customPaymentSuccessResponse',
            callbackCustomErrorFunction: 'customPaymentErrorResponse',
            botonPagarId: 'btn_ConfirmarPago',
            botonPagarConBilleteraId: 'btn_Billetera',
            modalCssClass: 'modal-class',
            modalContentCssClass: 'modal-content',
            beforeRequest: 'initLoading',
            afterRequest: 'stopLoading'
        });

        /************* SETEO UN ITEM PARA COMPRAR ************************/
        window.TPFORMAPI.hybridForm.setItem({
            publicKey: '<?php echo $publicKey; ?>',
            defaultNombreApellido: '<?php echo $user; ?>',
            defaultNumeroDoc: '',
            defaultMail: '<?php echo $mail; ?>',
            defaultTipoDoc: 'DNI'
        });
        $("#btn_Billetera").html("Iniciar Sesión");
    }

</script>
</html>
