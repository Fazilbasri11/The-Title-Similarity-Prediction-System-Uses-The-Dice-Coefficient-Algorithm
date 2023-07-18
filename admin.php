<?php
session_start();

if (!isset($_SESSION['npm'])) {
    header('Location: login.php');
    exit();
}

include 'db_connect.php';

$pdo = pdo_connect_mysql();

// Ambil data pengguna yang sedang login
$npm = $_SESSION['npm'];

$stmt = $pdo->prepare('SELECT * FROM users WHERE npm = ?');
$stmt->execute([$npm]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Mengambil nama dari pengguna yang sedang login
$nama = $user['nama'];

// Ambil data pengajuan dari tabel pengajuan dengan JOIN ke tabel users dan status
$stmt = $pdo->prepare('SELECT pengajuan.*, users.npm, users.nama, users.semester, status.nama_status
                      FROM pengajuan
                      JOIN users ON pengajuan.id_users = users.id_users
                      JOIN status ON pengajuan.id_status = status.id_status
                      ORDER BY pengajuan.tanggal DESC');
$stmt->execute();
$pengajuan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar status dari tabel status
$stmtStatus = $pdo->prepare('SELECT * FROM status');
$stmtStatus->execute();
$statuses = $stmtStatus->fetchAll(PDO::FETCH_ASSOC);

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

// Proses perubahan status
if (isset($_GET['pengajuan_id']) && isset($_GET['status_id'])) {
    $pengajuan_id = $_GET['pengajuan_id'];
    $status_id = $_GET['status_id'];

    // Update status pengajuan
    $stmtUpdate = $pdo->prepare('UPDATE pengajuan SET id_status = ? WHERE id_pengajuan = ?');
    $stmtUpdate->execute([$status_id, $pengajuan_id]);

    // Redirect back to the page
    header('Location: admin.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/style_admin.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <title>Laporan Pengajuan</title>
</head>
<style>
    body {
        background-color: #C4D7B2;
        font-family: Arial, sans-serif;
    }
</style>

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
        <h2>Daftar Pengajuan Judul TA</h2>
        <table id="pengajuanTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Judul</th>
                    <th>% Kemiripan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pengajuan as $index => $row) : ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo $row['nama']; ?></td>
                        <td><?php echo $row['judul']; ?></td>
                        <td>
                            <?php
                            foreach ($pengajuan as $index2 => $row2) {
                                if ($index !== $index2) {
                                    $similarity = calculateDiceCoefficient($row['judul'], $row2['judul']);
                                    echo number_format($similarity * 100, 2) . "%<br>";
                                }
                            }
                            ?>
                        </td>
                        <td>
                            <span id="status_<?php echo $row['id_pengajuan']; ?>">
                                <?php echo $row['nama_status']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton_<?php echo $row['id_pengajuan']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                    Ubah Status
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton_<?php echo $row['id_pengajuan']; ?>">
                                    <?php foreach ($statuses as $status) : ?>
                                        <?php if ($status['id_status'] != $row['id_status']) : ?>
                                            <li>
                                                <a class="dropdown-item" href="admin.php?pengajuan_id=<?php echo $row['id_pengajuan']; ?>&status_id=<?php echo $status['id_status']; ?>"><?php echo $status['nama_status']; ?></a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#pengajuanTable').DataTable();
        });
    </script>
</body>

</html>
