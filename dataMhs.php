<?php
include 'db_connect.php';

// Mengambil data pengguna dengan level=0
$pdo = pdo_connect_mysql();
$stmt = $pdo->query('SELECT * FROM users WHERE level = 0');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Data Pengguna</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .add-button {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Data Pengguna</h2>
        <a href="addUser.php" class="btn btn-primary add-button">Tambah</a>
        <table id="userTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>NPM</th>
                    <th>Nama</th>
                    <th>Semester</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : ?>
                    <tr>
                        <td><?php echo $user['npm']; ?></td>
                        <td><?php echo $user['nama']; ?></td>
                        <td><?php echo $user['semester']; ?></td>
                        <td>
                            <a href="delete_user.php?id=<?php echo $user['id_users']; ?>">Hapus</a>
                            <a href="edit_user.php?id=<?php echo $user['id_users']; ?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#userTable').DataTable();
        });
    </script>
</body>

</html>