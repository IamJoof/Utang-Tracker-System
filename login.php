<?php
session_start();

// Fixed admin credentials
$admin_username = "admin";
$admin_password = "admin123"; // You can change this securely

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid admin credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-100 flex items-center justify-center h-screen">

    <div class="bg-white p-10 rounded-lg shadow-lg w-full max-w-sm">
        <h1 class="text-2xl font-bold text-center text-green-700 mb-6">UTANG TRACKER - Login</h1>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-green-700 mb-1 font-medium">Username</label>
                <input type="text" name="username" required class="w-full px-4 py-2 border border-green-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400">
            </div>
            <div>
                <label class="block text-green-700 mb-1 font-medium">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 border border-green-300 rounded focus:outline-none focus:ring-2 focus:ring-green-400">
            </div>
            <div>
                <button type="submit" class="w-full bg-green-700 text-white py-2 rounded hover:bg-green-800 transition">
                    Login
                </button>
            </div>
        </form>
    </div>

</body>
</html>
