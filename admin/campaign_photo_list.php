<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

$dbfile = '/var/www/html/dbconn/dbconn-pdo.php';
include $dbfile;

$getConn = new DatabaseConfig();
$conn = $getConn->getConnection();

$conn->exec("set names utf8");

$data = file_get_contents('php://input');
$decodeData = json_decode($data, true);

$campaign_photo_list = array();

$query = "SELECT * FROM campaign";

$stmt = $conn->prepare($query);
$execute = $stmt->execute();
$count = $stmt->rowCount();

if ($count > 0) {

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $json_value = array(
            'campaign_id' => $row['c_id'],
            'title' => $row['title'],
            'url' => $row['url_info'],
            'logo' => $row['logo_url'],
            'details' => $row['campaign_details'],
            'created_time' => $row['created_at'],
        );
        array_push($campaign_photo_list, $json_value);
    }

    $json_value2 = array(
        'statusCode' => '200',
        'description' => 'Campaign List Found',
        'Campaign List' => $campaign_photo_list,
    );

    $cont = json_encode($json_value2);

} else {
    $json_value = array(
        'campaign_id' => 0,
        'title' => 'NA',
        'url' => 'NA',
        'logo' => 'NA',
        'details' => 'NA',
        'created_time' => 'NA',
    );
    array_push($campaign_photo_list, $json_value);

    $json_value2 = array(
        'statusCode' => '400',
        'description' => 'No Data Found for Campaign List.',
        'Campaign List' => $campaign_photo_list,
    );

    $cont = json_encode($json_value2);

}

print_r($cont);