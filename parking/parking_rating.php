<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');

$dbfile = '/var/www/html/dbconn/dbconn-pdo.php';
include $dbfile;

$getConn = new DatabaseConfig();
$conn = $getConn->getConnection();

function isJson($string)
{
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

$data = file_get_contents('php://input');

if (isJson($data)) {

    $decodeData = json_decode($data, true);

    $parking_id = $decodeData['parking_id'];
    $rating = $decodeData['rating'];

    $query = "SELECT * FROM parking_lot WHERE id = '$parking_id' LIMIT 1";
    $stmt = $conn->prepare($query);

    $stmt->execute();
    $count = $stmt->rowCount();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($count == 1 && !empty($row)) {
        $query_update = "UPDATE parking_lot SET rating = (rating+'$rating')/2 WHERE id = '$parking_id' ";

        $stmt = $conn->prepare($query_update);
        $execute = $stmt->execute();

        if ($execute) {
            $json_value = array('statusCode' => '200', 'description' => 'Parking Rating Updated Successfully');
            $content = json_encode($json_value);
        } else {
            $json_value = array('statusCode' => '400', 'description' => 'Error Occured. Parking Rating Update Failed');
            $content = json_encode($json_value);
        }

    } else {
        $json_value = array('statusCode' => '404', 'descrption' => 'Parking Lot Not Found');
        $content = json_encode($json_value);
    }

    print_r($content);

} else {
    echo 'Something went wrong!';
    exit;
}