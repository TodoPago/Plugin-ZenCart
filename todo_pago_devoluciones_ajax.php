<?php
use TodoPago\Sdk as Sdk;

require('includes/application_top.php');
require('includes/modules/payment/todopago/includes/todopago_ctes.php');
require('includes/modules/payment/todopago/includes/loggerFactory.php');
//require_once dirname(__FILE__).'/includes/modules/payment/todopago/includes/Sdk.php';
require_once dirname(__FILE__) . '/includes/modules/payment/todopago/vendor/autoload.php';

global $db;

$order_id = isset($_POST['order_id']) ? $_POST['order_id'] : null;
$amount = isset($_POST['amount']) ? $_POST['amount'] : null;
$refund_type = isset($_POST['refund_type']) ? $_POST['refund_type'] : null;

if ($order_id != null && $refund_type != null) {

    //get configuration data
    $config = $db->Execute('SELECT * FROM ' . TABLE_TP_CONFIGURACION);
    $config = $config->fields;

    $http_header = json_decode($config["authorization"], 1);
    $http_header["user_agent"] = 'PHPSoapClient';

        $tplogger = loggerFactory::createLogger(
            false,
            $config['ambiente'],
	    null,
            $order_id
        );

    $connector = new Sdk($http_header, ($config["ambiente"] == 'test') ? 'test' : 'prod');

    //get requestKey y amountGAA
    $sql = "SELECT request_key,response_GAA FROM todopago_transaccion WHERE id_orden = " . $order_id;
    $response = $db->Execute($sql);
    $Gaa = $response->fields;
    $requestKey = $Gaa['request_key'];
    $responseGaa = json_decode($Gaa['response_GAA'], 1);
    $amountGAA = floatval($responseGaa['Payload']['Request']['AMOUNT']);

    if ($refund_type == "total") {
        //anulacion
        $options = array(
            "Security" => $config['test_security'],
            "Merchant" => $config['test_merchant'],
            "RequestKey" => $requestKey
        );

	$tplogger->info("Devolucion total");
		$tplogger->info("Parametros: " . json_encode($options));
		$voidResponse = $connector->voidRequest($options);
		$tplogger->info("Respuesta: " . json_encode($voidResponse));

        if ($voidResponse['StatusCode'] == 2011) {

            //Id status Total Refund
            $sql = "SELECT orders_status_id FROM " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Refund'";
            $response = $db->Execute($sql);
            $new_order_status = $response->fields['orders_status_id'];

            if ($new_order_status == 0) $new_order_status = 1;

            //get void total
            $sql = "SELECT * FROM " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'";
            $response = $db->Execute($sql);
            $todoPagoVoid = $response->fields;

            //add history
            $sql_data_array = array('orders_id' => (int)$order_id,
                'orders_status_id' => (int)$new_order_status,
                'date_added' => 'now()',
                'comments' => 'Devolucion total, monto devuelto: $' . (string)$todoPagoVoid['order_total'],
                'customer_notified' => 0
            );

            zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

            //update order
            $db->Execute("update " . TABLE_ORDERS . "
                      set orders_status = '" . (int)$new_order_status . "'
                      where orders_id = '" . (int)$order_id . "'");

            $response = "La anulacion se realizo satisfactoriamente";

        } else {
            $response = "Ocurrio un error en la anulacion, vuelva a intentarlo en unos minutos. Codigo de error: " . $voidResponse['StatusCode'] . ' - ' . $voidResponse['StatusMessage'];
        }

    } elseif ($refund_type == "parcial") {
        //devolucion parcial
        $options = array(
            "Security" => $config['test_security'],
            "Merchant" => $config['test_merchant'],
            "RequestKey" => $requestKey,
            "AMOUNT" => $amount
        );
        if ($amount > $amountGAA)
            $response = "La devolución no puede ser mayor al valor real de la orden.";
        else {
		$tplogger->info("Devolucion parcial");
		$tplogger->info("Parametros: " . json_encode($options));
		$refResponse = $connector->returnRequest($options);
		$tplogger->info("Respuesta: " . json_encode($refResponse));
            if ($refResponse['StatusCode'] == 2011) {

                //Id status Total Refund
                $sql = "SELECT orders_status_id FROM " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Partial Refund'";
                $response = $db->Execute($sql);
                $new_order_status = $response->fields['orders_status_id'];

                if ($new_order_status == 0) $new_order_status = 1;

                // Success, so save the results
                $sql_data_array = array('orders_id' => $order_id,
                    'orders_status_id' => (int)$new_order_status,
                    'date_added' => 'now()',
                    'comments' => 'Devolucion parcial, monto devuelto: $' . (string)$amount,
                    'customer_notified' => 0
                );
                zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

                $db->Execute("update " . TABLE_ORDERS . "
		                  set orders_status = '" . (int)$new_order_status . "'
		                  where orders_id = '" . (int)$order_id . "'");

                $response = "La devolución se realizó satisfactoriamente";
            } else {
                $response = "Ocurrió un error en la devolución, vuelva a intentarlo en unos minutos. Código de error: " . $refResponse['StatusCode'] . ' - ' . $refResponse['StatusMessage'];
            }
        }
    }

} else {

    $response = "No se pudo realizar la operacion, se requiere id y monto de la orden";
}
echo ($response);
?>
