<?php
require 'koneksi.php';

$jumlah_mata_kuliah = $conn->query(
    "SELECT COUNT(*) AS total FROM mata_kuliah"
)->fetch_assoc()['total'];

$jumlah_materi = $conn->query(
    "SELECT COUNT(*) AS total FROM materi"
)->fetch_assoc()['total'];

$jumlah_tugas = $conn->query(
    "SELECT COUNT(*) AS total FROM tugas"
)->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda LMS</title>
</head>
<body>
    <h1>Learning Management System</h1>
    <p>Selamat datang di LMS Laboratorium EAD.</p>

    <h2>Ringkasan Data</h2>

    <p>Jumlah Mata Kuliah: <?= $jumlah_mata_kuliah ?></p>
    <p>Jumlah Materi: <?= $jumlah_materi ?></p>
    <p>Jumlah Tugas: <?= $jumlah_tugas ?></p>

    <h2>Menu Pengelolaan</h2>
    <ul>
        <li><a href="daftar_mata_kuliah.php">Mata Kuliah</a></li>
        <li><a href="daftar_materi.php">Materi</a></li>
        <li><a href="daftar_tugas.php">Tugas</a></li>
    </ul>
</body>
</html>
