<?php
require 'koneksi.php';

$id = filter_var(
    $_GET['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($id === false || $id === null) {
    http_response_code(400);
    exit('ID materi tidak valid.');
}

$stmt = $conn->prepare(
    "SELECT materi.id, materi.judul, materi.isi, materi.tautan,
            mata_kuliah.nama AS nama_mata_kuliah
     FROM materi
     INNER JOIN mata_kuliah
        ON materi.mata_kuliah_id = mata_kuliah.id
     WHERE materi.id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();
$hasil = $stmt->get_result();
$materi = $hasil->fetch_assoc();
$stmt->close();

if (!$materi) {
    http_response_code(404);
    exit('Data materi tidak ditemukan.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Materi</title>
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
    <h1>Detail Materi</h1>

    <div class="konten">
        <div class="baris">
            <strong>Judul Materi:</strong>
            <?= htmlspecialchars($materi['judul'], ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="baris">
            <strong>Mata Kuliah:</strong>
            <?= htmlspecialchars($materi['nama_mata_kuliah'], ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="baris">
            <strong>Isi Materi:</strong>
            <p><?= nl2br(htmlspecialchars($materi['isi'], ENT_QUOTES, 'UTF-8')) ?></p>
        </div>

        <div class="baris">
            <strong>Tautan Materi:</strong>
            <a href="<?= htmlspecialchars($materi['tautan'], ENT_QUOTES, 'UTF-8') ?>"
               target="_blank"
               rel="noopener noreferrer">Buka Materi</a>
        </div>

        <p>
            <a href="edit_materi.php?id=<?= (int) $materi['id'] ?>">Edit Materi</a>
        </p>

        <p>
            <a href="daftar_materi.php">Kembali ke Daftar Materi</a>
        </p>
    </div>
</body>
</html>
