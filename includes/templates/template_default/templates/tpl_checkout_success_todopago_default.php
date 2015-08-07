<?php

use TodoPago\Sdk as Sdk;

include('includes/modules/payment/todopago/includes/Sdk.php');

$logo = HTTP_SERVER.''.DIR_WS_CATALOG.'includes/modules/payment/todopago/includes/todopago.jpg';
$amount = $orders->fields['order_total'];
$orden = $orders->fields['orders_id'];
$name = $orders->fields['customers_name'];
$text = '';
$bartype = '';


$params = '';

$sql = "select * from todo_pago_configuracion";
$res = $db->Execute($sql);

//second_step_todopago
if (!$res->EOF){    
    
    $row = $res->fields;
   
    $modo = $row["ambiente"]."_";
    $http_header = json_decode($row["authorization"],1);
    $http_header["user_agent"] = 'PHPSoapClient';
    $end_point =  $row[$modo."endpoint"];
    $security_code = $row[$modo."security"];
    $merchant =  $row[$modo."merchant"];  
    
    $connector = new Sdk($http_header, ($row["ambiente"] == 'test') ? 'test' : 'prod');
    $optionsGS = array('MERCHANT'=>$row[$modo."merchant"], 'OPERATIONID'=>$orden); 
    
    $requestKey = $_COOKIE['RequestKey'];
    $answerKey = $_GET['Answer'];
    
    $optionsGAA = array (
        'Security'   => $security_code,      
        'Merchant'   => $merchant,     
        'RequestKey' => $requestKey,       
        'AnswerKey'  => $answerKey    
    );
    
    $logDir = dirname(__FILE__).'/../../../modules/payment/todopago.log';            
    $logText = date('d-m-Y H:i:s').' - todopago - orden '.$orden.': ';
    $logText .= 'second step';
    error_log($logText."\n", 3, $logDir);

    error_log(date('d-m-Y H:i:s').' - todopago - orden '.$orden.': params GAA - parametros: '.json_encode($optionsGAA)."\n", 3, $logDir);

    $rta2 = $connector->getAuthorizeAnswer($optionsGAA);
    error_log(date('d-m-Y H:i:s').' - todopago - orden '.$orden.': response GAA - parametros: '.json_encode($rta2)."\n", 3, $logDir);

    if ($rta2['StatusCode']== -1){
        if ($rta2['Payload']['Answer']['PAYMENTMETHODNAME'] == 'PAGOFACIL' || $rta2['Payload']['Answer']['PAYMENTMETHODNAME']== 'RAPIPAGO' ){           
            $db->Execute('UPDATE '.TABLE_ORDERS.' SET orders_status = '.$row['estado_rechazada'].' WHERE orders_id = '.$orden);               
        }
        else{ 
            $db->Execute('UPDATE '.TABLE_ORDERS.' SET orders_status = '.$row['estado_aprobada'].' WHERE orders_id = '.$orden); 
        }
    }
    
    $status = $connector->getStatus($optionsGS);   

    if (!isset($status['Operations']['CARDNUMBER'])){
        if (isset($status['Operations']['BARCODE'])){   
            $barcode = $status['Operations']['BARCODE'];
            $barcode = '123456123123';
            
            if($barcode != ""){
                $bartype = $status['Operations']['BARCODETYPE'];
                $params = base64_encode('name='.$name.'&orden='.$orden.'&amount='.$amount.'&logo='.$logo.'&filetype=PNG&dpi=72&scale=2&rotation=0&font_family=Arial.ttf&font_size=8&text='.$barcode.'&thickness=30&checksum=&code='.$bartype.'');
            }
        }
    }

}//End if (!$res->EOF) 

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
?>
<!--eof -gift certificate- send or spend box-->

<h1 id="checkoutSuccessHeading"><?php echo "Muchas gracias por tu compra"; ?></h1>
<div id="checkoutSuccessOrderNumber"><?php echo 'Tu nro de orden es: ' . $zv_orders_id; ?></div>
<?php if (DEFINE_CHECKOUT_SUCCESS_STATUS >= 1 and DEFINE_CHECKOUT_SUCCESS_STATUS <= 2) { ?>
<div id="checkoutSuccessMainContent" class="content">
<?php
/**
 * require the html_defined text for checkout success
 */
echo "<label>Gracias por pagar con TodoPago!</label>";

if ($params != ''){
    //echo "Para imprimir tu cup&oacute;n haz click <a target='_blank' href='".HTTP_SERVER.'/'.DIR_WS_CATALOG."/extras/todopago/todo_pago_print.php?params=".$params."'>aqu&iacute; </a>";      
}

echo "<img width='350px' src='".$logo."' />";
?>
</div>
<?php } ?>
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