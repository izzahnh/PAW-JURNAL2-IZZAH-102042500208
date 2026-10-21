<?php
require 'koneksi.php';

$pesan = '';

$kode = '';
$nama = '';
$dosen = '';
$semester = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = strtoupper(trim($_POST['kode'] ?? ''));
    $nama = trim($_POST['nama'] ?? '');
    $dosen = trim($_POST['dosen'] ?? '');
    $semester = filter_var(
        $_POST['semester'] ?? '',
        FILTER_VALIDATE_INT
    );

    if (
        !preg_match('/^[A-Z0-9-]{3,20}$/', $kode) ||
        strlen($nama) < 3 || strlen($nama) > 100 ||
        strlen($dosen) < 3 || strlen($dosen) > 100 ||
        $semester === false ||
        $semester < 1 || $semester > 14
    ) {
        $pesan = 'Periksa kembali isian. Semua data harus valid.';
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO mata_kuliah
            (kode, nama, dosen, semester)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssi",
            $kode,
            $nama,
            $dosen,
            $semester
        );

        try {
            if ($stmt->execute()) {
                $stmt->close();
                header('Location: daftar_mata_kuliah.php');
                exit;
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                $pesan = 'Kode mata kuliah sudah digunakan. Silakan gunakan kode lain.';
            } else {
                $pesan = 'Data gagal disimpan.';
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
    <title>Tambah Mata Kuliah</title>
</head>
<body>
    <h1>Tambah Mata Kuliah</h1>

    <?php if ($pesan !== ''): ?>
        <p><?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="kode">Kode Mata Kuliah</label><br>
        <input
            type="text"
            id="kode"
            name="kode"
            maxlength="20"
            value="<?= htmlspecialchars($kode, ENT_QUOTES, 'UTF-8') ?>"
            required
        ><br><br>

        <label for="nama">Nama Mata Kuliah</label><br>
        <input
            type="text"
            id="nama"
            name="nama"
            minlength="3"
            maxlength="100"
            value="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?>"
            required
        ><br><br>

        <label for="dosen">Nama Dosen</label><br>
        <input
            type="text"
            id="dosen"
            name="dosen"
            minlength="3"
            maxlength="100"
            value="<?= htmlspecialchars($dosen, ENT_QUOTES, 'UTF-8') ?>"
            required
        ><br><br>

        <label for="semester">Semester (1–14)</label><br>
        <input
            type="number"
            id="semester"
            name="semester"
            min="1"
            max="14"
            value="<?= htmlspecialchars((string)($semester === false ? '' : $semester), ENT_QUOTES, 'UTF-8') ?>"
            required
        ><br><br>

        <button type="submit">Simpan</button>
    </form>

    <p>
        <a href="daftar_mata_kuliah.php">Kembali ke Daftar</a>
    </p>
</body>
</html>
