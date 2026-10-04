<?php
    require_once "../include/auth_guard.php";
    require_once "../config/database.php";
    require_once "../include/helpers.php";
    $error = '';
    if(isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) {
            $error = 'Email already exists. Please use a different email.';
        } else {
            // Public signup always creates customer, never staff/admin
            $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role, is_active) VALUES (?, ?, ?, 'customer', 1)");
            $stmt->bind_param("sss", $name, $email, $passwordHash);
            if($stmt->execute()) {
                redirectTo('/ordering-system/auth/signin.php?created=1');
            } else {
                $error = 'Error creating account. Please try again.';
            }
        }
    }
    if(isLoggedIn()) {
        redirectByRole();
    }
    require_once "../include/header.php";
?>  

<div class="w-full border-b border-[#E5E5E5] bg-white">
    <nav class="max-w-6xl mx-auto flex items-center justify-between px-4 sm:px-6 py-3">
        <?php require "../include/brand.php"; ?>
        <a href="/ordering-system/customer/menu.php" class="btn-ghost !min-h-[40px] text-sm">Back to menu</a>
    </nav>
</div>

<main class="flex items-start sm:items-center justify-center px-4 py-12 sm:py-16 bg-[#F7F7F7] min-h-[calc(100vh-73px)]">
    <div class="max-w-md w-full card p-6 sm:p-8">
        <p class="eyebrow text-center">BrewCafe account</p>
        <h1 class="mt-2 text-3xl font-display font-semibold tracking-tight text-[#111111] text-center">Create account</h1>
        <p class="mt-2 text-sm text-[#666666] text-center">Track orders and speed up your next checkout.</p>
        <?php if ($error): ?><div role="alert" class="alert mt-5 bg-red-50 border-red-200 text-red-800"><?php echo esc($error); ?></div><?php endif; ?>
        <form action="/ordering-system/auth/signup.php" method="POST" class="mt-5 space-y-4">
            <div>
                <label for="name" class="label">Name</label>
                <input type="text" name="name" id="name" required autocomplete="name" class="input mt-1">
            </div>
            <div>
                <label for="email" class="label">Email</label>
                <input type="email" name="email" id="email" required autocomplete="email" class="input mt-1">
            </div>
            <div>
                <label for="password" class="label">Password (6+ chars)</label>
                <input type="password" name="password" id="password" required minlength="6" autocomplete="new-password" class="input mt-1">
            </div>
            <button type="submit" class="btn-primary w-full !py-3">Sign Up</button>
        </form>
        <p class="mt-4 text-center text-sm text-[#666666]">
            Already have an account?
            <a href="/ordering-system/auth/signin.php" class="font-medium text-[#111111] underline">Sign In</a>
        </p>
    </div>
</main>
