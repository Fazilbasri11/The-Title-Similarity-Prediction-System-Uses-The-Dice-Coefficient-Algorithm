<?php
include 'db_connect.php';

// Mengecek apakah ID pengguna telah diberikan melalui parameter URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Memeriksa apakah pengguna dengan ID yang diberikan ada dalam database
    $pdo = pdo_connect_mysql();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id_users = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Mengecek apakah pengguna dengan ID yang diberikan ditemukan
    if (!$user) {
        exit('Pengguna tidak ditemukan.');
    }
} else {
    exit('ID pengguna tidak diberikan.');
}

// Mengecek apakah form telah disubmit
if (isset($_POST['submit'])) {
    // Mengambil data dari form
    $npm = $_POST['npm'];
    $password = $_POST['password'];
    $nama = $_POST['nama'];
    $semester = $_POST['semester'];

    // Memperbarui data pengguna dalam tabel users
    $stmt = $pdo->prepare('UPDATE users SET npm = ?, password = ?, nama = ?, semester = ? WHERE id_users = ?');
    $stmt->execute([$npm, $password, $nama, $semester, $id]);

    // Redirect atau tampilkan pesan sukses
    if ($stmt) {
        header('Location: dataMhs.php');
        exit;
    } else {
        echo "Gagal memperbarui data pengguna.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Pengguna</title>
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
        <h2>Edit Pengguna</h2>
        <form method="POST" action="">
            <label for="npm">NPM:</label><br>
            <input type="text" id="npm" name="npm" value="<?php echo $user['npm']; ?>" required><br><br>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" value="<?php echo $user['password']; ?>" required><br><br>
            <label for="nama">Nama:</label><br>
            <input type="text" id="nama" name="nama" value="<?php echo $user['nama']; ?>" required><br><br>
            <label for="semester">Semester:</label><br>
            <input type="text" id="semester" name="semester" value="<?php echo $user['semester']; ?>" required><br><br>
            <input type="submit" name="submit" value="Simpan">
        </form>
    </div>
</body>

</html>