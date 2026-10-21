<?php
require 'koneksi.php';
require 'fungsi.php';

$pesan = '';

$id_raw = $_GET['id'] ?? $_POST['id'] ?? null;
$id = valid_id($id_raw);

if ($id === null) {
    http_response_code(400);
    exit('ID mata kuliah tidak valid.');
}

$stmt = $conn->prepare(
    "SELECT id, kode, nama, dosen, semester
     FROM mata_kuliah
     WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$hasil = $stmt->get_result();
$mata_kuliah = $hasil->fetch_assoc();
$stmt->close();

if (!$mata_kuliah) {
    http_response_code(404);
    exit('Data mata kuliah tidak ditemukan.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_valid()) {
        http_response_code(403);
        exit('Token keamanan tidak valid. Silakan muat ulang halaman.');
    }

    if (($_POST['konfirmasi'] ?? '') === 'batal') {
        header('Location: daftar_mata_kuliah.php');
        exit;
    }

    if (($_POST['konfirmasi'] ?? '') !== 'ya') {
        $pesan = 'Permintaan penghapusan tidak valid.';
    } else {

        $stmt = $conn->prepare(
            "DELETE FROM mata_kuliah WHERE id = ?"
        );

        $stmt->bind_param("i", $id);

        try {
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $stmt->close();
                    header('Location: daftar_mata_kuliah.php');
                    exit;
                }

                $pesan = 'Data tidak berhasil dihapus.';
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1451) {
                $pesan = 'Mata kuliah tidak dapat dihapus karena masih digunakan oleh materi atau tugas.';
            } else {
                $pesan = 'Data gagal dihapus.';
            }
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Hapus Mata Kuliah</title>

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
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .hapus {
            background: #e57373;
            color: white;
        }

        .batal {
            background: #ddd;
            color: black;
        }

        .pesan {
            color: #b42318;
            background: #fff0f0;
            padding: 12px;
            border: 1px solid #f5c2c2;
        }
    </style>
</head>

<body>
<div class="konten">

    <h1>Konfirmasi Hapus Mata Kuliah</h1>

    <?php if ($pesan !== ''): ?>
        <p class="pesan"><?= e($pesan) ?></p>
    <?php endif; ?>

    <p>Apakah kamu yakin ingin menghapus mata kuliah berikut?</p>

    <ul>
        <li><strong>Kode:</strong> <?= e($mata_kuliah['kode']) ?></li>
        <li><strong>Nama:</strong> <?= e($mata_kuliah['nama']) ?></li>
        <li><strong>Dosen:</strong> <?= e($mata_kuliah['dosen']) ?></li>
        <li><strong>Semester:</strong> <?= e($mata_kuliah['semester']) ?></li>
    </ul>

    <form method="post">
        <input
            type="hidden"
            name="id"
            value="<?= (int) $id ?>"
        >

        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrf_token()) ?>"
        >

        <button
            class="hapus"
            type="submit"
            name="konfirmasi"
            value="ya"
        >
            Ya, Hapus
        </button>

        <button
            class="batal"
            type="submit"
            name="konfirmasi"
            value="batal"
        >
            Batal
        </button>
    </form>

    <p>
        <a href="daftar_mata_kuliah.php">
            Kembali ke Daftar Mata Kuliah
        </a>
    </p>

</div>
</body>
</html>
