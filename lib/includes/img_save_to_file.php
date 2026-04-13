<?php
ini_set('memory_limit', '256M');
error_reporting(0);
ob_start();
/*
*	!!! THIS IS JUST AN EXAMPLE !!!, PLEASE USE ImageMagick or some other quality image processing libraries
*/
    $imagePath = "../../assets/upload/";

	if(!is_dir($imagePath)) {
	    @mkdir($imagePath, 0777, true);
	}

    if (!isset($_FILES["img"]) || empty($_FILES["img"]["name"])) {
        $response = array(
            "status" => 'error',
            "message" => 'No file uploaded or file too large.',
        );
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

	$allowedExts = array("gif", "jpeg", "jpg", "png", "GIF", "JPEG", "JPG", "PNG", "webp", "WEBP");
	$temp = explode(".", $_FILES["img"]["name"]);
	$extension = strtolower(end($temp));

	if ( in_array($extension, $allowedExts))
	  {
	  if ($_FILES["img"]["error"] > 0)
		{
			 $response = array(
				"status" => 'error',
				"message" => 'Upload Error: '. $_FILES["img"]["error"],
			);
		}
	  else {
		  $filename = $_FILES["img"]["tmp_name"];
		  list($width, $height) = getimagesize( $filename );

          // Use a unique name to avoid conflicts
          $new_filename = 'temp_'.time().'_'.rand(100,999).'.'.$extension;

		  if(move_uploaded_file($filename,  $imagePath . $new_filename)) {
              $response = array(
                "status" => 'success',
                "url" => 'assets/upload/'.$new_filename,
                "width" => $width,
                "height" => $height
              );
          } else {
              $response = array(
                "status" => 'error',
                "message" => 'Could not save file to assets/upload/. Please check folder permissions (777).',
              );
          }
		}
	  } else {
			$response = array(
				"status" => 'error',
				"message" => 'Invalid file format. Use JPG, PNG, GIF or WEBP.',
			);
	  }
    ob_clean();
    header('Content-Type: application/json');
	echo json_encode($response);
    exit;