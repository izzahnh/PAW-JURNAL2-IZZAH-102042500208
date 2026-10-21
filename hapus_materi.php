<?php
require 'koneksi.php';
require 'fungsi.php';

$pesan = '';

$id = valid_id($_GET['id'] ?? $_POST['id'] ?? null);

if ($id === null) {
    http_response_code(400);
    exit('ID materi tidak valid.');
}

$stmt = $conn->prepare(
    "SELECT materi.id, materi.judul, materi.isi,
            mata_kuliah.nama AS nama_mata_kuliah
     FROM materi
     INNER JOIN mata_kuliah
        ON materi.mata_kuliah_id = mata_kuliah.id
     WHERE materi.id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$materi = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$materi) {
    http_response_code(404);
    exit('Data materi tidak ditemukan.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        http_response_code(403);
        exit('Token keamanan tidak valid. Silakan muat ulang halaman.');
    }

    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if ($konfirmasi === 'batal') {
        header('Location: daftar_materi.php');
        exit;
    }

    if ($konfirmasi !== 'ya') {
        $pesan = 'Permintaan penghapusan tidak valid.';
    } else {
        $stmt = $conn->prepare(
            "DELETE FROM materi WHERE id = ?"
        );
        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $stmt->close();
            header('Location: daftar_materi.php');
            exit;
        }

        $pesan = 'Materi gagal dihapus.';
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Materi</title>
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
            background: #e6bbbb;
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
        <h1>Konfirmasi Hapus Materi</h1>

        <?php if ($pesan !== ''): ?>
            <p class="pesan"><?= e($pesan) ?></p>
        <?php endif; ?>

        <p>Apakah kamu yakin ingin menghapus materi berikut?</p>

        <ul>
            <li>
                <strong>Judul:</strong>
                <?= e($materi['judul']) ?>
            </li>
            <li>
                <strong>Mata Kuliah:</strong>
                <?= e($materi['nama_mata_kuliah']) ?>
            </li>
            <li>
                <strong>Isi:</strong>
                <?= e($materi['isi']) ?>
            </li>
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

        <p><a href="daftar_materi.php">Kembali ke Daftar Materi</a></p>
    </div>
</body>
</html>
