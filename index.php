<?php
session_start();

// Jika sudah login, redirect ke dashboard masing-masing
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'mahasiswa') {
        header("Location: mahasiswa/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] === 'asisten') {
        header("Location: asisten/dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Selamat Datang di SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-500 to-cyan-400 min-h-screen flex items-center justify-center">

    <div class="bg-white p-10 rounded-xl shadow-xl text-center w-full max-w-lg">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">SIMPRAK</h1>
        <p class="text-gray-600 mb-6">Sistem Informasi Manajemen Praktikum<br>Program Studi Teknik Informatika</p>

        <div class="flex justify-center space-x-4">
            <a href="login.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg">
                Login
            </a>
            <a href="register.php" class="bg-green-500 hover:bg-green-600 text-white font-semibold px-6 py-2 rounded-lg">
                Register
            </a>
        </div>

        <footer class="mt-8 text-sm text-gray-400">
            &copy; <?php echo date('Y'); ?> SIMPRAK UMY
        </footer>
    </div>

</body>
</html>
