<?php
// Konfigurasi database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'pengajuan_judulta';

session_start();

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form login
    $npm = $_POST['npm']; // Mengambil nilai npm dari input form
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE npm = '$npm' AND password = '$password'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if ($row) {
        $_SESSION['npm'] = $npm;
        $_SESSION['id_users'] = $row['id_users']; // Menyimpan nilai id_users dalam session

        if ($row['level'] == 1) {
            header('Location: admin.php');
            exit(); // Tambahkan pernyataan exit() untuk menghentikan eksekusi skrip setelah pengalihan
        } elseif ($row['level'] == 0) {
            header('Location: mahasiswa.php');
            exit(); // Tambahkan pernyataan exit() untuk menghentikan eksekusi skrip setelah pengalihan
        }
    } else {
        echo 'NPM atau password salah';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>
    <div class="container">
        <div class="card">
            <h2>Silakan Masuk</h2>
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="npm">NPM:</label>
                    <input type="text" id="npm" name="npm" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <input type="submit" value="Masuk">
                </div>
            </form>
        </div>
    </div>
</body>

</html>