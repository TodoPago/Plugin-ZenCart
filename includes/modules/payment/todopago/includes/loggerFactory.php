<?php
require_once 'logger.php';
require_once dirname(__FILE__).'/../todopago_ctes.php';
require_once dirname(__FILE__).'/../TodoPago/lib/Sdk.php';


class loggerFactory{
    public static function createLogger($payment=false, $mode=null, $customer=null, $order=null){
        $logger = new TodoPagoLogger();
        
        $logger->setFile(TP_LOGDIR);
        $logger->setPhpVersion(phpversion());
        $logger->setCommerceVersion(VERSION);
        $logger->setPluginVersion(TP_VERSION);
        $logger->setLevels(TP_LOGLEVEL, 'fatal');
        
        if($payment){
            $endpoint = ($mode=='prod')?"TODOPAGO_ENDPOINT_PROD":"TODOPAGO_ENDPOINT_TEST";
            $logger->setEndPoint($endpoint);
            $logger->setCustomer($customer);
            $logger->setOrder($order);
        }
        
        try{
        return $logger->getLogger($payment);
        }
        catch(Exception $e){
            $return = $logger->getLogger(false);
            $return->warn("Ha ocurrido un error creando el logger", $e);
            return $return;
        }
    }
}
