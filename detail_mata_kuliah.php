<?php
require 'koneksi.php';
require 'fungsi.php';

$id = valid_id($_GET['id'] ?? null);

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

$result = $stmt->get_result();
$mata_kuliah = $result->fetch_assoc();
$stmt->close();

if (!$mata_kuliah) {
    http_response_code(404);
    exit('Data mata kuliah tidak ditemukan.');
}

header_html('Detail Mata Kuliah');
?>

<h2>Detail Mata Kuliah</h2>

<table>
    <tr>
        <th>Kode Mata Kuliah</th>
        <td><?= e($mata_kuliah['kode']) ?></td>
    </tr>
    <tr>
        <th>Nama Mata Kuliah</th>
        <td><?= e($mata_kuliah['nama']) ?></td>
    </tr>
    <tr>
        <th>Nama Dosen</th>
        <td><?= e($mata_kuliah['dosen']) ?></td>
    </tr>
    <tr>
        <th>Semester</th>
        <td><?= e($mata_kuliah['semester']) ?></td>
    </tr>
</table>

<p>
    <a class="btn" href="daftar_mata_kuliah.php">
        Kembali ke Daftar
    </a>

    <a class="btn primary"
       href="edit_mata_kuliah.php?id=<?= (int) $mata_kuliah['id'] ?>">
        Edit Mata Kuliah
    </a>
</p>

<?php footer_html(); ?>
