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
    exit('ID materi tidak valid.');
}

$stmt = $conn->prepare(
    "SELECT id, mata_kuliah_id, judul, isi, tautan
     FROM materi
     WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$materi = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$materi) {
    http_response_code(404);
    exit('Data materi tidak ditemukan.');
}

$mata_kuliah_id = $materi['mata_kuliah_id'];
$judul = $materi['judul'];
$isi = $materi['isi'];
$tautan = $materi['tautan'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mata_kuliah_id = filter_var(
        $_POST['mata_kuliah_id'] ?? '',
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
    $judul = trim($_POST['judul'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $tautan = trim($_POST['tautan'] ?? '');

    if ($mata_kuliah_id === false) {
        $pesan = 'Silakan pilih mata kuliah yang valid.';
    } elseif (mb_strlen($judul) < 3 || mb_strlen($judul) > 150) {
        $pesan = 'Judul harus 3–150 karakter.';
    } elseif ($isi === '') {
        $pesan = 'Isi materi wajib diisi.';
    } elseif (
        mb_strlen($tautan) > 255 ||
        !filter_var($tautan, FILTER_VALIDATE_URL) ||
        !in_array(parse_url($tautan, PHP_URL_SCHEME), ['http', 'https'], true)
    ) {
        $pesan = 'Tautan harus berupa URL valid dengan awalan http atau https.';
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
                "UPDATE materi
                 SET mata_kuliah_id = ?, judul = ?, isi = ?, tautan = ?
                 WHERE id = ?"
            );
            $stmt->bind_param(
                "isssi",
                $mata_kuliah_id,
                $judul,
                $isi,
                $tautan,
                $id
            );

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $stmt->close();
                    header('Location: detail_materi.php?id=' . $id);
                    exit;
                }

                $pesan = 'Data tidak berubah atau tidak ada perubahan yang perlu disimpan.';
            } else {
                $pesan = 'Materi gagal diperbarui.';
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
    <title>Edit Materi</title>
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
    <h1>Edit Materi</h1>

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

        <label for="judul">Judul Materi</label>
        <input type="text" id="judul" name="judul"
            minlength="3" maxlength="150"
            value="<?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?>"
            required>

        <label for="isi">Isi Materi</label>
        <textarea id="isi" name="isi" rows="5" required><?= htmlspecialchars($isi, ENT_QUOTES, 'UTF-8') ?></textarea>

        <label for="tautan">Tautan Materi</label>
        <input type="url" id="tautan" name="tautan"
            maxlength="255"
            value="<?= htmlspecialchars($tautan, ENT_QUOTES, 'UTF-8') ?>"
            required>

        <button type="submit">Simpan Perubahan</button>
        <a class="btn" href="daftar_materi.php">Batal</a>
    </form>

    <p><a href="index.php">Kembali ke Beranda</a></p>
</body>
</html>
<?php
$daftar_mk->free();
?>
