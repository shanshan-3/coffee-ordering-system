<?php
require_once "../include/auth_guard.php";
require_once "../config/database.php";
require_once "../include/helpers.php";

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
$canOrder = !isStaff();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_id'])) {
    if (!$canOrder) {
        header("Location: /ordering-system/customer/menu.php?blocked=1");
        exit();
    }
    $menuItemId = (int)$_POST['add_id'];
    $qty = max(1, min(MAX_CART_QTY, (int)($_POST['qty'] ?? 1)));
    $availabilityStmt = $conn->prepare("SELECT id FROM menu_items WHERE id=? AND is_available=1");
    $availabilityStmt->bind_param("i", $menuItemId);
    $availabilityStmt->execute();
    if ($availabilityStmt->get_result()->num_rows > 0) {
        $_SESSION['cart'][$menuItemId] = min(MAX_CART_QTY, ($_SESSION['cart'][$menuItemId] ?? 0) + $qty);
    }
    $availabilityStmt->close();
    header("Location: /ordering-system/customer/menu.php");
    exit();
}

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $searchPattern = "%$search%";
    $stmt = $conn->prepare("SELECT * FROM menu_items WHERE is_available=1 AND (name LIKE ? OR category LIKE ?) ORDER BY category, name");
    $stmt->bind_param("ss", $searchPattern, $searchPattern);
} else {
    $stmt = $conn->prepare("SELECT * FROM menu_items WHERE is_available=1 ORDER BY category, name");
}
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once "../include/header.php";
require_once "../include/navbar.php";
?>
<?php
$categories = array_values(array_unique(array_filter(array_map(fn($row) => trim($row['category'] ?? ''), $items))));
sort($categories);
$activeCategory = trim($_GET['cat'] ?? '');
if ($activeCategory !== '') { $items = array_values(array_filter($items, fn($row) => strcasecmp(trim($row['category'] ?? ''), $activeCategory) === 0)); }
?>
<main class="max-w-6xl mx-auto px-4 sm:px-6 py-10 pb-28 md:pb-12">
    <div class="flex items-end justify-between gap-6 flex-wrap border-b border-[#E5E5E5] pb-6">
        <div>
            <p class="eyebrow">BrewCafe menu</p>
            <h1 class="mt-2 text-3xl sm:text-4xl font-display font-semibold tracking-tight">Find your next cup</h1>
            <p class="mt-2 text-[#666666] text-[15px]">Order ahead for pickup. No login needed.</p>
        </div>
        <form method="GET" class="flex gap-2 w-full sm:w-auto" role="search">
            <label class="relative flex-1 sm:w-64">
                <span class="sr-only">Search menu</span>
                <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#666666]" aria-hidden="true"></i>
                <input type="text" name="search" value="<?php echo esc($search); ?>" placeholder="Search coffee..." class="input !pl-10 sm:w-64">
            </label>
            <button class="btn-dark shrink-0">Search</button>
        </form>
    </div>
    <?php if (!empty($categories)): ?>
    <nav class="mt-4 flex gap-2 overflow-x-auto pb-1 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap" aria-label="Menu categories">
        <a href="?<?php echo http_build_query(array_filter(['search' => $search])); ?>" class="shrink-0 inline-flex items-center min-h-[40px] px-4 rounded-md border text-sm font-medium transition <?php echo $activeCategory === '' ? 'bg-[#111111] text-white border-[#111111]' : 'bg-white border-[#E5E5E5] hover:border-[#111111]'; ?>">All</a>
        <?php foreach ($categories as $category): ?>
        <a href="?<?php echo http_build_query(array_filter(['search' => $search, 'cat' => $category])); ?>" class="shrink-0 inline-flex items-center min-h-[40px] px-4 rounded-md border text-sm font-medium transition <?php echo strcasecmp($activeCategory, $category) === 0 ? 'bg-[#111111] text-white border-[#111111]' : 'bg-white border-[#E5E5E5] hover:border-[#111111]'; ?>"><?php echo esc($category); ?></a>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>
    <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($items as $item): ?>
        <article class="card p-4 flex flex-col">
            <?php $image = imageUrl($item['image_url']); ?>
            <?php if ($image): ?>
                <img src="<?php echo esc($image); ?>" alt="<?php echo esc($item['name']); ?>" loading="lazy" class="photo" onerror="this.style.display='none'">
            <?php else: ?>
                <div class="photo-block" aria-hidden="true"><span><?php echo esc(strtoupper(mb_substr($item['name'] ?? 'B', 0, 1))); ?></span></div>
            <?php endif; ?>
            <div class="mt-3 flex items-start justify-between gap-2">
                <h2 class="font-semibold text-[17px] leading-snug"><?php echo esc($item['name']); ?></h2>
                <span class="font-display font-semibold text-[17px] whitespace-nowrap tabular-nums">₱<?php echo number_format($item['price'], 2); ?></span>
            </div>
            <div class="mt-1 text-xs font-semibold uppercase tracking-[.14em] text-[#666666]"><?php echo esc($item['category']); ?></div>
            <?php if (!empty($item['description'])): ?><p class="text-sm text-[#666666] mt-1 line-clamp-2"><?php echo esc($item['description']); ?></p><?php endif; ?>
            <?php if (!$canOrder): ?>
                <div class="mt-3 text-sm text-[#666666]">View-only for staff/admin.</div>
            <?php else: ?>
            <form method="POST" class="flex gap-2 mt-auto pt-4 border-t border-[#E5E5E5]">
                <input type="hidden" name="add_id" value="<?php echo (int)$item['id']; ?>">
                <label class="sr-only" for="q-<?php echo (int)$item['id']; ?>">Quantity for <?php echo esc($item['name']); ?></label>
                <input id="q-<?php echo (int)$item['id']; ?>" type="number" name="qty" value="1" min="1" max="<?php echo MAX_CART_QTY; ?>" class="w-[68px] px-2 py-2 min-h-[44px] border border-[#E5E5E5] rounded-md text-center font-semibold">
                <button class="btn-primary flex-1 !min-h-[44px] text-[15px]"><i data-lucide="plus" aria-hidden="true"></i>Add to cart</button>
            </form>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
            <div class="card p-10 text-center sm:col-span-2 lg:col-span-3">
                <div class="photo-block mx-auto max-w-[220px]" aria-hidden="true"><i data-lucide="coffee" class="w-7 h-7" aria-hidden="true"></i></div>
                <p class="mt-4 font-medium">No drinks match that search.</p>
                <p class="text-sm text-[#666666]">Try “latte”, “espresso”, or clear filters.</p>
                <a href="menu.php" class="btn-ghost mt-4">Clear filters</a>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php if ($canOrder): ?>
<div class="fixed bottom-0 inset-x-0 z-30 md:hidden border-t border-[#E5E5E5] bg-white px-4 pt-3" style="padding-bottom:env(safe-area-inset-bottom,12px)">
    <div class="flex gap-2">
        <a href="/ordering-system/track.php" class="btn-ghost flex-1">Track</a>
        <a href="/ordering-system/customer/cart.php" class="btn-dark flex-[2]"><i data-lucide="shopping-cart" aria-hidden="true"></i>Cart, <?php echo (int)cartCount(); ?> items</a>
    </div>
</div>
<div class="hidden md:flex max-w-6xl mx-auto px-6 pb-10 gap-3">
    <a href="/ordering-system/customer/cart.php" class="btn-dark">Go to cart (<?php echo (int)cartCount(); ?>)</a>
    <a href="/ordering-system/track.php" class="btn-ghost">Track order</a>
</div>
<?php endif; ?>
<?php require_once "../include/footer.php"; ?>
