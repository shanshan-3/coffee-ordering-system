<?php
    require_once "../include/header.php";

    
?>  
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
    </nav>
</div>

<div class="flex items-center justify-center min-h-screen bg-[#FAF6F0]">
    <div class="max-w-md w-full bg-white p-8 rounded-xl shadow-md">
        <h1 class="text-3xl font-display font-semibold text-stone-900 mb-4 text-center">Create Your Account</h1>
        <form action="/ordering-system/auth/signup.php" method="POST" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-stone-700">Name</label>
                <input type="text" name="name" id="name" required class="mt-1 block w-full px-3 py-2 border border-stone-300 rounded-md shadow-sm focus:outline-none focus:ring-amber-600 focus:border-amber-600 sm:text-sm">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-stone-700">Email</label>
                <input type="email" name="email" id="email" required class="mt-1 block w-full px-3 py-2 border border-stone-300 rounded-md shadow-sm focus:outline-none focus:ring-amber-600 focus:border-amber-600 sm:text-sm">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-stone-700">Password</label>
                <input type="password" name="password" id="password" required class="mt-1 block w-full px-3 py-2 border border-stone-300 rounded-md shadow-sm focus:outline-none focus:ring-amber-600 focus:border-amber-600 sm:text-sm">
            </div>
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-600">
                Sign Up
            </button>
        </form>
        <p class="mt-4 text-center text-sm text-stone-600">
            Already have an account? 
            <a href="/ordering-system/auth/signin.php" class="text-amber-600 hover:text-amber-500">Sign In</a>
        </p>
    </div>
</div>
