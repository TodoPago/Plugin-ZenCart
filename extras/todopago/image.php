<?php
define('IN_CB', true);

function convertText($text) {

    $text = stripslashes($text);

    if (function_exists('mb_convert_encoding')) {

        $text = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
    }

    return $text;
}


$requiredKeys = array('code', 'filetype', 'dpi', 'scale', 'rotation', 'font_family', 'font_size', 'text');

foreach ($requiredKeys as $key) {

    if (!isset($_GET[$key])) {

    }
}

if (!preg_match('/^[A-Za-z0-9]+$/', $_GET['code'])) {

}

$code = $_GET['code'];

if (!file_exists('codebar' . DIRECTORY_SEPARATOR . $code . '.php')) {

}

switch ($code) {


	case 'CODE_128':

        $code = 'BCGcode128';
        break;
        
	case 'INTERLEAVED_2_OF_5':

        $code = 'BCGi25';
        break;
        
    case '':

        $code = 'BCGi25';
        break;
}  

include_once('codebar' . DIRECTORY_SEPARATOR . $code . '.php');

$class_dir = '..' . DIRECTORY_SEPARATOR . 'class';

require_once('codebar' . DIRECTORY_SEPARATOR . 'BCGColor.php');

require_once('codebar' . DIRECTORY_SEPARATOR . 'BCGBarcode.php');

require_once('codebar' . DIRECTORY_SEPARATOR . 'BCGDrawing.php');

require_once('codebar' . DIRECTORY_SEPARATOR . 'BCGFontFile.php');

$filetypes = array('PNG' => BCGDrawing::IMG_FORMAT_PNG, 'JPEG' => BCGDrawing::IMG_FORMAT_JPEG, 'GIF' => BCGDrawing::IMG_FORMAT_GIF);

$drawException = null;

try {

    $color_black = new BCGColor(0, 0, 0);

    $color_white = new BCGColor(255, 255, 255);

    if ($code == 'BCGcode128'){

        $code_generated = new BCGcode128();

    }

    else{

        $code_generated = new BCGi25();

    }

    if (function_exists('baseCustomSetup')) {
        baseCustomSetup($code_generated, $_GET);
    }

    if (function_exists('customSetup')) {

        customSetup($code_generated, $_GET);
    }

    $code_generated->setScale(max(1, min(4, $_GET['scale'])));
    $code_generated->setBackgroundColor($color_white);
    $code_generated->setForegroundColor($color_black);

    if ($_GET['text'] != '') {

        $text = convertText($_GET['text']);

        $code_generated->parse($text);
    }
} catch(Exception $exception) {

    $drawException = $exception;
}

$drawing = new BCGDrawing('', $color_white);

if($drawException) {

    $drawing->drawException($drawException);

} else {

    $drawing->setBarcode($code_generated);
    $drawing->setRotationAngle($_GET['rotation']);
    $drawing->setDPI($_GET['dpi'] === 'NULL' ? null : max(72, min(300, intval($_GET['dpi']))));
    $drawing->draw();
}

switch ($_GET['filetype']) {

    case 'PNG':

        header('Content-Type: image/png');
        break;

    case 'JPEG':

        header('Content-Type: image/jpeg');
        break;

    case 'GIF':
        header('Content-Type: image/gif');
        break;
}
$drawing->finish($filetypes[$_GET['filetype']]);
?>