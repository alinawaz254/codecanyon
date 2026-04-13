<?php
ini_set('memory_limit', '256M');
error_reporting(0);
ob_start();
if(!isset($_POST['imgUrl']) || empty($_POST['imgUrl'])) {
    header('Content-Type: application/json');
    echo json_encode(array("status" => "error", "message" => "Image URL missing."));
    exit;
}

$imgUrl 	= '../../'.$_POST['imgUrl'];
$imgInitW 	= $_POST['imgInitW'];
$imgInitH 	= $_POST['imgInitH'];
$imgW 		= $_POST['imgW'];
$imgH 		= $_POST['imgH'];
$imgY1 		= $_POST['imgY1'];
$imgX1 		= $_POST['imgX1'];
$cropW 		= $_POST['cropW'];
$cropH 		= $_POST['cropH'];

$jpeg_quality = 100;

$upload_dir_rel = "assets/upload/";
$upload_dir_full = "../../".$upload_dir_rel;

if(!is_dir($upload_dir_full)) {
    @mkdir($upload_dir_full, 0777, true);
}

$output_filename_base = "croppedImg_".time()."_".rand(100,999);
$output_filename_full = $upload_dir_full . $output_filename_base;

if(!file_exists($imgUrl)) {
    header('Content-Type: application/json');
    echo json_encode(array("status" => "error", "message" => "Source image not found."));
    exit;
}

$what = getimagesize($imgUrl);
switch(strtolower($what['mime']))
{
    case 'image/png':
        $source_image = @imagecreatefrompng($imgUrl);
		$type = '.png';
        break;
    case 'image/jpg':
    case 'image/jpeg':
        $source_image = @imagecreatefromjpeg($imgUrl);
		$type = '.jpg';
        break;	
    case 'image/gif':
        $source_image = @imagecreatefromgif($imgUrl);
		$type = '.gif';
        break;
    case 'image/webp':
        $source_image = @imagecreatefromwebp($imgUrl);
        $type = '.webp';
        break;
    default: 
        header('Content-Type: application/json');
        echo json_encode(array("status" => "error", "message" => "Image type not supported."));
        exit;
}

if(!$source_image) {
    header('Content-Type: application/json');
    echo json_encode(array("status" => "error", "message" => "Could not load source image."));
    exit;
}

$resizedImage = imagecreatetruecolor($imgW, $imgH);

// Handle transparency for PNG and GIF
if($type == '.png' || $type == '.gif' || $type == '.webp') {
    imagealphablending($resizedImage, false);
    imagesavealpha($resizedImage, true);
    $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
    imagefilledrectangle($resizedImage, 0, 0, $imgW, $imgH, $transparent);
}

imagecopyresampled($resizedImage, $source_image, 0, 0, 0, 0, $imgW, $imgH, $imgInitW, $imgInitH);	

$dest_image = imagecreatetruecolor($cropW, $cropH);

// Handle transparency for PNG and GIF
if($type == '.png' || $type == '.gif' || $type == '.webp') {
    imagealphablending($dest_image, false);
    imagesavealpha($dest_image, true);
    $transparent = imagecolorallocatealpha($dest_image, 255, 255, 255, 127);
    imagefilledrectangle($dest_image, 0, 0, $cropW, $cropH, $transparent);
}

imagecopyresampled($dest_image, $resizedImage, 0, 0, $imgX1, $imgY1, $cropW, $cropH, $cropW, $cropH);	

// Save based on type
$final_url = $upload_dir_rel . $output_filename_base . $type;
$final_dest = $upload_dir_full . $output_filename_base . $type;

$success = false;
if($type == '.png') {
    $success = imagepng($dest_image, $final_dest);
} elseif($type == '.gif') {
    $success = imagegif($dest_image, $final_dest);
} elseif($type == '.webp') {
    $success = imagewebp($dest_image, $final_dest);
} else {
    $success = imagejpeg($dest_image, $final_dest, $jpeg_quality);
}

if($success) {
    $response = array(
        "status" => 'success',
        "url" => $final_url
    );
} else {
    $response = array(
        "status" => 'error',
        "message" => "Could not save cropped image. Check folder permissions."
    );
}

ob_clean();
header('Content-Type: application/json');
echo json_encode($response);
exit;