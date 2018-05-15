<?php
/*
$Id: todopago.php
osCommerce, Open Source E-Commerce Solutions
http://www.oscommerce.com
Copyright (c) 2003 osCommerce
Released under the GNU General Public License
*/
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)));

require_once(dirname(__FILE__) . '/todopago/vendor/autoload.php');
require_once(dirname(__FILE__) . '/todopago/includes/todopago_ctes.php');
include_once(dirname(__FILE__) . '/todopago/includes/ControlFraude/ControlFraudeFactory.php');
include_once(dirname(__FILE__) . '/todopago/includes/loggerFactory.php');
include_once(dirname(__FILE__) . '/todopago/includes/TodopagoTransaccion.php');


use TodoPago\Sdk as Sdk;

class todopago extends base
{

    private $tplogger, $todoPagoConfig, $todopagoTransaccion, $mantenerCarritom, $configTable;
    var $code, $title, $description, $enabled, $logo;

    function __construct()
    {
        global $order;
        $this->todopagoTransaccion = new TodopagoTransaccion();
        $this->code = 'todopago';
        $this->title = "TodoPago";
        $this->description = "TodoPago Plugin de pago.";
        $this->sort_order = MODULE_PAYMENT_TODOPAGOPLUGIN_SORT_ORDER;
        $this->enabled = MODULE_PAYMENT_TODOPAGOPLUGIN_STATUS == 'True';

        if ((int)MODULE_PAYMENT_TODOPAGOPLUGIN_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_TODOPAGOPLUGIN_ORDER_STATUS_ID;
        }

        if (is_object($order)) $this->update_status();

        $this->logo = 'http://www.todopago.com.ar/sites/todopago.com.ar/files/pluginstarjeta.jpg';
    }

    function update_status()
    {
    }


    function javascript_validation()
    {
        return false;
    }


    function selection()
    {
        return array(
            'id' => $this->code,
            'module' => '<img style="width:350px" src="http://www.todopago.com.ar/sites/todopago.com.ar/files/pluginstarjeta.jpg">',
            'icon' => '<img style="width:350px" src="http://www.todopago.com.ar/sites/todopago.com.ar/files/pluginstarjeta.jpg">'
        );
    }


    function pre_confirmation_check()
    {
        return false;
    }


    function confirmation()
    {
        return true;
    }


    function process_button()
    {
        return false;
    }


    function before_process()
    {
        return true;
    }


    function checkout_initialization_method()
    {
        $string = '';
        return $string;
    }

    function after_process()
    {

        $this->first_step_todopago();
        return true;
    }

    public function first_step_todopago()
    {
        global $db;
        $this->setConfigTable($db->Execute('SELECT * FROM ' . TABLE_TP_CONFIGURACION));
        global $insert_id;
        $this->boot();
        if ($this->todopagoTransaccion->_getStep($insert_id) == TodopagoTransaccion::FIRST_STEP) {
            $this->tplogger->info('first step');
            $connector_data = $this->create_tp_connector();
            if (!isset($_GET['Answer'])) {
                $paramsSAR = $this->get_paydata($connector_data);
                $this->call_sar($connector_data['connector'], $connector_data['header'], $paramsSAR);
            } else {
                header('Location: ' . zen_href_link('checkout_success_todopago?Answer=' . $_GET['Answer'], 'referer=todopago', 'SSL'));
            }
        } else {
            $this->tplogger->warn("No se pudo efectuar el first step, ya se encuentra un first step exitoso registrado en la tabla todopago_transaccion");
            $this->modificarCarrito();
            zen_redirect(zen_href_link(FILENAME_DEFAULT));
        }
    }

    protected function boot()
    {
        global $insert_id;

        $this->todoPagoConfig = $this->get_tp_configuracion();
        $this->tplogger = loggerFactory::createLogger(
            true,
            $this->todoPagoConfig['ambiente'],
            $this->getCustomerId(),
            $insert_id
        );
        $this->todopagoTransaccion->createRegister($insert_id);
    }

    public function getCustomerId()
    {
        global $db, $order;

        if (isset($_SESSION['customer_id'])) {
            return $_SESSION['customer_id'];
        }

        if (is_object($order)) {
            $gv_check = $db->Execute("SELECT customers_id FROM " . TABLE_CUSTOMERS
                . " WHERE customers_email_address = '" . $order->customer['email_address'] . "'"
            );
            $_SESSION['customer_id'] = $gv_check->fields['customers_id'];
            return $_SESSION['customer_id'];
        } else
            return null;
    }

    protected function get_paydata($data)
    {
        global $order, $insert_id;
        $segmento = 'Retail'; //$this->todoPagoConfig['segmento'];
        $custid = $this->getCustomerId();
        $params = $this->get_tp_configuracion();
        $codigoTipoFormulario = '-E';
        if ($this->getConfigTable()->fields['tipo_formulario'] == '1')
            $codigoTipoFormulario = '-H';
        //Control de fraude
        $controlFraude = ControlFraudeFactory::get_ControlFraude_extractor($segmento, $order, $custid);
        $optionsSAR_operacion = $controlFraude->getDataCF();
		$optionsSAR_operacion['ECOMMERCENAME'] = strtoupper(PROJECT_VERSION_NAME);
		$optionsSAR_operacion['ECOMMERCEVERSION'] = PROJECT_VERSION_MAJOR . "." . PROJECT_VERSION_MINOR;
		$optionsSAR_operacion['PLUGINVERSION'] = TP_VERSION . $codigoTipoFormulario;
        $optionsSAR_operacion['MERCHANT'] = strval($data['merchant']);
        $optionsSAR_operacion['OPERATIONID'] = strval($insert_id);
        $optionsSAR_operacion['CURRENCYCODE'] = '032';
        if ($params['maxinstallments'] > 0) $optionsSAR_operacion['MAXINSTALLMENTS'] = strval($params['maxinstallments']);
        $optionsSAR_operacion['CSMDD6'] = $this->todoPagoConfig['canal'];

        if ($this->todoPagoConfig['active_form_checker']) $optionsSAR_operacion['TIMEOUT'] = $this->todoPagoConfig['form_timeout'];

        //$optionsSAR_operacion['PUSHNOTIFYMETHOD'] = 'application/x-www-form-urlencoded';
        //$optionsSAR_operacion['PUSHNOTIFYENDPOINT'] = HTTP_SERVER . DIR_WS_CATALOG . 'todo_pago_push_notification.php';
        //$optionsSAR_operacion['PUSHNOTIFYSTATES'] = 'CouponCharged';

        unset($params);
        $optionsSAR_comercio = array(
            'Security' => $data['security'],
            'EncodingMethod' => 'XML',
            'Merchant' => strval($data['merchant']),
            'URL_OK' => zen_href_link('checkout_success_todopago', 'referer=todopago', 'SSL'),
            'URL_ERROR' => zen_href_link('checkout_success_todopago', 'referer=todopago', 'SSL'),
            'AVAILABLEPAYMENTMETHODSIDS' => $this->getAvailablePaymentMethods()
        );
        $paramsSAR['comercio'] = $optionsSAR_comercio;
        $paramsSAR['operacion'] = $optionsSAR_operacion;
        $this->tplogger->info('params SAR ' . json_encode($paramsSAR));
        return $paramsSAR;
    }

    protected function getAvailablePaymentMethods()
    {
        $paymentMethods = json_decode($this->todoPagoConfig['medios_pago'], true);
        if ($paymentMethods != NULL) {
            $paymentMethods = array_filter($paymentMethods, function ($item) {
                return $item === true;
            });
            $this->tplogger->debug('Payment methods: ' . json_encode($paymentMethods));
            return implode('#', array_keys($paymentMethods));
        } else {
            return 0;
        }
    }

    private function call_sar($connector, $http_header, $paramsSAR)
    {
        global $db, $insert_id;
        $googleMapsValidator = NULL;
        $md5Billing = $this->SAR_hasher($paramsSAR, 'billing');
        $md5Shipping = $this->SAR_hasher($paramsSAR, 'shipping');
        if ($this->todoPagoConfig['gmaps_validator'])
            $googleMapsValidator = $this->gmaps_validation($md5Billing, $md5Shipping);
        if ($googleMapsValidator && $this->todoPagoConfig['gmaps_validator'])
            $connector->setGoogleClient($googleMapsValidator);
        elseif (!$googleMapsValidator && $this->todoPagoConfig['gmaps_validator'])
            $paramsSAR = $this->paramsSAR_rebuilder($paramsSAR, $md5Billing, $md5Shipping);
        try {
            $rta = $connector->sendAuthorizeRequest($paramsSAR['comercio'], $paramsSAR['operacion']);
        } catch (Exception $e) {
            $this->tplogger->error("Error al realizar el SAR:\n" . json_encode($e));
            $this->showError();
        }
        if ($googleMapsValidator && !empty($googleMapsValidator->getFinalAddress()))
            $this->gmaps_address_updater($connector->getGoogleClient(), $paramsSAR['operacion'], $md5Billing, $md5Shipping);
        $this->tplogger->info('response SAR ' . json_encode($rta));
        if ($rta["StatusCode"] == 702 && !empty($http_header) && !empty($paramsSAR['comercio']['Merchant']) && !empty($paramsSAR['comercio']['Security'])) {
            $this->tplogger->info('reintento');
            $rta = $connector->sendAuthorizeRequest($paramsSAR['comercio'], $paramsSAR['operacion']);
            $this->tplogger->info('response SAR ' . json_encode($rta));
        }

        if ($rta['StatusCode'] !== TP_STATUS_OK) {
            $this->showError();
        }
        try {
            $this->todopagoTransaccion->recordFirstStep($insert_id, $paramsSAR, $rta);
        } catch (Exception $e) {
            $this->tplogger->error("Error al grabar en la base de datos:\n" . $e->getMessage());
        }

        $formType = $this->getConfigTable()->fields['tipo_formulario'];
        $this->setMantenerCarrito($this->getConfigTable()->fields['keep_shopping_cart']);

        if ($formType == 0) {
            // form externo
            header('Location: ' . $rta['URL_Request']);
            exit;
        } elseif ($formType == 1) {
            // form hibrido
            //sanitized
            $url = str_replace("&amp;", "&", zen_href_link("tp_form_hibrid", "id=" . $insert_id, 'SSL'));
            header('Location: ' . $url);
            //echo $url;
            exit;
        }
    }

    public function showError()
    {
        global $messageStack;
        $this->modificarCarrito();
        $messageStack->add_session('header', 'No se pudo realizar la acción, intente nuevamente.', 'error');
        zen_redirect(zen_href_link(FILENAME_DEFAULT));
    }

    function output_error()
    {
        return false;
    }


    function check()
    {
        global $db;

        if (!isset($this->_check)) {
            $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_TODOPAGOPLUGIN_STATUS'");
            $this->_check = $check_query->RecordCount();
        }

        return $this->_check;
    }

    private function get_tp_configuracion()
    {
        global $db;
        $todoPagoConfig = $db->Execute('SELECT * FROM ' . TABLE_TP_CONFIGURACION);
        $todoPagoConfig = $todoPagoConfig->fields;
        return $todoPagoConfig;
    }


    function create_tp_connector()
    {
        $googleValidator = NULL;
        if ($this->todoPagoConfig['ambiente'] == "test") {
            $security = $this->todoPagoConfig['test_security'];
            $merchant = $this->todoPagoConfig['test_merchant'];
        } else {
            $security = $this->todoPagoConfig['production_security'];
            $merchant = $this->todoPagoConfig['production_merchant'];
        }

        $auth = json_decode($this->todoPagoConfig['authorization'], 1); //Es el Authorization HTTP del header

        $http_header = array('Authorization' => $auth['Authorization'], 'user_agent' => 'PHPSoapClient');

        $this->tplogger->info('Header Sdk: ' . json_encode($http_header));

        $config = array_merge($auth, $http_header);
        $config[] = $merchant;
        $config[] = $security;

        $config_validation = true;

        foreach ($config as $field) {
            if (empty($field)) {
                $config_validation = false;
                break;
            }
        }

        if ($config_validation) {
            $connector = new Sdk($http_header, ($this->todoPagoConfig['ambiente'] == 'test') ? 'test' : 'prod');
        } else {
            $this->tplogger->error("FALTA CONFIGURAR PLUGIN TODOPAGO");
            $this->showError();
        }

        $return = array(
            'connector' => $connector,
            'merchant' => $merchant,
            'security' => $security,
            'header' => $http_header
        );
        return $return;
    }


    //* GOOGLE MAPS *//
    //Carga los parámetros a enviar con los datos obtenidos desde la tabla.
    private function paramsSAR_rebuilder($paramsSAR, $md5Billing, $md5Shipping)
    {
        global $db, $order;
        $sqlBilling = $db->Execute("SELECT street,state,city,country,postal  FROM " . TABLE_TP_ADDRESS_BOOK . " WHERE md5_hash =  '{$md5Billing}' LIMIT 1");
        $sqlShipping = $db->Execute("SELECT street,state,city,country,postal  FROM " . TABLE_TP_ADDRESS_BOOK . " WHERE md5_hash =  '{$md5Shipping}' LIMIT 1");
        if (!empty($sqlBilling->fields)) {
            $paramsSAR['operacion']['CSBTSTREET1'] = $sqlBilling->fields['street'];
            $paramsSAR['operacion']['CSBTSTATE'] = $sqlBilling->fields['state'];
            $paramsSAR['operacion']['CSBTCITY'] = $sqlBilling->fields['city'];
            $paramsSAR['operacion']['CSBTCOUNTRY'] = $sqlBilling->fields['country'];
            $paramsSAR['operacion']['CSBTPOSTALCODE'] = $sqlBilling->fields['postal'];
        }
        if (!empty($sqlShipping->fields)) {
            $paramsSAR['operacion']['CSSTSTREET1'] = $sqlShipping->fields['street'];
            $paramsSAR['operacion']['CSSTSTATE'] = $sqlShipping->fields['state'];
            $paramsSAR['operacion']['CSSTCITY'] = $sqlShipping->fields['city'];
            $paramsSAR['operacion']['CSSTCOUNTRY'] = $sqlShipping->fields['country'];
            $paramsSAR['operacion']['CSSTPOSTALCODE'] = $sqlShipping->fields['postal'];
        }
        return $paramsSAR;
    }

    // Actualiza la base de datos en caso de recibir información útil desde google
    private function gmaps_address_updater($gmaps, $originalData, $md5Billing, $md5Shipping)
    {
        global $db;
        $originalBilling = array_intersect_key($originalData, $this->requiredDataBuilder('B'));
        $originalShipping = array_intersect_key($originalData, $this->requiredDataBuilder('S'));
        $gmapsResponse = $gmaps->getFinalAddress();
        $arrayBilling = $this->googleResponseValidator($gmapsResponse['billing'], $originalBilling, 'B');
        $arrayShipping = $this->googleResponseValidator($gmapsResponse['shipping'], $originalShipping, 'S');
        $sqlBilling = $db->Execute("SELECT md5_hash  FROM " . TABLE_TP_ADDRESS_BOOK . " WHERE md5_hash =  '{$md5Billing}' LIMIT 1");
        if ($md5Billing !== $md5Shipping)
            $sqlShipping = $db->Execute("SELECT md5_hash  FROM " . TABLE_TP_ADDRESS_BOOK . " WHERE md5_hash =  '{$md5Shipping}' LIMIT 1");
        if (is_null($sqlBilling->fields['md5_hash']) && !empty($arrayBilling))
            $db->Execute('INSERT INTO ' . TABLE_TP_ADDRESS_BOOK . "(md5_hash,street,state,city,country,postal) VALUES ('{$md5Billing}','{$arrayBilling['CSBTSTREET1']}','{$arrayBilling['CSBTSTATE']}','{$arrayBilling['CSBTCITY']}','{$arrayBilling['CSBTCOUNTRY']}','{$arrayBilling['CSBTPOSTALCODE']}')");
        if (isset($sqlShipping->fields['md5_hash']) && !empty($arrayShipping))
            $db->Execute('INSERT INTO ' . TABLE_TP_ADDRESS_BOOK . "(md5_hash,street,state,city,country,postal) VALUES ('{$md5Shipping}','{$arrayShipping['CSSTSTREET1']}','{$arrayShipping['CSSTSTATE']}','{$arrayShipping['CSSTCITY']}','{$arrayShipping['CSSTCOUNTRY']}','{$arrayShipping['CSSTPOSTALCODE']}')");
    }

    // Devuelve el objeto gmaps si es necesario
    private function gmaps_validation($md5Billing, $md5Shipping)
    {
        global $db;
        $googleNormalizer = NULL;
        $sqlBilling = $db->Execute("SELECT md5_hash FROM " . TABLE_TP_ADDRESS_BOOK . "  WHERE md5_hash = '{$md5Billing}'   LIMIT 1");
        if ($md5Billing !== $md5Shipping)
            $sqlShipping = $db->Execute("SELECT md5_hash FROM " . TABLE_TP_ADDRESS_BOOK . "   WHERE md5_hash = '{$md5Shipping}'   LIMIT 1");
        if (is_null($sqlBilling->fields['md5_hash']) || isset($sqlShipping->fields['md5_hash']))
            $googleNormalizer = new \TodoPago\Client\Google();
        return $googleNormalizer;
    }

    //Construye el array a pedir
    private function requiredDataBuilder($tipoDeCompra)
    {
        return array(
            'CS' . $tipoDeCompra . 'TSTREET1' => 1,
            'CS' . $tipoDeCompra . 'TSTATE' => 1,
            'CS' . $tipoDeCompra . 'TCITY' => 1,
            'CS' . $tipoDeCompra . 'TCITY' => 1,
            'CS' . $tipoDeCompra . 'TPOSTALCODE' => 1
        );
    }

    //Comprueba que Google haya devuelo la información correcta
    private function googleResponseValidator($gFinal, $originalData, $tipoDeCompra)
    {
        $dataDeseada = $this->requiredDataBuilder($tipoDeCompra);
        $comparacion = array_diff_key($dataDeseada, $gFinal);
        if (empty($comparacion))
            return $gFinal;
        else if (array_key_exists('CS' . $tipoDeCompra . 'TPOSTALCODE', $comparacion)) {
            $gFinal = array_merge($gFinal, $originalData);
            return $gFinal;
        } else
            return null;
    }

    //Genera un hash en base a los datos recibidos del usuario
    private function SAR_hasher($paramsSAR, $tipoDeCompra)
    {
        if ($tipoDeCompra === 'billing')
            $arrayCompra = array('CSBTSTREET1' => 1, 'CSBTSTATE' => 2, 'CSBTCITY' => 3, 'CSBTCOUNTRY' => 3, 'CSBTPOSTALCODE' => 5);
        elseif ($tipoDeCompra === 'shipping')
            $arrayCompra = array('CSSTSTREET1' => 1, 'CSSTSTATE' => 2, 'CSSTCITY' => 3, 'CSSTCOUNTRY' => 3, 'CSSTPOSTALCODE' => 5);
        else {
            $this->tplogger->error("No se recibió un input válido en el array de SAR_hasher()");
            $arrayCompra = array('CSSTSTREET1' => 1, 'CSSTSTATE' => 2, 'CSSTCITY' => 3, 'CSSTCOUNTRY' => 3, 'CSSTPOSTALCODE' => 5);
        }
        return md5(implode(",", array_intersect_key($paramsSAR['operacion'], $arrayCompra)));
    }

    function set_tp_order_status($order_id, $status_id)
    {
        global $db;
        $db->Execute('UPDATE ' . TABLE_ORDERS . ' SET orders_status = ' . $status_id . ' WHERE orders_id = ' . $order_id);
    }


    function get_tp_custom_values($product_id)
    {
        global $db;
        $product_id = explode('{', $product_id);
        $product_id = explode(':', $product_id[0]);

        $todoPagoConfig = $db->Execute('SELECT * FROM ' . TABLE_TP_ATRIBUTOS . ' WHERE product_id = ' . $product_id[0]);
        $todoPagoConfig = $todoPagoConfig->fields;

        return $todoPagoConfig;
    }

    /* INSTALL */

    function install()
    {
        global $db, $messageStack;

        if (defined('MODULE_PAYMENT_TODOPAGO_STATUS')) {
            $messageStack->add_session('TODOPAGO Payments Standard module already installed.', 'error');
            zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=todopago', 'NONSSL'));
            return 'failed';
        }

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Habilitar modulo TodoPago', 'MODULE_PAYMENT_TODOPAGOPLUGIN_STATUS', 'True', 'Desea aceptar pagos a traves de TodoPago?', '6', '3', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('ID CUENTA', 'MODULE_PAYMENT_TODOPAGOPLUGIN_ID', '', 'Codigo de Comercio', '6', '4', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort order of display.', 'MODULE_PAYMENT_TODOPAGOPLUGIN_SORT_ORDER', '0', 'Order de despliegue. El mas bajo se despliega primero.', '6', '0', now())");
        $db->Execute("INSERT INTO " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) VALUES (5, 1, 'Canceled')");
        $db->Execute("CREATE TABLE IF NOT EXISTS `" . TABLE_TP_ATRIBUTOS . "` ( `product_id` BIGINT NOT NULL , `CSITPRODUCTCODE` VARCHAR(150) NOT NULL COMMENT 'Codigo del producto' , `CSMDD33` VARCHAR(150) NOT NULL COMMENT 'Dias para el evento' , `CSMDD34` VARCHAR(150) NOT NULL COMMENT 'Tipo de envio' , `CSMDD28` VARCHAR(150) NOT NULL COMMENT 'Tipo de servicio' , `CSMDD31` VARCHAR(150) NOT NULL COMMENT 'Tipo de delivery' ) ENGINE = MyISAM;");
        $db->Execute("CREATE TABLE IF NOT EXISTS `" . TABLE_TP_CONFIGURACION . "` ( `idConf` INT NOT NULL PRIMARY KEY, `authorization` VARCHAR(100) NOT NULL , `segmento` VARCHAR(100) NOT NULL , `canal` VARCHAR(100) NOT NULL , `ambiente` VARCHAR(100) NOT NULL , `deadline` VARCHAR(100) NOT NULL ,`maxinstallments` TINYINT UNSIGNED NOT NULL DEFAULT 0, `test_merchant` VARCHAR(100) NOT NULL , `test_security` VARCHAR(100) NOT NULL , `production_merchant` VARCHAR(100) NOT NULL , `production_security` VARCHAR(100) NOT NULL , `estado_inicio` VARCHAR(100) NOT NULL , `estado_aprobada` VARCHAR(100) NOT NULL , `estado_rechazada` VARCHAR(100) NOT NULL , `tipo_formulario` TINYINT UNSIGNED DEFAULT 0, `estado_offline` VARCHAR(100) NOT NULL, `active_form_checker` TINYINT UNSIGNED DEFAULT 0, `form_timeout` INT UNSIGNED, `keep_shopping_cart` TINYINT UNSIGNED DEFAULT 0,`gmaps_validator` BOOL DEFAULT 0) ENGINE = MyISAM;");
        $db->Execute("CREATE TABLE IF NOT EXISTS `" . TABLE_TP_ADDRESS_BOOK . "` ( `id` INT NOT NULL AUTO_INCREMENT, `md5_hash` VARCHAR(33), `street` VARCHAR(100), `state` VARCHAR(3), `city` VARCHAR(100), `country` VARCHAR(3), `postal` VARCHAR(50), PRIMARY KEY (id)) ENGINE = MyISAM;");
        $db->Execute("DELETE FROM `" . TABLE_TP_CONFIGURACION . "`");
        $db->Execute("INSERT INTO `" . TABLE_TP_CONFIGURACION . "` (`idConf`, `authorization`, `segmento`, `canal`, `ambiente`, `deadline`, `test_merchant`, `test_security`, `production_merchant`, `production_security`, `estado_inicio`, `estado_aprobada`, `estado_rechazada`, `tipo_formulario`, `estado_offline`) VALUES ('1', '', '', '', '', '', '', '', '', '', '', '', '', '0', '')");
        $db->Execute("CREATE TABLE IF NOT  EXISTS `" . TABLE_TP_TRANSACCION . "` (
                       `id` INT NOT NULL AUTO_INCREMENT,
                       `id_orden` INT NULL,
                       `first_step` TIMESTAMP NULL,
                       `params_SAR` TEXT NULL,
                       `response_SAR` TEXT NULL,
                       `second_step` TIMESTAMP NULL,
                       `params_GAA` TEXT NULL,
                       `response_GAA` TEXT NULL,
                       `request_key` TEXT NULL,
                       `public_request_key` TEXT NULL,
                       `answer_key` TEXT NULL,
                       PRIMARY KEY (`id`)
        )");

        $this->notify('NOTIFY_PAYMENT_TODOPAGO_INSTALLED');
    }

    function remove()
    {
        global $db;
        $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
        $db->Execute("DELETE FROM " . TABLE_ORDERS_STATUS . " WHERE orders_status_name = 'Canceled'");
        $db->Execute("DROP TABLE IF EXISTS " . TABLE_TP_CONFIGURACION);
        $db->Execute("DROP TABLE IF EXISTS " . TABLE_TP_ATRIBUTOS);
        $db->Execute("DROP TABLE IF EXISTS " . TABLE_TP_ADDRESS_BOOK);
    }

    function keys()
    {
        return array('MODULE_PAYMENT_TODOPAGOPLUGIN_STATUS', 'MODULE_PAYMENT_TODOPAGOPLUGIN_ID', 'MODULE_PAYMENT_TODOPAGOPLUGIN_SORT_ORDER');
    }

    /**
     * @return mixed
     */
    public function getMantenerCarrito()
    {
        return $this->mantenerCarrito;
    }

    /**
     * @param mixed $mantenerCarrito
     */
    public function setMantenerCarrito($mantenerCarrito)
    {
        $this->mantenerCarrito = $mantenerCarrito;
    }

    private function modificarCarrito()
    {
        global $db;
        $mantenerCarrito = $this->getMantenerCarrito();
        if ($mantenerCarrito === null) {
            $resultConfig = $db->Execute("SELECT * FROM " . TABLE_TP_CONFIGURACION);
            $mantenerCarrito = $resultConfig->fields['keep_shopping_cart'];
        }
        if (!$mantenerCarrito)
            $_SESSION['cart']->reset(true);
    }

    /**
     * @return mixed
     */
    public function getConfigTable()
    {
        return $this->configTable;
    }

    /**
     * @param mixed $tipoFormulario
     */
    public function setConfigTable($configTable)
    {
        $this->configTable = $configTable;
    }



}
