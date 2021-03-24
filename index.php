<?php

const EARTH_RADIUS = 6371;
$pdo = new PDO('mysql: host=localhost; dbname=altocar_test; charset: utf8', 'root', 'password');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startPoint = $_POST['startPoint'];
    $endPoint = $_POST['endPoint'];

    $startPoint = explode(',', $startPoint);
    $endPoint = explode(',', $endPoint);
    $start['latitude'] = $startPoint[0];
    $start['longitude'] = $startPoint[1];
    $end['latitude'] = $endPoint[0];
    $end['longitude'] = $endPoint[1];

    $distance = calculateDistance($start['latitude'], $start['longitude'], $end['latitude'], $end['longitude']);

    savePoints($start, $end, $distance, $pdo);

    echo json_encode(getStatistics($pdo));
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['statistics'])) {
            echo json_encode(getStatistics($pdo));
        } elseif (isset($_GET['reset'])) {
            resetStatistics($pdo);
        } else {
            include 'html/main_page.html';
        }
    }
}

function calculateDistance($startLatitude, $startLongitude, $endLatitude, $endLongitude)
{
    $startLat = deg2rad($startLatitude);
    $startLong = deg2rad($startLongitude);
    $endLat = deg2rad($endLatitude);
    $endLong = deg2rad($endLongitude);

    $lonDelta = $endLong - $startLong;
    $a = pow(cos($endLat) * sin($lonDelta), 2) +
        pow(cos($startLat) * sin($endLat) - sin($startLat) * cos($endLat) * cos($lonDelta), 2);
    $b = sin($startLat) * sin($endLat) + cos($startLat) * cos($endLat) * cos($lonDelta);

    $angle = atan2(sqrt($a), $b);
    return $angle * EARTH_RADIUS;
}

function savePoints($start, $end, $distance, $pdo)
{
    $query = "insert into `route`
                set start_latitude=:start_latitude,
                    start_longitude=:start_longitude,
                    end_latitude=:end_latitude,
                    end_longitude=:end_longitude,
                    distance=:distance";
    $stmt = $pdo->prepare($query);
    $start['latitude'] = htmlspecialchars(strip_tags($start['latitude']));
    $start['longitude'] = htmlspecialchars(strip_tags($start['longitude']));
    $end['latitude'] = htmlspecialchars(strip_tags($end['latitude']));
    $end['longitude'] = htmlspecialchars(strip_tags($end['longitude']));
    $stmt->bindParam('start_latitude', $start['latitude']);
    $stmt->bindParam('start_longitude', $start['longitude']);
    $stmt->bindParam('end_latitude', $end['latitude']);
    $stmt->bindParam('end_longitude', $end['longitude']);
    $stmt->bindParam('distance', $distance);
    $stmt->execute();
}

function getStatistics($pdo)
{
    $query = "select (
           select count(id)
           from route
       ) as `routes_count`,
       (
           select avg(route.distance)
           from route
       ) as `average_distance`,
       (
           select count(distance)
           from route
           where distance < 2
       ) as `count_routes_up_to_2_km`,
       (
           select count(distance) from route where distance <= 5 and distance >= 2
       ) as `count_routes_from_2_to_5_km`,
       (
           select count(distance)
           from route
           where distance > 5
       ) as `count_routes_up_to_5_km`;";
    return $pdo->query($query)->fetch(PDO::FETCH_ASSOC);
}

function resetStatistics($pdo)
{
    $query = "truncate route;";
    $pdo->query($query);
}