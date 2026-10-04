<?php
require_once "../include/auth_guard.php";
requireRole(['staff', 'admin']);
require_once "../config/database.php";
require_once "../include/helpers.php";

function nextOrderStatus(string $currentStatus, string $statusAction): ?string {
    if ($statusAction === 'cancel') return 'cancelled';
    if ($statusAction !== 'advance') return null;
    return ['pending' => 'preparing', 'preparing' => 'ready', 'ready' => 'completed'][$currentStatus] ?? null;
}

// status transition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['action'])) {
    $orderId = (int)$_POST['order_id'];
    $action = $_POST['action'];
    $stmt = $conn->prepare("SELECT status FROM orders WHERE id=?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) {
        $next = nextOrderStatus($row['status'], $action);
        if ($next) {
            if ($next === 'completed') {
                $stmt = $conn->prepare("UPDATE orders SET status=?, payment_status='paid', claimed_at=NOW() WHERE id=?");
                $stmt->bind_param("si", $next, $orderId);
            } else {
                $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
                $stmt->bind_param("si", $next, $orderId);
            }
            $stmt->execute(); $stmt->close();
        }
    }
    header("Location: /ordering-system/staff/orders.php");
    exit();
}

// queue filter
$filter = trim($_GET['q'] ?? '');
$orderColumns = "id, order_code, guest_name, guest_phone, total, status, payment_status";
if ($filter !== '') {
    $searchPattern = "%$filter%";
    $stmt = $conn->prepare("SELECT $orderColumns FROM orders WHERE order_code LIKE ? OR guest_name LIKE ? OR guest_phone LIKE ? ORDER BY FIELD(status,'pending','preparing','ready','completed','cancelled'), created_at ASC LIMIT 100");
    $stmt->bind_param("sss", $searchPattern, $searchPattern, $searchPattern);
} else {
    $stmt = $conn->prepare("SELECT $orderColumns FROM orders ORDER BY FIELD(status,'pending','preparing','ready','completed','cancelled'), created_at ASC LIMIT 100");
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// line items (single query)
$linesByOrder = [];
if (!empty($orders)) {
    $ids = array_column($orders, 'id');
    $types = str_repeat('i', count($ids));
    $stmt = $conn->prepare("SELECT order_id, item_name, qty FROM order_items WHERE order_id IN (" . placeholders($ids) . ")");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $line) $linesByOrder[$line['order_id']][] = $line;
    $stmt->close();
}

require_once "../include/header.php";
require_once "../include/navbar.php";
?>
<main class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
    <div class="flex justify-between items-end flex-wrap gap-3">
        <div><p class="eyebrow">Operations</p><h1 class="mt-2 text-3xl sm:text-4xl font-display font-semibold tracking-tight">Order queue</h1><p class="mt-2 text-sm text-[#666666]"><?php echo count($orders); ?> orders, pending to completed</p></div>
        <form method="GET" class="flex gap-2 w-full sm:w-auto" role="search">
            <label class="sr-only" for="q">Filter orders</label>
            <input id="q" name="q" value="<?php echo esc($filter); ?>" placeholder="BC-XXXX or name" class="input sm:w-56">
            <button class="btn-dark shrink-0">Filter</button>
        </form>
    </div>
    <div class="mt-5 space-y-2.5">
        <?php foreach ($orders as $order): ?>
        <?php $lines = $linesByOrder[$order['id']] ?? []; ?>
        <div class="card p-4 sm:p-5 flex justify-between gap-5 flex-wrap sm:flex-nowrap">
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap"><span class="font-bold text-lg tracking-wider"><?php echo esc($order['order_code']); ?></span><span class="text-xs font-bold px-2.5 py-1 rounded-md border <?php echo statusPill($order['status']); ?>"><?php echo esc($order['status']); ?></span><span class="text-xs font-bold px-2.5 py-1 rounded-md border <?php echo statusPill($order['payment_status']); ?>"><?php echo esc($order['payment_status']); ?></span></div>
                <div class="text-sm text-[#666666] mt-1"><?php echo esc($order['guest_name']); ?>, <?php echo esc($order['guest_phone']); ?>, <span class="tabular-nums">₱<?php echo number_format($order['total'], 2); ?></span></div>
                <div class="text-sm mt-1.5 text-[#111111]"><?php foreach ($lines as $line) echo '<span class="chip mr-1.5 mb-1">' . esc($line['qty'] . '× ' . $line['item_name']) . '</span>'; ?></div>
            </div>
            <div class="flex gap-2 items-start shrink-0">
                <?php if (!in_array($order['status'], ['completed', 'cancelled'], true)): ?>
                <form method="POST"><input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>"><button name="action" value="advance" class="btn-primary !min-h-[40px] !px-4 text-sm"><i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>Advance</button></form>
                <form method="POST" onsubmit="return confirm('Cancel this order?')"><input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>"><button name="action" value="cancel" class="btn-ghost !min-h-[40px] !px-4 text-sm !text-red-700 !border-red-200"><i data-lucide="x" class="w-4 h-4" aria-hidden="true"></i>Cancel</button></form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($orders)): ?><div class="card p-10 text-center text-[#666666]"><div class="photo-block mx-auto max-w-[180px]" aria-hidden="true"><i data-lucide="package" class="w-7 h-7" aria-hidden="true"></i></div><p class="mt-4">No orders. Fresh queue, nice.</p></div><?php endif; ?>
    </div>
</main>
<?php require_once "../include/footer.php"; ?>
