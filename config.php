<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'biak_upload');

// Koneksi ke Database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Jakarta');
$conn->query("SET time_zone = '+07:00'");

// Base URL
define('BASE_URL', 'http://localhost/');
?>