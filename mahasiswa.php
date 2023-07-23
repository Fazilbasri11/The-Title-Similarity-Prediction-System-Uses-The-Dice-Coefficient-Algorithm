<?php
session_start();
if (!isset($_SESSION['npm'])) {
    header('Location: login.php');
    exit();
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

// Ambil semua data pengajuan kecuali pengajuan milik mahasiswa yang sedang login
$stmtAllPengajuan = $pdo->prepare('SELECT pengajuan.*, users.npm, users.nama, users.semester, status.nama_status
                                  FROM pengajuan
                                  JOIN users ON pengajuan.id_users = users.id_users
                                  JOIN status ON pengajuan.id_status = status.id_status
                                  WHERE pengajuan.id_users != ?
                                  ORDER BY pengajuan.tanggal DESC');
$stmtAllPengajuan->execute([$id_users]);
$all_pengajuan = $stmtAllPengajuan->fetchAll(PDO::FETCH_ASSOC);

// Menghitung tingkat kemiripan judul menggunakan Dice Coefficient
function calculateDiceCoefficient($string1, $string2)
{
    $string1 = strtolower($string1);
    $string2 = strtolower($string2);

    $bigrams1 = getBigrams($string1);
    $bigrams2 = getBigrams($string2);

    $intersection = count(array_intersect($bigrams1, $bigrams2));
    $total = count($bigrams1) + count($bigrams2);

    return ($intersection * 2) / $total;
}

function getBigrams($string)
{
    $bigrams = [];
    $length = strlen($string);

    for ($i = 0; $i < $length - 1; $i++) {
        $bigrams[] = substr($string, $i, 2);
    }

    return $bigrams;
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
            <span class="navbar-brand">Beranda Admin</span>
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
                <th>Kemiripan</th>
                <th>Status</th>
            </tr>
            <?php
            // Display rows and their corresponding similarity values
            foreach ($pengajuan as $index => $row) :
            ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= $row['npm'] ?></td>
                <td><?= $row['nama'] ?></td>
                <td><?= $row['semester'] ?></td>
                <td><?= $row['tanggal'] ?></td>
                <td><?= $row['judul'] ?></td>
                <td>
                    <?php
                        // Calculate similarity and store the results in an array
                        $similarityArray = array();
                        foreach ($all_pengajuan as $row2) {
                            $similarity = calculateDiceCoefficient($row['judul'], $row2['judul']);
                            $similarityArray[] = $similarity;
                        }
                        foreach ($similarityArray as $similarityValue) {
                            echo number_format($similarityValue * 100, 2) . "%<br>";
                        }
                        ?>
                </td>
                <td><?= $row['nama_status'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>

</html>