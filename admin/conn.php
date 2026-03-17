<?php
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPassEnv = getenv('DB_PASS');
$dbPass = $dbPassEnv === false ? '' : $dbPassEnv;
$dbName = getenv('DB_NAME') ?: 'SSS';
$dbPort = (int)(getenv('DB_PORT') ?: 3306);

mysqli_report(MYSQLI_REPORT_OFF);

if (!function_exists('sss_connect_db')) {
    function sss_connect_db($host, $user, $pass, $name, $port) {
        $link = mysqli_init();
        if (!$link) {
            return false;
        }

        mysqli_options($link, MYSQLI_OPT_CONNECT_TIMEOUT, 2);

        if (!@mysqli_real_connect($link, $host, $user, $pass, $name, $port)) {
            mysqli_close($link);
            return false;
        }

        return $link;
    }
}

$hosts = array_values(array_unique([$dbHost, 'localhost', '127.0.0.1']));
$ports = [$dbPort];
if ($dbPort === 3306) {
    $ports[] = 3307;
}

$con = false;
foreach ($hosts as $host) {
    foreach ($ports as $port) {
        $con = sss_connect_db($host, $dbUser, $dbPass, $dbName, $port);
        if ($con) {
            break 2;
        }
    }
}

if (!$con) {
    http_response_code(500);
    exit('Database connection failed. Start MySQL in XAMPP and ensure database "' . $dbName . '" exists. Error: ' . mysqli_connect_error());
}

?>
