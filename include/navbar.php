<div class="w-full border-b border-stone-900/10">
    <nav class="max-w-6xl mx-auto flex items-center justify-between px-6 py-4">
        <a href="/ordering-system/index.php" class="text-2xl font-display font-semibold tracking-tight flex items-center gap-2">
            <svg class="w-7 h-7 text-stone-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M17 8h1a4 4 0 1 1 0 8h-1" />
                <path d="M3 8h14v6a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z" />
                <line x1="6" y1="2" x2="6" y2="4" />
                <line x1="10" y1="2" x2="10" y2="4" />
                <line x1="14" y1="2" x2="14" y2="4" />
                <path d="M7 22h10" />
            </svg>
            <span class="text-stone-900">BrewCafe</span>
        </a>

        <!-- Navigation -->
        <div class="flex items-center gap-3 font-sans">
            <a href="/ordering-system/menu.php"
                class="font-medium px-5 py-2 text-stone-900 hover:bg-stone-900/5 transition">
                Menu
            </a>
            
            <form action="/ordering-system/menu.php" method="GET" class="relative flex items-center">
                <svg class="w-4 h-4 absolute left-3.5 text-stone-500 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" />
                    <line x1="21" y1="21" x2="16.5" y2="16.5" />
                </svg>
                <input type="text" name="search" placeholder="Search..." class="pl-10 pr-4 py-2 rounded-full border border-stone-900/20 font-sans focus:outline-none focus:ring-2 focus:ring-amber-600 focus:border-transparent transition">
                
            </form>

            <form action="/ordering-system/cart.php" method="GET" class="relative">
                <button type="submit" class="relative">
                    <svg class="w-5 h-5 text-stone-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="9" cy="21" r="1" />
                        <circle cx="20" cy="21" r="1" />
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                    </svg>
                    <?php
                    // Display the number of items in the cart
                    $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                    if ($cartCount > 0) {
                        echo '<span class="absolute -top-2 -right-2 bg-amber-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">' . $cartCount . '</span>';
                    }
                    ?>
                </button>
            </form>

            <a href="/ordering-system/auth/signin.php"
                class="px-5 py-2 rounded-full bg-amber-600 text-white font-sans font-medium tracking-tight hover:bg-amber-500 transition">
                Sign In
            </a>
        </div>
    </nav>
</div>