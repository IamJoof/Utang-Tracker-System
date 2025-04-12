<?php
// Start session
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utang Tracker - Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans">
    <nav class="bg-green-800 text-white px-6 py-4 shadow">
        <div class="container mx-auto flex justify-between items-center">
            <div class="text-2xl font-bold">Utang Tracker</div>
            <div class="space-x-4">
                <a href="index.php" class="hover:underline">Home</a>
                <a href="#" class="hover:underline">Customer List</a>
                <a href="#" class="hover:underline">Transactions</a>
                <a href="login.php" class="hover:underline">Login</a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-6 py-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <!-- Logo Section -->
            <div class="flex justify-center">
                <div class="bg-white rounded-xl shadow p-10 flex flex-col justify-center items-center w-full max-w-sm">
                    <div class="bg-green-700 w-32 h-32 rounded-md flex items-center justify-center text-white font-bold text-lg mb-4">
                        LOGO
                    </div>
                    <div class="text-gray-700 font-semibold">Location</div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="flex flex-col gap-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-center text-xl font-semibold text-green-800 mb-4">Search Transaction</div>
                    <form method="GET" class="space-y-4">
                        <input type="text" name="customer" placeholder="Enter Customer Name" class="w-full border-gray-500 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-600">
                        <button type="submit" class="w-full bg-green-800 hover:bg-green-900 text-white py-2 px-4 rounded">Search</button>
                    </form>
                </div>

                <?php
                if (isset($_GET['customer']) && !empty($_GET['customer'])) {
                    include 'db.php'; // Make sure this connects to your DB
                    $name = mysqli_real_escape_string($conn, $_GET['customer']);

                    $query = "SELECT purchases.*, customers.name AS customer_name FROM purchases INNER JOIN customers ON purchases.customer_id = customers.id WHERE customers.name LIKE '%$name%' ORDER BY purchases.date_purchased DESC";
                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) > 0) {
                        echo "<div class='bg-white p-4 rounded-lg shadow'>";
                        echo "<h2 class='text-lg font-semibold text-green-800 mb-3'>Transaction History</h2>";
                        echo "<div class='space-y-2'>";
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<div class='border p-3 rounded bg-gray-50'>";
                            echo "<p><strong>Name:</strong> " . htmlspecialchars($row['customer_name']) . "</p>";
                            echo "<p><strong>Item:</strong> " . htmlspecialchars($row['item_name']) . "</p>";
                            echo "<p><strong>Date Purchased:</strong> " . htmlspecialchars($row['date_purchased']) . "</p>";
                            echo "<p><strong>Debt Date:</strong> " . htmlspecialchars($row['date_debt']) . "</p>";
                            echo "<p><strong>Payment Date:</strong> " . htmlspecialchars($row['date_payment']) . "</p>";
                            echo "<p><strong>Quantity:</strong> " . htmlspecialchars($row['quantity']) . "</p>";
                            echo "<p><strong>Total Price:</strong> ₱" . htmlspecialchars($row['total_price']) . "</p>";
                            echo "<p><strong>Remaining Balance:</strong> ₱" . htmlspecialchars($row['remaining_balance']) . "</p>";
                            echo "</div>";
                        }
                        echo "</div></div>";
                    } else {
                        echo "<div class='bg-white p-4 rounded-lg shadow text-center text-gray-500'>No transactions found.</div>";
                    }
                }
                ?>

            </div>
        </div>
    </main>

</body>

</html>
