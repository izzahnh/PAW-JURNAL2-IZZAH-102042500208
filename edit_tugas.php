<?php
require 'koneksi.php';

$pesan = '';

$id = filter_var(
    $_GET['id'] ?? $_POST['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($id === false || $id === null) {
    http_response_code(400);
    exit('ID tugas tidak valid.');
}

$stmt = $conn->prepare(
    "SELECT id, mata_kuliah_id, judul, instruksi, tenggat, status
     FROM tugas
     WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$tugas = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tugas) {
    http_response_code(404);
    exit('Data tugas tidak ditemukan.');
}

$mata_kuliah_id = $tugas['mata_kuliah_id'];
$judul = $tugas['judul'];
$instruksi = $tugas['instruksi'];
$tenggat = $tugas['tenggat'];
$status = $tugas['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mata_kuliah_id = filter_var(
        $_POST['mata_kuliah_id'] ?? '',
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
    $judul = trim($_POST['judul'] ?? '');
    $instruksi = trim($_POST['instruksi'] ?? '');
    $tenggat = trim($_POST['tenggat'] ?? '');
    $status = $_POST['status'] ?? '';

    if ($mata_kuliah_id === false) {
        $pesan = 'Silakan pilih mata kuliah yang valid.';
    } elseif (mb_strlen($judul) < 3 || mb_strlen($judul) > 150) {
        $pesan = 'Judul tugas harus 3–150 karakter.';
    } elseif ($instruksi === '') {
        $pesan = 'Instruksi tugas wajib diisi.';
    } elseif (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tenggat) ||
        !checkdate(
            (int) substr($tenggat, 5, 2),
            (int) substr($tenggat, 8, 2),
            (int) substr($tenggat, 0, 4)
        )
    ) {
        $pesan = 'Tanggal tenggat tidak valid.';
    } elseif (!in_array($status, ['Aktif', 'Draf', 'Selesai'], true)) {
        $pesan = 'Status tugas tidak valid.';
    } else {
        $cek = $conn->prepare(
            "SELECT id FROM mata_kuliah WHERE id = ?"
        );
        $cek->bind_param("i", $mata_kuliah_id);
        $cek->execute();
        $ada = $cek->get_result()->num_rows > 0;
        $cek->close();

        if (!$ada) {
            $pesan = 'Mata kuliah tidak ditemukan.';
        } else {
            $stmt = $conn->prepare(
                "UPDATE tugas
                 SET mata_kuliah_id = ?, judul = ?, instruksi = ?,
                     tenggat = ?, status = ?
                 WHERE id = ?"
            );
            $stmt->bind_param(
                "issssi",
                $mata_kuliah_id,
                $judul,
                $instruksi,
                $tenggat,
                $status,
                $id
            );

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $stmt->close();
                    header('Location: detail_tugas.php?id=' . $id);
                    exit;
                }

                $pesan = 'Data tidak berubah atau tidak ada perubahan yang perlu disimpan.';
            } else {
                $pesan = 'Tugas gagal diperbarui.';
            }

            $stmt->close();
        }
    }
}

$daftar_mk = $conn->query(
    "SELECT id, nama FROM mata_kuliah ORDER BY nama"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f5f5f5;
        }

        form {
            max-width: 550px;
            padding: 20px;
            background: white;
            border: 1px solid #ddd;
        }

        label {
            display: block;
            margin-top: 12px;
        }

        input, textarea, select {
            box-sizing: border-box;
            width: 100%;
            padding: 9px;
            margin-top: 5px;
        }

        button, .btn {
            display: inline-block;
            padding: 10px 14px;
            margin-top: 15px;
            border: none;
            text-decoration: none;
            cursor: pointer;
        }

        button {
            background: #2563eb;
            color: white;
        }

        .pesan {
            color: #b91c1c;
        }
    </style>
</head>
<body>
    <h1>Edit Tugas</h1>

    <?php if ($pesan !== ''): ?>
        <p class="pesan"><?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?= (int) $id ?>">

        <label for="mata_kuliah_id">Mata Kuliah</label>
        <select id="mata_kuliah_id" name="mata_kuliah_id" required>
            <option value="">-- Pilih Mata Kuliah --</option>
            <?php while ($mk = $daftar_mk->fetch_assoc()): ?>
                <option value="<?= (int) $mk['id'] ?>"
                    <?= (string) $mata_kuliah_id === (string) $mk['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($mk['nama'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="judul">Judul Tugas</label>
        <input type="text" id="judul" name="judul"
            minlength="3" maxlength="150"
            value="<?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?>"
            required>

        <label for="instruksi">Instruksi Tugas</label>
        <textarea id="instruksi" name="instruksi" rows="5" required><?= htmlspecialchars($instruksi, ENT_QUOTES, 'UTF-8') ?></textarea>

        <label for="tenggat">Tanggal Tenggat</label>
        <input type="date" id="tenggat" name="tenggat"
            value="<?= htmlspecialchars($tenggat, ENT_QUOTES, 'UTF-8') ?>"
            required>

        <label for="status">Status Tugas</label>
        <select id="status" name="status" required>
            <option value="Aktif" <?= $status === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="Draf" <?= $status === 'Draf' ? 'selected' : '' ?>>Draf</option>
            <option value="Selesai" <?= $status === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
        </select>

        <button type="submit">Simpan Perubahan</button>
        <a class="btn" href="daftar_tugas.php">Batal</a>
    </form>

    <p><a href="index.php">Kembali ke Beranda</a></p>
</body>
</html>
<?php
$daftar_mk->free();
?>
