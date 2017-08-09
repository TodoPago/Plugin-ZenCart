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
        foreach ($status['Operations'] as $key => $value) {

            if(is_array($value) && $key == $auxColection){
               
                $rta .= " $key: \n";
                foreach ($auxArray[$aux] as $key2 => $value2) {              
                    $rta .= "  $aux: \n";                
                    if(is_array($value2)){                    
                        foreach ($value2 as $key3 => $value3) {
                            if(is_array($value3)){                    
                                foreach ($value3 as $key4 => $value4) {
                                    $rta .= "   - $key4: $value4 </br>";
                                }
                             } else {
				$rta .= "$key3: $value3 </br>";
			     }
                        }
                    } else {
			$rta .= "$key2: $value2 </br>";
                    }
                }

            }else{

                if(is_array($value) && in_array($key, $listArrayShow)){

                        if(sizeof($value) == 0){
                            $rta .= "$key: </br>"; 
                        }else{

                            $rta .= "$key: </br>";
                            $rta .= "<table style='margin-left:15px;'>";
                            $rta .= "<tr>";    
                            foreach($value as $id => $content){
                                $rta .= "<td>".$content."</td>";                                
                            }
                            $rta .= "</tr>";
                            $rta .= "</table>";
                        }
                    
                }else{
		    if(is_array($value)) {
                        foreach($value as $keyX => $valueX) {
                            $rta .= "$keyX: $valueX </br>";
                        }
                    } else {
                        $rta .= "$key: $value </br>";
                    }
                }
            } 
        }
    }else{ $rta = 'No hay operaciones para esta orden.'; }    
    echo($rta);
}
