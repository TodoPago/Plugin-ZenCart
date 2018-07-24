<?php
/*
$Id: todopagobilletera.php
osCommerce, Open Source E-Commerce Solutions
http://www.oscommerce.com
Copyright (c) 2003 osCommerce
Released under the GNU General Public License
*/
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)));

require_once(dirname(__FILE__) . '/todopago.php');

class todopagobilletera extends todopago
{

    function __construct()
    {
        parent::__construct();
        $this->code = 'todopago';
        $this->title = "TodoPagoBannerBilletera";
        $this->sort_order = MODULE_PAYMENT_TODOPAGOPLUGIN_SORT_ORDER;
        $this->enabled = MODULE_PAYMENT_TODOPAGOPLUGIN_STATUS == 'True';

        if ((int)MODULE_PAYMENT_TODOPAGOPLUGIN_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_TODOPAGOPLUGIN_ORDER_STATUS_ID;
        }

        if (is_object($order)) $this->update_status();

        $this->init();
    }

   

    function init(){
        $this->logoimg = '';
        $this->code = 'todopagobilletera';
        global $db;
        $res = array();
        $sql = "SELECT configuration_value FROM " . TABLE_CONFIGURATION. " WHERE configuration_value LIKE '%todopagobilletera%' ";
        $res = $db->Execute($sql);
        $qty = $res->RecordCount();

        if($qty > 0){
            $sql = "SELECT banner_billetera FROM " . TABLE_TP_CONFIGURACION;
            $res = $db->Execute($sql);
            $row = $res->fields;
            $this->logoimg = '<img src="'.$row['banner_billetera'].'">';
        }
        
    }

    function selection()
    {
        return array(
            'id' => $this->code,
            'module' => $this->logoimg,
            'icon' => $this->logoimg
        );
    }
}
