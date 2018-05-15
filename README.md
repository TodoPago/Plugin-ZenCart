<a name="inicio"></a>
ZenCart - módulo Todo Pago (v1.5.x)
============

Plug in para la integración con gateway de pago <strong>Todo Pago</strong>
- [Consideraciones Generales](#consideracionesgenerales)
- [Instalación](#instalacion)
- [Configuración](#confplugin)
  - [Configuración plugin](#confplugin)
  - [Obtener credenciales](#obtenercredenciales)
  - [Formulario Hibrido](#formHibrido)
- [Características](#features) 
  - [Devoluciones](#devoluciones)
  - [Datos adiccionales para prevención de fraude](#cybersource) 
  - [Tiempo de vida del formulario](#temform)
  - [Validación de datos con Google Maps](#gmapsdatos)
- [Tablas de referencia](#tablas)
- [Tabla de errores](#codigoerrores)
- [Versiones disponibles](#availableversions)

<a name="consideracionesgenerales"></a>
## Consideraciones Generales
El plug in de pagos de Todo Pago, provee a las tiendas Zen-Cart de un nuevo m&eacute;todo de pago, integrando la tienda al gateway de pago.
La versión de este plug in esta testeada en PHP 5.3-5.4-5.6, Zen-Cart 1.5.4

<a name="instalacion"></a>
## Instalación
1.  Renombrar la carpeta Administrator al nombre que le hayamos puesto a nuestra carpeta de administrador y subir los archivos a la raíz del sitio
2.  Ir a Modules -> Payment -> TodoPago -> Install

![imagen de instalacion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/install.png)
Observación: Descomentar <strong>extension=php_soap.dll</strong> y <strong>extension=php_openssl.dll</strong> del php.ini, ya que para la conexión al gateway se utiliza la clase SoapClient del API de PHP.

[<sub>Volver a inicio</sub>](#inicio)


<a name="confplugin"></a>
## Configuración
<a name="confplugin"></a>
#### Configuración plugin
Para llegar al menu de configuración ir a:  Tools -> TodoPago Configuración

En esta pantalla existen 3 tabs

1.  Configuración: Se dan de alta los valores para el funcionamiento de TodoPago
2.  Productos: En esta tab se le asignan los campos a los productos para Prevención de Fraude. Los campos nuevos se agregan automáticamente. Sólo hay que asignar los valores correspondientes a cada producto
3.  Ordenes: Aquí estarán las órdenes y el botón para Ver Status para ver las actualizaciones de estado

#### Configuración

<strong>Authorization HTTP:</strong> Codigo de autorización otorgado por Todo Pago. Ejemplo: PRISMA 912EC803B2CE49E4A541068D12345678<br />
<strong>Security Code:</strong> Código provisto por Todo Pago<br />
<strong>ID Site Todo Pago:</strong> Nombre de comercio provisto por Todo Pago<br />
<strong>Máxima cantidad de cuotas:</strong> Máxima cantidad de cuotas para la operación, en caso de hacer click en el checkbox a su derecha, el campo no será tenido en cuenta<br />
<strong>End Point:</strong> Provisto por Todo Pago (es una url)<br />
<strong>WSDL:</strong> WSDLs en formato JSON. Ejemplo: {"Authorize":"https://developers.todopago.com.ar/services/Authorize?wsdl","PaymentMethods":"https://developers.todopago.com.ar/services/PaymentMethods?wsdl","Operations":"https://developers.todopago.com.ar/services/Operations?wsdl"}<br />

![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/todopago_maxcuotas_1.png)

<a name="cosulta_transacciones"></a>
#### Consulta de Transacciones
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/todopago-transacciones.png)
Para las devoluciones se debe agregar los estados "Refund" y "Partial Refund", desde la seccion, Admin -> Localization -> Order Status.
[<sub>Volver a inicio</sub>](#inicio)


<a name="obtenercredenciales"></a>
#### Obtener crendenciales
Se puede obtener los datos de configuracion del plugin con solo loguearte con tus credenciales de Todopago. </br>
a. Ir a la opcion Obtener credenciales<br>
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/todopago_maxcuotas_2a.png)
<br>b. En el popup loguearse con el mail y password de Todopago.
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/todopago_maxcuotas_2_credencial.png)
<br>c. Los datos se cargaran automaticamente en los campos Merchant ID y Security code en el ambiente correspondiente (Desarrollo o produccion ) y solo hay que hacer click en el boton guardar datos y listo.
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/todopago_maxcuotas_2_1.png)
[<sub>Volver a inicio</sub>](#inicio)

<a name="formHibrido"></a>
#### Formulario Hibrido
En la configuracion del plugin tambien estara la posibilidad de mostrarle al cliente el formulario de pago de TodoPago integrada en el sitio.
Para esto , en la configuracion se debe seleccionar el campo formulario integrado al e-commerce:
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/todopago_maxcuotas_2_forms.png)
<sub></br>Del lado del cliente el formulario se vera asi:</br></sub>
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/form_hibridov3_gen.png)
</br>
<br>
El formulario tiene dos formas de pago, ingresando los datos de una tarjeta ó utilizando la billetera de Todopago. Al ir a "Pagar con Billetera" desplegara una ventana que permitira ingresar a billetera y realizar el pago.
<br>
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/formhibridov3_billetera_zencart.png)
[<sub>Volver a inicio</sub>](#inicio)

<a name="temform"></a>
#### Tiempo de vida del formulario

En la configuración del formulario se puede definir el tiempo de duración que el formulario de pago permanecerá activo. El valor permitido es de tipo numerico entero en milisegundos entre un rango de tiempo de 300000 (5 minutos) y 21600000 (6hs).
Si no se envia este campo, por defecto el formulario vivira 1800000 (30 minutos).  

[<sub>Volver a inicio</sub>](#inicio)

<a name="temform"></a>
#### Validación de datos con Google Maps

Se pueden normalizar los datos de facturación y entrega usados al momento de realizar la compra. El plugin envía a Google Maps los datos ingresados por el usuario y corrige con la información devuelta. Las correcciones más habituales que realiza son, por ejemplo, del código postal y del nombre de la ciudad. En caso de que Google no encuentre la dirección ingresada, se registra como warn en el log de TodoPago incluyendo la información que el usuario cargó.

Esta validación es optativa y puede ser configurada desde el menú de configuración de TodoPago.

[<sub>Volver a inicio</sub>](#inicio)

<a name="features"></a>
## Caracteristicas
<a name="devoluciones"></a>
<a name="devoluciones"></a>
#### Devoluciones
TodoPago permite realizar la devolucion total o parcial de dinero de una orden de compra.<br>
Para ello dirigirse en el menú a Tools->TodoPago configuracion->Ordenes, en esta pagina se encuentra las ordenes de compra realizadas con Todopago.<br>
En cada orden se encuentra la opcion "Devolver" que mostrara un modal con la opcion de devolucion total y devolucion parcial junto con el campo para ingresar el monto.

![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/devoluciones-zencart.png)

<a name="cybersource"></a>
#### Prevención de Fraude
Los campos se crean automáticamente y se asignan en Tools -> TodoPago Configuración -> Productos

#### Consideraciones Generales (para todas las verticales, por defecto RETAIL)
El plug in, toma valores estándar del framework para validar los datos del comprador.
Para acceder a los datos del vendedor, productos y carrito se usa el  objeto $order que llega como parámetro en los métodos en los que se necesita.
Este es un ejemplo de la mayoría de los campos que se necesitan para comenzar la operación <br />

```PHP
'CSBTCITY' => $cart->billing['state'],
'CSBTCOUNTRY' => $cart->billing['country']['iso_code_2'], 	
'CSBTCUSTOMERID' => $customer_id,
'CSBTIPADDRESS' => $this->get_todo_pago_client_ip(), 	
'CSBTEMAIL' => $cart->customer['email_address'], 		
'CSBTFIRSTNAME'=> $cart->customer['firstname'],
'CSBTLASTNAME'=> $cart->customer['lastname'],
'CSBTPHONENUMBER'=> $cart->customer['telephone'],
'CSBTPOSTALCODE'=> $cart->customer['postcode'], 	
'CSBTSTATE' => $this->tp_states,
'CSBTSTREET1' => $cart->customer['street_address'] ,
```

[<sub>Volver a inicio</sub>](#inicio)

<a name="codigoerrores"></a>
## Tabla de errores operativos

<table>
<tr><th>Id mensaje</th><th>Mensaje</th></tr>
<tr><td>-1</td><td>Aprobada.</td></tr>
<tr><td>1100</td><td>El monto ingresado es menor al mínimo permitido</td></tr>
<tr><td>1101</td><td>El monto ingresado supera el máximo permitido.</td></tr>
<tr><td>1102</td><td>Tu tarjeta no corresponde con el banco seleccionado. Iniciá nuevamente la compra.</td></tr>
<tr><td>1104</td><td>El precio ingresado supera al máximo permitido.</td></tr>
<tr><td>1105</td><td>El precio ingresado es menor al mínimo permitido.</td></tr>
<tr><td>1070</td><td>El plazo para realizar esta devolución caducó.</td></tr>
<tr><td>1081</td><td>El saldo de tu cuenta es insuficiente para realizar esta devolución.</td></tr>
<tr><td>2010</td><td>En este momento la operación no pudo ser realizada. Por favor intentá más tarde. Volver a Resumen.</td></tr>
<tr><td>2031</td><td>En este momento la validación no pudo ser realizada, por favor intentá más tarde.</td></tr>
<tr><td>2050</td><td>Tu compra no puede ser realizada. Comunicate con tu vendedor.</td></tr>
<tr><td>2051</td><td>Tu compra no pudo ser procesada. Comunicate con tu vendedor.</td></tr>
<tr><td>2052</td><td>Tu compra no pudo ser procesada. Comunicate con tu vendedor. </td></tr>
<tr><td>2053</td><td>Tu compra no pudo ser procesada. Comunicate con tu vendedor.</td></tr>
<tr><td>2054</td><td>El producto que querés comprar se encuentra agotado. Por favor contactate con tu vendedor.</td></tr>
<tr><td>2056</td><td>Tu compra no pudo ser procesada.</td></tr>
<tr><td>2057</td><td>La operación no pudo ser procesada. Por favor intentá más tarde.</td></tr>
<tr><td>2058</td><td>La operación fué rechazada. Comunicate con el 0800 333 0010.</td></tr>
<tr><td>2059</td><td>La operación no pudo ser procesada. Por favor intentá más tarde.</td></tr>
<tr><td>2062</td><td>Tu compra no puede ser realizada. Comunicate con tu vendedor.</td></tr>
<tr><td>2064</td><td>Tu compra no puede ser realizada. Comunicate con tu vendedor.</td></tr>
<tr><td>2074</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>2075</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>2076</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>90000</td><td>La cuenta destino de los fondos es inválida. Verificá la información ingresada en Mi Perfil.</td></tr>
<tr><td>90001</td><td>La cuenta ingresada no pertenece al CUIT/ CUIL registrado.</td></tr>
<tr><td>90002</td><td>No pudimos validar tu CUIT/CUIL.  Comunicate con nosotros <a href="#contacto" target="_blank">acá</a> para más información.</td></tr>
<tr><td>99900</td><td>Tu compra fue exitosa.</td></tr>
<tr><td>99901</td><td>Tu Billetera Virtual no tiene medios de pago adheridos. Ingresá a tu cuenta de Todo Pago y cargá tus tarjetas.</td></tr>
<tr><td>99902</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99903</td><td>Lo sentimos, hubo un error al procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99904</td><td>El saldo de tu tarjeta no te permite realizar esta compra. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99905</td><td>En este momento la operación no pudo ser procesada. Intentá nuevamente.</td></tr>
<tr><td>99907</td><td>Tu compra no pudo ser procesada. Comunicate con tu vendedor. </td></tr>
<tr><td>99910</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99911</td><td>Lo sentimos, se terminó el tiempo para confirmar esta compra. Por favor iniciá nuevamente el proceso de pago.</td></tr>
<tr><td>99950</td><td>Tu compra no pudo ser procesada.</td></tr>
<tr><td>99960</td><td>Esta compra requiere autorización de VISA. Comunicate al número que se encuentra al dorso de tu tarjeta.</td></tr>
<tr><td>99961</td><td>Esta compra requiere autorización de AMEX. Comunicate al número que se encuentra al dorso de tu tarjeta.</td></tr>
<tr><td>99970</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99971</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99972</td><td>Tu compra no pudo realizarse. Iniciala nuevamente utilizando otro medio de pago. </td></tr>
<tr><td>99974</td><td>Tu compra no pudo realizarse. Iniciala nuevamente utilizando otro medio de pago. </td></tr>
<tr><td>99975</td><td>Tu compra no pudo realizarse. Iniciala nuevamente utilizando otro medio de pago. </td></tr>
<tr><td>99977</td><td>Tu compra no pudo realizarse. Iniciala nuevamente utilizando otro medio de pago. </td></tr>
<tr><td>99979</td><td>Tu compra no pudo realizarse. Iniciala nuevamente utilizando otro medio de pago. </td></tr>
<tr><td>99978</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99979</td><td>Lo sentimos, el pago no pudo ser procesado.</td></tr>
<tr><td>99980</td><td>Ya realizaste una compra por el mismo importe. Iniciala nuevamente en unos minutos.</td></tr>
<tr><td>99982</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando.</td></tr>
<tr><td>99983</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99984</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99985</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99986</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99987</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99988</td><td>Tu compra no pudo ser procesada. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99989</td><td>Tu tarjeta no autorizó tu compra. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99990</td><td>Tu tarjeta está vencida. Iniciá nuevamente la compra utilizando otro medio de pago.</td></tr>
<tr><td>99991</td><td>Los datos informados son incorrectos. Por favor ingresalos nuevamente.</td></tr>
<tr><td>99992</td><td>El saldo de tu tarjeta no te permite realizar esta compra. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99993</td><td>Tu tarjeta no autorizó el pago. Iniciá nuevamente la compra utilizando otro medio de pago.</td></tr>
<tr><td>99994</td><td>El saldo de tu tarjeta no te permite realizar esta operacion.</td></tr>
<tr><td>99995</td><td>Tu tarjeta no autorizó tu compra. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
<tr><td>99996</td><td>La operación fué rechazada por el medio de pago porque el monto ingresado es inválido.</td></tr>
<tr><td>99997</td><td>Lo sentimos, en este momento la operación no puede ser realizada. Por favor intentá más tarde.</td></tr>
<tr><td>99998</td><td>Tu tarjeta no autorizó tu compra. Iniciala nuevamente utilizando otro medio de pago.
<tr><td>99999</td><td>Tu compra no pudo realizarse. Iniciala nuevamente utilizando otro medio de pago.</td></tr>
</table>

[<sub>Volver a inicio</sub>](#inicio)

<a name="interrores"></a>
## Tabla de errores de integración

<table>
<tr><td>**Id mensaje**</td><td>**Descripción**</td></tr>
<tr><td>98001 </td><td>ERROR: El campo CSBTCITY es requerido</td></tr>
<tr><td>98002 </td><td>ERROR: El campo CSBTCOUNTRY es requerido</td></tr>
<tr><td>98003 </td><td>ERROR: El campo CSBTCUSTOMERID es requerido</td></tr>
<tr><td>98004 </td><td>ERROR: El campo CSBTIPADDRESS es requerido</td></tr>
<tr><td>98005 </td><td>ERROR: El campo CSBTEMAIL es requerido</td></tr>
<tr><td>98006 </td><td>ERROR: El campo CSBTFIRSTNAME es requerido</td></tr>
<tr><td>98007 </td><td>ERROR: El campo CSBTLASTNAME es requerido</td></tr>
<tr><td>98008 </td><td>ERROR: El campo CSBTPHONENUMBER es requerido</td></tr>
<tr><td>98009 </td><td>ERROR: El campo CSBTPOSTALCODE es requerido</td></tr>
<tr><td>98010 </td><td>ERROR: El campo CSBTSTATE es requerido</td></tr>
<tr><td>98011 </td><td>ERROR: El campo CSBTSTREET1 es requerido</td></tr>
<tr><td>98012 </td><td>ERROR: El campo CSBTSTREET2 es requerido</td></tr>
<tr><td>98013 </td><td>ERROR: El campo CSPTCURRENCY es requerido</td></tr>
<tr><td>98014 </td><td>ERROR: El campo CSPTGRANDTOTALAMOUNT es requerido</td></tr>
<tr><td>98015 </td><td>ERROR: El campo CSMDD7 es requerido</td></tr>
<tr><td>98016 </td><td>ERROR: El campo CSMDD8 es requerido</td></tr>
<tr><td>98017 </td><td>ERROR: El campo CSMDD9 es requerido</td></tr>
<tr><td>98018 </td><td>ERROR: El campo CSMDD10 es requerido</td></tr>
<tr><td>98019 </td><td>ERROR: El campo CSMDD11 es requerido</td></tr>
<tr><td>98020 </td><td>ERROR: El campo CSSTCITY es requerido</td></tr>
<tr><td>98021 </td><td>ERROR: El campo CSSTCOUNTRY es requerido</td></tr>
<tr><td>98022 </td><td>ERROR: El campo CSSTEMAIL es requerido</td></tr>
<tr><td>98023 </td><td>ERROR: El campo CSSTFIRSTNAME es requerido</td></tr>
<tr><td>98024 </td><td>ERROR: El campo CSSTLASTNAME es requerido</td></tr>
<tr><td>98025 </td><td>ERROR: El campo CSSTPHONENUMBER es requerido</td></tr>
<tr><td>98026 </td><td>ERROR: El campo CSSTPOSTALCODE es requerido</td></tr>
<tr><td>98027 </td><td>ERROR: El campo CSSTSTATE es requerido</td></tr>
<tr><td>98028 </td><td>ERROR: El campo CSSTSTREET1 es requerido</td></tr>
<tr><td>98029 </td><td>ERROR: El campo CSMDD12 es requerido</td></tr>
<tr><td>98030 </td><td>ERROR: El campo CSMDD13 es requerido</td></tr>
<tr><td>98031 </td><td>ERROR: El campo CSMDD14 es requerido</td></tr>
<tr><td>98032 </td><td>ERROR: El campo CSMDD15 es requerido</td></tr>
<tr><td>98033 </td><td>ERROR: El campo CSMDD16 es requerido</td></tr>
<tr><td>98034 </td><td>ERROR: El campo CSITPRODUCTCODE es requerido</td></tr>
<tr><td>98035 </td><td>ERROR: El campo CSITPRODUCTDESCRIPTION es requerido</td></tr>
<tr><td>98036 </td><td>ERROR: El campo CSITPRODUCTNAME es requerido</td></tr>
<tr><td>98037 </td><td>ERROR: El campo CSITPRODUCTSKU es requerido</td></tr>
<tr><td>98038 </td><td>ERROR: El campo CSITTOTALAMOUNT es requerido</td></tr>
<tr><td>98039 </td><td>ERROR: El campo CSITQUANTITY es requerido</td></tr>
<tr><td>98040 </td><td>ERROR: El campo CSITUNITPRICE es requerido</td></tr>
<tr><td>98101 </td><td>ERROR: El formato del campo CSBTCITY es incorrecto</td></tr>
<tr><td>98102 </td><td>ERROR: El formato del campo CSBTCOUNTRY es incorrecto</td></tr>
<tr><td>98103 </td><td>ERROR: El formato del campo CSBTCUSTOMERID es incorrecto</td></tr>
<tr><td>98104 </td><td>ERROR: El formato del campo CSBTIPADDRESS es incorrecto</td></tr>
<tr><td>98105 </td><td>ERROR: El formato del campo CSBTEMAIL es incorrecto</td></tr>
<tr><td>98106 </td><td>ERROR: El formato del campo CSBTFIRSTNAME es incorrecto</td></tr>
<tr><td>98107 </td><td>ERROR: El formato del campo CSBTLASTNAME es incorrecto</td></tr>
<tr><td>98108 </td><td>ERROR: El formato del campo CSBTPHONENUMBER es incorrecto</td></tr>
<tr><td>98109 </td><td>ERROR: El formato del campo CSBTPOSTALCODE es incorrecto</td></tr>
<tr><td>98110 </td><td>ERROR: El formato del campo CSBTSTATE es incorrecto</td></tr>
<tr><td>98111 </td><td>ERROR: El formato del campo CSBTSTREET1 es incorrecto</td></tr>
<tr><td>98112 </td><td>ERROR: El formato del campo CSBTSTREET2 es incorrecto</td></tr>
<tr><td>98113 </td><td>ERROR: El formato del campo CSPTCURRENCY es incorrecto</td></tr>
<tr><td>98114 </td><td>ERROR: El formato del campo CSPTGRANDTOTALAMOUNT es incorrecto</td></tr>
<tr><td>98115 </td><td>ERROR: El formato del campo CSMDD7 es incorrecto</td></tr>
<tr><td>98116 </td><td>ERROR: El formato del campo CSMDD8 es incorrecto</td></tr>
<tr><td>98117 </td><td>ERROR: El formato del campo CSMDD9 es incorrecto</td></tr>
<tr><td>98118 </td><td>ERROR: El formato del campo CSMDD10 es incorrecto</td></tr>
<tr><td>98119 </td><td>ERROR: El formato del campo CSMDD11 es incorrecto</td></tr>
<tr><td>98120 </td><td>ERROR: El formato del campo CSSTCITY es incorrecto</td></tr>
<tr><td>98121 </td><td>ERROR: El formato del campo CSSTCOUNTRY es incorrecto</td></tr>
<tr><td>98122 </td><td>ERROR: El formato del campo CSSTEMAIL es incorrecto</td></tr>
<tr><td>98123 </td><td>ERROR: El formato del campo CSSTFIRSTNAME es incorrecto</td></tr>
<tr><td>98124 </td><td>ERROR: El formato del campo CSSTLASTNAME es incorrecto</td></tr>
<tr><td>98125 </td><td>ERROR: El formato del campo CSSTPHONENUMBER es incorrecto</td></tr>
<tr><td>98126 </td><td>ERROR: El formato del campo CSSTPOSTALCODE es incorrecto</td></tr>
<tr><td>98127 </td><td>ERROR: El formato del campo CSSTSTATE es incorrecto</td></tr>
<tr><td>98128 </td><td>ERROR: El formato del campo CSSTSTREET1 es incorrecto</td></tr>
<tr><td>98129 </td><td>ERROR: El formato del campo CSMDD12 es incorrecto</td></tr>
<tr><td>98130 </td><td>ERROR: El formato del campo CSMDD13 es incorrecto</td></tr>
<tr><td>98131 </td><td>ERROR: El formato del campo CSMDD14 es incorrecto</td></tr>
<tr><td>98132 </td><td>ERROR: El formato del campo CSMDD15 es incorrecto</td></tr>
<tr><td>98133 </td><td>ERROR: El formato del campo CSMDD16 es incorrecto</td></tr>
<tr><td>98134 </td><td>ERROR: El formato del campo CSITPRODUCTCODE es incorrecto</td></tr>
<tr><td>98135 </td><td>ERROR: El formato del campo CSITPRODUCTDESCRIPTION es incorrecto</td></tr>
<tr><td>98136 </td><td>ERROR: El formato del campo CSITPRODUCTNAME es incorrecto</td></tr>
<tr><td>98137 </td><td>ERROR: El formato del campo CSITPRODUCTSKU es incorrecto</td></tr>
<tr><td>98138 </td><td>ERROR: El formato del campo CSITTOTALAMOUNT es incorrecto</td></tr>
<tr><td>98139 </td><td>ERROR: El formato del campo CSITQUANTITY es incorrecto</td></tr>
<tr><td>98140 </td><td>ERROR: El formato del campo CSITUNITPRICE es incorrecto</td></tr>
<tr><td>98201 </td><td>ERROR: Existen errores en la información de los productos</td></tr>
<tr><td>98202 </td><td>ERROR: Existen errores en la información de CSITPRODUCTDESCRIPTION los productos</td></tr>
<tr><td>98203 </td><td>ERROR: Existen errores en la información de CSITPRODUCTNAME los productos</td></tr>
<tr><td>98204 </td><td>ERROR: Existen errores en la información de CSITPRODUCTSKU los productos</td></tr>
<tr><td>98205 </td><td>ERROR: Existen errores en la información de CSITTOTALAMOUNT los productos</td></tr>
<tr><td>98206 </td><td>ERROR: Existen errores en la información de CSITQUANTITY los productos</td></tr>
<tr><td>98207 </td><td>ERROR: Existen errores en la información de CSITUNITPRICE de los productos</td></tr>
</table>

[<sub>Volver a inicio</sub>](#inicio)

<a name="tablas"></a>
## Tablas de Referencia
###### [Provincias](#p)

<a name="p"></a>
<p>Provincias</p>
<table>
<tr><th>Provincia</th><th>Código</th></tr>
<tr><td>CABA</td><td>C</td></tr>
<tr><td>Buenos Aires</td><td>B</td></tr>
<tr><td>Catamarca</td><td>K</td></tr>
<tr><td>Chaco</td><td>H</td></tr>
<tr><td>Chubut</td><td>U</td></tr>
<tr><td>Córdoba</td><td>X</td></tr>
<tr><td>Corrientes</td><td>W</td></tr>
<tr><td>Entre Ríos</td><td>R</td></tr>
<tr><td>Formosa</td><td>P</td></tr>
<tr><td>Jujuy</td><td>Y</td></tr>
<tr><td>La Pampa</td><td>L</td></tr>
<tr><td>La Rioja</td><td>F</td></tr>
<tr><td>Mendoza</td><td>M</td></tr>
<tr><td>Misiones</td><td>N</td></tr>
<tr><td>Neuquén</td><td>Q</td></tr>
<tr><td>Río Negro</td><td>R</td></tr>
<tr><td>Salta</td><td>A</td></tr>
<tr><td>San Juan</td><td>J</td></tr>
<tr><td>San Luis</td><td>D</td></tr>
<tr><td>Santa Cruz</td><td>Z</td></tr>
<tr><td>Santa Fe</td><td>S</td></tr>
<tr><td>Santiago del Estero</td><td>G</td></tr>
<tr><td>Tierra del Fuego</td><td>V</td></tr>
<tr><td>Tucumán</td><td>T</td></tr>
</table>

#### Muy Importante
Provincias: Al ser un campo MANDATORIO para enviar y propio del plugin este campo se completa por parte del usuario al momento del check out.

[<sub>Volver a inicio</sub>](#inicio)


<a name="availableversions"></a>
## Versiones Disponibles
<table>
  <thead>
    <tr>
      <th>Version del Plugin</th>
      <th>Estado</th>
      <th>Versiones Compatibles</th>
    </tr>
  <thead>
  <tbody>
    <tr>
      <td><a href="https://github.com/TodoPago/Plugin-ZenCart/archive/master.zip">v1.7.0</a></td>
      <td>Stable (Current version)</td>
      <td>Community Edition 1.5.x
      </td>
    </tr>
  </tbody>
</table>

*Click on the links above for instructions on installing and configuring the module.*

[<sub>Volver a inicio</sub>](#inicio)
