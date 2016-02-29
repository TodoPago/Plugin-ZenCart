<?php

use TodoPago\Sdk as Sdk;

include_once(dirname(__FILE__).'/../../../modules/payment/todopago/includes/Sdk.php');
include_once(dirname(__FILE__).'/../../../modules/payment/todopago/includes/logger.php');

define('TP_STATUS_OK', -1);

function _recollect_data($order_id) {
    global $db;

    $sql = "select * from todo_pago_configuracion";
    $res = $db->Execute($sql);

    if ($res->EOF) {
        error_log('No se cargo la configuracion.');
        return array();
    }

    $config = $res->fields;

    $modo = $config["ambiente"]."_";
    $http_header = json_decode($config["authorization"],1);
    $http_header["user_agent"] = 'PHPSoapClient';
    $end_point =  $config[$modo."endpoint"];
    $security_code = $config[$modo."security"];
    $merchant =  $config[$modo."merchant"];

    $connector = new Sdk($http_header, ($config["ambiente"] == 'test') ? 'test' : 'prod');
    $optionsGS = array('MERCHANT'=>$config[$modo."merchant"], 'OPERATIONID'=>$order_id);

    $requestKey = $_COOKIE['RequestKey'];
    $answerKey = $_GET['Answer'];

    $optionsGAA = array (
        'Security'   => $security_code,
        'Merchant'   => $merchant,
        'RequestKey' => $requestKey,
        'AnswerKey'  => $answerKey
    );
    $config['order_id'] = $order_id;

    $logger = createLogger($modo, $order_id);
    $logger->debug('todoPagoConfig: '.json_encode($config));

    return compact('connector', 'optionsGS', 'optionsGAA', 'logger', 'config');
}

function createLogger($modo, $order_id) {

    $tplogger = new TodoPagoLogger();
    $tplogger->setPhpVersion(phpversion());
    $tplogger->setCommerceVersion(zend_version());
    $tplogger->setPluginVersion('1.4.0');
    $tplogger->setEndPoint($modo);
    $tplogger->setCustomer('customers_id');
    $tplogger->setOrder($order_id);

    return $tplogger->getLogger(true);
}

function callGAA($order_id) {

    $dataGAA = _recollect_data($order_id);
    if(empty($dataGAA)) {
        return false;
    }

    $logger = $dataGAA['logger'];
    $connector = $dataGAA['connector'];

    $logger->info('second step');
    $logger->info("params GAA: ".json_encode($dataGAA['optionsGAA']));
    $rta2 = $connector->getAuthorizeAnswer($dataGAA['optionsGAA']);
    $logger->info("response GAA: ".json_encode($rta2));

    return array('rta' => $rta2, 'logger' => $logger, 'config' => $dataGAA['config']);
}

function take_action($data) {
    global $db;
    if ($data['rta']['StatusCode'] == TP_STATUS_OK) {
        $anwer = $data['rta']['Payload']['Answer'];
        if ($anwer['PAYMENTMETHODNAME'] == 'PAGOFACIL' || $anwer['PAYMENTMETHODNAME']== 'RAPIPAGO' ) {
            $db->Execute('UPDATE '.TABLE_ORDERS.' SET orders_status = '.$data['config']['estado_rechazada'].' WHERE orders_id = '.$data['config']['order_id']);
        }
        else{
            $db->Execute('UPDATE '.TABLE_ORDERS.' SET orders_status = '.$data['config']['estado_aprobada'].' WHERE orders_id = '.$data['config']['order_id']);
        }

        echo '<h1 id="checkoutSuccessHeading">Muchas gracias por tu compra</h1>
        <div id="checkoutSuccessOrderNumber">Tu nro de orden es: ' . $data['config']['order_id'] . '</div>
        <div id="checkoutSuccessMainContent" class="content">
            <label>Gracias por pagar con TodoPago!</label>
        </div>';

    } else {
        echo "<label>No se ha podido realizar el pago, por favor intente nuevamente.</label>";
    }
}



function second_step_todopago($order_id) {

    $response = callGAA($order_id);
    if($response) {
        take_action($response);
    } else {
        echo 'No se pudo realizar la acción, consulte con el administrador.';
    }
}

$logo = HTTP_SERVER.''.DIR_WS_CATALOG.'includes/modules/payment/todopago/includes/todopago.jpg';
$order_id = $orders->fields['orders_id'];

second_step_todopago($order_id);


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