<?php
require 'koneksi.php';

$stmt = $conn->prepare(
    "SELECT tugas.id, tugas.judul, tugas.instruksi,
            tugas.tenggat, tugas.status,
            mata_kuliah.nama AS nama_mata_kuliah
     FROM tugas
     INNER JOIN mata_kuliah
        ON tugas.mata_kuliah_id = mata_kuliah.id
     ORDER BY tugas.tenggat ASC"
);

$stmt->execute();
$hasil = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f5f5f5;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: white;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #e5e7eb;
        }
    </style>
</head>
<body>
    <h1>Daftar Tugas</h1>

    <p><a href="index.php">Kembali ke Beranda</a></p>
    <p><a href="tambah_tugas.php">Tambah Tugas</a></p>

    <?php if ($hasil->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Judul Tugas</th>
                    <th>Mata Kuliah</th>
                    <th>Instruksi</th>
                    <th>Tenggat</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php while ($baris = $hasil->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($baris['judul'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($baris['nama_mata_kuliah'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($baris['instruksi'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($baris['tenggat'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($baris['status'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a href="detail_tugas.php?id=<?= (int) $baris['id'] ?>">Detail</a> |
                            <a href="edit_tugas.php?id=<?= (int) $baris['id'] ?>">Edit</a> |
                            <a href="hapus_tugas.php?id=<?= (int) $baris['id'] ?>">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Belum ada data tugas.</p>
    <?php endif; ?>
</body>
</html>
<?php
$stmt->close();
?>
