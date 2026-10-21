<?php
require 'koneksi.php';

$pesan = '';

$id = filter_var(
    $_GET['id'] ?? $_POST['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($id === false || $id === null) {
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
    exit('Data mata kuliah tidak ditemukan.');
}

$kode = $mata_kuliah['kode'];
$nama = $mata_kuliah['nama'];
$dosen = $mata_kuliah['dosen'];
$semester = $mata_kuliah['semester'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = strtoupper(trim($_POST['kode'] ?? ''));
    $nama = trim($_POST['nama'] ?? '');
    $dosen = trim($_POST['dosen'] ?? '');
    $semester_input = filter_var(
        $_POST['semester'] ?? '',
        FILTER_VALIDATE_INT
    );

    if (
        !preg_match('/^[A-Z0-9-]{3,20}$/', $kode)
    ) {
        $pesan = 'Kode harus 3–20 karakter berupa huruf kapital, angka, atau tanda hubung.';
    } elseif (mb_strlen($nama) < 3 || mb_strlen($nama) > 100) {
        $pesan = 'Nama mata kuliah harus 3–100 karakter.';
    } elseif (mb_strlen($dosen) < 3 || mb_strlen($dosen) > 100) {
        $pesan = 'Nama dosen harus 3–100 karakter.';
    } elseif (
        $semester_input === false ||
        $semester_input < 1 ||
        $semester_input > 14
    ) {
        $pesan = 'Semester harus berupa bilangan bulat 1–14.';
    } else {
        $stmt = $conn->prepare(
            "UPDATE mata_kuliah
             SET kode = ?, nama = ?, dosen = ?, semester = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "sssii",
            $kode,
            $nama,
            $dosen,
            $semester_input,
            $id
        );

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                header('Location: detail_mata_kuliah.php?id=' . $id);
                exit;
            }

            $pesan = 'Data tidak berubah atau tidak ada perubahan yang perlu disimpan.';
        } else {
            $pesan = $stmt->errno === 1062
                ? 'Kode mata kuliah sudah digunakan.'
                : 'Data gagal diperbarui.';
        }

        $stmt->close();

        $semester = $semester_input;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mata Kuliah</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f5f5f5;
        }

        form {
            max-width: 500px;
            padding: 20px;
            background: white;
            border: 1px solid #ddd;
        }

        label {
            display: block;
            margin-top: 12px;
        }

        input {
            box-sizing: border-box;
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }

        button, .btn {
            display: inline-block;
            padding: 9px 12px;
            margin-top: 15px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            background: #ddd;
            color: black;
        }

        button {
            background: #2563eb;
            color: white;
        }

        .pesan {
            margin-bottom: 15px;
            color: #b91c1c;
        }
    </style>
</head>
<body>
    <h1>Edit Mata Kuliah</h1>

    <?php if ($pesan !== ''): ?>
        <p class="pesan"><?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?= (int) $id ?>">

        <label for="kode">Kode Mata Kuliah</label>
        <input
            type="text"
            id="kode"
            name="kode"
            minlength="3"
            maxlength="20"
            value="<?= htmlspecialchars($kode, ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <label for="nama">Nama Mata Kuliah</label>
        <input
            type="text"
            id="nama"
            name="nama"
            minlength="3"
            maxlength="100"
            value="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <label for="dosen">Nama Dosen</label>
        <input
            type="text"
            id="dosen"
            name="dosen"
            minlength="3"
            maxlength="100"
            value="<?= htmlspecialchars($dosen, ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <label for="semester">Semester</label>
        <input
            type="number"
            id="semester"
            name="semester"
            min="1"
            max="14"
            value="<?= htmlspecialchars((string) $semester, ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <button type="submit">Simpan Perubahan</button>
        <a class="btn" href="daftar_mata_kuliah.php">Batal</a>
    </form>

    <p><a href="index.php">Kembali ke Beranda</a></p>
</body>
</html>
