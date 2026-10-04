<?php
require_once "../include/auth_guard.php";
require_once "../config/database.php";
require_once "../include/helpers.php";

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reorder_id']) && isLoggedIn() && !isStaff()) {
    $userId = (int)$_SESSION['user_id'];
    $reorderId = (int)$_POST['reorder_id'];
    $stmt = $conn->prepare("SELECT id FROM orders WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $reorderId, $userId);
    $stmt->execute();
    $owned = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($owned) {
        $stmt = $conn->prepare("SELECT menu_item_id, qty FROM order_items WHERE order_id=?");
        $stmt->bind_param("i", $reorderId);
        $stmt->execute();
        $lines = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $ids = array_values(array_unique(array_map(fn($l) => (int)$l['menu_item_id'], $lines)));
        $ids = array_values(array_filter($ids, fn($id) => $id > 0));
        if ($ids) {
            $types = str_repeat('i', count($ids));
            $stmt = $conn->prepare("SELECT id FROM menu_items WHERE id IN (" . placeholders($ids) . ") AND is_available=1");
            $stmt->bind_param($types, ...$ids);
            $stmt->execute();
            $available = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'id');
            $stmt->close();
            $allowed = array_flip(array_map('intval', $available));
            foreach ($lines as $line) {
                $mid = (int)$line['menu_item_id'];
                if (!isset($allowed[$mid])) continue;
                $qty = max(1, min(MAX_CART_QTY, (int)$line['qty']));
                $_SESSION['cart'][$mid] = min(MAX_CART_QTY, ($_SESSION['cart'][$mid] ?? 0) + $qty);
            }
        }
    }
    header("Location: /ordering-system/customer/cart.php");
    exit();
}

$myOrders = [];
$linesByOrder = [];
if (isLoggedIn() && !isStaff()) {
    $userId = (int)$_SESSION['user_id'];
    $orderStmt = $conn->prepare("SELECT id, order_code, status, total, created_at FROM orders WHERE user_id=? ORDER BY created_at DESC LIMIT 20");
    $orderStmt->bind_param("i", $userId);
    $orderStmt->execute();
    $myOrders = $orderStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $orderStmt->close();
    if ($myOrders) {
        $ids = array_column($myOrders, 'id');
        $types = str_repeat('i', count($ids));
        $stmt = $conn->prepare("SELECT order_id, item_name, qty FROM order_items WHERE order_id IN (" . placeholders($ids) . ")");
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $line) $linesByOrder[$line['order_id']][] = $line;
        $stmt->close();
    }
}

require_once "../include/header.php";
require_once "../include/navbar.php";
?>
<main class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
    <p class="eyebrow">Account</p>
    <h1 class="mt-2 text-3xl sm:text-4xl font-display font-semibold tracking-tight">My orders</h1>
    <?php if (isStaff()): ?>
        <div class="card mt-4 p-5 text-[#666666]">Staff/admin accounts cannot place orders. Use the <a class="underline font-medium text-[#111111]" href="/ordering-system/staff/orders.php">order queue</a> to fulfill customer orders.</div>
    <?php elseif (!isLoggedIn()): ?>
        <div class="card mt-4 p-5 text-[#666666]">You are ordering as guest. <a class="underline font-medium text-[#111111]" href="/ordering-system/customer/menu.php">Browse menu</a> or <a class="underline font-medium text-[#111111]" href="/ordering-system/track.php">track with BC-XXXX</a>.</div>
    <?php elseif (empty($myOrders)): ?>
        <div class="card mt-4 p-10 text-center"><div class="photo-block mx-auto max-w-[180px]" aria-hidden="true"><i data-lucide="receipt" class="w-7 h-7" aria-hidden="true"></i></div><p class="mt-4 text-[#666666]">No orders yet.</p><a class="btn-primary mt-4" href="/ordering-system/customer/menu.php">Order now</a></div>
    <?php else: ?>
        <div class="mt-6 space-y-3">
        <?php foreach ($myOrders as $order): ?>
        <?php $lines = $linesByOrder[$order['id']] ?? []; ?>
            <div class="card p-4 sm:p-5">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0"><div class="font-bold tracking-wider"><?php echo esc($order['order_code']); ?></div><div class="text-sm text-[#666666] mt-0.5"><span class="text-xs font-bold px-2 py-0.5 rounded-md border <?php echo statusPill($order['status']); ?>"><?php echo esc($order['status']); ?></span> <span class="tabular-nums">₱<?php echo number_format($order['total'], 2); ?></span> <span>· <?php echo esc(date('M j, Y g:i A', strtotime($order['created_at']))); ?></span></div></div>
                    <a class="btn-ghost !min-h-[40px] !px-4 text-sm shrink-0" href="/ordering-system/track.php?order=<?php echo urlencode($order['order_code']); ?>"><i data-lucide="arrow-right" class="w-4 h-4" aria-hidden="true"></i>View</a>
                </div>
                <?php if ($lines): ?><div class="text-sm mt-2 text-[#666666]"><?php foreach ($lines as $line) echo '<span class="chip mr-1.5 mb-1">' . (int)$line['qty'] . '× ' . esc($line['item_name']) . '</span>'; ?></div><?php endif; ?>
                <form method="POST" class="mt-3"><input type="hidden" name="reorder_id" value="<?php echo (int)$order['id']; ?>"><button class="btn-ghost !min-h-[40px] text-sm"><i data-lucide="rotate-cw" class="w-4 h-4" aria-hidden="true"></i>Reorder</button></form>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php require_once "../include/footer.php"; ?>
