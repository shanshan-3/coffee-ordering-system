<?php $pageTitle = 'BrewCafe — Coffee Ordering'; ?>
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

<body class="min-h-screen flex flex-col bg-[#FAF6F0] text-stone-900 font-sans antialiased">

    <!-- Navbar -->
    <?php require_once __DIR__ . '/include/navbar.php'; ?>

    <!-- Hero -->
    <main class="flex-1 flex items-center justify-center px-6">
        <div class="max-w-2xl text-center py-20">
            <h1 class="text-4xl md:text-6xl font-display font-semibold tracking-tight leading-[1.05]">
                Your Favorite Brew,<br />Ordered Ahead.
            </h1>
            <p class="mt-6 text-lg text-stone-600 font-sans">
                Skip the line with BrewCafe — fresh coffee, ready when you are.
            </p>
            <div class="mt-10 flex items-center justify-center gap-4">
                <a href="/ordering-system/menu.php"
                    class="px-8 py-3 rounded-xl bg-amber-600 text-lg font-sans font-medium tracking-tight hover:bg-amber-500 transition">
                    Explore Menu
                </a>
                <a href="/ordering-system/signin.php"
                    class="px-8 py-3 rounded-xl border border-stone-900/30 text-stone-900 text-lg font-sans font-medium tracking-tight hover:bg-stone-900/5 transition">
                    Order Now ->
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php require_once __DIR__ . '/include/footer.php'; ?>