<?php
require 'koneksi.php';

$id = filter_var(
    $_GET['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($id === false || $id === null) {
    http_response_code(400);
    exit('ID tugas tidak valid.');
}

$stmt = $conn->prepare(
    "SELECT tugas.id, tugas.judul, tugas.instruksi,
            tugas.tenggat, tugas.status,
            mata_kuliah.nama AS nama_mata_kuliah
     FROM tugas
     INNER JOIN mata_kuliah
        ON tugas.mata_kuliah_id = mata_kuliah.id
     WHERE tugas.id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();
$tugas = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tugas) {
    http_response_code(404);
    exit('Data tugas tidak ditemukan.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tugas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f5f5f5;
        }

        .konten {
            max-width: 650px;
            padding: 20px;
            background: white;
            border: 1px solid #ddd;
        }

        .baris {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <h1>Detail Tugas</h1>

    <div class="konten">
        <div class="baris">
            <strong>Judul Tugas:</strong>
            <?= htmlspecialchars($tugas['judul'], ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="baris">
            <strong>Mata Kuliah:</strong>
            <?= htmlspecialchars($tugas['nama_mata_kuliah'], ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="baris">
            <strong>Instruksi:</strong>
            <p><?= nl2br(htmlspecialchars($tugas['instruksi'], ENT_QUOTES, 'UTF-8')) ?></p>
        </div>

        <div class="baris">
            <strong>Tanggal Tenggat:</strong>
            <?= htmlspecialchars($tugas['tenggat'], ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="baris">
            <strong>Status:</strong>
            <?= htmlspecialchars($tugas['status'], ENT_QUOTES, 'UTF-8') ?>
        </div>

        <p>
            <a href="edit_tugas.php?id=<?= (int) $tugas['id'] ?>">Edit Tugas</a>
        </p>

        <p>
            <a href="daftar_tugas.php">Kembali ke Daftar Tugas</a>
        </p>
    </div>
</body>
</html>
