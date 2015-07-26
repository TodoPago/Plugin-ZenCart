<?php

$params = base64_decode($_GET['params']);
parse_str($params, $output);

$params = '';

foreach($output as $index => $value){

    if ($index != 'amount'){

        $params .= $index."=".$value."&";
    }
}

$params = trim($params,'&');
?>
<img src="<?php echo $output['logo']?>" />
<hr />
<?php
echo "Importe: $".$output['amount'];
?>
<hr />
<?php
echo "Nro de Orden:".$output['orden'];
?>
<hr />
<?php
echo "Nombre:".$output['name'];
?>
<hr />
<img src="image.php?<?php echo $params?>" />
<script type="text/javascript"> // window.print();</script>