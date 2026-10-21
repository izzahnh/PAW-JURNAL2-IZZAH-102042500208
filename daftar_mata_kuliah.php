<?php
require 'koneksi.php';

$cari = trim($_GET['cari'] ?? '');

if ($cari !== '') {
    $kata_kunci = '%' . $cari . '%';

    $stmt = $conn->prepare(
        "SELECT id, kode, nama, dosen, semester
         FROM mata_kuliah
         WHERE kode LIKE ? OR nama LIKE ?
         ORDER BY nama"
    );

    $stmt->bind_param("ss", $kata_kunci, $kata_kunci);
} else {
    $stmt = $conn->prepare(
        "SELECT id, kode, nama, dosen, semester
         FROM mata_kuliah
         ORDER BY nama"
    );
}

$stmt->execute();
$hasil = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Mata Kuliah</title>
</head>
<body>
    <h1>Daftar Mata Kuliah</h1>

    <p><a href="index.php">Kembali ke Beranda</a></p>
    <p><a href="tambah_mata_kuliah.php">Tambah Mata Kuliah</a></p>

    <form method="GET" action="daftar_mata_kuliah.php">
        <label for="cari">Cari kode atau nama mata kuliah:</label>
        <input
            type="text"
            id="cari"
            name="cari"
            value="<?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8') ?>"
            placeholder="Masukkan kode atau nama"
        >
        <button type="submit">Cari</button>
        <a href="daftar_mata_kuliah.php">Reset</a>
    </form>

    <?php if ($hasil->num_rows > 0): ?>
        <table border="1" cellpadding="8">
            <tr>
                <th>Kode</th>
                <th>Nama Mata Kuliah</th>
                <th>Dosen</th>
                <th>Semester</th>
                <th>Aksi</th>
            </tr>

            <?php while ($baris = $hasil->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($baris['kode'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($baris['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($baris['dosen'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $baris['semester'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <a href="detail_mata_kuliah.php?id=<?= (int) $baris['id'] ?>">Detail</a>
                        |
                        <a href="edit_mata_kuliah.php?id=<?= (int) $baris['id'] ?>">Edit</a>
                        |
                        <a href="hapus_mata_kuliah.php?id=<?= (int) $baris['id'] ?>">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>
            Tidak ada mata kuliah yang cocok dengan pencarian
            "<?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8') ?>".
        </p>
        <p><a href="daftar_mata_kuliah.php">Tampilkan semua mata kuliah</a></p>
    <?php endif; ?>

</body>
</html>
<?php
$stmt->close();
?>
