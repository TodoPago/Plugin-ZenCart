<a name="inicio"></a>
zencart-plugin
============

Plug in para la integración con gateway de pago <strong>Todo Pago</strong>
- [Consideraciones Generales](#consideracionesgenerales)
- [Instalación](#instalacion)
- [Configuración plugin](#confplugin)
- [Obtener credenciales](#obtenercredenciales)
- [Devoluciones] (#devoluciones)
- [Datos adiccionales para prevención de fraude](#cybersource) 
- [Tablas de referencia](#tablas)

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
##Configuración plugin
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

![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/orders.png)<br><br>
Para las devoluciones se debe agregar los estados "Refund" y "Partial Refund", desde la seccion, Admin -> Localization -> Order Status.
<a name="cosulta_transacciones"></a>
## Consulta de Transacciones
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/todopago-transacciones.png)
[<sub>Volver a inicio</sub>](#inicio)


<a name="obtenercredenciales"></a>
#Obtener crendenciales
Se puede obtener los datos de configuracion del plugin con solo loguearte con tus credenciales de Todopago. </br>
a. Ir a la opcion Obtener credenciales
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/obtenercredenciales_1.png)
b. En el popup loguearse con el mail y password de Todopago.
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/obtenercredenciales_2.png)
c. Los datos se cargaran automaticamente en los campos Merchant ID y Security code en el ambiente correspondiente (Desarrollo o produccion ) y solo hay que hacer click en el boton guardar datos y listo.
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/obtenercredenciales_3.png)


<a name="devoluciones"></a>
## Devoluciones
TodoPago permite realizar la devolucion total o parcial de dinero de una orden de compra.<br> 
Para ello dirigirse en el menú a Tools->TodoPago configuracion->Ordenes, en esta pagina se encuentra las ordenes de compra realizadas con Todopago.<br> 
En cada orden se encuentra la opcion "Devolver" que mostrara un modal con la opcion de devolucion total y devolucion parcial junto con el campo para ingresar el monto.

![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/zencart/devoluciones-zencart.png)

<a name="cybersource"></a>
## Prevención de Fraude
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
