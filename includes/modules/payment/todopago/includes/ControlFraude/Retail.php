<?php

include_once dirname(__FILE__).'/ControlFraude.php';
include_once dirname(__FILE__).'/phone.php';

class ControlFraude_Retail extends ControlFraude{

    protected function completeCFVertical(){
        $payDataOperacion = array();
        $payDataOperacion['CSSTCITY'] = $this->getField($this->order->delivery['city']);
        $payDataOperacion['CSSTCOUNTRY'] = $this->getField($this->order->delivery['country']['iso_code_2']);
        $payDataOperacion['CSSTEMAIL'] = $this->order->customer['email_address'];
        $payDataOperacion['CSSTFIRSTNAME'] = $this->getField($this->order->delivery['firstname']);
        $payDataOperacion['CSSTLASTNAME'] = $this->getField($this->order->delivery['lastname']);
        $payDataOperacion['CSSTPHONENUMBER'] = phone::clean($this->order->customer['telephone']);
        $payDataOperacion['CSSTPOSTALCODE'] = $this->getField($this->order->delivery['postcode']);
        $payDataOperacion['CSSTSTATE'] = $this->getField($this->order->delivery['country']['iso_code_2']);
        $payDataOperacion['CSSTSTREET1'] =$this->getField($this->order->delivery['street_address']);
        //$payDataOperacion['CSMDD12'] = Mage::getStoreConfig('payment/modulodepago2/cs_deadline');
        //$payDataOperacion['CSMDD13'] = $this->getField($this->order->getShippingDescription());
        //$payData['CSMDD14'] = "";
        //$payData['CSMDD15'] = "";
        //$payDataOperacion['CSMDD16'] = $this->getField($this->order->getCuponCode());
        $payDataOperacion = array_merge($this->getMultipleProductsInfo(), $payDataOperacion);
        return $payDataOperacion;
    }

}