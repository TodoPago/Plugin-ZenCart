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

    $rta4 = $connector->getStatus($optionsGS);

    if (isset($rta4["Operations"])) {

        $rta4 = $rta4["Operations"];
        $response = "<table>";

        foreach($rta4 as $key => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $response .="<tr><td>".$key."</td><td>".$value."</td></tr>";
        }

        echo "</table>";
    } else {
        $response = "No hay operaciones para consultar";
    }

    echo $response;
}