<?php
require 'koneksi.php';

$stmt = $conn->prepare(
    "SELECT materi.id, materi.judul, materi.isi, materi.tautan,
            mata_kuliah.nama AS nama_mata_kuliah
     FROM materi
     INNER JOIN mata_kuliah
        ON materi.mata_kuliah_id = mata_kuliah.id
     ORDER BY materi.id DESC"
);

$stmt->execute();
$hasil = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Materi</title>
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

        a {
            color: #2563eb;
        }
    </style>
</head>
<body>
    <h1>Daftar Materi</h1>

    <p><a href="index.php">Kembali ke Beranda</a></p>
    <p><a href="tambah_materi.php">Tambah Materi</a></p>

    <?php if ($hasil->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Judul Materi</th>
                    <th>Mata Kuliah</th>
                    <th>Isi Materi</th>
                    <th>Tautan</th>
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
                        <td><?= htmlspecialchars($baris['isi'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a href="<?= htmlspecialchars($baris['tautan'], ENT_QUOTES, 'UTF-8') ?>"
                               target="_blank"
                               rel="noopener noreferrer">Buka Materi</a>
                        </td>
                        <td>
                            <a href="detail_materi.php?id=<?= (int) $baris['id'] ?>">Detail</a> |
                            <a href="edit_materi.php?id=<?= (int) $baris['id'] ?>">Edit</a> |
                            <a href="hapus_materi.php?id=<?= (int) $baris['id'] ?>">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Belum ada data materi.</p>
    <?php endif; ?>
</body>
</html>
<?php
$stmt->close();
?>
