<?php
declare(strict_types=1);

/* EduFind Admission - MySQL connection for XAMPP */

$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'edufind_admission';

// XAMPP commonly uses 3306; some installations use 3307.
$ports = [3306, 3307];

// Prevent mysqli from throwing a fatal exception while we test ports.
mysqli_report(MYSQLI_REPORT_OFF);

$conn = null;
$errors = [];

foreach ($ports as $port) {
    $test = mysqli_init();

    if ($test->real_connect($host, $username, $password, $database, $port)) {
        $conn = $test;
        break;
    }

    $errors[] = "Port {$port}: " . ($test->connect_error ?: 'connection refused');
    $test->close();
}

if ($conn === null) {
    http_response_code(500);
    exit(
        'Database connection failed. Please start MySQL in XAMPP and make sure the database '
        . '"edufind_admission" exists. Tried ports 3306 and 3307. '
        . htmlspecialchars(implode(' | ', $errors), ENT_QUOTES, 'UTF-8')
    );
}

$conn->set_charset('utf8mb4');
?>
