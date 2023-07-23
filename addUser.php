<?php
include 'db_connect.php';

// Mengecek apakah form telah disubmit
if (isset($_POST['submit'])) {
    // Mengambil data dari form
    $npm = $_POST['npm'];
    $password = $_POST['password'];
    $nama = $_POST['nama'];
    $semester = $_POST['semester'];

    // Memasukkan data ke dalam tabel users
    $pdo = pdo_connect_mysql();
    $stmt = $pdo->prepare('INSERT INTO users (npm, password, nama, semester, level) VALUES (?, ?, ?, ?, 0)');
    $stmt->execute([$npm, $password, $nama, $semester]);

    // Redirect atau tampilkan pesan sukses
    if ($stmt) {
        header('Location: dataMhs.php');
        exit;
    } else {
        echo "Gagal menambahkan data pengguna.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Tambah Pengguna</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        h2 {
            text-align: center;
        }

        form {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
        }

        input[type="submit"] {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Tambah Pengguna</h2>
        <form method="POST" action="">
            <label for="npm">NPM:</label><br>
            <input type="text" id="npm" name="npm" required><br><br>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>
            <label for="nama">Nama:</label><br>
            <input type="text" id="nama" name="nama" required><br><br>
            <label for="semester">Semester:</label><br>
            <input type="text" id="semester" name="semester" required><br><br>
            <input type="submit" name="submit" value="Tambah">
        </form>
    </div>
</body>

</html>