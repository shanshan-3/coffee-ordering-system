<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/helpers.php';
$role = currentRole();
$isLoggedIn = isLoggedIn();
$cartCount = cartCount();
$userInitial = strtoupper(mb_substr($_SESSION['user_name'] ?? 'G', 0, 1));
$mobileLinks = [
    ['href' => '/ordering-system/customer/menu.php', 'label' => 'Menu', 'icon' => 'coffee', 'show' => true],
    ['href' => '/ordering-system/customer/dashboard.php', 'label' => 'My Orders', 'icon' => 'receipt', 'show' => !isStaff()],
    ['href' => '/ordering-system/track.php', 'label' => 'Track order', 'icon' => 'package', 'show' => !isStaff()],
    ['href' => '/ordering-system/staff/orders.php', 'label' => 'Queue', 'icon' => 'timer', 'show' => isStaff()],
    ['href' => '/ordering-system/staff/products.php', 'label' => 'Products', 'icon' => 'cup-soda', 'show' => isStaff()],
    ['href' => '/ordering-system/admin/staff.php', 'label' => 'Staff', 'icon' => 'users', 'show' => isAdmin()],
];
?>
<header class="sticky top-0 z-40 w-full border-b border-[#E5E5E5] bg-white">
    <nav class="max-w-6xl mx-auto flex items-center justify-between gap-3 px-4 sm:px-6 py-3.5" aria-label="Primary">
        <?php require __DIR__ . '/brand.php'; ?>
        <div class="hidden md:flex items-center gap-1.5 font-sans text-[15px]">
            <a href="/ordering-system/customer/menu.php" class="navlink<?php echo navActive('/customer/menu.php'); ?>"><i data-lucide="coffee" aria-hidden="true"></i>Menu</a>
            <?php if (!isStaff()): ?>
            <a href="/ordering-system/customer/dashboard.php" class="navlink<?php echo navActive('/customer/dashboard.php'); ?>"><i data-lucide="receipt" aria-hidden="true"></i>My Orders</a>
            <a href="/ordering-system/track.php" class="navlink<?php echo navActive('/track.php'); ?>"><i data-lucide="package" aria-hidden="true"></i>Track</a>
            <?php endif; ?>
            <?php if (isStaff()): ?>
                <a href="/ordering-system/staff/orders.php" class="navlink<?php echo navActive('/staff/orders.php'); ?>"><i data-lucide="timer" aria-hidden="true"></i>Queue</a>
                <a href="/ordering-system/staff/products.php" class="navlink<?php echo navActive('/staff/products.php'); ?>"><i data-lucide="cup-soda" aria-hidden="true"></i>Products</a>
            <?php endif; ?>
            <?php if (isAdmin()): ?>
                <a href="/ordering-system/admin/staff.php" class="navlink<?php echo navActive('/admin/staff.php'); ?>"><i data-lucide="users" aria-hidden="true"></i>Staff</a>
            <?php endif; ?>
            <?php if (!isStaff()): ?>
            <a href="/ordering-system/customer/cart.php" class="navlink border border-[#E5E5E5]<?php echo navActive('/customer/cart.php'); ?>">
                <i data-lucide="shopping-cart" aria-hidden="true"></i>Cart<?php if ($cartCount > 0): ?><span class="ml-1 inline-flex items-center justify-center bg-[#111111] text-white text-xs font-bold tabular-nums rounded-md min-w-5 h-5 px-1"><?php echo (int)$cartCount; ?></span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if ($isLoggedIn): ?>
                <span class="ml-2 hidden lg:inline-flex items-center gap-2 text-sm text-[#666666] max-w-44 truncate"><span class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-[#111111] text-white text-xs font-bold shrink-0"><?php echo esc($userInitial); ?></span><span class="truncate"><?php echo esc($_SESSION['user_name'] ?? ''); ?></span><span class="status !min-h-6 !py-0 !px-2 !font-medium !normal-case border-[#E5E5E5] bg-white text-[#666666]"><?php echo esc($role); ?></span></span>
                <a href="/ordering-system/auth/signout.php" class="btn-ghost !px-4 !py-2 !min-h-[40px] ml-2 text-sm">Sign Out</a>
            <?php else: ?>
                <a href="/ordering-system/auth/signin.php" class="btn-primary !px-5 !py-2 !min-h-[40px] ml-2 text-sm">Sign In</a>
            <?php endif; ?>
        </div>
        <div class="flex md:hidden items-center gap-2">
            <?php if (!isStaff()): ?>
            <a href="/ordering-system/customer/cart.php" class="relative inline-flex items-center justify-center w-11 h-11 rounded-md border border-[#E5E5E5]" aria-label="Cart<?php echo $cartCount > 0 ? ', ' . (int)$cartCount . ' items' : ''; ?>"><i data-lucide="shopping-cart" class="text-xl" aria-hidden="true"></i><?php if ($cartCount > 0): ?><span class="absolute -top-1 -right-1 inline-flex items-center justify-center bg-[#111111] text-white text-[11px] font-bold tabular-nums rounded-md min-w-5 h-5 px-1"><?php echo (int)$cartCount; ?></span><?php endif; ?></a>
            <?php endif; ?>
            <button type="button" onclick="var menu=document.getElementById('mnav');var open=menu.classList.toggle('hidden')===false;this.setAttribute('aria-expanded',open);this.setAttribute('aria-label',open?'Close menu':'Open menu');var ic=this.querySelector('svg,i');ic.setAttribute('data-lucide',open?'x':'list');if(window.lucide)lucide.createIcons()" class="inline-flex items-center justify-center w-11 h-11 rounded-md border border-[#E5E5E5]" aria-expanded="false" aria-controls="mnav" aria-label="Open menu"><i data-lucide="list" class="text-xl" aria-hidden="true"></i></button>
        </div>
    </nav>
    <div id="mnav" class="hidden md:hidden border-t border-[#E5E5E5] bg-white px-4 py-3">
        <div class="grid gap-1 font-sans">
            <?php foreach ($mobileLinks as $link): ?>
            <?php if ($link['show']): ?><a href="<?php echo $link['href']; ?>" class="navlink"><i data-lucide="<?php echo $link['icon']; ?>" aria-hidden="true"></i><?php echo $link['label']; ?></a><?php endif; ?>
            <?php endforeach; ?>
            <?php if (!isStaff()): ?><a href="/ordering-system/customer/cart.php" class="navlink"><i data-lucide="shopping-cart" aria-hidden="true"></i>Cart<?php if ($cartCount > 0) echo ' (' . (int)$cartCount . ')'; ?></a><?php endif; ?>
            <?php if ($isLoggedIn): ?>
                <div class="flex items-center justify-between pt-2 mt-1 border-t border-[#E5E5E5]"><span class="text-sm text-[#666666] truncate"><?php echo esc($_SESSION['user_name'] ?? ''); ?>, <?php echo esc($role); ?></span><a href="/ordering-system/auth/signout.php" class="btn-ghost !min-h-[40px] text-sm">Sign Out</a></div>
            <?php else: ?>
                <a href="/ordering-system/auth/signin.php" class="btn-primary mt-2">Sign In</a>
            <?php endif; ?>
        </div>
    </div>
</header>
