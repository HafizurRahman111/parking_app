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

function isJson($string)
{
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

$data = file_get_contents('php://input');

if (isJson($data)) {

    $decodeData = json_decode($data, true);

    $uid = $decodeData['userid'];
    $parking_list = array();

    $query = "SELECT * FROM parking_lot WHERE user_id = '$uid' ";
    $stmt = $conn->prepare($query);

    $stmt->bindParam('userid', $uid, PDO::PARAM_STR);
    $stmt->execute();
    $count = $stmt->rowCount();

    if ($count > 0) {

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $json_value = array(
                'parking_id' => $row['id'],
                'name_parking' => $row['parking_name'],
                'status' => $row['status'],
                'user_msisdn' => $row['user_msisdn'],
                'number_of_blocks' => $row['number_of_blocks'],
                'is_slot_available' => $row['is_slot_available'],
                'address' => $row['address'],
                'lat_v' => $row['lat_v'],
                'long_val' => $row['long_val'],
                'location_lat_value' => $row['location_lat_value'],
                'location_long_value' => $row['location_long_value'],
                'operational_in_night' => $row['operational_in_night'],
                'minimum_hr_to_pay' => $row['minimum_hr_to_pay'],
                'is_monthly_pass_allowed' => $row['is_monthly_pass_allowed'],
                'monthly_pass_cost' => $row['monthly_pass_cost'],
                'otp_verified' => $row['is_otp_verified'],
                'rating' => $row['rating'],
                'bike_rent_hr' => $row['bike_rent_hr'],
                'bike_rent_monthly' => $row['bike_rent_monthly'],
                'car_rent_hr' => $row['car_rent_hr'],
                'car_rent_monthly' => $row['car_rent_monthly'],
                'micro_rent_hr' => $row['micro_rent_hr'],
                'micro_rent_monthly' => $row['micro_rent_monthly'],
                'minibus_rent_hr' => $row['minibus_rent_hr'],
                'minibus_rent_monthly' => $row['minibus_rent_monthly'],
                'bus_rent_hr' => $row['bus_rent_hr'],
                'bus_rent_monthly' => $row['bus_rent_monthly'],
                'truck_rent_hr' => $row['truck_rent_hr'],
                'truck_rent_monthly' => $row['truck_rent_monthly'],

            );
            array_push($parking_list, $json_value);

        }

        $json_value2 = array(
            'statusCode' => '200',
            'description' => 'User Parking List Found',
            'User Parking List' => $parking_list,
        );

        $cont = json_encode($json_value2);

    } else {
        $json_value = array(
            'parking_id' => 0,
            'name_parking' => 'NA',
            'status' => 'NA',
            'user_msisdn' => 'NA',
            'number_of_blocks' => 'NA',
            'is_slot_available' => 'NA',
            'address' => 'NA',
            'lat_v' => 'NA',
            'long_val' => 'NA',
            'location_lat_value' => 'NA',
            'location_long_value' => 'NA',
            'operational_in_night' => 'NA',
            'minimum_hr_to_pay' => 'NA',
            'is_monthly_pass_allowed' => 'NA',
            'monthly_pass_cost' => 'NA',
            'otp_verified' => 'NA',
            'rating' => 0.00,
            'bike_rent_hr' => 'NA',
            'bike_rent_monthly' => 'NA',
            'car_rent_hr' => 'NA',
            'car_rent_monthly' => 'NA',
            'micro_rent_hr' => 'NA',
            'micro_rent_monthly' => 'NA',
            'minibus_rent_hr' => 'NA',
            'minibus_rent_monthly' => 'NA',
            'bus_rent_hr' => 'NA',
            'bus_rent_monthly' => 'NA',
            'truck_rent_hr' => 'NA',
            'truck_rent_monthly' => 'NA',

        );
        array_push($parking_list, $json_value);

        $json_value2 = array(
            'statusCode' => '400',
            'description' => 'No Data Found for User Parking List.',
            'User Parking List' => $parking_list,

        );

        $cont = json_encode($json_value2);

    }

    print_r($cont);

} else {
    echo 'Something went wrong! Try Again';
    exit;

}

// Md. Hafizur Rahman