<?php
require_once "../include/auth_guard.php";
require_once "../config/database.php";
require_once "../include/helpers.php";

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
$error = '';
$canOrder = !isStaff();
if (!$canOrder) {
    $_SESSION['cart'] = [];
    $error = 'Staff/admin accounts cannot place orders. Use a customer account or order as guest.';
}

// cart actions
if ($canOrder && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    foreach (($_POST['qty'] ?? []) as $id => $qty) {
        $id = (int)$id; $qty = (int)$qty;
        if ($qty <= 0) unset($_SESSION['cart'][$id]);
        else $_SESSION['cart'][$id] = min(MAX_CART_QTY, $qty);
    }
    header("Location: /ordering-system/customer/cart.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear'])) {
    $_SESSION['cart'] = [];
    header("Location: /ordering-system/customer/cart.php");
    exit();
}

// checkout
if ($canOrder && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $isCustomerCheckout = isLoggedIn() && !isStaff();
    $fallbackName = $_SESSION['user_name'] ?? '';
    $guestName = trim($_POST['guest_name'] ?? $fallbackName);
    $guestPhoneRaw = trim($_POST['guest_phone'] ?? '');
    $guestPhone = normalizePhone($guestPhoneRaw);
    if (empty($_SESSION['cart'])) {
        $error = 'Cart is empty.';
    } elseif ($guestName === '' || strlen($guestName) > 50) {
        $error = 'Please enter your name.';
    } elseif ($guestPhone === false) {
        $error = 'Please enter a valid PH phone (09XXXXXXXXX or +639XXXXXXXXX).';
    } else {
        $ids = array_keys($_SESSION['cart']);
        $types = str_repeat('i', count($ids));
        $stmt = $conn->prepare("SELECT id, name, price FROM menu_items WHERE id IN (" . placeholders($ids) . ") AND is_available=1");
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $menuById = [];
        foreach ($rows as $row) $menuById[$row['id']] = $row;
        $lines = [];
        $total = 0;
        foreach ($_SESSION['cart'] as $id => $qty) {
            $qty = max(1, min(MAX_CART_QTY, (int)$qty));
            if (!isset($menuById[$id])) { $error = 'An item in your cart is no longer available. Cart updated.'; unset($_SESSION['cart'][$id]); break; }
            $lines[] = ['id' => $id, 'name' => $menuById[$id]['name'], 'price' => $menuById[$id]['price'], 'qty' => $qty];
            $total += $menuById[$id]['price'] * $qty;
        }
        if ($error === '' && empty($lines)) $error = 'Cart is empty.';
        if ($error === '') {
            $conn->begin_transaction();
            try {
                $orderCode = generateOrderCode($conn);
                $userId = $isCustomerCheckout ? (int)$_SESSION['user_id'] : null;
                $isGuest = $isCustomerCheckout ? 0 : 1;
                $stmt = $conn->prepare("INSERT INTO orders (order_code, user_id, is_guest, guest_name, guest_phone, total, status, payment_status) VALUES (?, ?, ?, ?, ?, ?, 'pending', 'unpaid')");
                $stmt->bind_param("siissd", $orderCode, $userId, $isGuest, $guestName, $guestPhone, $total);
                $stmt->execute();
                $orderId = $stmt->insert_id;
                $stmt->close();
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, item_name, qty, unit_price) VALUES (?, ?, ?, ?, ?)");
                foreach ($lines as $line) {
                    $stmt->bind_param("iisid", $orderId, $line['id'], $line['name'], $line['qty'], $line['price']);
                    $stmt->execute();
                }
                $stmt->close();
                $conn->commit();
                $_SESSION['cart'] = [];
                $_SESSION['last_order'] = $orderCode;
                header("Location: /ordering-system/customer/success.php?order=" . urlencode($orderCode));
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $error = 'Could not place order. Please try again.';
            }
        }
    }
}

// cart display data
$cartItems = [];
$cartTotal = 0;
if ($canOrder && !empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $types = str_repeat('i', count($ids));
    $stmt = $conn->prepare("SELECT id, name, price FROM menu_items WHERE id IN (" . placeholders($ids) . ")");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    foreach ($cartItems as $item) $cartTotal += $item['price'] * ($_SESSION['cart'][$item['id']] ?? 0);
}

require_once "../include/header.php";
require_once "../include/navbar.php";
?>
<main class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
    <p class="eyebrow">Checkout</p>
    <h1 class="mt-2 text-3xl sm:text-4xl font-display font-semibold tracking-tight">Your cart</h1>
    <p class="mt-2 text-[#666666] text-[15px]">Review your drinks, then add pickup details.</p>
    <?php if ($error): ?><div role="alert" class="alert mt-5 bg-red-50 border-red-200 text-red-800 flex gap-2"><i data-lucide="circle-alert" class="text-lg shrink-0" aria-hidden="true"></i><span><?php echo esc($error); ?></span></div><?php endif; ?>
    <?php if (!$canOrder): ?>
        <div class="card mt-6 p-6 text-[#666666]">Ordering is for guests and customers only. <a class="underline font-medium text-[#111111]" href="/ordering-system/staff/orders.php">Go to staff queue</a></div>
    <?php elseif (empty($cartItems)): ?>
        <div class="card mt-6 p-10 text-center">
            <div class="photo-block mx-auto max-w-[180px]" aria-hidden="true"><i data-lucide="shopping-cart" class="w-7 h-7" aria-hidden="true"></i></div>
            <p class="mt-4 font-medium">Cart is empty.</p>
            <a class="btn-primary mt-4" href="/ordering-system/customer/menu.php">Back to menu</a>
        </div>
    <?php else: ?>
        <form method="POST" class="mt-7 card p-4 sm:p-6">
            <?php foreach ($cartItems as $item): $qty = $_SESSION['cart'][$item['id']]; ?>
            <div class="flex items-center justify-between gap-3 py-4 border-b border-[#E5E5E5] last:border-0">
                <div class="min-w-0"><div class="font-medium truncate"><?php echo esc($item['name']); ?></div><div class="text-sm text-[#666666]">₱<?php echo number_format($item['price'], 2); ?> each</div></div>
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <label class="sr-only" for="qty-<?php echo (int)$item['id']; ?>">Quantity for <?php echo esc($item['name']); ?></label>
                    <input id="qty-<?php echo (int)$item['id']; ?>" type="number" name="qty[<?php echo (int)$item['id']; ?>]" value="<?php echo (int)$qty; ?>" min="0" max="<?php echo MAX_CART_QTY; ?>" class="w-[68px] px-2 py-2 min-h-[44px] border border-[#E5E5E5] rounded-md text-center font-semibold">
                    <span class="font-semibold tabular-nums w-20 text-right">₱<?php echo number_format($item['price'] * $qty, 2); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="flex justify-between gap-2 mt-4">
                <button name="update_qty" value="1" class="btn-ghost !min-h-[40px] text-sm"><i data-lucide="rotate-cw" aria-hidden="true"></i>Update</button>
                <button name="clear" value="1" class="btn-ghost !min-h-[40px] text-sm !text-red-700 !border-red-200">Clear</button>
            </div>
        </form>
        <form method="POST" class="mt-5 card p-5 sm:p-6 space-y-5">
            <div><h2 class="font-display font-semibold text-xl">Pickup details</h2><p class="mt-1 text-sm text-[#666666]">Pay at the counter when your order is ready.</p></div>
            <div>
                <label class="label" for="guest_name">Name</label>
                <input id="guest_name" name="guest_name" required maxlength="50" autocomplete="name" value="<?php echo esc($_SESSION['user_name'] ?? ''); ?>" class="input mt-1">
            </div>
            <div>
                <label class="label" for="guest_phone">Phone (09XXXXXXXXX)</label>
                <input id="guest_phone" name="guest_phone" required inputmode="tel" autocomplete="tel" placeholder="09..." class="input mt-1">
            </div>
            <div class="flex items-center justify-between font-semibold text-lg border-t border-dashed border-[#E5E5E5] pt-5"><span>Total</span><span class="tabular-nums">₱<?php echo number_format($cartTotal, 2); ?></span></div>
            <button name="checkout" value="1" class="btn-primary w-full !py-3.5 text-base"><i data-lucide="receipt" aria-hidden="true"></i>Place pickup order</button>
        </form>
    <?php endif; ?>
</main>
<?php require_once "../include/footer.php"; ?>
