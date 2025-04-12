<?php
include('db.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Handle new purchase
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $item = $_POST['item'];
    $qty = $_POST['quantity'];
    $total = $_POST['total'];
    $date_purchase = $_POST['date_purchased'];
    $payment = floatval($_POST['payment']);
    $balance = floatval($_POST['total']) - $payment;


    // Check if customer exists
    $stmt = $conn->prepare("SELECT id FROM customers WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $customer_id = $row['id'];
    } else {
        $unique_id = uniqid("CUS");
        $stmt = $conn->prepare("INSERT INTO customers (name, unique_id) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $unique_id);
        $stmt->execute();
        $customer_id = $conn->insert_id;
    }

    // Insert purchase (excluding date_debt and date_payment)
    $stmt = $conn->prepare("INSERT INTO purchases (customer_id, item_name, quantity, total_price, date_purchased, payments, remaining_balance) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isidddd", $customer_id, $item, $qty, $total, $date_purchase, $payment, $balance);
    $stmt->execute();
}

// Fetch customers and their balances
$customers = $conn->query("SELECT c.name, c.unique_id, SUM(p.remaining_balance) AS total_balance FROM customers c JOIN purchases p ON c.id = p.customer_id GROUP BY c.id");?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
</head>
<body class="bg-green-100 min-h-screen">

    <nav class="bg-green-800 text-white px-6 py-4 shadow">
        <div class="container mx-auto flex justify-between items-center">
            <div class="text-2xl font-bold">Utang Tracker</div>
            <div class="space-x-4">
                <a href="logout.php" class="mt-4 bg-green-700 text-white px-4 py-2 rounded hover:bg-green-900">Logout</a>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto mt-20">
        <form method="POST" class="bg-white p-6 rounded-lg shadow-md mb-10">
            <h2 class="text-xl font-bold text-green-700 mb-4">Add Customer Purchase</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-gray-700 font-medium mb-1">Customer Name</label>
                    <input type="text" name="name" id="name" required class="p-2 border rounded w-full focus:outline-none focus:ring-2 focus:ring-green-600">
                </div>

                <div>
                    <label for="item" class="block text-gray-700 font-medium mb-1">Item Name</label>
                    <input type="text" name="item" id="item" required class="p-2 border rounded w-full focus:outline-none focus:ring-2 focus:ring-green-600">
                </div>

                <div>
                    <label for="quantity" class="block text-gray-700 font-medium mb-1">Quantity</label>
                    <input type="number" name="quantity" id="quantity" required min="1" class="p-2 border rounded w-full focus:outline-none focus:ring-2 focus:ring-green-600">
                </div>

                <div>
                    <label for="date_purchase" class="block text-gray-700 font-medium mb-1">Date Purchased</label>
                    <input type="date" name="date_purchase" id="date_purchase" required class="p-2 border rounded w-full focus:outline-none focus:ring-2 focus:ring-green-600">
                </div>

                <div class="md:col-span-2">
                    <label for="total" class="block text-gray-700 font-medium mb-1">Total Price (₱)</label>
                    <input type="number" step="0.01" name="total" id="total" required min="0" class="p-2 border rounded w-full focus:outline-none focus:ring-2 focus:ring-green-600">
                </div>
                <div class="md:col-span-2">
                    <label for="payment" class="block text-gray-700 font-medium mb-1">Initial Payment (₱)</label>
                    <input type="number" step="0.01" name="payment" id="payment" required min="0" class="p-2 border rounded w-full focus:outline-none focus:ring-2 focus:ring-green-600">
                </div>

            </div>
            <button type="submit" class="mt-4 bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800">Save Purchase</button>
        </form>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold text-green-700 mb-4">Customer Balance Overview</h2>
            <table class="min-w-full table-auto border-collapse border border-green-300">
                <thead>
                    <tr class="bg-green-200 text-green-800">
                        <th class="border border-green-300 px-4 py-2">Name</th>
                        <th class="border border-green-300 px-4 py-2">Unique ID</th>
                        <th class="border border-green-300 px-4 py-2">Total Balance</th>
                        <th class="border border-green-300 px-4 py-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $customers->fetch_assoc()): ?>
                    <tr class="text-center">
                        <td class="border px-4 py-2"><?= htmlspecialchars($row['name']) ?></td>
                        <td class="border px-4 py-2"><?= htmlspecialchars($row['unique_id']) ?></td>
                        <td class="border px-4 py-2 text-red-600 font-semibold">₱<?= number_format($row['total_balance'] ?? 0, 2) ?>
                        </td>
                        <td class="border px-4 py-2">
                            <a href="history.php?uid=<?= urlencode($row['unique_id']) ?>" class="text-green-600 underline">View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
