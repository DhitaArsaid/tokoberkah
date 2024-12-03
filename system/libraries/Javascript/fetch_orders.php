<?php
// Sambungkan ke database
$servername = "localhost";
$username = "root"; // Ganti dengan username database Anda
$password = ""; // Ganti dengan password database Anda
$dbname = "tokoberkah"; // Ganti dengan nama database Anda

$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil rentang tanggal yang dipilih dari URL
$startDate = $_GET['startDate'];
$endDate = $_GET['endDate'];

// Buat dan jalankan query untuk mengambil pesanan
$sql = "SELECT * FROM orders WHERE order_date BETWEEN '$startDate' AND '$endDate'";
$result = $conn->query($sql);

// Siapkan array untuk menyimpan pesanan
$orders = array();

// Ambil data dari hasil query dan tambahkan ke array
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

// Tutup koneksi database
$conn->close();

// Kembalikan data dalam format JSON
header('Content-Type: application/json');
echo json_encode($orders);
