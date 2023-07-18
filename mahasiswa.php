<?php
session_start();
if (!isset($_SESSION['npm'])) {
    header('Location: login.php');
    exit(); // Tambahkan pernyataan exit() untuk menghentikan eksekusi skrip setelah pengalihan
}
include 'db_connect.php';
$pdo = pdo_connect_mysql();
$msg = '';

// Ambil data pengguna yang sedang login
$npm = $_SESSION['npm'];

$stmt = $pdo->prepare('SELECT * FROM users WHERE npm = ?');
$stmt->execute([$npm]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Mengambil nama dari pengguna yang sedang login
$nama = $user['nama'];

// Ambil data pengajuan untuk mahasiswa yang sedang login
$id_users = $_SESSION['id_users']; // Ambil id_users dari session
$stmt = $pdo->prepare('SELECT pengajuan.*, users.npm, users.nama, users.semester, status.nama_status
                      FROM pengajuan
                      JOIN users ON pengajuan.id_users = users.id_users
                      JOIN status ON pengajuan.id_status = status.id_status
                      WHERE pengajuan.id_users = ?
                      ORDER BY pengajuan.tanggal DESC');
$stmt->execute([$id_users]);
$pengajuan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses pengiriman form pengajuan
if (!empty($_POST)) {
    $judul = isset($_POST['judul']) ? $_POST['judul'] : '';

    if ($pengajuan) {
        // Jika pengguna telah mengajukan judul sebelumnya, lakukan update judul di database
        $id_pengajuan = $pengajuan[0]['id_pengajuan'];

        $stmt = $pdo->prepare('UPDATE pengajuan SET judul = ? WHERE id_pengajuan = ?');
        $stmt->execute([$judul, $id_pengajuan]);
    } else {
        // Jika pengguna belum pernah mengajukan judul, lakukan insert judul baru ke database
        $stmt = $pdo->prepare('INSERT INTO pengajuan (id_users, judul, id_status) VALUES (?, ?, 4)');
        $stmt->execute([$id_users, $judul]);
    }

    // Redirect to mahasiswa.php after submission
    header('Location: mahasiswa.php');
    exit();
} else {
    // Jika pengajuan sudah ada, tampilkan pesan
    $msg = 'Anda telah mengajukan judul sebelumnya.';

    // Jika tombol Save di klik, tampilkan konfirmasi
    if (isset($_POST['save'])) {
        $judul = isset($_POST['judul']) ? $_POST['judul'] : '';

        // Tampilkan konfirmasi dengan menggunakan JavaScript
        echo '<script>
                if (confirm("Apakah Anda yakin ingin mengajukan perubahan judul?")) {
                    document.getElementById("formUpdate").submit();
                }
                return false;
              </script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/style_mhs.css">
    <title>Pengajuan</title>
</head>

<body>
    <nav class="navbar">
        <div class="navbar-left">
            <span class="navbar-brand">Beranda Mahasiswa</span>
        </div>
        <div class="navbar-right">
            <span class="username">Halo, <?php echo $nama; ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>
    <div class="container">
        <div class="card">
            <h1>Pengajuan Judul Tugas Akhir</h1>
            <?php if (!$pengajuan) : ?>
            <form action="mahasiswa.php" method="POST">
                <div class="form-group">
                    <label for="judul">Judul Tugas Akhir</label>
                    <input type="text" id="judul" name="judul" required>
                </div>
                <button type="submit">Ajukan</button>
            </form>
            <?php else : ?>
            <form id="formUpdate" action="mahasiswa.php" method="POST">
                <div class="form-group">
                    <label for="judul">Judul Tugas Akhir</label>
                    <input type="text" id="judul" name="judul" required value="<?= $pengajuan[0]['judul'] ?>">
                </div>
                <button type="submit" name="save">Save</button>
            </form>
            <p><?= $msg ?></p>
            <?php endif; ?>
        </div>

        <h2>Daftar Pengajuan Judul TA</h2>
        <table>
            <tr>
                <th>No</th>
                <th>NPM</th>
                <th>Nama</th>
                <th>Semester</th>
                <th>Tanggal</th>
                <th>Judul</th>
                <th>Status</th>
            </tr>
            <?php foreach ($pengajuan as $index => $row) : ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= $row['npm'] ?></td>
                <td><?= $row['nama'] ?></td>
                <td><?= $row['semester'] ?></td>
                <td><?= $row['tanggal'] ?></td>
                <td><?= $row['judul'] ?></td>
                <td><?= $row['nama_status'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>

</html>
