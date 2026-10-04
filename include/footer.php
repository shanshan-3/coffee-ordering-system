<footer class="w-full border-t border-[#E5E5E5] bg-[#F7F7F7]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 grid gap-8 sm:grid-cols-3 text-sm">
        <div><div class="font-display font-semibold text-base text-[#111111]">BrewCafe</div><p class="mt-1 text-[#666666]">Fresh coffee, ready when you are.<br>Pickup only. Pay at counter.</p></div>
        <div class="text-[#666666]"><div class="font-medium text-[#111111] font-display font-semibold">Hours</div><p class="mt-1">Mon to Sat, 7:00 to 19:00<br>Sun, 8:00 to 17:00</p></div>
        <div class="flex sm:justify-end items-start gap-2">
            <a href="/ordering-system/customer/menu.php" class="btn-ghost !min-h-[40px] text-sm">Menu</a>
            <?php if (function_exists('isStaff') && !isStaff()): ?><a href="/ordering-system/track.php" class="btn-ghost !min-h-[40px] text-sm">Track</a><?php endif; ?>
        </div>
    </div>
    <div class="border-t border-[#E5E5E5]"><div class="max-w-6xl mx-auto px-6 py-4 text-center text-xs text-[#666666]">&copy; <?php echo date('Y'); ?> BrewCafe. All rights reserved.</div></div>
</footer>

<script src="https://unpkg.com/lucide@latest"></script>
<script>window.lucide&&lucide.createIcons();</script>

</body>

</html>
