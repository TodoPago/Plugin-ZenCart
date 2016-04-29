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
  - [Devoluciones] (#devoluciones)
  - [Datos adiccionales para prevención de fraude](#cybersource) 
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
##Configuración
<a name="confplugin"></a>
####Configuración plugin
Para llegar al menu de configuración ir a:  Tools -> TodoPago Configuración 

En esta pantalla existen 3 tabs

1.  Configuración: Se dan de alta los valores para el funcionamiento de TodoPago
2.  Productos: En esta tab se le asignan los campos a los productos para Prevención de Fraude. Los campos nuevos se agregan automáticamente. Sólo hay que asignar los valores correspondientes a cada producto
3.  Ordenes: Aquí estarán las órdenes y el botón para Ver Status para ver las actualizaciones de estado

Configuración

<strong>Authorization HTTP:</strong> Codigo de autorización otorgado por Todo Pago. Ejemplo: PRISMA 912EC803B2CE49E4A541068D12345678<br />
<strong>Security Code:</strong> Código provisto por Todo Pago<br />
<strong>ID Site Todo Pago:</strong> Nombre de comercio provisto por Todo Pago<br />
<strong>End Point:</strong> Provisto por Todo Pago (es una url)<br />
<strong>WSDL:</strong> WSDLs en formato JSON. Ejemplo: {"Authorize":"https://developers.todopago.com.ar/services/Authorize?wsdl","PaymentMethods":"https://developers.todopago.com.ar/services/PaymentMethods?wsdl","Operations":"https://developers.todopago.com.ar/services/Operations?wsdl"}

![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/config.png)

<a name="cosulta_transacciones"></a>
#### Consulta de Transacciones
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/todopago-transacciones.png)
Para las devoluciones se debe agregar los estados "Refund" y "Partial Refund", desde la seccion, Admin -> Localization -> Order Status.
[<sub>Volver a inicio</sub>](#inicio)


<a name="obtenercredenciales"></a>
####Obtener crendenciales
Se puede obtener los datos de configuracion del plugin con solo loguearte con tus credenciales de Todopago. </br>
a. Ir a la opcion Obtener credenciales
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/obtenercredenciales_1.png)
b. En el popup loguearse con el mail y password de Todopago.
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/obtenercredenciales_2.png)
c. Los datos se cargaran automaticamente en los campos Merchant ID y Security code en el ambiente correspondiente (Desarrollo o produccion ) y solo hay que hacer click en el boton guardar datos y listo.
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/obtenercredenciales_3.png)
[<sub>Volver a inicio</sub>](#inicio)

<a name="formHibrido"></a>
####Formulario Hibrido
En la configuracion del plugin tambien estara la posibilidad de mostrarle al cliente el formulario de pago de TodoPago integrada en el sitio. 
Para esto , en la configuracion se debe seleccionar el campo formulario integrado al e-commerce:
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/formHib1.png)
<sub></br>Del lado del cliente el formulario se vera asi:</br></sub> 
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/formHib2.png)
</br>
[<sub>Volver a inicio</sub>](#inicio)


<a name="features"></a>
##Caracteristicas
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

####Consideraciones Generales (para todas las verticales, por defecto RETAIL)
El plug in, toma valores estándar del framework para validar los datos del comprador.
Para acceder a los datos del vendedor, productos y carrito se usa el  objeto $order que llega como parámetro en los métodos en los que se necesita. 
Este es un ejemplo de la mayoría de los campos que se necesitan para comenzar la operación <br />
'CSBTCITY' => $cart->billing['state'], 	tr
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

[<sub>Volver a inicio</sub>](#inicio)

<a name="codigoerrores"></a>    
## Tabla de errores     

<table>		
<tr><th>Id mensaje</th><th>Mensaje</th></tr>				
<tr><td>-1</td><td>Aprobada.</td></tr>
<tr><td>1081</td><td>Tu saldo es insuficiente para realizar la transacción.</td></tr>
<tr><td>1100</td><td>El monto ingresado es menor al mínimo permitido</td></tr>
<tr><td>1101</td><td>El monto ingresado supera el máximo permitido.</td></tr>
<tr><td>1102</td><td>La tarjeta ingresada no corresponde al Banco indicado. Revisalo.</td></tr>
<tr><td>1104</td><td>El precio ingresado supera al máximo permitido.</td></tr>
<tr><td>1105</td><td>El precio ingresado es menor al mínimo permitido.</td></tr>
<tr><td>2010</td><td>En este momento la operación no pudo ser realizada. Por favor intentá más tarde. Volver a Resumen.</td></tr>
<tr><td>2031</td><td>En este momento la validación no pudo ser realizada, por favor intentá más tarde.</td></tr>
<tr><td>2050</td><td>Lo sentimos, el botón de pago ya no está disponible. Comunicate con tu vendedor.</td></tr>
<tr><td>2051</td><td>La operación no pudo ser procesada. Por favor, comunicate con tu vendedor.</td></tr>
<tr><td>2052</td><td>La operación no pudo ser procesada. Por favor, comunicate con tu vendedor.</td></tr>
<tr><td>2053</td><td>La operación no pudo ser procesada. Por favor, intentá más tarde. Si el problema persiste comunicate con tu vendedor</td></tr>
<tr><td>2054</td><td>Lo sentimos, el producto que querés comprar se encuentra agotado por el momento. Por favor contactate con tu vendedor.</td></tr>
<tr><td>2056</td><td>La operación no pudo ser procesada. Por favor intentá más tarde.</td></tr>
<tr><td>2057</td><td>La operación no pudo ser procesada. Por favor intentá más tarde.</td></tr>
<tr><td>2059</td><td>La operación no pudo ser procesada. Por favor intentá más tarde.</td></tr>
<tr><td>90000</td><td>La cuenta destino de los fondos es inválida. Verificá la información ingresada en Mi Perfil.</td></tr>
<tr><td>90001</td><td>La cuenta ingresada no pertenece al CUIT/ CUIL registrado.</td></tr>
<tr><td>90002</td><td>No pudimos validar tu CUIT/CUIL.  Comunicate con nosotros <a href="#contacto" target="_blank">acá</a> para más información.</td></tr>
<tr><td>99900</td><td>El pago fue realizado exitosamente</td></tr>
<tr><td>99901</td><td>No hemos encontrado tarjetas vinculadas a tu Billetera. Podés  adherir medios de pago desde www.todopago.com.ar</td></tr>
<tr><td>99902</td><td>No se encontro el medio de pago seleccionado</td></tr>
<tr><td>99903</td><td>Lo sentimos, hubo un error al procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99970</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99971</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99977</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99978</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99979</td><td>Lo sentimos, el pago no pudo ser procesado.</td></tr>
<tr><td>99980</td><td>Ya realizaste un pago en este sitio por el mismo importe. Si querés realizarlo nuevamente esperá 5 minutos.</td></tr>
<tr><td>99982</td><td>En este momento la operación no puede ser realizada. Por favor intentá más tarde.</td></tr>
<tr><td>99983</td><td>Lo sentimos, el medio de pago no permite la cantidad de cuotas ingresadas. Por favor intentá más tarde.</td></tr>
<tr><td>99984</td><td>Lo sentimos, el medio de pago seleccionado no opera en cuotas.</td></tr>
<tr><td>99985</td><td>Lo sentimos, el pago no pudo ser procesado.</td></tr>
<tr><td>99986</td><td>Lo sentimos, en este momento la operación no puede ser realizada. Por favor intentá más tarde.</td></tr>
<tr><td>99987</td><td>Lo sentimos, en este momento la operación no puede ser realizada. Por favor intentá más tarde.</td></tr>
<tr><td>99988</td><td>Lo sentimos, momentaneamente el medio de pago no se encuentra disponible. Por favor intentá más tarde.</td></tr>
<tr><td>99989</td><td>La tarjeta ingresada no está habilitada. Comunicate con la entidad emisora de la tarjeta para verificar el incoveniente.</td></tr>
<tr><td>99990</td><td>La tarjeta ingresada está vencida. Por favor seleccioná otra tarjeta o actualizá los datos.</td></tr>
<tr><td>99991</td><td>Los datos informados son incorrectos. Por favor ingresalos nuevamente.</td></tr>
<tr><td>99992</td><td>La fecha de vencimiento es incorrecta. Por favor seleccioná otro medio de pago o actualizá los datos.</td></tr>
<tr><td>99993</td><td>La tarjeta ingresada no está vigente. Por favor seleccioná otra tarjeta o actualizá los datos.</td></tr>
<tr><td>99994</td><td>El saldo de tu tarjeta no te permite realizar esta operacion.</td></tr>
<tr><td>99995</td><td>La tarjeta ingresada es invalida. Seleccioná otra tarjeta para realizar el pago.</td></tr>
<tr><td>99996</td><td>La operación fué rechazada por el medio de pago porque el monto ingresado es inválido.</td></tr>
<tr><td>99997</td><td>Lo sentimos, en este momento la operación no puede ser realizada. Por favor intentá más tarde.</td></tr>
<tr><td>99998</td><td>Lo sentimos, la operación fue rechazada. Comunicate con la entidad emisora de la tarjeta para verificar el incoveniente o seleccioná otro medio de pago.</td></tr>
<tr><td>99999</td><td>Lo sentimos, la operación no pudo completarse. Comunicate con la entidad emisora de la tarjeta para verificar el incoveniente o seleccioná otro medio de pago.</td></tr>
</table>

[<sub>Volver a inicio</sub>](#inicio)

<a name="tablas"></a>
## Tablas de Referencia
######[Provincias](#p)

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

####Muy Importante
Provincias: Al ser un campo MANDATORIO para enviar y propio del plugin este campo se completa por parte del usuario al momento del check out.

[<sub>Volver a inicio</sub>](#inicio)


<a name="availableversions"></a>
## Versiones Disponibles##
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
      <td><a href="https://github.com/TodoPago/Plugin-ZenCart/archive/master.zip">v1.6.0</a></td>
      <td>Stable (Current version)</td>
      <td>Community Edition 1.5.x
      </td>
    </tr>
  </tbody>
</table>

*Click on the links above for instructions on installing and configuring the module.*


[<sub>Volver a inicio</sub>](#inicio)
