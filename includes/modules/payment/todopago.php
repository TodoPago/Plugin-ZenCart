<?php
/*
$Id: todopago.php
osCommerce, Open Source E-Commerce Solutions
http://www.oscommerce.com
Copyright (c) 2003 osCommerce
Released under the GNU General Public License
*/

use TodoPago\Sdk as Sdk;

set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)));
include_once(dirname(__FILE__).'/todopago/includes/Sdk.php');
include_once(dirname(__FILE__).'/todopago/includes/ControlFraude/ControlFraudeFactory.php');
include_once(dirname(__FILE__).'/todopago/includes/logger.php');

define('PLUGIN_VERSION','1.4.0');
define('TABLE_TP_ATRIBUTOS' , 'todo_pago_atributos');
define('TABLE_TP_CONFIGURACION' , 'todo_pago_configuracion');

class todopago extends base {
    
    public $tplogger;
    var $code, $title, $description, $enabled, $logo;

    function todopago() {
    
        global $order;
        $this->code = 'todopago';
        $this->title = "TodoPago";
        $this->description = "TodoPago Plugin de pago.";
        $this->sort_order = MODULE_PAYMENT_TODOPAGOPLUGIN_SORT_ORDER;
        $this->enabled = ((MODULE_PAYMENT_TODOPAGOPLUGIN_STATUS == 'True') ? true : false);
        
        if((int)MODULE_PAYMENT_TODOPAGOPLUGIN_ORDER_STATUS_ID > 0){
            $this->order_status = MODULE_PAYMENT_TODOPAGOPLUGIN_ORDER_STATUS_ID;
        }
        
        if(is_object($order)) $this->update_status();
        
        $this->logo = '/includes/modules/payment/todopagoplugin/includes/todopago.jpg';

        $this->tplogger = new TodoPagoLogger();
    }
    

    function update_status() {}
    

    function javascript_validation() {
        return false;
    }
    

    function selection() {    
        return array('id' => $this->code,
                    'module' => '<img style="width:350px" src="'.HTTP_SERVER.'/'.DIR_WS_CATALOG.'/includes/modules/payment/todopago/includes/todopago.jpg">',
                    'icon' => '<img style="width:350px" src="'.HTTP_SERVER.'/'.DIR_WS_CATALOG.'/includes/modules/payment/todopago/includes/todopago.jpg">' );
    }
    
    
    function pre_confirmation_check() { 
        return false;
    }
    
  
    function confirmation() {
        return true;
    }
 
    
    function process_button() {
        return false;
    }
    
    
    function before_process() { 
        return true;
    }


    function checkout_initialization_method() {
        $string = '';
        return $string;
    }
    

    //first_step_todopago
    function after_process() {

        //Quito $customer_id, $response_array, $HTTP_POST_VARS
        global $order, $sendto, $ppe_token, $ppe_payerid, $ppe_secret, $ppe_order_total_check, $comments, $currencies, $insert_id;
        //echo json_encode($order).'<br/>';

        global $db;
        $gv_check = $db->Execute("select customers_id from ".DB_PREFIX."customers where customers_email_address = '".$order->customer['email_address']."'");
        $custid = $gv_check->fields['customers_id'];

        $connector_data = $this->create_tp_connector();
        $logger = $this->_obtain_logger(PLUGIN_VERSION, $connector_data['config']['ambiente'], $custid, $insert_id, true);
        $logger->info('first step');

        if(!isset($_GET['Answer'])){
            $paramsSAR = $this->get_paydata($order, $custid, $connector_data['merchant'], $insert_id, $connector_data['config'], $connector_data['security'], $logger);
            $this->call_sar($connector_data['connector'], $connector_data['header'], $paramsSAR, $logger);
        }else{
            header('Location: '.zen_href_link('checkout_success_todopago?Answer='.$_GET['Answer'], 'referer=todopago', 'SSL'));
            die();
        }
        
        return true;
    }
    
    private function get_paydata($order, $custid, $merchant, $insert_id, $config_tp, $security_code, $logger){
        $segmento = 'Retail'; //$segmento = $config_tp['segmento'];

        //Control de fraude
        $controlFraude = ControlFraudeFactory::get_ControlFraude_extractor($segmento, $order, $custid);
        $optionsSAR_operacion = $controlFraude->getDataCF();

        $optionsSAR_operacion['MERCHANT'] = strval($merchant);
        $optionsSAR_operacion['OPERATIONID'] = strval($insert_id);
        $optionsSAR_operacion['CURRENCYCODE'] = '032';
        $optionsSAR_operacion['CSMDD6'] = $config_tp['canal'];
        $optionsSAR_operacion['CSMDD12'] = $config_tp['deadline'];

        $optionsSAR_comercio = array (
            'Security' => $security_code,
            'EncodingMethod' => 'XML',
            'Merchant' => strval($merchant),
            'URL_OK' =>  zen_href_link('checkout_success_todopago', 'referer=todopago', 'SSL'),
            'URL_ERROR' => zen_href_link('checkout_success_todopago', 'referer=todopago', 'SSL')
        );           

        $paramsSAR['comercio'] = $optionsSAR_comercio;
        $paramsSAR['operacion'] = $optionsSAR_operacion;
        $logger->info('params SAR '.json_encode($paramsSAR));

        return $paramsSAR;
    }

    private function call_sar($connector, $http_header, $paramsSAR, $logger){
        $rta = $connector->sendAuthorizeRequest($paramsSAR['comercio'], $paramsSAR['operacion']);
        $logger->info('response SAR '.json_encode($rta));
        
        if($rta["StatusCode"] == 702 && !empty($http_header) && !empty($paramsSAR['comercio']['Merchant']) && !empty($paramsSAR['comercio']['Security'])){
            $rta = $connector->sendAuthorizeRequest($paramsSAR['comercio'], $paramsSAR['operacion']);
            $logger->info('reintento');
            $logger->info('response SAR '.json_encode($rta));
        }

        setcookie('RequestKey', $rta["RequestKey"], time() + (86400 * 30), "/");     
        
        header('Location: '.$rta['URL_Request']);
        die();
    }

    private function _obtain_logger($plugin_version, $endpoint, $customer_id, $order_id, $is_payment){
      $this->tplogger->setPhpVersion(phpversion());
      $this->tplogger->setCommerceVersion(zend_version());
      $this->tplogger->setPluginVersion($plugin_version);
      $this->tplogger->setEndPoint($endpoint);
      $this->tplogger->setCustomer($customer_id);
      $this->tplogger->setOrder($order_id);

      return  $this->tplogger->getLogger(true);
    }

    
    function output_error() {
        return false;
    }
     
    
    function check() { 
        global $db;
        
        if(!isset($this->_check)){
            $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_TODOPAGOPLUGIN_STATUS'");
            $this->_check = $check_query->RecordCount() ;
        }
        
        return $this->_check;
    }
    
    
    function install() {
        global $db, $messageStack;
        
         if(defined('MODULE_PAYMENT_TODOPAGO_STATUS')){
          $messageStack->add_session('TODOPAGO Payments Standard module already installed.', 'error');
          zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=todopago', 'NONSSL'));
          return 'failed';
        }
        
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Habilitar modulo TodoPago', 'MODULE_PAYMENT_TODOPAGOPLUGIN_STATUS', 'True', 'Desea aceptar pagos a traves de TodoPago?', '6', '3', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('ID CUENTA', 'MODULE_PAYMENT_TODOPAGOPLUGIN_ID', '', 'Codigo de Comercio', '6', '4', now())");   
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_TODOPAGOPLUGIN_SORT_ORDER', '0', 'Order de despliegue. El mas bajo se despliega primero.', '6', '0', now())");
        $db->Execute("CREATE TABLE IF NOT EXISTS `".TABLE_TP_ATRIBUTOS."` ( `product_id` BIGINT NOT NULL , `CSITPRODUCTCODE` VARCHAR(150) NOT NULL COMMENT 'Codigo del producto' , `CSMDD33` VARCHAR(150) NOT NULL COMMENT 'Dias para el evento' , `CSMDD34` VARCHAR(150) NOT NULL COMMENT 'Tipo de envio' , `CSMDD28` VARCHAR(150) NOT NULL COMMENT 'Tipo de servicio' , `CSMDD31` VARCHAR(150) NOT NULL COMMENT 'Tipo de delivery' ) ENGINE = MyISAM;");
        $db->Execute("CREATE TABLE IF NOT EXISTS `".TABLE_TP_CONFIGURACION."` ( `idConf` INT NOT NULL PRIMARY KEY, `authorization` VARCHAR(100) NOT NULL , `segmento` VARCHAR(100) NOT NULL , `canal` VARCHAR(100) NOT NULL , `ambiente` VARCHAR(100) NOT NULL , `deadline` VARCHAR(100) NOT NULL , `test_merchant` VARCHAR(100) NOT NULL , `test_security` VARCHAR(100) NOT NULL , `production_merchant` VARCHAR(100) NOT NULL , `production_security` VARCHAR(100) NOT NULL , `estado_inicio` VARCHAR(100) NOT NULL , `estado_aprobada` VARCHAR(100) NOT NULL , `estado_rechazada` VARCHAR(100) NOT NULL , `estado_offline` VARCHAR(100) NOT NULL ) ENGINE = MyISAM;");
        $db->Execute("DELETE FROM `".TABLE_TP_CONFIGURACION."`");
        $db->Execute("INSERT INTO `".TABLE_TP_CONFIGURACION."` (`idConf`, `authorization`, `segmento`, `canal`, `ambiente`, `deadline`, `test_merchant`, `test_security`, `production_merchant`, `production_security`, `estado_inicio`, `estado_aprobada`, `estado_rechazada`, `estado_offline`) VALUES ('1', '', '', '', '', '', '', '', '', '', '', '', '', '')");
        
        $this->notify('NOTIFY_PAYMENT_TODOPAGO_INSTALLED');
    }
    

    function remove() {
        global $db;
        $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
        $db->Execute("DELETE FROM todo_pago_configuracion");
    }
    
    
    function keys() {
        return array('MODULE_PAYMENT_TODOPAGOPLUGIN_STATUS', 'MODULE_PAYMENT_TODOPAGOPLUGIN_ID', 'MODULE_PAYMENT_TODOPAGOPLUGIN_SORT_ORDER');
    }
    

    function get_tp_configuracion(){
        global $db;
        $todoPagoConfig = $db->Execute('SELECT * FROM todo_pago_configuracion');
        $todoPagoConfig = $todoPagoConfig->fields;
        
        return $todoPagoConfig;
    }
    

    function create_tp_connector(){
        
        $todoPagoConfig = $this->get_tp_configuracion();
        
        if($todoPagoConfig['ambiente'] == "test"){
            $security =  $todoPagoConfig['test_security'];
            $merchant = $todoPagoConfig['test_merchant'];
        }else{
            $security =  $todoPagoConfig['production_security'];
            $merchant = $todoPagoConfig['production_merchant'];
        }
        
        $auth = json_decode($todoPagoConfig['authorization'], 1); //Es el Authorization HTTP del header
        $todoPagoParams = json_decode($todoPagoParams, 1);

        $http_header = array('Authorization'=>$auth['Authorization'], 'user_agent'=>'PHPSoapClient');
        
        $config = array_merge($auth ,$http_header);
        $config[] = $merchant;
        $config[] = $security;
        
        $config_validation = true;
        
        foreach($config as $c){
            if(empty($c)){
                $config_validation =  false;
            }
        }
        
        if($config_validation){
            $connector = new Sdk($http_header, ($todoPagoConfig['ambiente'] == 'test') ? 'test' : 'prod');
        }else{
            echo "FALTA CONFIGURAR PLUGIN TODOPAGO";        
        }
                 
        $return = array('connector'=>$connector, 'merchant'=>$merchant, 'security'=>$security, 'config' => $todoPagoConfig, 'header' => $http_header);
        
        return $return;
    }


    function set_tp_order_status($order_id, $status_id){
        global $db;
        $db->Execute('UPDATE '.TABLE_ORDERS.' SET orders_status = '.$status_id.' WHERE orders_id = '.$order_id);
    }
    

    function get_tp_custom_values($product_id){
        global $db;
        $product_id = explode('{', $product_id);
        $product_id = explode(':', $product_id[0]);
      
        $todoPagoConfig = $db->Execute('SELECT * FROM '.TABLE_TP_ATRIBUTOS.' WHERE product_id = '.$product_id[0]);
        $todoPagoConfig = $todoPagoConfig->fields;
        
        return $todoPagoConfig; 
    }
    

}