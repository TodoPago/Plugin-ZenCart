<?php

define('VERSION', '0.2.0');

class TodoPagoSdk{

	private $host = NULL;
	private $port = NULL;
	private $user = NULL;
	private $pass = NULL;
	private $connection_timeout = NULL;
	private $local_cert = NULL;
	
	public function __construct($header_http_array, $wsdl, $end_point){
		$this->wsdl = $wsdl;
		$this->end_point = $end_point;
		$this->header_http = $this->getHeaderHttp($header_http_array);
	
	}
	
	//devuelve el http header 
	private function getHeaderHttp($header_http_array){
		$header = "";
		foreach($header_http_array as $key=>$value){
			$header .= "$key : $value\r\n";
		}
		
		return $header;
	}
	/*
	* configuraciones
	/

	/**
	* Setea parametros en caso de utilizar proxy
	* ejemplo:
	* $todopago->setProxyParameters('199.0.1.33', '80', 'usuario','contrasenya');
	*/
	public function setProxyParameters($host = null, $port = null, $user = null, $pass = null){
		$this->host = $host;
		$this->port = $port;
		$this->user = $user;
		$this->pass = $pass;
	}
	
	/**
	* Setea time out (deaulft=NULL)
	* ejemplo:
	* $todopago->setConnectionTimeout(1000);
	*/
	public function setConnectionTimeout($connection_timeout){
		$this->connection_timeout = $connection_timeout;
	}
	
	/**
	* Setea ruta del certificado .pem (deaulft=NULL)
	* ejemplo:
	* $todopago->setLocalCert('c:/miscertificados/decidir.pem');
	*/	
	public function setLocalCert($local_cert){
		$this->local_cert= file_get_contents($local_cert);
	}
	

	/*
	* servicio : SendAuthorizeRequest
	*/

	public function sendAuthorizeRequest($options_comercio, $options_operacion){
		// parseo de los valores enviados por el e-commerce/custompage
		$authorizeRequest = $this->parseToAuthorizeRequest($options_comercio, $options_operacion);
		
		$authorizeRequestResponse = $this->getAuthorizeRequestResponse($authorizeRequest);

		//devuelve el formato de array el resultado de de la operación SendAuthorizeRequest
		$authorizeRequestResponseValues = $this->parseAuthorizeRequestResponseToArray($authorizeRequestResponse);

		return $authorizeRequestResponseValues;
	}
	
	private function parseToAuthorizeRequest($options_comercio, $options_operacion){
		$authorizeRequest = (object)$options_comercio;
		$authorizeRequest->Payload = $this->getPayload($options_operacion);
		return $authorizeRequest;
	}
	
	//devuleve el cliente SOAP
	private function getClientSoap($typo){
		$local_wsdl = $this->wsdl["$typo"];
		$local_end_point = $this->end_point."$typo";
		$context = array('http' =>
			array(
				'header'  => $this->header_http
							
			)
		);
         
		$clientSoap = new SoapClient($local_wsdl, array(
				
				'stream_context' => stream_context_create($context),
				'local_cert'=>($this->local_cert), 
				'connection_timeout' => $this->connection_timeout,
				'location' => $local_end_point,
				'encoding' => 'UTF-8',
				'proxy_host' => $this->host,
				'proxy_port' => $this->port,
				'proxy_login' => $this->user,
				'proxy_password' => $this->pass
			));

		return $clientSoap;
	}
	
	private function getAuthorizeRequestResponse($authorizeRequest){
		$clientSoap = $this->getClientSoap('Authorize');

		$authorizeRequestResponse = $clientSoap->SendAuthorizeRequest($authorizeRequest);

		return $authorizeRequestResponse;
	}

	private function parseAuthorizeRequestResponseToArray($authorizeRequestResponse){
		$authorizeRequestResponseOptions = json_decode(json_encode($authorizeRequestResponse), true);

		return $authorizeRequestResponseOptions;
	}

	private function getPayload($optionsAuthorize){
		$xmlPayload = "<Request>";
		foreach($optionsAuthorize as $key => $value){
	
			$xmlPayload .= "<" . $key . ">" . $value . "</" . $key . ">";
		}
		$xmlPayload .= "</Request>";
		return $xmlPayload;
	}

	/*
	* servicio : GetAuthorizeAnswer
	*/

	public function getAuthorizeAnswer($optionsAnswer){
		$authorizeAnswer = $this->parseToAuthorizeAnswer($optionsAnswer);

		$authorizeAnswerResponse = $this->getAuthorizeAnswerResponse($authorizeAnswer);

		$authorizeAnswerResponseValues = $this->parseAuthorizeAnswerResponseToArray($authorizeAnswerResponse);

		return $authorizeAnswerResponseValues;
	}

	private function parseToAuthorizeAnswer($optionsAnswer){
		
		$obj_options_answer = (object) $optionsAnswer;
		
		return $obj_options_answer;
	}

	private function getAuthorizeAnswerResponse($authorizeAnswer){
		$client = $this->getClientSoap('Authorize');
		$authorizeAnswer = $client->GetAuthorizeAnswer($authorizeAnswer);
		return $authorizeAnswer;
	}

	private function parseAuthorizeAnswerResponseToArray($authorizeAnswerResponse){
		$authorizeAnswerResponseOptions = json_decode(json_encode($authorizeAnswerResponse), true);

		return $authorizeAnswerResponseOptions;
	}
	
	// GetAll, devuelve todos lo metodos de pago
	public function getAllPaymentMethods($merchant){
		$clientSoap = $this->getClientSoap('PaymentMethods');
		
		$get_all_data = (object) $merchant;
		
		$getAll = $clientSoap->GetAll($get_all_data);
		return json_decode(json_encode($getAll), TRUE);
	}
	
	//GetByOperationId, devuleve el estado de la operacion
	public function getStatus($arr_datos_status){
		$clientSoap = $this->getClientSoap('Operations');
		
		$obj_datos_to_status = (object) $arr_datos_status;
		
		$get_status = $clientSoap->GetByOperationId($obj_datos_to_status);
		
		return json_decode(json_encode($get_status), TRUE);
	}
	
}
