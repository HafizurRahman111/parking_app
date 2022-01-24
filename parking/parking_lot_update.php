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

    $parking_id =  $decodeData[ 'parking_id' ];
    $p_name =  $decodeData[ 'name_parking' ];

    $msisdn =  $decodeData[ 'user_msisdn' ];
    $blocks =  $decodeData[ 'number_of_blocks' ];
    $address =  $decodeData[ 'address' ];

    $lat_v =  $decodeData[ 'lat_v' ];
    $long_value =  $decodeData[ 'long_val' ];
    
    $location_lat =  $decodeData[ 'location_lat_value' ];
    $location_long =  $decodeData[ 'location_long_value' ];
    
    $bike_rent =  $decodeData[ 'bike_rent_hr' ];
    $bike_monthly =  $decodeData[ 'bike_rent_monthly' ];
    $car_rent =  $decodeData[ 'car_rent_hr' ];
    $car_monthly =  $decodeData[ 'car_rent_monthly' ];
    $micro_rent =  $decodeData[ 'micro_rent_hr' ];
    $micro_monthly =  $decodeData[ 'micro_rent_monthly' ];
    $minibus_rent =  $decodeData[ 'minibus_rent_hr' ];
    $minibus_monthly =  $decodeData[ 'minibus_rent_monthly' ];
    $bus_rent =  $decodeData[ 'bus_rent_hr' ];
    $bus_monthly =  $decodeData[ 'bus_rent_monthly' ];
    $truck_rent =  $decodeData[ 'truck_rent_hr' ];
    $truck_monthly =  $decodeData[ 'truck_rent_monthly' ];

    $query = "UPDATE parking_lot SET parking_name = '$p_name', user_msisdn = '$msisdn', number_of_blocks = '$blocks',  address = '$address', lat_v = '$lat_v', long_val = '$long_value',
     location_lat_value = '$location_lat', location_long_value = '$location_long', bike_rent_hr = '$bike_rent', bike_rent_monthly = '$bike_monthly', car_rent_hr ='$car_rent', car_rent_monthly = '$car_monthly',
      micro_rent_hr = '$micro_rent', micro_rent_monthly = '$micro_monthly', minibus_rent_hr = '$minibus_rent', minibus_rent_monthly = '$minibus_monthly' , 
    bus_rent_hr = '$bus_rent' , bus_rent_monthly = '$bus_monthly' , truck_rent_hr = '$truck_rent' ,truck_rent_monthly = '$truck_monthly' WHERE id = '$parking_id' ";

    $stmt = $conn->prepare( $query );
    $stmt->execute();
    $count = $stmt->rowCount();

    if ( $count> 0  ) 
 {
        $json_value = array( 'statusCode' => '200', 'description' => 'Parking Lot Information Updated successfully' );
        $content = json_encode( $json_value );
    } else {
        $json_value = array( 'statusCode' => '400', 'description' => 'Error Occured. Parking Lot Information Update Failed.' );
        $content = json_encode( $json_value );
    }

    print_r( $content );

} else {
    echo 'Something went wrong!';
    exit;
}

?>