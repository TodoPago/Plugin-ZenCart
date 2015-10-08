<?php

include_once dirname(__FILE__).'/phone.php';

abstract class ControlFraude {

	protected $order;
	private $customer;

	public function __construct($order, $customer_id){
		$this->order = $order;
		$this->customer_id = $customer_id;
	}

	public function getDataCF(){
		$datosCF = $this->completeCF();
		return array_merge($datosCF, $this->completeCFVertical());
	}

	private function completeCF(){
		$payDataOperacion = array();
        
        $payDataOperacion['AMOUNT'] = strval($this->order->info['total']);
        //Revisar
        $payDataOperacion['EMAILCLIENTE'] = $this->order->customer['email_address'];
		$payDataOperacion['CSBTCITY'] = $this->getField($this->order->billing['city']);
		$payDataOperacion['CSBTCOUNTRY'] = $this->order->billing['country']['iso_code_2'];
		$payDataOperacion['CSBTCUSTOMERID'] = $this->customer_id;        
		$payDataOperacion['CSBTIPADDRESS'] = $this->get_todo_pago_client_ip();
		$payDataOperacion['CSBTEMAIL'] = $this->order->customer['email_address'];
		$payDataOperacion['CSBTFIRSTNAME'] = $this->order->billing['firstname'];
		$payDataOperacion['CSBTLASTNAME'] = $this->order->billing['lastname'];      
		$payDataOperacion['CSBTPOSTALCODE'] = $this->order->billing['postcode'];
		$payDataOperacion['CSBTPHONENUMBER'] = phone::clean($this->order->customer['telephone']);    
		$payDataOperacion['CSBTSTATE'] =  $this->getStateCode($this->order->billing['state']);    
		$payDataOperacion['CSBTSTREET1'] = $this->order->billing['street_address'];
		//$payDataOperacion ['CSBTSTREET2'] = $this->order->billing_address_2;
		$payDataOperacion['CSPTCURRENCY'] = "ARS";
		$payDataOperacion['CSPTGRANDTOTALAMOUNT'] = number_format($payDataOperacion['AMOUNT'],2,".","");
        
		if(!empty($this->customer)) {
        	//CSMDD7 - Fecha Registro Comprador (num Dias) - ver que pasa si es guest");
        	$payDataOperacion['CSMDD7'] = $this->getDaysQty($this->customer['date_added']);
			//CSMDD8 - Usuario Guest? (S/N). En caso de ser Y, el campo CSMDD9 no deber&acute; enviarse");
            $payDataOperacion['CSMDD8'] = "S";
			//Customer password Hash: criptograma asociado al password del comprador final");
            $payDataOperacion['CSMDD9'] = $this->customer['password'];   
            //Histórica de compras del comprador (Num transacciones).
            $payDataOperacion['CSMDD10'] = $this->getQtyOrders($this->customer['customer_id']);
            
        } else {
            $payDataOperacion['CSMDD8'] = "N";
        }

		//$this->log->writeTP(" CSMDD11 Customer Cell Phone");
		//$payDataOperacion['CSMDD11'] = phone::clean($this->order['telephone']);

		return $payDataOperacion;
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

	private function _sanitize_string($string){
		$string = htmlspecialchars_decode($string);

		$re = "/\\[(.*?)\\]|<(.*?)\\>/i";
		$subst = "";
		$string = preg_replace($re, $subst, $string);

		$replace = array("!","'","\'","\"","  ","$","\\","\n","\r",
			'\n','\r','\t',"\t","\n\r",'\n\r','&nbsp;','&ntilde;',".,",",.","+", "%", "-", ")", "(", "°");
		$string = str_replace($replace, '', $string);

		$cods = array('\u00c1','\u00e1','\u00c9','\u00e9','\u00cd','\u00ed','\u00d3','\u00f3','\u00da','\u00fa','\u00dc','\u00fc','\u00d1','\u00f1');
		$susts = array('Á','á','É','é','Í','í','Ó','ó','Ú','ú','Ü','ü','Ṅ','ñ');
		$string = str_replace($cods, $susts, $string);

		$no_permitidas= array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
		$permitidas= array ("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");
		$string = str_replace($no_permitidas, $permitidas ,$string);

		return $string;
	}

	protected function getMultipleProductsInfo(){
		$payDataOperacion = array();

		$productcode_array = array();
		$description_array = array();
		$name_array = array();
		$sku_array = array();
		$price_array = array();
		$quantity_array = array();
		$totalamount_array = array();
		

		foreach($this->order->products as $item){
			
			$product_id = current(explode(":",$item['id']));
			global $db;
		    $sql = "SELECT cd.categories_name 
					FROM ".TABLE_CATEGORIES_DESCRIPTION." cd 
					INNER JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." ptc
					ON ptc.categories_id = cd.categories_id 
					WHERE ptc.products_id = ".$product_id." AND cd.language_id = 1 LIMIT 1";
		    $res = $db->Execute($sql);
			$row = $res->fields;

			$productcode_array[] = $row['categories_name'] ?: 'default';

			$description_array[] = $item['name'];
			$name_array[] = $item['name'];
			$sku_array[] = empty($item['model']) ? $product_id : $item['model'];
			$product_price = number_format($item['price'], 2, ".", "");
			$price_array [] = $product_price;
			$product_quantity = $item['qty'];
			$quantity_array [] = intval($product_quantity);
			$totalamount_array[] = number_format($product_quantity * $product_price, 2, ".", "");
		}
		$payDataOperacion ['CSITPRODUCTCODE'] = join('#', $productcode_array);
		$payDataOperacion ['CSITPRODUCTDESCRIPTION'] = join("#", $description_array);
		$payDataOperacion ['CSITPRODUCTNAME'] = join("#", $name_array);
		$payDataOperacion ['CSITPRODUCTSKU'] = join("#", $sku_array);
		$payDataOperacion ['CSITTOTALAMOUNT'] = join("#", $totalamount_array);
		$payDataOperacion ['CSITQUANTITY'] = join("#", $quantity_array);
		$payDataOperacion ['CSITUNITPRICE'] = join("#", $price_array);
		return $payDataOperacion;
	}

    private function get_todo_pago_client_ip() {
        
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

	public function getField($datasources){
		$return = "";
		//try{
			$return = $this->_sanitize_string($datasources);
		//}catch(Exception $e){
			//Log
		//}

		return $return;
	}

	protected abstract function completeCFVertical();

}