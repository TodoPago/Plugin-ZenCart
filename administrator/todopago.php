<?php

require('includes/application_top.php');
require_once(dirname(__FILE__) . '/../includes/modules/payment/todopago/includes/todopago_ctes.php');
//require_once(dirname(__FILE__).'/../includes/modules/payment/todopago/vendor/autoload.php');
?>
    <!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=EDGE"/>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?> </title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script language="javascript" src="includes/menu.js"></script>
    <script language="javascript" src="includes/general.js"></script>
    <script type="text/javascript">
        function init() {
            cssjsmenu('navbar');
            if (document.getElementById) {
                var kill = document.getElementById('hoverJS');
                kill.disabled = true;
            }
            deshabilitarInputHijo(document.getElementById('active_form_check'), 'form_timeout');
            deshabilitarInputHijo(document.getElementById('enable_maxinstallments'), 'maxinstallments');
        }
        function habilitar() {
            var checkbox_enable = document.getElementById('enable_maxinstallments');
            var max_cuotas = document.getElementById('maxinstallments');
            var cant = max_cuotas.length - 1;
            if (!checkbox_enable.checked) {
                max_cuotas.value = 12;
                max_cuotas.selectedIndex = 12;
                max_cuotas.setAttribute('style', 'display:block');
            }
            else {
                max_cuotas.setAttribute('style', 'display:block');
                max_cuotas.selectedIndex = cant;
            }
            deshabilitarInputHijo(checkbox_enable, max_cuotas.name)
        }
        function active_timeout(id) {
            var active_enable = document.getElementById(id);
            if (active_enable.value === 1) {
                active_enable.value = 0;
            } else {
                active_enable.value = 1;
            }
        }
        function deshabilitarInputHijo(el, aDeshabilitar) {
            el.checked ? document.getElementById(aDeshabilitar).disabled = false : document.getElementById(aDeshabilitar).disabled = true;
        }
        function validarAlVuelo(el) {
            var valor = el.value;
            var regEx = new RegExp('^[a-zA-Z0-9 ]*$');
            if (regEx.test(valor)) {
                el.style = null;
                el.removeAttribute("onChange");
            }
        }

        function colorear(elemento) {
            elemento.setAttribute("onChange", "validarAlVuelo(this)");
            elemento.style.borderColor = 'red';
            elemento.style.borderWidth = '2px';
            elemento.style.backgroundColor = 'coral';
        }

        function validar() {
            var elementos = document.forms[0];
            var parrafo = document.createElement("div");
            var valido = true;
            var texto = document.createTextNode("Ha ingresado texto no válido. Recuerda no utilizar caracteres especiales");
            var timeoutInput = document.getElementById('form_timeout');
            parrafo.appendChild(texto);
            for (index = 0, length = elementos.length; index < length; ++index) {
                var valor = elementos[index].value;
                var regEx = new RegExp('^[a-zA-Z0-9 ]*$');
                if (!regEx.test(valor)) {
                    colorear(elementos[index]);
                    valido = false;
                }
            }
            if (!timeoutInput.hasAttribute('disabled')) {
                if (timeoutInput.value > 21600000 || timeoutInput.value < 300000) {
                    colorear(timeoutInput);
                    valido = false;
                }
            }
            if (valido) {
                elementos.submit();
            } else {
                alert("Revisar los campos señalados en rojo. Recuerde no utilizar caracteres especiales");
            }
        }
    </script>
</head>
<body onload="init()">
<?php
global $db;
require(DIR_WS_INCLUDES . 'header.php');
$mensaje = "";
$sql = "select * from " . TABLE_TP_CONFIGURACION;

$res = $db->Execute($sql);
$row = $res->fields;
if (!isset($_POST['active_form_checker']))
    $_POST['active_form_checker'] = 0;
if (isset($_POST['form_timeout']) && $_POST['form_timeout'] === null) {
    $_POST['form_timeout'] = 300000; //min value of timeout
}
if (isset($_POST['authorization'])) {
    $autorization_post = str_replace('\"', '"', $_POST["authorization"]);
    if (json_decode($autorization_post) == NULL) {
        //armo json de autorization
        $autorizationId = new stdClass();
        $autorizationId->Authorization = $_POST["authorization"];
        $_POST["authorization"] = json_encode($autorizationId);
    }
    // unset($_POST['authorization']);
    // NUEVO CHEQUEO DE DATOS
    if (!$row) { // Si no hay datos, agrego
        $query = 'insert into ' . TABLE_TP_CONFIGURACION . ' (idConf,';
        foreach ($_POST as $key => $value) {
            $query .= $key . ",";
        }
        $query = trim($query, ",");
        $query = $query . ")";
        $query .= ' values (1,';
        foreach ($_POST as $key => $value) {
            if ($key == 'maxinstallments' && $value == null) {// SI EXISTE EL ELEMENTO maxinstallments
                $query .= "0,";
            } else {
                $query .= "'" . $value . "',";
            }
        }
        $query = trim($query, ",");
        $query = $query . ")";

        $db->Execute($query);
        $mensaje = "La configuracion se guardo correctamente";
    } else { // Si ya existen datos, actualizo
        $query = 'update ' . TABLE_TP_CONFIGURACION . ' set ';
        foreach ($_POST as $key => $value) {
            if ($key == 'maxinstallments' && $value == null) {// SI EXISTE EL ELEMENTO maxinstallments
                $query .= $key . "=0,";
            } else {
                $query .= $key . "='" . $value . "',";
            }
        }
        $query = trim($query, ",");
        $db->Execute($query);
        $mensaje = "La configuracion se guardo correctamente";
    }
}
$res = array();
$sql = "select * from " . TABLE_TP_CONFIGURACION;
$res = $db->Execute($sql);
$row = $res->fields;
$jsonAuthorization = json_decode($row['authorization']);
?>
<link rel="stylesheet" type="text/css"
      href="<?php echo HTTP_SERVER . '/' . DIR_WS_CATALOG ?>/includes/modules/payment/todopago/todopago.css"/>
<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css"
      href="<?php echo HTTP_SERVER . '/' . DIR_WS_CATALOG ?>/includes/modules/payment/todopago/includes/datatables/jquery.dataTables.css"/>
<!-- jQuery -->
<script type="text/javascript" charset="utf8"
        src="<?php echo HTTP_SERVER . '/' . DIR_WS_CATALOG ?>/includes/modules/payment/todopago/includes/datatables/jquery-1.10.2.min.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<!-- DataTables -->
<script type="text/javascript" charset="utf8"
        src="<?php echo HTTP_SERVER . '/' . DIR_WS_CATALOG ?>/includes/modules/payment/todopago/includes/datatables/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8"
        src="<?php echo HTTP_SERVER . '/' . DIR_WS_CATALOG ?>/includes/modules/payment/todopago/includes/datatables/dataTables.tableTools.min.js"></script>

<table border="0" width="100%" cellspacing="0" cellpadding="2">
    <tr>
        <td>
            <table style="margin-left: 35px;" border="0" width="100%" cellspacing="0" cellpadding="2" height="40">
                <tr>
                    <td class="pageHeading">TodoPago v<?php echo TP_VERSION ?> | Configuraci&oacute;n</td>
                    <td class="pageHeading"></td>
                    <td class="smallText" align="right"></td>
                </tr>
                <tr>
                    <td><img src="http://www.todopago.com.ar/sites/todopago.com.ar/files/pluginstarjeta.jpg"/></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td><?php echo($mensaje); ?></td>
    </tr>
    <tr>
        <td>
            <div id="todopago">
                <ul class="secciones-todopago-config">
                    <li><a class="tabs-todopago" todopago="#config">Configuracion</a></li>
                    <li><a class="tabs-todopago" todopago="#prod">Productos</a></li>
                    <!--li><a class="tabs-todopago" todopago="#mediosdepago">Medios de Pago</a></li-->
                    <li><a class="tabs-todopago" todopago="#orden">Ordenes</a></li>
                </ul>
                <div id="config">
                    <div id="credentials-login" class="order-action-popup ui-dialog" style="display:none;">
                        <img src="http://www.todopago.com.ar/sites/todopago.com.ar/files/logo.png">
                        <p>Ingresa con tus credenciales para Obtener los datos de configuración</p>
                        <label class="control-label">E-mail</label>
                        <input id="mail" class="form-control" name="mail" type="email" value="" placeholder="E-mail"/>
                        <label class="control-label">Contrase&ntilde;a</label>
                        <input id="pass" class="form-control" name="pass" type="password" value=""
                               placeholder="Contrase&ntilde;a"/>
                        <button id="btn-form-credentials" style="margin:20%;" class="btn-config-credentials">Acceder
                        </button>
                    </div>
                    <button id="btn-credentials" class="btn-config-credentials" style=>Obtener credenciales</button>
                    <script type="text/javascript">
                        $("#btn-credentials").click(function () {
                            //console.log($("ui-button-text") );
                            $("#credentials-login").dialog();
                        });
                        $("#btn-form-credentials").click(function () {
                            console.log('obtengo credenciales por ajax.');
                            $.post("todopago_credentials.php",
                                {
                                    mail: $("#mail").val(),
                                    pass: $("#pass").val()
                                }, function (data) {
                                    console.log('obtuve respuesta.. ');
                                    var obj = jQuery.parseJSON(data);
                                    if (obj.error_message !== '0') {
                                        console.log(obj.error_message);
                                        alert(obj.error_message);
                                    } else {
                                        $('input:text[name=authorization]').val(obj.Authorization);
                                        if (obj.ambiente === 'test') {
                                            $('input:text[name=test_merchant]').val(obj.merchantId);
                                            $('input:text[name=test_security]').val(obj.apiKey);
                                        } else {
                                            $('input:text[name=production_merchant]').val(obj.merchantId);
                                            $('input:text[name=production_security]').val(obj.apiKey);
                                        }
                                    }
                                }
                            );
                            $("#credentials-login").dialog('close');
                        });
                    </script>
                    <form action="" method="post" id="formulario" name="formulario">
                        <div class="input-todopago">
                            <label>Authorization HTTP (c&oacute;digo de autorizaci&oacute;n)</label>
                            <input type="text"
                                   value='<?php echo(isset($jsonAuthorization->Authorization) ? $jsonAuthorization->Authorization : "") ?>'
                                   placeholder="Authorization HTTP" name="authorization"/>
                        </div>
                        <?php
                        $segmento = (isset($row["segmento"]) ? $row["segmento"] : "");
                        ?>
                        <div class="input-todopago">
                            <label>Segmento del Comercio</label>
                            <select name="segmento">
                                <option value="">Seleccione</option>
                                <option value="retail" <?php echo($segmento == "retail" ? "selected" : "") ?>>Retail
                                </option>
                            </select>
                        </div>
                        <?php
                        $canal = (isset($row["canal"]) ? $row["canal"] : "");
                        ?>
                        <div class="input-todopago">
                            <label>Canal de Ingreso del Pedido</label>
                            <select name="canal">
                                <option value="">Seleccione</option>
                                <option value="web" <?php echo($canal == "web" ? "selected" : "") ?>>Web</option>
                                <option value="mobile" <?php echo($canal == "mobile" ? "selected" : "") ?>>Mobile
                                </option>
                                <option value="telefonica" <?php echo($canal == "telefonica" ? "selected" : "") ?>>
                                    Telefonica
                                </option>
                            </select>
                        </div>
                        <?php
                        $ambiente = (isset($row["ambiente"]) ? $row["ambiente"] : "");
                        ?>
                        <div class="input-todopago">
                            <label>Modo Developers o Producci&oacute;n</label>
                            <select name="ambiente">
                                <option value="">Seleccione</option>
                                <option value="test" <?php echo($ambiente == "test" ? "selected" : "") ?>>Developers
                                </option>
                                <option value="production" <?php echo($ambiente == "production" ? "selected" : "") ?>>
                                    Produccion
                                </option>
                            </select>
                        </div>

                        <div class="input-todopago">
                            <label>Dead Line</label>
                            <input value="<?php echo(isset($row["deadline"]) ? $row["deadline"] : "") ?>"
                                   type="number" placeholder="Dead Line" name="deadline"/>
                        </div>
                        <div class="input-todopago">
                            <!-- MÁXIMO VALOR DE LAS CUOTAS SETEADAS -->
                            <label>M&aacute;xima cantidad de cuotas</label>
                            <div style="float:left;">
                                <input type="checkbox" name="" id="enable_maxinstallments"
                                       title="Deshabilita la maxima cantidad de cuotas" onclick="habilitar();">
                            </div>
                            <div style="float:left;margin-left:5px;">
                                <select name="maxinstallments" id="maxinstallments">
                                    <option value="">Seleccione</option>
                                    <?php for ($i = 1; $i < 13; $i++): ?>
                                        <?php if ($row["maxinstallments"] == $i) {
                                            $selected = "selected";
                                        } else {
                                            $selected = NULL;
                                        }
                                        ?>
                                        <?php if (!$row["maxinstallments"] && $i == 12): ?>
                                            <option value="12" selected="selected">12</option>
                                        <?php else: ?>
                                            <option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $i; ?></option>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div style="clear:both;"></div>
                        </div>
                        <div class="subtitulo-todopago">AMBIENTE DEVELOPERS</div>
                        <div class="input-todopago">
                            <label>ID Site Todo Pago</label>
                            <input type="text"
                                   value="<?php echo(isset($row["test_merchant"]) ? $row["test_merchant"] : "") ?>"
                                   placeholder="ID Site Todo Pago" name="test_merchant"/>
                        </div>
                        <div class="input-todopago">
                            <label>Security Code</label>
                            <input type="text"
                                   value="<?php echo(isset($row["test_security"]) ? $row["test_security"] : "") ?>"
                                   placeholder="Security Code" name="test_security"/>
                        </div>
                        <div class="subtitulo-todopago">AMBIENTE PRODUCCION</div>
                        <div class="input-todopago">
                            <label>ID Site Todo Pago</label>
                            <input type="text"
                                   value="<?php echo(isset($row["production_merchant"]) ? $row["production_merchant"] : "") ?>"
                                   placeholder="ID Site Todo Pago" name="production_merchant"/>
                        </div>
                        <div class="input-todopago">
                            <label>Security Code</label>
                            <input type="text"
                                   value="<?php echo(isset($row["production_security"]) ? $row["production_security"] : "") ?>"
                                   placeholder="Security Code" name="production_security"/>
                        </div>
                        <div class="subtitulo-todopago">ESTADOS DE LA ORDEN</div>
                        <div class="input-todopago">
                            <?php
                            $sql = "select  orders_status_id,orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = 1";
                            $res = $db->Execute($sql);
                            while (!$res->EOF) {
                                $opciones[$res->fields["orders_status_id"]] = $res->fields['orders_status_name'];
                                $res->MoveNext();
                            }
                            ?>
                            <label>Estado cuando la transaccion ha sido iniciada</label>
                            <select name="estado_inicio">
                                <?php
                                foreach ($opciones as $key => $value) {
                                    $selected = "";
                                    if ($key == $row["estado_inicio"]) $selected = "selected"
                                    ?>
                                    <option <?php echo($selected) ?>
                                            value="<?php echo($key) ?>"><?php echo($value) ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="input-todopago">
                            <label>Estado cuando la transaccion ha sido aprobada</label>
                            <select name="estado_aprobada">
                                <?php
                                foreach ($opciones as $key => $value) {
                                    $selected = "";
                                    if ($key == $row["estado_aprobada"]) $selected = "selected"
                                    ?>
                                    <option <?php echo($selected) ?>
                                            value="<?php echo($key) ?>"><?php echo($value) ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="input-todopago">
                            <label>Estado cuando la transaccion ha sido rechazada</label>
                            <select name="estado_rechazada">
                                <?php
                                foreach ($opciones as $key => $value) {
                                    $selected = "";
                                    if ($key == $row["estado_rechazada"]) $selected = "selected"
                                    ?>
                                    <option <?php echo($selected) ?>
                                            value="<?php echo($key) ?>"><?php echo($value) ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="input-todopago">
                            <label>Estado cuando la transaccion ha sido offline</label>
                            <select name="estado_offline">
                                <?php
                                foreach ($opciones as $key => $value) {
                                    $selected = "";
                                    if ($key == $row["estado_offline"]) $selected = "selected"
                                    ?>
                                    <option <?php echo($selected) ?>
                                            value="<?php echo($key) ?>"><?php echo($value) ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>

                        <div class="subtitulo-todopago">CONFIGURACIÓN CARRITO DE COMPRAS</div>
                        <div class="input-todopago">
                            <label>Carrito tras fallar compra por error o timeout</label>
                            <div class="input-todopago-campos">
                                <input type="radio" name="keep_shopping_cart"
                                       value="1" <?php echo ($row['keep_shopping_cart'] == 1) ? 'checked="checked"' : ''; ?> >
                                Conservar carrito
                                <br>
                                <input type="radio" name="keep_shopping_cart"
                                       value="0" <?php echo ($row['keep_shopping_cart'] == 0) ? 'checked="checked"' : ''; ?> >
                                Vaciar carrito
                            </div>
                        </div>

                        <div class="subtitulo-todopago">FORMULARIO DE PAGO</div>

                        <div class="input-todopago">
                            <label style="float:left;">Seleccione el tipo de formulario de pago</label>
                            <div style="float:left;">
                                <div style="margin-bottom:8px;"><input type="radio" name="tipo_formulario"
                                                                       value="0" <?php echo ($row['tipo_formulario'] == 0) ? 'checked="checked"' : '' ?> >Formulario
                                    externo<br></div>
                                <div><input type="radio" name="tipo_formulario"
                                            value="1" <?php echo ($row['tipo_formulario'] == 1) ? 'checked="checked"' : '' ?> >Formulario
                                    integrado al e-commerce
                                </div>
                            </div>
                            <div style="clear:both;"></div>
                            <br>
                            <label style="float:left;">Tiempo de vida del Formulario</label>
                            <div style="float:left;">
                                <div style="margin-bottom:8px;"><input type="checkbox" id="active_form_check"
                                                                       name="active_form_checker"
                                                                       value="<?php echo $row['active_form_checker'] ?>" <?php echo ($row['active_form_checker'] == 1) ? 'checked="checked"' : '' ?>
                                                                       onclick="active_timeout(this.id)"
                                                                       onchange="deshabilitarInputHijo(this, 'form_timeout')">Activar
                                </div>
                                <input min="300000" max="21600000" name="form_timeout" id="form_timeout"
                                       type="number" value="<?php echo $row['form_timeout'] ?>"> (Valor en milisegundos)
                            </div>
                            <div style="clear:both;"></div>
                        </div>
                        <br>
                        <input type="button" id="btnsubmit" name="btnsubmit" value="Guardar Datos" onclick="validar()"/>
                    </form>
                </div>
                <div id="prod">
                    <form action="" method="POST">
                        <table id="data-table" style="width:100%">
                            <thead>
                            <tr>
                                <td>ID</td>
                                <td>Nombre</td>
                                <td>Codigo de Producto</td>
                                <td>Fecha Evento</td>
                                <td>Tipo de Envio</td>
                                <td>Tipo de Servicio</td>
                                <td>Tipo de Delivery</td>
                                <td>Editar</td>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $sql = "select p.products_id,pd.products_name,p.products_model from " . TABLE_PRODUCTS . " as p inner join " . TABLE_PRODUCTS_DESCRIPTION . " as pd on p.products_id = pd.products_id where language_id=1";
                            $res = $db->Execute($sql);
                            $i = 0;
                            while (!$res->EOF) {
                                $sql = 'select * from ' . TABLE_TP_ATRIBUTOS . ' where product_id=' . $res->fields["products_id"];
                                $res2 = $db->Execute($sql);
                                $tipoDelivery = "";
                                $tipoEnvio = "";
                                $tipoServicio = "";
                                $codigoProducto = "";
                                $diasEvento = "";
                                if (!$res2->EOF) {
                                    if ($res2->fields["CSITPRODUCTCODE"] != "") $codigoProducto = $res2->fields["CSITPRODUCTCODE"];
                                    if ($res2->fields["CSMDD33"] != "") $diasEvento = $res2->fields["CSMDD33"];
                                    if ($res2->fields["CSMDD34"] != "") $tipoEnvio = $res2->fields["CSMDD34"];
                                    if ($res2->fields["CSMDD28"] != "") $tipoServicio = $res2->fields["CSMDD28"];
                                    if ($res2->fields["CSMDD31"] != "") $tipoDelivery = $res2->fields["CSMDD31"];
                                }
                                $i = $res->fields["products_id"];
                                echo "<tr><td>" . $res->fields["products_id"] . "</td><td id='nombre" . $i . "'>" . $res->fields["products_name"] . "</td><td id='codigo" . $i . "'>" . $codigoProducto . "</td><td id='evento" . $i . "'>" . $diasEvento . "</td><td id='envio" . $i . "'>" . $tipoEnvio . "</td><td id='servicio" . $i . "'>" . $tipoServicio . "</td><td id='delivery" . $i . "'>" . $tipoDelivery . "</td><td class='editar' id='" . $i . "'>Editar</td></tr>";
                                $res->MoveNext();
                            }
                            ?>
                            </tbody>
                        </table>
                        <div id="config-producto-todopago">
                            <div class="close-todopago">x</div>
                            <table>
                                <tr>
                                    <td align="center" id="titulo">Titulo del producto</td>
                                </tr>
                                <tr>
                                    <td>
                                        <table>
                                            <tr>
                                                <td>Codigo de Producto</td>
                                                <td><select id="codigo_producto">
                                                        <option value="">- None -</option>
                                                        <option value="adult_content">adult_content</option>
                                                        <option value="coupon">coupon</option>
                                                        <option value="default">default</option>
                                                        <option value="electronic_good" selected="selected">
                                                            electronic_good
                                                        </option>
                                                        <option value="electronic_software">electronic_software</option>
                                                        <option value="gift_certificate">gift_certificate</option>
                                                        <option value="handling_only">handling_only</option>
                                                        <option value="service">service</option>
                                                        <option value="shipping_and_handling">shipping_and_handling
                                                        </option>
                                                        <option value="shipping_only">shipping_only</option>
                                                        <option value="subscription">subscription</option>
                                                    </select></td>
                                            </tr>
                                            <tr>
                                                <td>Dias para el Evento</td>
                                                <td><input id="dias_evento" type="text" value=""/></td>
                                            </tr>
                                            <tr>
                                                <td>Tipo de Envio</td>
                                                <td><select id="envio_producto">
                                                        <option value="">- None -</option>
                                                        <option value="Pickup">Pickup</option>
                                                        <option value="Email">Email</option>
                                                        <option value="Smartphone">Smartphone</option>
                                                        <option value="Other">Other</option>
                                                    </select></td>
                                            </tr>
                                            <tr>
                                                <td>Tipo de Delivery</td>
                                                <td><select id="delivery_producto">
                                                        <option value="">- None -</option>
                                                        <option value="WEB Session">WEB Session</option>
                                                        <option value="Email">Email</option>
                                                        <option value="SmartPhone">SmartPhone</option>
                                                    </select></td>
                                            </tr>
                                            <tr>
                                                <td>Tipo de Servicio</td>
                                                <td><select id="servicio_producto">
                                                        <option value="">- None -</option>
                                                        <option value="Luz">Luz</option>
                                                        <option value="Gas">Gas</option>
                                                        <option value="Agua">Agua</option>
                                                        <option value="TV">TV</option>
                                                        <option value="Cable">Cable</option>
                                                        <option value="Internet">Internet</option>
                                                        <option value="Impuestos">Impuestos</option>
                                                    </select></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2"><input type="hidden" value="" id="id_producto"/><input
                                                            id="guardar" type="button" value="Guardar"/></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <script type="text/javascript">
                            $(document).ready(function () {
                                $('.close-todopago').click(function () {
                                    $('#config-producto-todopago').hide();
                                });
                                $("#guardar").click(function () {
                                    $('#config-producto-todopago').hide();
                                    $.post("<?php echo HTTP_SERVER . '/' . DIR_WS_CATALOG?>/todo_pago_config_ajax.php",
                                        {
                                            CSITPRODUCTCODE: $("#codigo_producto").val(),
                                            CSMDD33: $("#dias_evento").val(),
                                            CSMDD34: $("#envio_producto").val(),
                                            CSMDD28: $("#servicio_producto").val(),
                                            CSMDD31: $("#delivery_producto").val(),
                                            product_id: $("#id_producto").val()
                                        }
                                    ).done(function () {
                                        $("#codigo" + $("#id_producto").val()).html($("#codigo_producto").val());
                                        $("#evento" + $("#id_producto").val()).html($("#dias_evento").val());
                                        $("#envio" + $("#id_producto").val()).html($("#envio_producto").val());
                                        $("#delivery" + $("#id_producto").val()).html($("#delivery_producto").val());
                                        $("#servicio" + $("#id_producto").val()).html($("#servicio_producto").val());
                                    }).fail(function () {
                                        $("#codigo" + $("#id_producto").val()).html($("#codigo_producto").val());
                                        $("#evento" + $("#id_producto").val()).html($("#dias_evento").val());
                                        $("#envio" + $("#id_producto").val()).html($("#envio_producto").val());
                                        $("#delivery" + $("#id_producto").val()).html($("#delivery_producto").val());
                                        $("#servicio" + $("#id_producto").val()).html($("#servicio_producto").val());
                                    });
                                });
                                $(".editar").click(function () {
                                    $('#config-producto-todopago').hide();
                                    $('#config-producto-todopago').show();
                                    $("#id_producto").val($(this).attr("id"));
                                    $("#titulo").html($("#nombre" + $(this).attr("id")).html());
                                    $("#codigo_producto").val($("#codigo" + $(this).attr("id")).html());
                                    $("#dias_evento").val($("#evento" + $(this).attr("id")).html());
                                    $("#envio_producto").val($("#envio" + $(this).attr("id")).html());
                                    $("#delivery_producto").val($("#delivery" + $(this).attr("id")).html());
                                    $("#servicio_producto").val($("#servicio" + $(this).attr("id")).html());
                                });
                                $('#data-table').dataTable({
                                    bFilter: true,
                                    bInfo: true,
                                    bPaginate: true,
                                });
                            })
                        </script>
                    </form>
                </div>
                <div id="orden">
                    <table id="orders-table" style="width:100%">
                        <thead>
                        <tr>
                            <td>ID</td>
                            <td>Nombre</td>
                            <td>Telefono</td>
                            <td>Email</td>
                            <td>Fecha</td>
                            <td>Status</td>
                            <td>Devoluci&oacute;n</td>
                            <td>GetStatus</td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $sql = "select orders_id,customers_name,customers_telephone,customers_email_address,date_purchased,orders_status_name from " . TABLE_ORDERS . " as o inner join " . TABLE_ORDERS_STATUS . " as os on os.orders_status_id = o.orders_status where os.language_id = " . $_SESSION['languages_id'] . " order by date_purchased desc";
                        $res = $db->Execute($sql);
                        $i = 0;
                        while (!$res->EOF) {
                            echo "<tr><td>" . $res->fields["orders_id"] . "</td><td>" . $res->fields["customers_name"] . "</td><td>" . $res->fields["customers_telephone"] . "</td><td>" . $res->fields["customers_email_address"] . "</td><td>" . $res->fields["date_purchased"] . "</td><td>" . $res->fields["orders_status_name"] . "</td><td class='refund-td' data-order_id='" . $res->fields["orders_id"] . "' style='cursor:pointer'>Devolver</td><td class='status' id='" . $res->fields["orders_id"] . "' style='cursor:pointer'>Ver Status</td></tr>";
                            $res->MoveNext();
                        }
                        ?>
                        </tbody>
                    </table>
                    <di1v id="status-orders" class="order-action-popup" style="overflow: scroll; height: 300px;">
                        <div class="close-status-todopago close-todopago">x</div>
                        <div id="status"></div>
                    </div>
                    <div id="refund-dialog" class="order-action-popup" hidden="hidden">
                        <div class="close-refund-todopago close-todopago">x</div>
                        <div id="refund-form">
                            <input type="hidden" id="order-id-hidden"/>
                            <label for="refund-type-select">Elija el tipo de devolución: </label>
                            <select id="refund-type-select" name="refund-type">
                                <option value="total" selected="selected">Total</option>
                                <option value="parcial">Parcial</option>
                            </select>
                            <div id="amount-div" hidden="hidden">
                                <div id="amount-warning">Recuerde ingresar el monto original, sin incluir los
                                    recargos.
                                </div>
                                <label for="amount-input">Monto: $</label>
                                <input type="number" id="amount-input" name="amount" min=0.01 step=0.01/><br>
                                <div id="invalid-amount-message">Ingrese un monto</div>
                                <br>
                            </div>
                            <div id="refund-button-div">
                                <button id="refund-button">Devolver</button>
                            </div>
                        </div>
                        <div id="refund-result"></div>
                    </div>
                </div>
            </div>
            <script>
                $(document).ready(function () {
                    $('.close-status-todopago').click(function () {
                        $('#status-orders').hide();
                    });
                    $(".status").click(function () {
                        $('#status-orders').hide();
                        $.post("<?php echo HTTP_SERVER . '/' . DIR_WS_CATALOG?>/todo_pago_status_ajax.php",
                            {
                                order_id: $(this).attr("id")
                            }, function (data) {
                                $("#status").html(data);
                                $('#status-orders').show();
                            })
                    });
                    //Devoluciones
                    $(".close-refund-todopago").click(function () {
                        $('#refund-dialog').hide();
                        $('#refund-result').hide();
                    });
                    $(".refund-td").click(function refundTd_click() {
                        //console.log("carajo");
                        $('.order-action-popup').hide();
                        $('#order-id-hidden').val($(this).attr("data-order_id"));
                        $("#refund-result").hide();
                        $("#invalid-amount-message").hide();
                        $("#amount-input").val("");
                        $('#refund-form').show();
                        $('#refund-dialog').show();
                    });
                    $("#refund-type-select").change(function refundTypeSelect_change() {
                        if ($(this).val() == 'parcial') {
                            $("#amount-div").show();
                        } else {
                            $("#amount-div").hide();
                        }
                    });
                    $("#refund-button").click(function refundButton_click() {
                        if (isValidAmount()) {
                            $.post("<?php echo HTTP_SERVER . '/' . DIR_WS_CATALOG?>/todo_pago_devoluciones_ajax.php", {
                                order_id: $("#order-id-hidden").val(),
                                refund_type: $("#refund-type-select").val(),
                                amount: $("#amount-input").val()
                            }, function (response) {
                                $("#refund-form").hide();
                                $("#refund-result").html(response);
                                $("#refund-result").show();
                            })
                        }
                        else {
                            $("#invalid-amount-message").show();
                        }
                    });
                    $('#orders-table').dataTable({
                        bFilter: true,
                        bInfo: true,
                        bPaginate: true
                    });
                })
            </script>
        </td>
    </tr>
</table>
<script>
    $(document).ready(function () {
        $("#prod").hide();
        $("#orden").hide();
        //$("#mediosdepago").hide();
        $(".tabs-todopago").each(function () {
            $(this).css("cursor", "pointer");
        })
        $(".tabs-todopago").click(function () {
            $("#config").hide();
            $("#prod").hide();
            //$("#mediosdepago").hide();
            $("#orden").hide();
            $("" + $(this).attr("todopago") + "").show();
        })
    });
    function isValidAmount() {
        return (($("#refund-type-select").val() === 'parcial' && !isNaN($("#amount-input").val()) && isFinite($("#amount-input").val()) && $("#amount-input").val() != "") || $("#refund-type-select").val() == 'total');
    }
</script>
</body>
<?php
require(DIR_WS_INCLUDES . 'application_bottom.php');
