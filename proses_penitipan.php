<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Konfigurasi koneksi database
$host = "localhost";
$username = "root"; // Ganti dengan username database Anda
$password = ""; // Ganti dengan password database Anda
$database = "sistem_parkir";

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Memeriksa apakah data dari form diterima
if (isset($_POST['nama'], $_POST['plat_nomor'], $_POST['jenis_kendaraan'], $_POST['merek_kendaraan'], $_POST['waktu_masuk'], $_POST['durasi'])) {
    // Mengambil data dari form
    $nama = $_POST['nama'];
    $plat_nomor = $_POST['plat_nomor'];
    $jenis_kendaraan = $_POST['jenis_kendaraan'];
    $merek_kendaraan = $_POST['merek_kendaraan'];
    $waktu_masuk = $_POST['waktu_masuk'];
    $durasi = $_POST['durasi'];

    // Menghitung biaya berdasarkan jenis kendaraan
    switch ($jenis_kendaraan) {
        case 'motor':
            $biaya_per_jam = 2000;
            break;
        case 'mobil':
            $biaya_per_jam = 3000;
            break;
        case 'truk':
            $biaya_per_jam = 5000;
            break;
        default:
            $biaya_per_jam = 0;
            break;
    }
    $biaya = $biaya_per_jam * $durasi;

    // Menyiapkan query SQL untuk kendaraan_parkir
    $sql_kendaraan = "INSERT INTO kendaraan_parkir (nama, plat_nomor, jenis_kendaraan, merek_kendaraan, waktu_masuk, durasi) 
                      VALUES (?, ?, ?, ?, ?, ?)";

    // Menyiapkan statement
    $stmt_kendaraan = $conn->prepare($sql_kendaraan);
    if ($stmt_kendaraan === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt_kendaraan->bind_param("sssssi", $nama, $plat_nomor, $jenis_kendaraan, $merek_kendaraan, $waktu_masuk, $durasi);

    // Menjalankan query
    if ($stmt_kendaraan->execute()) {
        // Mendapatkan ID kendaraan yang baru dimasukkan
        $kendaraan_id = $stmt_kendaraan->insert_id;

        // Menyiapkan query SQL untuk pembayaran
        $sql_pembayaran = "INSERT INTO pembayaran (kendaraan_id, biaya, waktu_bayar) 
                           VALUES (?, ?, NOW())";

        // Menyiapkan statement
        $stmt_pembayaran = $conn->prepare($sql_pembayaran);
        if ($stmt_pembayaran === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt_pembayaran->bind_param("ii", $kendaraan_id, $biaya);

        // Menjalankan query
        if ($stmt_pembayaran->execute()) {
            echo "Data parkir dan pembayaran berhasil dicatat. Biaya: Rp " . $biaya;
        } else {
            echo "Error: " . $stmt_pembayaran->error;
        }

        // Menutup statement pembayaran
        $stmt_pembayaran->close();
    } else {
        echo "Error: " . $stmt_kendaraan->error;
    }

    // Menutup statement kendaraan
    $stmt_kendaraan->close();
} else {
    echo "Data form tidak lengkap.";
}

$conn->close();
?>