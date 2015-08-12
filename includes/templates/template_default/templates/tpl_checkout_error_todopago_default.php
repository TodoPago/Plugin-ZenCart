<?php

use TodoPago\Sdk as Sdk;

include('includes/modules/payment/todopago/includes/Sdk.php');

$orden = $orders->fields['orders_id'];

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
    
}//End if (!$res->EOF) 

echo "<label>No se ha podido realizar el pago, por favor intente nuevamente.</label>";
