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

    ## Get JSON Response values

    $start_end_code = $decodeData['start_end_code'];
    $user_id = $decodeData['user_id'];
    $p_his_id = $decodeData['p_his_id'];
    $parking_id = $decodeData['parking_id'];
    $vehicle_model = $decodeData['vehicle_model'];
    $vehicle_number = $decodeData['vehicle_number'];
    $vehicle_type_id = $decodeData['vehicle_type_id'];
    $rent_type = $decodeData['rent_type'];

    $parking_history_list = array();
    $parking_details = array();

    date_default_timezone_set('Asia/Dhaka');

    # Parking History: Parking Time Start When $start_end_code will be 1

    if ($start_end_code == 1) {
        $count = $conn->query("SELECT COUNT(*) FROM parking_lot WHERE id='$parking_id' AND number_of_blocks>0 AND number_of_blocks<=block_limit LIMIT 0,1")->fetchColumn();

        if ($count == 1) {

            $start_time = date('Y-m-d H:i:s');

            if ($rent_type == 1) {
                $final_end = date('Y-m-d H:i:s', strtotime($start_time . ' +1 day'));
            } else if ($rent_type == 2) {
                $final_end = date('Y-m-d H:i:s', strtotime($start_time . ' +1 month'));
            } else {
                $final_end = "NA";
            }

            $query = "INSERT INTO parking_history ( user_id, parking_id, vehicle_model, vehicle_number, start_time, final_end, vehicle_type_id, rent_type  ) VALUES('$user_id', '$parking_id', '$vehicle_model' , '$vehicle_number', '$start_time', '$final_end', '$vehicle_type_id'  ,'$rent_type' )";
            $stmt = $conn->prepare($query);
            $execute = $stmt->execute();

            $last_id = $conn->lastInsertId();

            if ($execute) {

                // number_of_blocks decrement when book a parking
                $query_block_de = "UPDATE parking_lot SET number_of_blocks=number_of_blocks-1 WHERE id='$parking_id' AND number_of_blocks>0 ";
                $stmt_block_de = $conn->prepare($query_block_de);
                $execute_block_de = $stmt_block_de->execute();

                // number_of_blocks decrement when book a parking part end ----------------------------

                $json_value = array('statusCode' => '200', 'description' => 'Parking Time Starts From Now', 'id' => $last_id);
                $content = json_encode($json_value);
            } else {
                $json_value = array('statusCode' => '400', 'description' => 'Error Occured. Parking Time Not Started.');
                $content = json_encode($json_value);
            }

        } elseif ($count == 0) {
            $json_value = array('statusCode' => '409', 'descrption' => 'No Parking Lot or Number of Block is Available');
            $content = json_encode($json_value);
        }

    } else if ($start_end_code == 0) {
        # Parking History: Parking Time End and Show Active Time When $start_end_code will be 0
        $count = $conn->query("SELECT COUNT(*) FROM parking_lot WHERE id='$parking_id' LIMIT 0,1")->fetchColumn();

        if ($count == 1) {
            // $val = 'NVY';

            $query = "UPDATE parking_history SET end_time= now() WHERE user_id = '$user_id' AND parking_id='$parking_id' AND p_hid = '$p_his_id' ";
            $stmt = $conn->prepare($query);
            $execute = $stmt->execute();

            if ($execute) {
                // $query2 = "SELECT * FROM parking_history WHERE user_id = '$user_id' AND parking_id='$parking_id' AND p_hid = '$p_his_id' ";
                $query2 = "SELECT *  FROM parking_lot as plot LEFT JOIN parking_history AS phis ON plot.id  = phis.parking_id  WHERE phis.p_hid = '$p_his_id' ";
                $stmt2 = $conn->prepare($query2);
                $execute2 = $stmt2->execute();

                $count2 = $stmt2->rowCount();

                // number_of_blocks increment when book a parking
                $query_block_de = "UPDATE parking_lot SET number_of_blocks=number_of_blocks+1 WHERE id='$parking_id'";
                $stmt_block_de = $conn->prepare($query_block_de);
                $execute_block_de = $stmt_block_de->execute();

                // number_of_blocks increment when book a parking part end ----------------------------

                if ($count > 0) {

                    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                        $start = new DateTime($row['start_time']);
                        $end = new DateTime($row['end_time']);

                        $tc = $start->diff($end);

                        $diff_minutes = $tc->days * 24 * 60;
                        $diff_minutes += $tc->h * 60;
                        $diff_minutes += $tc->i;

                        $time_count = $diff_minutes;

                        $t_count_cost = ceil($time_count / 60);

                        if ($vehicle_type_id == 101 && $rent_type == 1) {
//bike
                            $amount = $row['bike_rent_hr'] * $t_count_cost;
                        } else if ($vehicle_type_id == 102 && $rent_type == 1) {
//car
                            $amount = $row['car_rent_hr'] * $t_count_cost;
                        } else if ($vehicle_type_id == 103 && $rent_type == 1) {
//micro
                            $amount = $row['micro_rent_hr'] * $t_count_cost;
                        } else if ($vehicle_type_id == 104 && $rent_type == 1) {
//minibus
                            $amount = $row['minibus_rent_hr'] * $t_count_cost;
                        } else if ($vehicle_type_id == 105 && $rent_type == 1) {
//bus
                            $amount = $row['bus_rent_hr'] * $t_count_cost;
                        } else if ($vehicle_type_id == 106 && $rent_type == 1) {
//truck
                            $amount = $row['truck_rent_hr'] * $t_count_cost;
                        } else {
                            $amount = 0;
                        }

                        //  $time_count = $tc->format( '%d-%m-%y %h:%i:%s' ) ;
                        // Time count in days, months, years, hours, mins, and sec
                        // $time_count = $tc ;
                        // Time count in days, months, years, hours, mins, and sec

                        // $time_diff = abs( $end - $start ) ;

                        //  $hours = floor( $time_diff / 3600 );
                        //  $minutes = floor( ( $time_diff / 60 ) % 60 );
                        // $seconds = $time_diff % 60;

                        //  $time_count = gmdate( 'd-m-Y H:i:s', $time_diff );

                        $json_value3 = array(

                            'id' => $row['id'],
                            'name_parking' => $row['parking_name'],
                            'status' => $row['status'],
                            'user_msisdn' => $row['user_msisdn'],
                            'user_id' => $row['user_id'],
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
                        array_push($parking_details, $json_value3);

                        $json_value = array(

                            'parking_strated' => $row['start_time'],
                            'parking_ended' => $row['end_time'],
                            'total_time_count' => $time_count,
                            'total_amount' => $amount,
                            'Parking_details' => $parking_details,

                        );
                        array_push($parking_history_list, $json_value);

                    }

                    $json_value2 = array(
                        'statusCode' => '200',
                        'description' => 'Parking Time Ends',
                        'Parking_activity' => $parking_history_list,
                    );

                    $content = json_encode($json_value2);

                } else {

                    $json_value3 = array(

                        'id' => 0,
                        'name_parking' => 'NA',
                        'status' => 'NA',
                        'user_msisdn' => 'NA',
                        'user_id' => 0,
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
                        'rating' => 0,
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

                    array_push($parking_details, $json_value3);

                    $json_value = array(
                        'parking_strated' => 'NA',
                        'parking_ended' => 'NA',
                        'total_time_count' => 'NA',
                        'total_amount' => 'NA',
                        'Parking_details' => $parking_details,

                    );
                    array_push($parking_history_list, $json_value);

                    $json_value2 = array(
                        'statusCode' => '400',
                        'description' => 'No Data Found for Parking Time History',
                        'Parking_activity' => $parking_history_list,
                    );

                    $content = json_encode($json_value2);

                }

            } else {
                $json_value = array('statusCode' => '400', 'description' => 'Error Occured. Parking Time Not Ended.');
                $content = json_encode($json_value);
            }

        } elseif ($count == 0) {
            $json_value = array('statusCode' => '409', 'descrption' => 'No user exist');
            $content = json_encode($json_value);
        }

    } else {

        $json_value = array('statusCode' => '404', 'descrption' => 'Invalid Code Used. Try again with correct code.');
        $content = json_encode($json_value);
    }

    print_r($content);

} else {
    echo 'Something went wrong!';
    exit;
}