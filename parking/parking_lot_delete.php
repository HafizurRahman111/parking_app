<?php

ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );
header( 'Content-type: application/json' );
header( 'Access-Control-Allow-Origin: *' );

$dbfile = '/var/www/html/dbconn/dbconn-pdo.php';
include( $dbfile );

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

    $parking_delete_id =  $decodeData[ 'parking_id' ];

    $query = "DELETE FROM parking_lot WHERE id = '$parking_delete_id' ";

    $stmt = $conn->prepare( $query );
    $stmt->execute();
    $count = $stmt->rowCount();

    if ( $count> 0 ) 
 {
        $json_value = array( 'statusCode' => '200', 'description' => 'Parking Lot Deleted Successfully' );
        $content = json_encode( $json_value );
    } else {
        $json_value = array( 'statusCode' => '400', 'description' => 'Error Occured. Parking Lot Delete Failed.' );
        $content = json_encode( $json_value );
    }

    print_r( $content );

} else {
    echo 'Something went wrong!';
    exit;
}

?>