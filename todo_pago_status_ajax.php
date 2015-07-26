<?php

    use TodoPago\Sdk as Sdk;
    
    require('includes/application_top.php');
    global $db;
    
    include(DIR_FS_CATALOG."/includes/modules/payment/todopago/includes/Sdk.php");
    
    $orderId = $_REQUEST["order_id"];

    $sql = "select * from todo_pago_configuracion";

    $res = $db->Execute($sql);

    if (!$res->EOF){    
    
        $modo = $res->fields["ambiente"]."_";

        //$wsdl = json_decode($res->fields[$modo."wsdl"],1);
        
        $http_header = json_decode($res->fields["authorization"],1);
        
        $http_header["user_agent"] = 'PHPSoapClient';
        
        define('END_POINT', $res->fields[$modo."endpoint"]);

        $connector = new Sdk($http_header, ($res->fields["ambiente"] == 'test') ? 'test' : 'prod');
    
        $optionsGS = array('MERCHANT'=>$res->fields[$modo."merchant"], 'OPERATIONID'=>$orderId); 

        $rta4 = $connector->getStatus($optionsGS);   
    
        if (isset($rta4["Operations"])){
    
            $rta4 = $rta4["Operations"];
            $tabla = "<table>";
        
            foreach($rta4 as $key => $value){
        
                $tabla .="<tr><td>".$key."</td><td>".$value."</td></tr>";
            }
        
            echo "</table>";
        }
        else{
    
            $tabla = "No hay operaciones para consultar";
        }
        
    echo $tabla;
    
    }