<?php
require 'koneksi.php';
require 'fungsi.php';

$pesan = '';

$id = valid_id($_GET['id'] ?? $_POST['id'] ?? null);

if ($id === null) {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        http_response_code(403);
        exit('Token keamanan tidak valid. Silakan muat ulang halaman.');
    }

    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if ($konfirmasi === 'batal') {
        header('Location: daftar_tugas.php');
        exit;
    }

    if ($konfirmasi !== 'ya') {
        $pesan = 'Permintaan penghapusan tidak valid.';
    } else {
        $stmt = $conn->prepare(
            "DELETE FROM tugas WHERE id = ?"
        );
        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $stmt->close();
            header('Location: daftar_tugas.php');
            exit;
        }

        $pesan = 'Tugas gagal dihapus.';
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Tugas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f5f5f5;
        }

        .konten {
            max-width: 600px;
            padding: 20px;
            background: white;
            border: 1px solid #ddd;
        }

        button, .btn {
            display: inline-block;
            padding: 10px 14px;
            margin-top: 12px;
            border: none;
            text-decoration: none;
            cursor: pointer;
        }

        .hapus {
            background: #f9cbcb;
            color: white;
        }

        .batal {
            background: #ddd;
            color: black;
        }

        .pesan {
            color: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="konten">
        <h1>Konfirmasi Hapus Tugas</h1>

        <?php if ($pesan !== ''): ?>
            <p class="pesan"><?= e($pesan) ?></p>
        <?php endif; ?>

        <p>Apakah kamu yakin ingin menghapus tugas berikut?</p>

        <ul>
            <li><strong>Judul:</strong> <?= e($tugas['judul']) ?></li>
            <li><strong>Mata Kuliah:</strong> <?= e($tugas['nama_mata_kuliah']) ?></li>
            <li><strong>Instruksi:</strong> <?= e($tugas['instruksi']) ?></li>
            <li><strong>Tenggat:</strong> <?= e($tugas['tenggat']) ?></li>
            <li><strong>Status:</strong> <?= e($tugas['status']) ?></li>
        </ul>

        <form method="post">
            <input type="hidden" name="id" value="<?= (int) $id ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <button class="hapus" type="submit" name="konfirmasi" value="ya">
                Ya, Hapus
            </button>

            <button class="batal" type="submit" name="konfirmasi" value="batal">
                Batal
            </button>
        </form>

        <p><a href="daftar_tugas.php">Kembali ke Daftar Tugas</a></p>
    </div>
</body>
</html>
