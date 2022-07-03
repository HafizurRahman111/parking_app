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

    $user_id = $decodeData['user_id'];

    $parking_history_active_list = array();

    //  $query = "SELECT *  FROM parking_lot as plot LEFT JOIN parking_history AS phis ON plot.user_id  = phis.user_id  WHERE plot.user_id = '$user_id' ";
    // $query = "SELECT * FROM parking_history AS ph LEFT JOIN parking_lot AS pl ON ph.user_id = pl.user_id WHERE ph.user_id = '$user_id' AND ph.end_time IS NULL ";

    $query = "SELECT * FROM parking_history AS ph LEFT JOIN parking_lot AS pl ON ph.parking_id = pl.id WHERE ph.user_id = '$user_id' AND ph.end_time IS NULL ";

    $stmt = $conn->prepare($query);
    $execute = $stmt->execute();
    $count = $stmt->rowCount();

    if ($count > 0) {

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $json_value = array(

                'parking_id' => $row['id'],
                'parking_history_id' => $row['p_hid'],
                'start_time' => $row['start_time'],
                'vehicle_type_id' => $row['vehicle_type_id'],
                'vehicle_model' => $row['vehicle_model'],
                'vehicle_number' => $row['vehicle_number'],
                'parking_address' => $row['address'],
                'parking_title' => $row['parking_name'],

            );
            array_push($parking_history_active_list, $json_value);
        }

        $json_value2 = array(
            'statusCode' => '200',
            'description' => 'User Active Parking List Found',
            'User Active Parking List' => $parking_history_active_list,
        );

        $cont = json_encode($json_value2);

    } else {
        $json_value = array(
            'parking_id' => 0,
            'parking_history_id' => 0,
            'start_time' => 'NA',
            'vehicle_type_id' => 'NA',
            'vehicle_model' => 'NA',
            'vehicle_number' => 'NA',
            'parking_address' => 'NA',
            'parking_title' => 'NA',

        );
        array_push($parking_history_active_list, $json_value);

        $json_value2 = array(
            'statusCode' => '400',
            'description' => 'No Data Found for User Active Parking List.',
            'User Active Parking List' => $parking_history_active_list,

        );

        $cont = json_encode($json_value2);

    }

    print_r($cont);

} else {
    echo 'Something went wrong! Try Again';
    exit;

}