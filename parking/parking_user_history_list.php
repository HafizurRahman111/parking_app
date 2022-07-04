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

    $parking_user_history_list = array();

    $query = "SELECT * FROM parking_history AS ph LEFT JOIN parking_lot AS pl ON ph.parking_id = pl.id WHERE ph.user_id = '$user_id' AND ph.parking_id = pl.id AND ph.end_time IS NOT NULL ORDER BY p_hid DESC";

    $stmt = $conn->prepare($query);
    $execute = $stmt->execute();
    $count = $stmt->rowCount();

    if ($count > 0) {

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $start = new DateTime($row['start_time']);
            $end = new DateTime($row['end_time']);

            $start_time_format = new DateTime($row['start_time']);
            $st_format = $start_time_format->format('d-m-Y || H:i:s');

            $end_time_format = new DateTime($row['end_time']);
            $et_format = $end_time_format->format('d-m-Y || H:i:s');

            // $tc  = $start->diff( $end );

            $diff_minutes = $start->diff($end)->format('%h hr %i min %s sec');

            //   $diff_minutes = $tc->days * 24 * 60;
            //   $diff_minutes += $tc->h * 60;
            //  $diff_minutes += $tc->i;

            $time_count = $diff_minutes;

            $json_value = array(

                'parking_id' => $row['parking_id'],

                'parking_history_id' => $row['p_hid'],
                'start_time' => $st_format,
                'end_time' => $et_format,
                'total_time_count' => $time_count,

                'vehicle_model' => $row['vehicle_model'],
                'vehicle_number' => $row['vehicle_number'],

                'total_amount' => $row['total_amount'],

                'parking_title' => $row['parking_name'],
                'parking_address' => $row['address'],
                'rating' => $row['rating'],

            );
            array_push($parking_user_history_list, $json_value);
        }

        $json_value2 = array(
            'statusCode' => '200',
            'description' => 'User Parking History List Found',
            'User Parking History List' => $parking_user_history_list,
        );

        $cont = json_encode($json_value2);

    } else {
        $json_value = array(
            'parking_id' => 0,
            'parking_history_id' => 0,
            'start_time' => 'NA',
            'end_time' => 'NA',
            'total_time_count' => 'NA',
            'vehicle_model' => 'NA',
            'vehicle_number' => 'NA',
            'total_amount' => 0.00,
            'parking_title' => 'NA',
            'parking_address' => 'NA',
            'rating' => 0.00,

        );
        array_push($parking_user_history_list, $json_value);

        $json_value2 = array(
            'statusCode' => '400',
            'description' => 'No Data Found for User Parking History List.',
            'User Parking History List' => $parking_user_history_list,

        );

        $cont = json_encode($json_value2);

    }

    print_r($cont);

} else {
    echo 'Something went wrong! Try Again';
    exit;

}