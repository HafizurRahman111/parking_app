<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Acess-Control-Allow-Methods: POST");
header('Content-type: multipart/form-data');
header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');

// Database Connection
$dbfile = '/var/www/html/dbconn/dbconn-pdo.php';
include $dbfile;

$getConn = new DatabaseConfig();
$conn = $getConn->getConnection();

$fileName = $_FILES['sendimage']['name'];
$tempPath = $_FILES['sendimage']['tmp_name'];
$fileSize = $_FILES['sendimage']['size'];
/*
$json_string = $_POST['user_id'];
$user_id = json_decode($json_string);
 */
if (empty($fileName)) {
    $errorMSG = json_encode(array('description' => 'Please Select an Image', 'statusCode' => '400'));
    echo $errorMSG;
} else {

    $upload_path = '../assets/images/user_photo/'; // Upload Folder Path
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // Get Image Extension

    $valid_extensions = array('jpeg', 'jpg', 'png', 'gif'); // valid image extensions

    // Valid image file formats
    if (in_array($fileExt, $valid_extensions)) {
        //check file not exist our upload folder path
        /*  if (!file_exists($upload_path . $fileName)) { */
        // File size '5MB'
        if ($fileSize < 5000000) {
            move_uploaded_file($tempPath, $upload_path . $fileName); // Move file from system temporary path to our upload folder path
        } else {
            $errorMSG = json_encode(array('description' => 'Sorry, Your file is too large, Please upload 5 MB size', 'statusCode' => '400'));
            echo $errorMSG;
        }
        /*  } else {
    $errorMSG = json_encode(array('description' => 'Sorry, File already exists check user photo folder', 'statusCode' => '400'));
    echo $errorMSG;
    } */
    } else {
        $errorMSG = json_encode(array('description' => 'Sorry, only JPG, JPEG, PNG & GIF files are allowed', 'statusCode' => '400'));
        echo $errorMSG;
    }
}

// If no error, then continue
if (!isset($errorMSG)) {

    $query = "INSERT INTO user_photo (name) VALUES('$fileName')";
    $stmt = $conn->prepare($query);
    $execute = $stmt->execute();

    $get_photo_id = $conn->lastInsertId();

    if ($execute) {
        $cont = json_encode(array('description' => 'Image Uploaded Successfully', 'statusCode' => '200', 'photo_id' => $get_photo_id));
        print_r($cont);

    } else {

        $cont = json_encode(array('description' => 'Image Uploaded Failed', 'statusCode' => '400'));
        print_r($cont);

    }

}
