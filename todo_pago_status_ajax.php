<?php

require('includes/application_top.php');
require(DIR_FS_CATALOG."includes/modules/payment/todopago/vendor/autoload.php");

use TodoPago\Sdk as Sdk;
global $db;
$orderId = $_REQUEST["order_id"];

$sql = "select * from todo_pago_configuracion";

$res = $db->Execute($sql);

if (!$res->EOF) {
    $modo = $res->fields["ambiente"]."_";

    $http_header = json_decode($res->fields["authorization"],1);

    $http_header["user_agent"] = 'PHPSoapClient';

    define('END_POINT', $res->fields[$modo."endpoint"]);

    $connector = new Sdk($http_header, ($res->fields["ambiente"] == 'test') ? 'test' : 'prod');

    $optionsGS = array('MERCHANT'=>$res->fields[$modo."merchant"], 'OPERATIONID'=>$orderId);

    $status = $connector->getStatus($optionsGS);
     
    $listArrayShow = array("FEEAMOUNT","TAXAMOUNT","SERVICECHARGEAMOUNT","FEEAMOUNTBUYER","TAXAMOUNTBUYER","REFUNDS");

    //refunds
    $rta = '';
    $refunds = $status['Operations']['REFUNDS'];

    $auxArray = array(
         "REFUND" => $refunds
         );

    if($refunds != null){  
        $aux = 'REFUND'; 
        $auxColection = 'REFUNDS'; 
    }

    if (isset($status['Operations']) && is_array($status['Operations']) ) {
        $rta = printGetStatus($status['Operations'], 0);

        echo($rta);
    }else{ 
        echo('No hay operaciones para esta orden.'); 
    }    
}

function printGetStatus($array, $indent) {
    $rta = '';

    foreach ($array as $key => $value) {
        if ($key !== 'nil' && $key !== "@attributes") {
            if (is_array($value) ){
                $rta .= "<tr>";
                $rta .= "<td>".str_repeat("-", $indent) . "<strong>$key: </strong></td>";
                $rta .= "<td>".printGetStatus($value, $indent + 2)."</td>";
                $rta .= "</tr>";
            } else {
                $rta .= "<tr><td>".str_repeat("-", $indent) . "<strong>$key:</strong></td><td>$value</td></tr>";
            }
        }
    }
    
    return $rta;
}
