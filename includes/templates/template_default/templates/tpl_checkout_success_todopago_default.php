<?php
require_once(dirname(__FILE__).'/../../../modules/payment/todopago/vendor/autoload.php');
include_once(dirname(__FILE__).'/../../../modules/payment/todopago/includes/todopago_ctes.php');
include_once(dirname(__FILE__).'/../../../modules/payment/todopago/includes/loggerFactory.php');
include_once(dirname(__FILE__).'/../../../modules/payment/todopago/includes/TodopagoTransaccion.php');
use TodoPago\Sdk as Sdk;
function getTpConfig() {
    global $db;
    $sql = "select * from " . TABLE_TP_CONFIGURACION;
    $res = $db->Execute($sql);
    if ($res->EOF) {
        error_log('No se cargo la configuracion.');
        return array();
    }
    return $res->fields;
}
function getCustomerId($order) {
    global $db;
    if (isset($_SESSION['customer_id'])) {
        return $_SESSION['customer_id'];
    }
    if (is_object($order)) {
        $gv_check = $db->Execute("select customers_id from " . TABLE_CUSTOMERS
            . " where customers_email_address = '" . $order->fields['customers_email_address']."'"
        );
        $_SESSION['customer_id'] = $gv_check->fields['customers_id'];
        return $_SESSION['customer_id'];
    }
}
function showError() {
    global $messageStack;
    $messageStack->add_session('header','No se pudo realizar la acci�n, intente nuevamente.', 'error');
    zen_redirect(zen_href_link(FILENAME_DEFAULT));
}
function _recollect_data($order) {
    global $todopagoTransaccion;
    $config = getTpConfig();
    $order_id = $order->fields['orders_id'];
    if (empty($config)) {
        return $config;
    }
    if ($order_id !== null && $todopagoTransaccion->_getStep($order_id) == TodopagoTransaccion::SECOND_STEP) {
        
        $config['order_id'] = $order_id;
        $logger = loggerFactory::createLogger(
            true,
            $config['ambiente'],
            getCustomerId($order),
            $order_id
        );
        $logger->info("second step");
        $logger->debug('todoPagoConfig: '.json_encode($config));
        if (empty($config['ambiente'])) {
            $logger->error("FALTA CONFIGURAR PLUGIN TODOPAGO");
            showError();
        }
        $modo = $config["ambiente"]."_";
        $http_header = json_decode($config["authorization"],1);
        $http_header["user_agent"] = 'PHPSoapClient';
        $security_code = isset($config[$modo."security"]) ? $config[$modo."security"] : '';
        $merchant = isset($config[$modo."merchant"]) ? $config[$modo."merchant"] : '';
        $connector = new Sdk($http_header, ($config["ambiente"] == 'test') ? 'test' : 'prod');
        $optionsGS = array('MERCHANT' => $config[$modo."merchant"], 'OPERATIONID' => $order_id);
        $transaction = $todopagoTransaccion->getTransaction($order_id);
        $requestKey = $transaction['request_key'];
        $answerKey = $_GET['Answer'];
        $optionsGAA = array (
            'Security'   => $security_code,
            'Merchant'   => $merchant,
            'RequestKey' => $requestKey,
            'AnswerKey'  => $answerKey
        );
        return compact('connector', 'optionsGS', 'optionsGAA', 'logger', 'config');
    }
    $logger->warn("No se puede entrar al second step porque ya se ha registrado una entrada exitosa en la tabla todopago_transaccion o el Order id no ha llegado correctamente");
    showError();
}
function callGAA($order) {
    $dataGAA = _recollect_data($order);
    if(empty($dataGAA)) {
        return false;
    }
    $logger = $dataGAA['logger'];
    $connector = $dataGAA['connector'];
    $logger->info("params GAA: ".json_encode($dataGAA['optionsGAA']));
    
    try {
        $rta2 = $connector->getAuthorizeAnswer($dataGAA['optionsGAA']);
    } catch (Exception $e) {
        $logger->error(json_encode($e));
        showError();
    }
    $logger->info("response GAA: ".json_encode($rta2));
    return array('rta' => $rta2, 'logger' => $logger, 'config' => $dataGAA['config'], 'optionsGAA' => $dataGAA['optionsGAA']);
}
function take_action($data) {
    global $db, $todopagoTransaccion;
    if ($data['rta']['StatusCode'] == TP_STATUS_OK) {
        $todopagoTransaccion->recordSecondStep($data['config']['order_id'], $data['optionsGAA'], $data['rta']);
        $answer = $data['rta']['Payload']['Answer'];
        if ($answer['PAYMENTMETHODNAME'] == 'PAGOFACIL' || $answer['PAYMENTMETHODNAME']== 'RAPIPAGO' ) {
            $db->Execute('UPDATE '.TABLE_ORDERS.' SET orders_status = '.$data['config']['estado_offline'].' WHERE orders_id = '.$data['config']['order_id']);
            if (empty($answer['ASSOCIATEDDOCUMENTATION'])) {
                $data['logger']->warn('No se mando la url del CUPON para el metodo de pago '. $answer["PAYMENTMETHODNAME"]);
            }
            $url = $answer['ASSOCIATEDDOCUMENTATION'];
            include zen_get_file_directory(DIR_FS_CATALOG.DIR_WS_TEMPLATES.'template_default/templates/', 'tpl_checkout_offline_todopago_default');
        } else {
            $db->Execute('UPDATE '.TABLE_ORDERS.' SET orders_status = '.$data['config']['estado_aprobada'].' WHERE orders_id = '.$data['config']['order_id']);
            
            echo '<h1 id="checkoutSuccessHeading">Muchas gracias por tu compra</h1>
            <div id="checkoutSuccessOrderNumber">Tu nro de orden es: ' . $data['config']['order_id'] . '</div>
            <div id="checkoutSuccessMainContent" class="content">
                <label>Gracias por pagar con TodoPago!</label>
            </div>';
        }
    } else {
        echo "<label>No se ha podido realizar el pago, por favor intente nuevamente.</label>";
    }
}
function second_step_todopago($order) {
    global $todopagoTransaccion;
    $todopagoTransaccion = new TodopagoTransaccion();
    $response = callGAA($order);
    if($response) {
        take_action($response);
    } else {
        showError();
    }
}
second_step_todopago($orders);
$logo = "http://www.todopago.com.ar/sites/todopago.com.ar/files/pluginstarjeta.jpg";
unset($_SESSION['sendto']);
unset($_SESSION['billto']);
unset($_SESSION['shipping']);
unset($_SESSION['payment']);
unset($_SESSION['comments']);
$_SESSION['cart']->reset(true);
?>
<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=checkout_success.<br />
 * Displays confirmation details after order has been successfully processed.
 *
 * @package templateSystem
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_checkout_success_default.php 16435 2010-05-28 09:34:32Z drbyte $
 */
?>
<div class="centerColumn" id="checkoutSuccess">
    <!--bof -gift certificate- send or spend box-->
    <?php
    // only show when there is a GV balance
    if ($customer_has_gv_balance ) {
        ?>
        <div id="sendSpendWrapper">
            <?php require($template->get_template_dir('tpl_modules_send_or_spend.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_send_or_spend.php'); ?>
        </div>
        <?php
    }
    echo "<img width='350px' src='".$logo."' />";
    ?>

    <!-- bof payment-method-alerts -->
    <?php
    if (isset($_SESSION['payment_method_messages']) && $_SESSION['payment_method_messages'] != '') {
        ?>
        <div class="content">
            <?php echo $_SESSION['payment_method_messages']; ?>
        </div>
        <?php
    }
    ?>
    <!-- eof payment-method-alerts -->
    <!--bof logoff-->
    <!--eof logoff-->
    <br class="clearBoth" />
    <!--bof -product notifications box-->
    <?php
    /**
     * The following creates a list of checkboxes for the customer to select if they wish to be included in product-notification
     * announcements related to products they've just purchased.
     **/
    if ($flag_show_products_notification == true) {
        ?>
        <fieldset id="csNotifications">
            <legend><?php echo 'Por favor avisarme sobre las novedades de estos productos'; ?></legend>
            <?php echo zen_draw_form('order', zen_href_link(FILENAME_CHECKOUT_SUCCESS, 'action=update', 'SSL')); ?>

            <?php foreach ($notificationsArray as $notifications) { ?>
                <?php echo zen_draw_checkbox_field('notify[]', $notifications['products_id'], true, 'id="notify-' . $notifications['counter'] . '"') ;?>
                <label class="checkboxLabel" for="<?php echo 'notify-' . $notifications['counter']; ?>"><?php echo $notifications['products_name']; ?></label>
                <br />
            <?php } ?>
            <div class="buttonRow forward"><?php echo zen_image_submit(BUTTON_IMAGE_UPDATE, BUTTON_UPDATE_ALT); ?></div>
            </form>
        </fieldset>
        <?php
    }
    ?>
    <!--eof -product notifications box-->



    <!--bof -product downloads module-->
    <?php
    if (DOWNLOAD_ENABLED == 'true') require($template->get_template_dir('tpl_modules_downloads.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_downloads.php');
    ?>
    <!--eof -product downloads module-->

</div>