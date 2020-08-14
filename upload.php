<?php
ini_set('display_errors', 1);
require "vendor/autoload.php";
//include('lib/phpqrcode/qrlib.php');
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;

$file_type = strtolower(pathinfo($_FILES["icon"]["name"], PATHINFO_EXTENSION));
$target_dir = "input";
$icon_file = $target_dir . '/icon.' . $file_type;
$qr_file = $target_dir . '/qr-code.png';
$marker_file = $target_dir . '/letterA.png';
$data_file = "input/data.json";

if ($file_type != "jpg" && $file_type != "png" && $file_type != "jpeg") {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    exit;
}

if (file_exists($icon_file)) {
    unlink($icon_file);
}
if (file_exists($qr_file)) {
    unlink($qr_file);
}

$codeContents = array("text" => $_POST["text"]);
$codeContents["color"] = $_POST["color"];
$codeContents["text-position"] = $_POST["text-position"];
$codeContents["text-size"] = $_POST["text-size"];
$codeContents["icon-position"] = $_POST["icon-position"];
$codeContents["icon-size"] = $_POST["icon-size"];

$codeContents["icon-file"] = $icon_file;
$codeContents["qr-file"] = $qr_file;

assert(move_uploaded_file($_FILES["icon"]["tmp_name"], $icon_file), "error uploading icon");
//$codeContents["icon"] = "PHOTO;$file_type;ENCODING=BASE64:" . base64_encode(file_get_contents($icon_file));
$codeContents = json_encode($codeContents);

file_put_contents($data_file, $codeContents);

$qrCode = new QrCode($codeContents);
$qrCode->setSize(1155);
$qrCode->setMargin(10);
// Set advanced options
$qrCode->setWriterByName('png');
$qrCode->setEncoding('UTF-8');
$qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
$qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
$qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
$qrCode->setLogoPath($marker_file);
$qrCode->setLogoSize(226, 226);
$qrCode->setValidateResult(false);
// Round block sizes to improve readability and make the blocks sharper in pixel based outputs (like png).
// There are three approaches:
$qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_ENLARGE); // The size of the qr code and the final image is enlarged, if necessary
// Save it to a file
$qrCode->writeFile($qr_file);
// Generate a data URI to include image data inline (i.e. inside an <img> tag)
$dataUri = $qrCode->writeDataUri();
//QRcode::png($codeContents, $qr_file, QR_ECLEVEL_L, 39);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Web JS - Augmented Reality</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/main.css" />
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
</head>

<body>
    <div class="main">
        <nav class="navbar navbar-dark bg-primary">
            <a class="navbar-brand" href="#">
                Web Augmented Reality Project
                <span class="clientname">Client: David Thomas</span>
            </a>
        </nav>
        <div id="qrcodes-main" class="content container" style="display: block;">
            <div class="row">
                <dv class="col-2"></dv>
                <div id="qrcodes" class="col-10" data-ride="carousel">
                    <div class="row">
                        <div>
                            <img src="<?php echo $dataUri; ?>" alt="..." width="70%" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>