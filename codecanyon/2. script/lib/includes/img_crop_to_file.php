<?php
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

$output_url = "assets/upload/croppedImg_".rand();
$output_filename = '../../'.$output_url;

$what = getimagesize($imgUrl);
switch(strtolower($what['mime']))
{
    case 'image/png':
        $img_r = imagecreatefrompng($imgUrl);
		$source_image = imagecreatefrompng($imgUrl);
		$type = '.png';
        break;
    case 'image/jpg':
        $img_r = imagecreatefromjpeg($imgUrl);
		$source_image = imagecreatefromjpeg($imgUrl);
		$type = '.jpg';
        break;
	case 'image/jpeg':
        $img_r = imagecreatefromjpeg($imgUrl);
		$source_image = imagecreatefromjpeg($imgUrl);
		$type = '.jpg';
        break;	
    case 'image/gif':
        $img_r = imagecreatefromgif($imgUrl);
		$source_image = imagecreatefromgif($imgUrl);
		$type = '.gif';
        break;
    default: die('image type not supported');
}

	$resizedImage = imagecreatetruecolor($imgW, $imgH);
	imagecopyresampled($resizedImage, $source_image, 0, 0, 0, 0, $imgW, 
				$imgH, $imgInitW, $imgInitH);	
	
	
	$dest_image = imagecreatetruecolor($cropW, $cropH);
	imagecopyresampled($dest_image, $resizedImage, 0, 0, $imgX1, $imgY1, $cropW, 
				$cropH, $cropW, $cropH);	


	imagejpeg($dest_image, $output_filename.$type, $jpeg_quality);
	
	$response = array(
			"status" => 'success',
			"url" => $output_url.$type 
		  );
	 print json_encode($response);