<?php
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPassEnv = getenv('DB_PASS');
$dbPass = $dbPassEnv === false ? '' : $dbPassEnv;
$dbName = getenv('DB_NAME') ?: 'SSS';
$dbPort = (int)(getenv('DB_PORT') ?: 3306);

mysqli_report(MYSQLI_REPORT_OFF);

$con = @mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, $dbPort);

if (!$con && $dbHost !== 'localhost') {
    $con = @mysqli_connect('localhost', $dbUser, $dbPass, $dbName, $dbPort);
}

if (!$con && $dbPort === 3306) {
    $con = @mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, 3307);
    if (!$con && $dbHost !== 'localhost') {
        $con = @mysqli_connect('localhost', $dbUser, $dbPass, $dbName, 3307);
    }
}

if (!$con) {
    http_response_code(500);
    exit('Database connection failed. Start MySQL in XAMPP and ensure database "' . $dbName . '" exists. Error: ' . mysqli_connect_error());
}

?>
