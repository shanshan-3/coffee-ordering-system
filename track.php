<?php
require_once __DIR__ . '/include/auth_guard.php';
if (isStaff()) {
    http_response_code(403);
    require_once __DIR__ . '/include/header.php';
    require __DIR__ . '/include/forbidden.php';
    require_once __DIR__ . '/include/footer.php';
    exit();
}
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/include/helpers.php';
require_once __DIR__ . '/include/header.php';

$orderCode = trim($_GET['order'] ?? '');
$phoneRaw = trim($_GET['phone'] ?? '');
$order = null; $items = []; $error = '';
if ($orderCode !== '') {
    if (isLoggedIn() && !isStaff()) {
        $userId = (int)$_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT id, order_code, guest_name, total, status, payment_status FROM orders WHERE order_code=? AND user_id=?");
        $stmt->bind_param("si", $orderCode, $userId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
    if (!$order && $phoneRaw !== '') {
        $phone = normalizePhone($phoneRaw);
        if ($phone === false) $error = 'Invalid phone format.';
        else {
            $stmt = $conn->prepare("SELECT id, order_code, guest_name, total, status, payment_status FROM orders WHERE order_code=? AND (guest_phone=? OR guest_phone=?)");
            $stmt->bind_param("sss", $orderCode, $phone, $phoneRaw);
            $stmt->execute();
            $order = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$order) $error = 'Order not found. Check number + phone.';
        }
    } elseif (!$order && $phoneRaw === '') {
        $error = '';
    }
    if ($order) {
        $stmt = $conn->prepare("SELECT item_name, qty, unit_price FROM order_items WHERE order_id=?");
        $stmt->bind_param("i", $order['id']);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
require_once __DIR__ . '/include/navbar.php';
?>
<main class="max-w-xl mx-auto px-4 sm:px-6 py-10">
    <p class="eyebrow">Order status</p>
    <h1 class="mt-2 text-3xl sm:text-4xl font-display font-semibold tracking-tight">Track your pickup</h1>
    <p class="mt-2 text-[#666666] text-[15px]">Guests enter code + phone. Logged-in customers open own orders with code only.</p>
    <form method="GET" class="mt-6 card p-5 sm:p-6 space-y-4">
        <div>
            <label class="label" for="order">Order code</label>
            <input id="order" name="order" value="<?php echo esc($orderCode); ?>" placeholder="BC-XXXX" autocomplete="off" class="input mt-1 uppercase placeholder:normal-case">
        </div>
        <div>
            <label class="label" for="phone">Phone</label>
            <input id="phone" name="phone" value="<?php echo esc($phoneRaw); ?>" placeholder="09XXXXXXXXX" inputmode="tel" class="input mt-1">
        </div>
        <button class="btn-dark w-full"><i data-lucide="search" aria-hidden="true"></i>Check status</button>
        <?php if ($error): ?><p role="alert" class="text-red-700 text-sm flex gap-1.5"><i data-lucide="circle-alert" aria-hidden="true"></i><?php echo esc($error); ?></p><?php endif; ?>
    </form>
    <?php if ($order): ?>
    <?php
        $steps = ['pending' => 'Queued', 'preparing' => 'Brewing', 'ready' => 'Ready', 'completed' => 'Done'];
        $statusKeys = array_keys($steps);
        $currentStatus = strtolower($order['status']);
        $currentIndex = array_search($currentStatus, $statusKeys);
        $isCancelled = $currentStatus === 'cancelled';
    ?>
    <div class="mt-5 card p-5 sm:p-6">
        <div class="flex items-start justify-between gap-4 flex-wrap border-b border-[#E5E5E5] pb-4">
            <div><div class="text-2xl font-display font-bold tracking-widest"><?php echo esc($order['order_code']); ?></div>
            <div class="text-sm text-[#666666] mt-0.5"><?php echo esc($order['guest_name']); ?>, total <span class="tabular-nums">₱<?php echo number_format($order['total'], 2); ?></span></div></div>
            <div class="flex gap-1.5"><span class="text-xs font-bold px-2.5 py-1 rounded-md border <?php echo statusPill($order['status']); ?>"><?php echo esc($order['status']); ?></span><span class="text-xs font-bold px-2.5 py-1 rounded-md border <?php echo statusPill($order['payment_status']); ?>"><?php echo esc($order['payment_status']); ?></span></div>
        </div>
        <?php if (!$isCancelled && $currentIndex !== false): ?>
        <ol class="mt-5 grid grid-cols-4 gap-1" aria-label="Order progress">
            <?php foreach ($statusKeys as $index => $statusKey): $isDone = $index < $currentIndex; $isCurrent = $index === $currentIndex; ?>
            <li class="text-center">
                <div class="mx-auto w-9 h-9 rounded-md flex items-center justify-center border text-sm font-bold <?php echo $isDone || $isCurrent ? 'bg-[#111111] text-white border-[#111111]' : 'bg-[#F7F7F7] text-[#666666] border-[#E5E5E5]'; ?>"><?php echo $isDone ? '<i data-lucide="check" aria-hidden="true"></i>' : ($index + 1); ?></div>
                <div class="mt-1.5 text-[11px] sm:text-xs font-medium <?php echo $isCurrent ? 'text-[#111111]' : 'text-[#666666]'; ?>"><?php echo $steps[$statusKey]; ?></div>
            </li>
            <?php endforeach; ?>
        </ol>
        <div class="mt-3 h-1.5 rounded-md bg-[#F7F7F7] border border-[#E5E5E5] overflow-hidden"><div class="h-full bg-[#111111] transition-all" style="width:<?php echo (int)((($currentIndex + 1) / 4) * 100); ?>%"></div></div>
        <?php elseif ($isCancelled): ?>
        <p class="mt-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md px-3 py-2">This order was cancelled. Ask the counter if you need help.</p>
        <?php endif; ?>
        <ul class="mt-4 text-sm border-t border-[#E5E5E5] pt-3">
            <?php foreach ($items as $item): ?>
            <li class="flex justify-between gap-3 py-1.5 border-b border-[#E5E5E5] last:border-0"><span><?php echo (int)$item['qty']; ?>× <?php echo esc($item['item_name']); ?></span><span class="tabular-nums">₱<?php echo number_format($item['unit_price'] * $item['qty'], 2); ?></span></li>
            <?php endforeach; ?>
        </ul>
        <p class="mt-3 text-sm text-[#666666] flex gap-1.5"><i data-lucide="banknote" class="mt-0.5" aria-hidden="true"></i>Pay at counter on pickup.</p>
    </div>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/include/footer.php'; ?>
