<?php
$pageTitle = 'Order confirmation | BrewCafe';
require_once "../include/auth_guard.php";
require_once "../config/database.php";
require_once "../include/helpers.php";
require_once "../include/header.php";
require_once "../include/navbar.php";

$fallbackCode = $_SESSION['last_order'] ?? '';
$orderCode = $_GET['order'] ?? $fallbackCode;
$order = null;
if ($orderCode !== '') {
    $stmt = $conn->prepare("SELECT order_code, guest_name, guest_phone, total, status FROM orders WHERE order_code=?");
    $stmt->bind_param("s", $orderCode);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<main class="max-w-xl mx-auto px-4 sm:px-6 py-12 sm:py-16 text-center">
    <div class="card p-6 sm:p-9">
        <?php if ($order): ?>
        <span class="inline-flex items-center justify-center w-14 h-14 rounded-md bg-[#111111] text-white"><i data-lucide="check" class="text-3xl" aria-hidden="true"></i></span>
        <h1 class="mt-4 text-3xl font-display font-semibold tracking-tight">Order placed!</h1>
        <?php else: ?>
        <span class="inline-flex items-center justify-center w-14 h-14 rounded-md bg-[#F7F7F7] text-[#111111] border border-[#E5E5E5]"><i data-lucide="receipt" class="text-3xl" aria-hidden="true"></i></span>
        <h1 class="mt-4 text-3xl font-display font-semibold tracking-tight">No order to show</h1>
        <?php endif; ?>
        <?php if ($order): ?>
            <p class="mt-2 text-[#666666] text-[15px]">Save this code, then show it at the counter when you pick up.</p>
            <div class="mt-5 mx-auto max-w-xs text-left">
                <div class="text-xs font-bold uppercase tracking-wider text-[#666666]">Your order code</div>
                <div class="mt-1 inline-flex w-full items-center justify-between gap-3 bg-[#F7F7F7] border border-[#E5E5E5] rounded-md px-4 py-3">
                <span class="text-3xl sm:text-4xl font-display font-bold tracking-widest"><?php echo esc($order['order_code']); ?></span>
                <button type="button" data-copy-code="<?php echo esc($order['order_code']); ?>" class="inline-flex items-center justify-center w-11 h-11 shrink-0 rounded-md bg-[#111111] text-white hover:bg-black" aria-label="Copy order code"><i data-lucide="copy" aria-hidden="true"></i></button>
                </div>
                <p id="copy-status" class="mt-2 min-h-5 text-left text-xs text-[#666666]" role="status" aria-live="polite"></p>
            </div>
            <dl class="mt-5 grid grid-cols-2 gap-x-4 gap-y-3 border-y border-[#E5E5E5] py-4 text-left text-sm">
                <div><dt class="text-xs text-[#666666]">Name</dt><dd class="mt-0.5 font-medium truncate"><?php echo esc($order['guest_name']); ?></dd></div>
                <div><dt class="text-xs text-[#666666]">Phone</dt><dd class="mt-0.5 font-medium tabular-nums"><?php echo esc($order['guest_phone']); ?></dd></div>
                <div><dt class="text-xs text-[#666666]">Total</dt><dd class="mt-0.5 font-medium tabular-nums">₱<?php echo number_format($order['total'], 2); ?></dd></div>
                <div><dt class="text-xs text-[#666666]">Status</dt><dd class="mt-0.5"><span class="text-xs font-bold px-2 py-0.5 rounded-md border <?php echo statusPill($order['status']); ?>"><?php echo esc($order['status']); ?></span></dd></div>
            </dl>
            <div class="mt-6 flex justify-center gap-2 flex-wrap">
                <a href="/ordering-system/track.php?order=<?php echo urlencode($order['order_code']); ?>&phone=<?php echo urlencode($order['guest_phone']); ?>" class="btn-dark">Track order</a>
                <a href="/ordering-system/customer/menu.php" class="btn-ghost">Back to menu</a>
            </div>
            <?php if (!isLoggedIn()): ?>
            <div class="mt-6 bg-[#F7F7F7] border border-[#E5E5E5] rounded-md p-4 text-sm text-left">
                Save your orders. <a class="underline font-medium" href="/ordering-system/auth/signup.php">Sign up</a>, no need to track manually next time.
            </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="mt-2 text-[#666666]">This page needs a recent order code to show your pickup details.</p>
            <a href="/ordering-system/customer/menu.php" class="btn-primary mt-4">Order now</a>
        <?php endif; ?>
    </div>
</main>
<?php if ($order): ?>
<script>
document.querySelector('[data-copy-code]')?.addEventListener('click', async function () {
    const status = document.getElementById('copy-status');
    try {
        await navigator.clipboard.writeText(this.dataset.copyCode);
        status.textContent = 'Order code copied.';
    } catch (error) {
        status.textContent = 'Copy unavailable. Select the code manually.';
    }
});
</script>
<?php endif; ?>
<?php require_once "../include/footer.php"; ?>
