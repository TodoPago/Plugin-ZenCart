<?php
    
    define('MODULE_PAYMENT_TODOPAGO_TEXT_TITLE', 'TodoPago <img align="middle" src="https://www.dineromail.com/imagenes/post-login/boton-comprar-01.jpg" border=0');
    define('MODULE_PAYMENT_TODOPAGO_TEXT_DESCRIPTION', 'TodoPago');
    
    
    
    define('MODULE_PAYMENT_TODOPAGO_TEXT_ADMIN_TITLE', 'TodoPago Payments Standard');
    define('MODULE_PAYMENT_TODOPAGO_TEXT_ADMIN_TITLE_NONUSA', 'TodoPago Website Payments Standard');
    define('MODULE_PAYMENT_TODOPAGO_TEXT_CATALOG_TITLE', 'TodoPago');
    if (IS_ADMIN_FLAG === true) {
    define('MODULE_PAYMENT_TODOPAGO_TEXT_DESCRIPTION', '<strong>PayPal Payments Standard</strong> (Older PayPal service, less reliable than Express Checkout)<br /><a href="https://www.todopago.com" target="_blank">Manage your PayPal account.</a><br /><br /><font color="green">Configuration Instructions:</font><br />1. <a href="http://www.zen-cart.com/partners/todopago-std" target="_blank">Sign up for your PayPal account - click here.</a><br />2. In your PayPal account, under "Profile",<ul><li>set your <strong>Instant Payment Notification Preferences</strong> URL to:<br /><nobr><pre>'.str_replace('index.php?main_page=index','ipn_main_handler.php',zen_catalog_href_link(FILENAME_DEFAULT, '', 'SSL')) . '</pre></nobr><br />(If another valid URL is already entered, you may leave it alone.)<br /><span class="alert">Be sure that the Checkbox to enable IPN is checked!</span><br /><br /></li><li>in <strong>Website Payments Preferences</strong> set your <strong>Automatic Return URL</strong> to:<br /><nobr><pre>'.zen_catalog_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL',false).'</pre></nobr></li>' . (defined('MODULE_PAYMENT_TODOPAGOSTD_STATUS') ? '' : '<li>... and click "install" above to enable PayPal Standard support... and "edit" to tell Zen Cart your PayPal settings.</li>') . '</ul><font color="green"><hr /><strong>Requirements:</strong></font><br /><hr />*<strong>PayPal Account</strong> (<a href="http://www.zen-cart.com/partners/todopago-std" target="_blank">click to setup/configure</a>)<br />*<strong>CURL with SSL</strong> is strongly recommended<br />*<strong>Port 80 (and port 443 if SSL is enabled)</strong> is used for <strong>*bidirectional*</strong> communication with the gateway, so must be open on your host\'s router/firewall.<br />*<strong>Settings</strong> within your PayPal account must be configured as described above.' );
    } else {
    define('MODULE_PAYMENT_TODOPAGO_TEXT_DESCRIPTION', '<strong>PayPal</strong>');
    }
    define('MODULE_PAYMENT_TODOPAGO_MARK_BUTTON_IMG', 'https://www.todopago.com/en_US/i/logo/PayPal_mark_37x23.gif');
    define('MODULE_PAYMENT_TODOPAGO_MARK_BUTTON_ALT', 'Checkout with PayPal');
    define('MODULE_PAYMENT_TODOPAGO_ACCEPTANCE_MARK_TEXT', 'Save time. Check out securely. <br />Pay without sharing your financial information.');
    
    define('MODULE_PAYMENT_TODOPAGO_TEXT_CATALOG_LOGO', '<img src="' . MODULE_PAYMENT_TODOPAGO_MARK_BUTTON_IMG . '" alt="' . MODULE_PAYMENT_TODOPAGO_MARK_BUTTON_ALT . '" title="' . MODULE_PAYMENT_TODOPAGO_MARK_BUTTON_ALT . '" /> &nbsp;' .
                                                    '<span class="smallText">' . MODULE_PAYMENT_TODOPAGO_ACCEPTANCE_MARK_TEXT . '</span>');
    
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_FIRST_NAME', 'First Name:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_LAST_NAME', 'Last Name:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_BUSINESS_NAME', 'Business Name:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_ADDRESS_NAME', 'Address Name:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_ADDRESS_STREET', 'Address Street:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_ADDRESS_CITY', 'Address City:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_ADDRESS_STATE', 'Address State:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_ADDRESS_ZIP', 'Address Zip:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_ADDRESS_COUNTRY', 'Address Country:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_EMAIL_ADDRESS', 'Payer Email:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_EBAY_ID', 'Ebay ID:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_PAYER_ID', 'Payer ID:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_PAYER_STATUS', 'Payer Status:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_ADDRESS_STATUS', 'Address Status:');
    
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_PAYMENT_TYPE', 'Payment Type:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_PAYMENT_STATUS', 'Payment Status:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_PENDING_REASON', 'Pending Reason:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_INVOICE', 'Invoice:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_PAYMENT_DATE', 'Payment Date:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_CURRENCY', 'Currency:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_GROSS_AMOUNT', 'Gross Amount:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_PAYMENT_FEE', 'Payment Fee:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_EXCHANGE_RATE', 'Exchange Rate:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_CART_ITEMS', 'Cart items:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_TXN_TYPE', 'Trans. Type:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_TXN_ID', 'Trans. ID:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_PARENT_TXN_ID', 'Parent Trans. ID:');
    define('MODULE_PAYMENT_TODOPAGO_ENTRY_COMMENTS', 'System Comments: ');
    
    
    define('MODULE_PAYMENT_TODOPAGO_PURCHASE_DESCRIPTION_TITLE', 'All the items in your shopping basket (see details in the store and on your store receipt).');
    define('MODULE_PAYMENT_TODOPAGO_PURCHASE_DESCRIPTION_ITEMNUM', STORE_NAME . ' Purchase');
    define('MODULES_PAYMENT_TODOPAGOSTD_LINEITEM_TEXT_ONETIME_CHARGES_PREFIX', 'One-Time Charges related to ');
    define('MODULES_PAYMENT_TODOPAGOSTD_LINEITEM_TEXT_SURCHARGES_SHORT', 'Surcharges');
    define('MODULES_PAYMENT_TODOPAGOSTD_LINEITEM_TEXT_SURCHARGES_LONG', 'Handling charges and other applicable fees');
    define('MODULES_PAYMENT_TODOPAGOSTD_LINEITEM_TEXT_DISCOUNTS_SHORT', 'Discounts');
    define('MODULES_PAYMENT_TODOPAGOSTD_LINEITEM_TEXT_DISCOUNTS_LONG', 'Credits applied, including discount coupons, gift certificates, etc');