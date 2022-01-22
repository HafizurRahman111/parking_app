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

    $phone_no =  $decodeData[ 'phone' ];
    $pass =  $decodeData[ 'password' ];
    $password = md5( $pass );

    $query = "SELECT * FROM customer WHERE phone='$phone_no' AND password='$password' LIMIT 1";
    $stmt = $conn->prepare( $query );

    $stmt->bindParam( 'phone', $phone_no, PDO::PARAM_STR );
    $stmt->bindValue( 'password', $password, PDO::PARAM_STR );

    $stmt->execute();
    $count = $stmt->rowCount();
    $row   = $stmt->fetch( PDO::FETCH_ASSOC );

    if ( $count == 1 && !empty( $row ) )
 {

        $json_value = array(
            'User ID' => $row[ 'id' ],
            'Name' => $row[ 'first_name' ],
            'Contact Number' => $row[ 'phone' ],
            'Email' => $row[ 'email' ],
            'Address' => $row[ 'billing_address' ],
            'statusCode' => '200',
            'description' => 'Login successfull'

        );

        $cont = json_encode( $json_value );

    } else {
        $json_value = array(
            'User ID' => 'NA',
            'Name' => 'NA',
            'Contact Number' => 'NA',
            'Email' => 'NA',
            'Address' => 'NA',
            'statusCode' => '400',
            'description' => 'Login Failed. Incorrect Phone Number or Password. Please Try Again.'

        );

        $cont = json_encode( $json_value );
    }

    print_r( $cont );

} else {
    echo 'Something went wrong! Try Again';
    exit;

}

?>