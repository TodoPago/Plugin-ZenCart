<?php

class TodopagoTransaccion {
    
    const NEW_ORDER = 0;
    const FIRST_STEP = 1;
    const SECOND_STEP = 2;
    const TRANSACTION_FINISHED = 3;
    
    public function getTransaction($orderId) {
        global $db;

        $res = $db->Execute("SELECT * FROM todopago_transaccion WHERE id_orden = ".$orderId, false, false, 0, true);

        if ( ! $res->EOF) {
            return $res->fields;
        }
    }
    
    public function _getStep($orderId) {
        $transaction = $this->getTransaction($orderId);
        if ($transaction == null) {
            $step = self::NEW_ORDER;
        }
        else if ($transaction['first_step'] == null) {
            $step = self::FIRST_STEP;
        }
        else if ($transaction['second_step'] == null) {
            $step = self::SECOND_STEP;
        }
        else {
            $step = self::TRANSACTION_FINISHED;
        }
        
        return $step;
    }
    
    public function createRegister($orderId) {
        global $db;

        if ($this->_getStep($orderId) == self::NEW_ORDER) {
            $db->Execute("INSERT INTO todopago_transaccion (id_orden) VALUES (".$orderId.")");
            return true;
        }
        else {
            return false;
        }
    }
    
    public function recordFirstStep($orderId, $paramsSAR, $responseSAR) {
        global $db;

        $datetime = new DateTime('NOW');
        if ($this->_getStep($orderId) == self::FIRST_STEP) {
            $requestKey = $responseSAR['RequestKey'];
            $publicRequestKey = $responseSAR['PublicRequestKey'];
            
            $query = "UPDATE todopago_transaccion SET first_step = '".$datetime->format('Y-m-d H:i:s')."', params_SAR = '".zen_db_input(zen_db_prepare_input(json_encode($paramsSAR)))."', response_SAR = '".zen_db_input(zen_db_prepare_input(json_encode($responseSAR)))."', request_key = '".zen_db_input(zen_db_prepare_input($requestKey))."', public_request_key = '".zen_db_input(zen_db_prepare_input($publicRequestKey))."' WHERE id_orden = ".$orderId;
            $db->Execute($query);
            return $query;
        }
        else {
            return 0;
        }
    }
    
    public function recordSecondStep($orderId, $paramsGAA, $responseGAA) {
        global $db;

        $datetime = new DateTime('NOW');
        if ($this->_getStep($orderId) == self::SECOND_STEP) {
            $answerKey = $paramsGAA['AnswerKey'];
            
            $query = "UPDATE todopago_transaccion SET second_step = '".$datetime->format('Y-m-d H:i:s')."', params_GAA = '".json_encode($paramsGAA)."', response_GAA = '".json_encode($responseGAA)."', answer_key = '".$answerKey."' WHERE id_orden = ".$orderId;
            $db->Execute($query);
            return $query;
        }
        else {
            return 0;
        }
    }
}