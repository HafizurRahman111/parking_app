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

    $id = $decodeData[ 'id' ];
    $name =  $decodeData[ 'name' ];
    $phone_no =  $decodeData[ 'phone' ];
    $email =  $decodeData[ 'email' ];
    $password =  $decodeData[ 'password' ];

    $address =  $decodeData[ 'address' ];
    $otp =  $decodeData[ 'otp' ];
    $otp_verification_check =  $decodeData[ 'otp_verification_check' ];

    # Registration API - User can register if no user exist under given email or phone number

    if ( $id == 0 )
 {

        $count = $conn->query( "SELECT COUNT(*) FROM customer WHERE email='$email' OR phone='$phone_no' LIMIT 0,1" )->fetchColumn();

        if ( $count == 0 ) 
 {
            $query = "INSERT INTO customer ( first_name, phone, email, password, billing_address, registration_date, otp, is_otp_verified ) VALUES('$name', '$phone_no', '$email', MD5('".$password."'), '$address', now(), '$otp', '$otp_verification_check' )";

            $stmt = $conn->prepare( $query );
            $execute = $stmt->execute();

            if ( $execute ) 
 {
                $json_value = array( 'statusCode' => '200', 'description' => 'Successfully Registered. New User Added.' );
                $content = json_encode( $json_value );
            } else {
                $json_value = array( 'statusCode' => '400', 'description' => 'Error Occured. Registered Failed' );
                $content = json_encode( $json_value );
            }

        } elseif ( $count == 1 ) 
 {

            $json_value = array( 'statusCode' => '409', 'descrption' => 'User Already Registered' );
            $content = json_encode( $json_value );
        }

    } else {

        # Update API - User can update profile if given id exist and password matched

        $query = "SELECT * FROM customer WHERE id ='$id' ";

        $stmt = $conn->prepare( $query );
        $stmt->execute();
        $coun = $stmt->rowCount();
        $row   = $stmt->fetch( PDO::FETCH_ASSOC );

        if ( $coun == 1 ) 
 {

            $pass =   MD5( $password );

            $query2 = " UPDATE customer SET first_name='$name', phone ='$phone_no', email='$email', password='$pass', billing_address='$address', otp ='$otp', is_otp_verified='$otp_verification_check' WHERE id='$id' ";

            $stmt = $conn->prepare( $query2 );
            $stmt->execute();
            $count = $stmt->rowCount();

            if ( $count> 0 ) 
 {
                $json_value = array( 'statusCode' => '200', 'description' => 'Profile Updated Successfully' );
                $content = json_encode( $json_value );
            } else {
                $json_value = array( 'statusCode' => '400', 'description' => 'Error Occured. Profile Update Failed' );
                $content = json_encode( $json_value );
            }

        } else {
            $json_value = array( 'statusCode' => '404', 'descrption' => 'User not found' );
            $content = json_encode( $json_value );
        }

    }

    print_r( $content );

} else {
    echo 'Something went wrong!';
    exit;
}

?>