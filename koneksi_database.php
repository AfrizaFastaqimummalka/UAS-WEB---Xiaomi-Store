<?php
/* ================= DATABASE CONNECTION ================= */
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'xiaomi_store';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("❌ CONNECTION FAILED: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Fungsi untuk membersihkan input
function clean_input($data) {
    global $conn;
    return $conn->real_escape_string(trim($data));
}

// Fungsi format rupiah
function format_rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>