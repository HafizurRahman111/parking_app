<?php

ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );
header( 'Content-type: application/json' );
header( 'Access-Control-Allow-Origin: *' );

$dbfile = '/var/www/html/dbconn/dbconn-pdo.php';
include( $dbfile );

// Md. Hafizur Rahman 

$getConn = new DatabaseConfig();
$conn = $getConn->getConnection();

function isJson( $string ) 
 {
    json_decode( $string );
    return ( json_last_error() == JSON_ERROR_NONE );
}

$data = file_get_contents( 'php://input' );

if ( isJson( $data ) ) 
 {

    $decodeData = json_decode( $data, true );

    $phone =  $decodeData[ 'phone' ];
    $vehicle_no =  $decodeData[ 'vehicle_no' ];
    $type =  $decodeData[ 'type' ];
    $model =  $decodeData[ 'model' ];
    $uid =  $decodeData[ 'uid' ];

    $query = "INSERT INTO vehicle ( customer_phone, userid, vehicle_number, vehicle_type, vehicle_model ) VALUES( '$phone', '$uid', '$vehicle_no', '$type', '$model' )";

    $stmt = $conn->prepare( $query );
    $execute = $stmt->execute();

    if ( $execute ) 
 {
        $json_value = array( 'Status Code' => '200', 'Description' => 'Vehicle Information Added Successfully' );
        $content = json_encode( $json_value );
    } else {
        $json_value = array( 'Status Code' => '400', 'Description' => 'Vehicle Information Add Failed. Try Again.' );
        $content = json_encode( $json_value );
    }

    print_r( $content );

} else {
    echo 'Something went wrong!';
    exit;
}

?>