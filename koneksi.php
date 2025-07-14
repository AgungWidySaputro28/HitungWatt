<?php
// Pengaturan Database
$host = "db";
$user = "AgungWidySaputro";
$pass = "Aws280803";
$db   = "db_listrik";

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}
?>