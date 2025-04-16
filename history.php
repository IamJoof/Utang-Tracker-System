<?php
include('db.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['uid'])) {
    die("No customer selected.");
}

$unique_id = $_GET['uid'];

// Fetch customer info
$stmt = $conn->prepare("SELECT id, name FROM customers WHERE unique_id = ?");
$stmt->bind_param("s", $unique_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

if (!$customer) {
    die("Customer not found.");
}

$customer_id = $customer['id'];

// Update payment logic
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['purchase_id'], $_POST['new_payment'])) {
    $purchase_id = $_POST['purchase_id'];
    $new_payment = floatval($_POST['new_payment']);

    if ($new_payment <= 0) {
        // Optional: add user feedback later
        header("Location: history.php?uid=" . urlencode($unique_id));
        exit;
    }

    // Get current purchase
    $purchase = $conn->query("SELECT total_price, payments FROM purchases WHERE id = $purchase_id")->fetch_assoc();
    if ($purchase) {
        $updated_payment = $purchase['payments'] + $new_payment;
        $remaining = max(0, $purchase['total_price'] - $updated_payment); // avoid negative values

        // Update record
        $stmt = $conn->prepare("UPDATE purchases SET payments = ?, remaining_balance = ? WHERE id = ?");
        $stmt->bind_param("ddi", $updated_payment, $remaining, $purchase_id);
        $stmt->execute();
    }

    header("Location: history.php?uid=" . urlencode($unique_id));
    exit;
}

// Filter purchases based on search query
$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT * FROM purchases WHERE customer_id = $customer_id";
if (strlen($search) > 0) {
    $filters = [];
    $filters[] = "item_name LIKE '%$search%'";
    $filters[] = "quantity LIKE '%$search%'";
    $filters[] = "total_price LIKE '%$search%'";
    $filters[] = "remaining_balance LIKE '%$search%'";
    $filters[] = "date_purchased LIKE '%$search%'";

    $query .= " AND " . implode(" OR ", $filters);
}
$query .= " ORDER BY date_purchased DESC";

$purchases = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase History - <?= htmlspecialchars($customer['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
</head>
<body class="bg-green-100 min-h-screen p-10">
    <div class="max-w-5xl mx-auto">
        <a href="dashboard.php" class="mb-6 inline-block bg-green-700 text-white px-4 py-2 rounded hover:bg-green-800">&larr; Back to Dashboard</a>
        <h1 class="text-3xl font-bold mb-6 text-green-800">Purchase History for <?= htmlspecialchars($customer['name']) ?></h1>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <form method="GET" action="history.php">
                <input type="hidden" name="uid" value="<?= $unique_id ?>">
                <label for="search" class="block text-gray-700 font-medium mb-1">Search:</label>
                <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>" class="p-2 border rounded w-full focus:outline-none focus:ring-2 focus:ring-green-600" onchange="this.form.submit()">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 hidden">Search</button>
            </form>
        </div>

        <table class="min-w-full table-auto border-collapse border border-green-300">
            <thead>
                <tr class="bg-green-200 text-green-800">
                    <th class="border border-green-300 px-4 py-2">Item</th>
                    <th class="border border-green-300 px-4 py-2">Quantity</th>
                    <th class="border border-green-300 px-4 py-2">Total Price</th>
                    <th class="border border-green-300 px-4 py-2">Remaining</th>
                    <th class="border border-green-300 px-4 py-2">Date</th>
                    <th class="border border-green-300 px-4 py-2">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $purchases->fetch_assoc()): ?>
                <tr class="text-center">
                    <td class="border px-4 py-2"><?= htmlspecialchars($row['item_name']) ?></td>
                    <td class="border px-4 py-2"><?= $row['quantity'] ?></td>
                    <td class="border px-4 py-2">₱<?= number_format($row['total_price'], 2) ?></td>
                    <td class="border px-4 py-2 text-red-600 font-semibold">₱<?= number_format($row['remaining_balance'] ?? 0, 2) ?></td>
                    <td class="border px-4 py-2">
                        <?php
                        $datePurchased = $row['date_purchased'];
                        $dateTime = new DateTime($datePurchased);
                        $formattedDate = $dateTime->format('Y-m-d'); 
                        echo $formattedDate;
                        ?>
                    </td>
                    <td class="border px-4 py-2">
                        <button onclick="openModal(<?= $row['id'] ?>)" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Pay</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div id="paymentModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Enter Payment Amount</h2>
            <form method="POST">
                <input type="hidden" name="purchase_id" id="purchase_id">
                <input type="number" step="0.01" name="new_payment" required placeholder="Enter payment" class="w-full p-2 border rounded mb-4">
                <div class="flex justify-end">
                    <button type="button" onclick="closeModal()" class="bg-gray-300 px-4 py-2 rounded mr-2 hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(purchaseId) {
            document.getElementById('purchase_id').value = purchaseId;
            document.getElementById('paymentModal').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }
    </script>
</body>
</html>


