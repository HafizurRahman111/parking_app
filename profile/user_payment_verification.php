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

    $payment_add_get = $decodeData['payment_add_get'];

    $parking_history_id = $decodeData['parking_history_id'];
    $userid = $decodeData['userid'];
    $amount = $decodeData['amount'];
    $payment_status = $decodeData['payment_status'];

    if ($payment_add_get == 0) {

        $query = "INSERT INTO user_payment_history ( parking_history_id, userid, amount, payment_status )
        VALUES( '$parking_history_id', '$userid', '$amount', '$payment_status' )";
        $stmt = $conn->prepare($query);
        $execute = $stmt->execute();

        if ($execute) {
            $json_value = array('statusCode' => '200', 'description' => 'User Payment History Information added successfully');
            $content = json_encode($json_value);
        } else {
            $json_value = array('statusCode' => '400', 'description' => 'Error Occured. User Payment History Information Add Failed.');
            $content = json_encode($json_value);
        }
        print_r($content);

    } else if ($payment_add_get == 1) {

        $user_payment_history_info = "SELECT * FROM user_payment_history WHERE userid ='$userid' AND parking_history_id ='$parking_history_id' ";
        $stmt_1 = $conn->prepare($user_payment_history_info);
        $execute_1 = $stmt_1->execute();
        $count_1 = $stmt_1->rowCount();
        $row   = $stmt_1->fetch( PDO::FETCH_ASSOC );
    
        if ($count_1 == 1) {

            $json_value = array(
                'statusCode' => '200',
                'description' => 'User Payment History Found',

                'payment_history_id' => $row['id'],
                'parking_history_id' => $row['parking_history_id'],
                'userid' => $row['userid'],
                'amount' => $row['amount'],
                'payment_status' => $row['payment_status'],

            );
            $content = json_encode($json_value);

        } else {

            $json_value = array(
                'statusCode' => '400',
                'description' => 'No User Payment History Found',

                'payment_history_id' => 0,
                'parking_history_id' => 0,
                'userid' => 0,
                'amount' => 'NA',
                'payment_status' => 'NA',

            );
            $content = json_encode($json_value);

        }

        print_r($content);
    }

} else {
    echo 'Something went wrong!';
    exit;
}