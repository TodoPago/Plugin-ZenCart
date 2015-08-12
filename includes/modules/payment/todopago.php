<?php
/*
$Id: mercadopago.php,v 1.00 2004/08/12 19:57:15 hpdl Exp $
osCommerce, Open Source E-Commerce Solutions
http://www.oscommerce.com
Copyright (c) 2003 osCommerce
Released under the GNU General Public License
*/

use TodoPago\Sdk as Sdk;

set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)));
include('todopago/includes/Sdk.php');
define('TABLE_TP_ATRIBUTOS' , 'todo_pago_atributos');
define('TABLE_TP_CONFIGURACION' , 'todo_pago_configuracion');

class todopago extends base {
    
    //Quito $tp_states
    var $code, $title, $description, $enabled, $logo;

    function todopago() {
    
        global $order;
        
        $this->code = 'todopago';
        
        $this->title = "TodoPago";
        
        $this->description = "TodoPago Plugin de pago.";
        
        $this->sort_order = MODULE_PAYMENT_TODOPAGOPLUGIN_SORT_ORDER;
        
        $this->enabled = ((MODULE_PAYMENT_TODOPAGOPLUGIN_STATUS == 'True') ? true : false);
        
        if ((int)MODULE_PAYMENT_TODOPAGOPLUGIN_ORDER_STATUS_ID > 0) {
        
            $this->order_status = MODULE_PAYMENT_TODOPAGOPLUGIN_ORDER_STATUS_ID;
        }
        
        if (is_object($order)) $this->update_status();
        
        $this->logo = '/includes/modules/payment/todopagoplugin/includes/todopago.jpg';
    }
    
    
    function update_status() {
    

    }
    
    
    function javascript_validation() {
    
        return false;
    }
    
    
    
    function selection() {
    
     //   echo '<img src="'.HTTP_SERVER.'/'.DIR_WS_CATALOG.'/includes/modules/payment/todopago/includes/todopago.jpg" />';
    
        return array('id' => $this->code,
                    'module' => '<img style="width:350px" src="'.HTTP_SERVER.'/'.DIR_WS_CATALOG.'/includes/modules/payment/todopago/includes/todopago.jpg">',
                    'icon' => 
        '<img style="width:350px" src="'.HTTP_SERVER.'/'.DIR_WS_CATALOG.'/includes/modules/payment/todopago/includes/todopago.jpg">' );
    
    }
    
    
    
    function pre_confirmation_check() {
        
        return false;
    }
    
    
    
    function confirmation() {
     
        $states = $this->get_tp_states();
        
        echo "<div style='color:red;font-weight:bold'>Por favor eleg&iacute; tu provincia para continuar</div>";
        echo "<select name='tp_states'>";
        
        foreach($states as $city => $code){
            echo '<option value="'.$code.'">'.$city.'</option>';
        }
        
        echo "</select>"; 
        return true;
    }
    
    
    
    function process_button() {
        /*
        global $order, $currencies, $currency;
        
        $my_currency = $currency;
        
        $a = $order->info['total'];
        
        $b = $order->info['shipping_cost'];
        
        $c = $currencies->get_value($my_currency);
        
        $d = $currencies->get_decimal_places($my_currency);
        
        $total = $a * $c;
        
        $precio = number_format($total, 2, '.', '');
        
        $productos = "";
        
        
        for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
        
            $productos .= "- " . $order->products[$i]['name'] . " ";
        
            }
        
        $productos = substr($productos,0,70) . '...';
        
        
        
        if ($my_currency == 'USD'){ 
        
        	$TipoMoneda = 'DOL';}else{
        
        	$TipoMoneda = 'ARG';}
        
        
        
        $process_button_string = zen_draw_hidden_field('name', $productos) .
      
        zen_draw_hidden_field('currency', $TipoMoneda) .
        
        zen_draw_hidden_field('price', $precio) .
        
        zen_draw_hidden_field('url_cancel', tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL')) .
        
        zen_draw_hidden_field('item_id', MODULE_PAYMENT_TODOPAGOPLUGIN_ID) .
        
        zen_draw_hidden_field('acc_id', MODULE_PAYMENT_TODOPAGOPLUGIN_ID) .
        
        zen_draw_hidden_field('shipping_cost', '' ) .
        
        zen_draw_hidden_field('url_process', '') .
        
        zen_draw_hidden_field('url_succesfull', tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL')) . 
        
        zen_draw_hidden_field('enc', MODULE_PAYMENT_TODOPAGOPLUGIN_CODE);
        */
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

        //Quito $customer_id, $response_array
        global $order, $sendto, $ppe_token, $ppe_payerid, $ppe_secret, $ppe_order_total_check, $HTTP_POST_VARS, $comments, $currencies, $insert_id;

        global $db;
        $gv_check = $db->Execute("select customers_id from ".DB_PREFIX."customers where customers_email_address = '".$order->customer['email_address']."'");
        $custid = $gv_check->fields['customers_id'];
        //echo 'custid: '.$custid.'<br/>';
        //echo json_encode($order).'<br/>';

        $connector_data = $this->create_tp_connector();
        
        $connector = $connector_data['connector'];
        $security_code = $connector_data['security'];
        $merchant = $connector_data['merchant']; 
        $config_tp = $connector_data['config'];
        
        
        if(!isset($_GET['Answer'])){
            $optionsSAR_operacion = $this->get_common_fields($order,$custid);          
            
            switch($config_tp['segmento']){
                case 'retail':  
                    $extra_fields = $this->get_retail_fields($order);
                    break;
                case 'ticketing':
                    $extra_fields = $this->get_ticketing_fields($order);
                    break;
                case 'services':
                    $extra_fields = $this->get_services_fields($order);
                    break;            
                case 'digital':
                    $extra_fields = $this->get_digital_goods_fields($order);
                    break;  
                default:
                    $extra_fields = $this->get_retail_fields($order);
                    break;
            }
         
            $optionsSAR_operacion = array_merge_recursive($optionsSAR_operacion, $extra_fields);   
            //echo $insert_id;
            $optionsSAR_operacion['MERCHANT'] = strval($merchant);
            $optionsSAR_operacion['OPERATIONID'] = strval($insert_id);
            $optionsSAR_operacion['CURRENCYCODE'] = '032';
            $optionsSAR_operacion['AMOUNT'] = strval($order->info['total']);
            $optionsSAR_operacion['EMAILCLIENTE'] = $order->customer['email_address'];
          
            $optionsSAR_comercio = array (
            	'Security' => $security_code,
            	'EncodingMethod' => 'XML',
            	'Merchant' => strval($merchant),
            	'URL_OK' =>  zen_href_link('checkout_success_todopago', 'referer=todopago', 'SSL'),
             	'URL_ERROR' => zen_href_link('checkout_error_todopago', '', 'SSL')
            );
            
            $logDir = dirname(__FILE__).'/todopago.log';            
            $logText = date('d-m-Y H:i:s').' - todopago - orden '.$insert_id.': ';
            $logText .= 'first step';
            error_log($logText."\n", 3, $logDir);

            $paramsSAR['comercio'] = $optionsSAR_comercio;
            $paramsSAR['operacion'] = $optionsSAR_operacion;
            error_log(date('d-m-Y H:i:s').' - todopago - orden '.$insert_id.': params SAR - parametros: '.json_encode($paramsSAR)."\n", 3, $logDir);

            $rta = $connector->sendAuthorizeRequest($optionsSAR_comercio, $optionsSAR_operacion);
            error_log(date('d-m-Y H:i:s').' - todopago - orden '.$insert_id.': response SAR - parametros: '.json_encode($rta)."\n", 3, $logDir);        
            setcookie('RequestKey', $rta["RequestKey"], time() + (86400 * 30), "/");     
            
            //echo json_encode($optionsSAR_comercio).'<br/>';
            //echo json_encode($optionsSAR_operacion).'<br/>';

            //echo json_encode($rta);

            header('Location: '.$rta['URL_Request']);
            die();
        }
        else{   
            header('Location: '.zen_href_link('checkout_success_todopago?Answer='.$_GET['Answer'], 'referer=todopago', 'SSL'));
            die();
        }
        
        return true;
    }
    
    
    function output_error() {
        return false;
    }
     
    
    function check() { 
        global $db;
        
        if (!isset($this->_check)) {
            $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_TODOPAGOPLUGIN_STATUS'");
            $this->_check = $check_query->RecordCount() ;
        }
        
        return $this->_check;
    }
    
    
    
    function install() {
        
        global $db, $messageStack;
        
         if (defined('MODULE_PAYMENT_TODOPAGO_STATUS')) {
          $messageStack->add_session('TODOPAGO Payments Standard module already installed.', 'error');
          zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=todopago', 'NONSSL'));
          return 'failed';
        }
        
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Habilitar módulo MercadoPago', 'MODULE_PAYMENT_TODOPAGOPLUGIN_STATUS', 'True', 'Desea aceptar pagos a traves de MercadoPago?', '6', '3', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('ID CUENTA', 'MODULE_PAYMENT_TODOPAGOPLUGIN_ID', '', 'Código de Comercio', '6', '4', now())");	
        
        
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
        
        if ($todoPagoConfig['ambiente'] == "test"){
            $security =  $todoPagoConfig['test_security'];
            $merchant = $todoPagoConfig['test_merchant'];
        }
        else{
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
        
            if (empty($c)){
                $config_validation =  false;
            }
        }
        
        if ($config_validation){
            $connector = new Sdk($http_header, ($todoPagoConfig['ambiente'] == 'test') ? 'test' : 'prod');
        } else {
            echo "FALTA CONFIGURAR PLUGIN TODOPAGO";        
        }
                 
        $return = array('connector'=>$connector, 'merchant'=>$merchant, 'security'=>$security, 'config' => $todoPagoConfig);
        
        return $return;
    }
    
    function get_common_fields($cart, $customer_id){
        $CSITPRODUCTDESCRIPTION = array();
        $CSITPRODUCTNAME = array();
        $CSITPRODUCTSKU = array();
        $CSITTOTALAMOUNT = array();
        $CSITQUANTITY = array();
        $CSITUNITPRICE = array();
        $CSITPRODUCTCODE = array();

        foreach($cart->products as $prod){
            $CSITPRODUCTDESCRIPTION[] = substr(Sdk::sanitizeValue($prod['name']),0,50);
            $CSITPRODUCTNAME[] =  substr(str_replace('#', '', Sdk::sanitizeValue($prod['name'])),0,50);
            $CSITPRODUCTSKU[] = str_replace('#', '', $prod['model']);
            $CSITTOTALAMOUNT[] = number_format($prod['qty'] * $prod['final_price'],2,".","");
            $CSITQUANTITY[] = $prod['qty'];
            $CSITUNITPRICE[] =  number_format($prod['final_price'],2,".","");
            
            $CSITPRODUCTCODE[] = 'default';

            /*$customfields = array();
            $customfields = $this->get_tp_custom_values($prod['id']);
            
            if (is_array($customfields)){
                
                foreach($customfields as $customIndex => $customValue){
                    
                    if ($customIndex == 'CSITPRODUCTCODE'){
                        //$CSITPRODUCTCODE[] = trim(urlencode(htmlentities(strip_tags($customValue))));
                        $CSITPRODUCTCODE[] = 'default';
                    }
                }
            }*/
        }
        
        
        $fields = array(		                        
    	   'CSBTCITY' => $cart->billing['state'], 	
    	   'CSBTCOUNTRY' => $cart->billing['country']['iso_code_2'], 	
    	   'CSBTCUSTOMERID' => $customer_id, 
    	   'CSBTIPADDRESS' => $this->get_todo_pago_client_ip(), 	
	       'CSBTEMAIL' => $cart->customer['email_address'], 		
	       'CSBTFIRSTNAME'=> Sdk::sanitizeValue($cart->customer['firstname']),
	       'CSBTLASTNAME'=> Sdk::sanitizeValue($cart->customer['lastname']), 
    	   'CSBTPHONENUMBER'=> $cart->customer['telephone'], 
    	   'CSBTPOSTALCODE'=> $cart->customer['postcode'], 	
    	   'CSBTSTATE' => $this->getStateCode($customer['state']), 
    	   'CSBTSTREET1' => $cart->customer['street_address'] ,				
    	   //'CSPTCURRENCY'=> $cart->info['currency'],
           'CSPTCURRENCY'=> 'ARS',	
    	   'CSPTGRANDTOTALAMOUNT' => number_format($cart->info['total'],2,".",""), 
    	   'CSITPRODUCTCODE'=>join("#",$CSITPRODUCTCODE), 
    	   'CSITPRODUCTDESCRIPTION'=> join('#',$CSITPRODUCTDESCRIPTION), 
    	   'CSITPRODUCTNAME'=>join('#',$CSITPRODUCTNAME),		
    	   'CSITPRODUCTSKU'=>join('#',$CSITPRODUCTSKU), 		
    	   'CSITTOTALAMOUNT'=> join('#',$CSITTOTALAMOUNT), 
    	   'CSITQUANTITY'=>join('#',$CSITQUANTITY), 		
    	   'CSITUNITPRICE'=>join('#',$CSITUNITPRICE),
           'AMOUNT' => strval($cart->cartPrices['billTotal'])	
        );
                            
        return $fields;

    }
    
    function get_retail_fields($cart){
        $fields = array(		
            'CSSTCITY'=> $cart->delivery['city'],
            'CSSTCOUNTRY'=> $cart->delivery['country']['iso_code_2'],	
            'CSSTEMAIL' => $cart->customer['email_address'], 		
        	'CSSTFIRSTNAME'=> Sdk::sanitizeValue($cart->customer['firstname']),
            'CSSTSTATE'=> $this->getStateCode($cart->delivery['state']), 		
        	'CSSTLASTNAME'=> Sdk::sanitizeValue($cart->customer['lastname']), 		
        	'CSSTPHONENUMBER'=>$cart->customer['telephone'],		
        	'CSSTPOSTALCODE'=> $cart->customer['postcode'],		
        	'CSSTSTREET1'=> $cart->customer['street_address'], 			
         );
                         
        return $fields;    
    }
    
    private function getStateCode($stateName){
      $array = array(
        "caba" => "C",
        "capital" => "C",
        "ciudad autonoma de buenos aires" => "C",
        "buenos aires" => "B",
        "bs as" => "B",
        "catamarca" => "K",
        "chaco" => "H",
        "chubut" => "U",
        "cordoba" => "X",
        "corrientes" => "W",
        "entre rios" => "R",
        "formosa" => "P",
        "jujuy" => "Y",
        "la pampa" => "L",
        "la rioja" => "F",
        "mendoza" => "M",
        "misiones" => "N",
        "neuquen" => "Q",
        "rio negro" => "R",
        "salta" => "A",
        "san juan" => "J",
        "san luis" => "D",
        "santa cruz" => "Z",
        "santa fe" => "S",
        "santiago del estero" => "G",
        "tierra del fuego" => "V",
        "tucuman" => "T"
      );

      $name = strtolower($stateName);
      
      $no_permitidas = array("á","é","í","ó","ú");
      $permitidas = array("a","e","i","o","u");
      $name = str_replace($no_permitidas, $permitidas ,$name);

      return isset($array[$name]) ? $array[$name] : 'C';
    }

    function get_digital_goods_fields(){
        
        $CSMDD31 = array();
        
        foreach($data->products as $prod){

            $customfields = array();
            $customfields = $this->get_tp_custom_values($prod['id']);
            
            if (is_array($customfields)){
                
                foreach($customfields as $customIndex => $customValue){
                    
                    if ($customIndex == 'CSMDD31'){
                        $CSMDD31[] = trim(urlencode(htmlentities(strip_tags($customValue))));
                    }
                }
            }
        }

        $fields = array(		
                	'CSMDD31'=>implode('#',$CSMDD31)
                   );

        return $fields;	    
    }
    
    function get_services_fields(){
        
        $CSMDD28 = array();
        
        foreach($data->products as $prod){

            $customfields = array();
            $customfields = $this->get_tp_custom_values($prod['id']);
            
            if (is_array($customfields)){
                
                foreach($customfields as $customIndex => $customValue){
                    
                    if ($customIndex == 'CSMDD28'){
                        $CSMDD28[] = trim(urlencode(htmlentities(strip_tags($customValue))));
                    }
                }
            }
        }

        $fields = array(		
                	'CSMDD28'=>implode('#',$CSMDD28)
                   );

        return $fields;	    
    }
    
    function get_travel_fields(){
        
        return array(); 
    }
    
    function get_ticketing_fields(){
        
        $CSMDD33 = array();
        $CSMDD34 = array();
        
        foreach($data->products as $prod){

            $customfields = array();
            $customfields = $this->get_tp_custom_values($prod['id']);
            
            if (is_array($customfields)){
                
                foreach($customfields as $customIndex => $customValue){
                    
                    if ($customIndex == 'CSMDD33'){
                        $CSMDD33[] = trim(urlencode(htmlentities(strip_tags($customValue))));
                    }
                    if ($customIndex == 'CSMDD34'){
                        $CSMDD34[] = trim(urlencode(htmlentities(strip_tags($customValue))));
                    }
                }
            }
        }

        $fields = array(		
                	'CSMDD33'=>implode('#',$CSMDD33),
                    'CSMDD34'=>implode('#',$CSMDD34)
                   );

        return $fields;	  
            
    }
    
    function get_todo_pago_client_ip() {
        
        $ipaddress = '';
        if ($_SERVER['HTTP_CLIENT_IP'])
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if($_SERVER['HTTP_X_FORWARDED_FOR'])
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if($_SERVER['HTTP_X_FORWARDED'])
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if($_SERVER['HTTP_FORWARDED_FOR'])
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if($_SERVER['HTTP_FORWARDED'])
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if($_SERVER['REMOTE_ADDR'])
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
            
        return $ipaddress;
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
    
    function get_tp_states(){
        
        $states = array('CABA' => 'C',
                        'Buenos Aires'  => 'B',
                        'Catamarca'  => 'K',
                        'Chaco'  => 'H' ,
                        'Chubut'  => 'U',
                        'C&oacute;rdoba'  => 'X',
                        'Corrientes'  => 'W',
                        'Entre R&iacute;os'  => 'R',
                        'Formosa'  => 'P',
                        'Jujuy'  => 'Y',
                        'La Pampa'  => 'L',
                        'La Rioja' =>  'F',
                        'Mendoza' => 'M',
                        'Misiones'  => 'N',
                        'Neuqu&eacute;n'  => 'Q',
                        'R&iacute;o Negro'  => 'R',
                        'Salta'  => 'A',
                        'San Juan'  => 'J',
                        'San Luis'  => 'D',
                        'Santa Cruz'  => 'Z',
                        'Santa F&eacute;' =>  	'S',
                        'Santiago del Estero'  => 'G',
                        'Tierra del Fuego'  => 'V',
                        'Tucum&aacute;n'  => 'T');

        return $states;    
    }
}