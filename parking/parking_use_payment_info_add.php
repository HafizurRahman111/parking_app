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

    $parking_history_id = $decodeData['parking_history_id'];
    $pay_by_user_id = $decodeData['pay_by_user_id'];
    $for_parking_id = $decodeData['for_parking_id'];
    $total_amount = $decodeData['total_amount'];

    $query = "UPDATE parking_history SET total_amount ='$total_amount' WHERE user_id = '$pay_by_user_id' AND parking_id='$for_parking_id' ";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $count = $stmt->rowCount();

    if ($count > 0) {
        $json_value = array('statusCode' => '200', 'description' => 'Payment Information for the Booked Parking Updated Successfully');
        $content = json_encode($json_value);
    } else {
        $json_value = array('statusCode' => '400', 'description' => 'Error Occured. Payment Information for the Booked Parking Update Failed');
        $content = json_encode($json_value);
    }

    print_r($content);

} else {
    echo 'Something went wrong!';
    exit;
}