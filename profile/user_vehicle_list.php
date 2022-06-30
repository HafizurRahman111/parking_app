<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');

// Database Connection
$dbfile = '/var/www/html/dbconn/dbconn-pdo.php';
include $dbfile;

$getConn = new DatabaseConfig();
$conn = $getConn->getConnection();

// Md. hafizur rahman

function isJson($string)
{
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

$data = file_get_contents('php://input');

if (isJson($data)) {

    $decodeData = json_decode($data, true);

    $uid = $decodeData['userid'];

    $vehicle_list = array();

    $query = "SELECT vtt.type, v.id, v.vehicle_number, v.vehicle_model, v.vehicle_type FROM vehicle_type_track as vtt LEFT JOIN vehicle AS v ON vtt.type_id  = v.vehicle_type WHERE v.userid = '$uid' ";
    $stmt = $conn->prepare($query);

    $stmt->bindParam('userid', $uid, PDO::PARAM_STR);

    $stmt->execute();
    $count = $stmt->rowCount();

    if ($count > 0) {

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $json_value = array(
                'VehicleID' => $row['id'],
                'VehicleTypeName' => $row['type'],
                'VehicleNo' => $row['vehicle_number'],
                'VehicleModel' => $row['vehicle_model'],
                'VehicleTypeID' => $row['vehicle_type'],

            );
            array_push($vehicle_list, $json_value);

        }

        $json_value2 = array(
            'StatusCode' => '200',
            'Description' => 'User Vehicle List Found',
            'VehcileList' => $vehicle_list,
        );

        $cont = json_encode($json_value2);

    } else {
        $json_value = array(
            'VehicleID' => 0,
            'VehicleTypeName' => 'NA',
            'VehicleNo' => 'NA',
            'VehicleModel' => 'NA',
            'VehicleTypeID' => 'NA',

        );
        array_push($vehicle_list, $json_value);

        $json_value2 = array(
            'StatusCode' => '400',
            'Description' => 'No Data Found for User Vehicle List.',
            'VehcileList' => $vehicle_list,
        );

        $cont = json_encode($json_value2);

    }

    print_r($cont);

} else {
    echo 'Something went wrong! Try Again';
    exit;

}