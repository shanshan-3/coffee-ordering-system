<?php require_once __DIR__ . '/include/auth_guard.php'; $pageTitle = 'BrewCafe: Coffee Ordering'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        <?php echo $pageTitle; ?>
    </title>
    <?php require_once __DIR__ . '/include/header.php'; ?>
</head>

<body class="min-h-screen flex flex-col bg-white text-[#111111] font-sans antialiased">

    <!-- Navbar -->
    <?php require_once __DIR__ . '/include/navbar.php'; ?>

    <!-- Hero -->
    <main class="flex-1 w-full">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-12 pb-10 md:pt-20 md:pb-16">
            <div class="max-w-2xl">
                <h1 class="mt-4 text-4xl sm:text-5xl md:text-6xl font-display font-semibold tracking-tight leading-[1.04]">
                    BrewCafe,<br /><span class="text-[#111111]">A cafe shop for coffee lovers</span>
                </h1>
                <p class="mt-6 text-lg leading-8 text-[#666666] max-w-lg">
                    Order your favorite drinks online and pick them up at the counter. No login required, pay when ready.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="/ordering-system/customer/menu.php" class="btn-primary !px-6 !py-3 text-base">Browse the menu</a>
                    <?php if (!isStaff()): ?><a href="/ordering-system/track.php" class="btn-ghost !px-6 !py-3 text-base">Track an order</a><?php endif; ?>
                </div>
                <dl class="mt-9 grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-xl text-sm">
                    <div><dt class="font-semibold text-[#111111]">No login</dt><dd class="mt-0.5 text-[#666666]">Order as a guest</dd></div>
                    <div><dt class="font-semibold text-[#111111]">Pay at counter</dt><dd class="mt-0.5 text-[#666666]">Pickup when ready</dd></div>
                    <div><dt class="font-semibold text-[#111111]">Open daily</dt><dd class="mt-0.5 text-[#666666]">Mon-Sat, 7:00-19:00</dd></div>
                </dl>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 pb-16">
            <div class="flex items-end justify-between gap-4 border-t border-[#E5E5E5] pt-8">
                <div><h2 class="font-display font-semibold text-2xl tracking-tight">From menu to pickup</h2></div>
            </div>
            <ol class="mt-5 grid md:grid-cols-3 border-y border-[#E5E5E5] divide-y md:divide-y-0 md:divide-x divide-[#E5E5E5]">
                <li class="py-5 md:pr-6 flex gap-4"><span class="font-display font-semibold text-lg text-[#111111] tabular-nums shrink-0">01</span><div><div class="font-semibold">Choose your drinks</div><p class="mt-1 text-sm leading-6 text-[#666666]">Browse the menu and add what you want, no account required.</p></div></li>
                <li class="py-5 md:px-6 flex gap-4"><span class="font-display font-semibold text-lg text-[#111111] tabular-nums shrink-0">02</span><div><div class="font-semibold">Keep your code</div><p class="mt-1 text-sm leading-6 text-[#666666]">Checkout gives you a BC-XXXX pickup code to save.</p></div></li>
                <li class="py-5 md:pl-6 flex gap-4"><span class="font-display font-semibold text-lg text-[#111111] tabular-nums shrink-0">03</span><div><div class="font-semibold">Pay and collect</div><p class="mt-1 text-sm leading-6 text-[#666666]">Track the status, pay at the counter, and grab your order.</p></div></li>
            </ol>
        </div>
    </main>

    <!-- Footer -->
    <?php require_once __DIR__ . '/include/footer.php'; ?>