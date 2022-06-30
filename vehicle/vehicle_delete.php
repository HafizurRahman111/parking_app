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

// Md. Hafizur Rahman

function isJson($string)
{
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

$data = file_get_contents('php://input');

if (isJson($data)) {
    $decodeData = json_decode($data, true);

    $uid = $decodeData['uid'];
    $vehicle_no = $decodeData['vehicle_no'];

    $count = $conn->query("SELECT COUNT(*) FROM vehicle WHERE userid='$uid' AND vehicle_number='$vehicle_no' LIMIT 0,1")->fetchColumn();

    if ($count > 0) {
        $query2 = "DELETE FROM vehicle WHERE userid='$uid' AND vehicle_number='$vehicle_no' ";

        $stmt = $conn->prepare($query2);
        $execute = $stmt->execute();

        if ($execute) {
            $json_value = array('Status Code' => '200', 'Description' => 'Vehicle Information Deleted Successfully');
            $content = json_encode($json_value);
        } else {
            $json_value = array('Status Code' => '400', 'Description' => 'Error Occured. Vehicle Information Delete Failed.');
            $content = json_encode($json_value);
        }

    } else {

        $json_value = array('Status Code' => '404', 'Descrption' => 'Data not available for deleting.');
        $content = json_encode($json_value);

    }

    print_r($content);

} else {
    echo 'Something went wrong!';
    exit;
}