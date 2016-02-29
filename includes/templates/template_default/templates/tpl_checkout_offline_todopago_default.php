<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=checkout_offline_todopago.<br />
 * Displays confirmation details after order has been successfully processed.
 *
 * @package templateSystem
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_checkout_offline_todopago_default.php 16435 2010-05-28 09:34:32Z drbyte $
 */
?>
<h1 id="checkoutSuccessHeading">Muchas gracias por usar Todopago.</h1>
<div id="checkoutSuccessMainContent" class="content">
    <label>Cupón de pago generado!</label>
    <a type="button" href="<?php echo $url ?>" value="Descargar Pdf" target="_blank">
    	<?php echo zen_image_button('button_download.gif', IMAGE_DOWNLOAD) ?>
    </a>
    <br /><br /><br />
</div>