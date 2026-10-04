<?php
    require_once "../include/auth_guard.php";
    require_once "../config/database.php";
    require_once "../include/helpers.php";

    $error = '';
    if(isset($_POST['email']) && isset($_POST['password'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, name, email, password_hash, role, is_active FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ((int)($user['is_active'] ?? 1) === 0) {
                $error = 'Account is deactivated. Contact admin.';
            } elseif(password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'] ?? 'customer';
                redirectByRole();
            } else {
                $error = 'Incorrect password. Please try again.';
            }
        } else {
            $error = 'Email not found. Please sign up first.';
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
        <h1 class="mt-2 text-3xl font-display font-semibold tracking-tight text-[#111111] text-center">Welcome back</h1>
        <p class="mt-2 text-sm text-[#666666] text-center">Sign in to see your orders faster.</p>
        <?php if ($error): ?><div role="alert" class="alert mt-5 bg-red-50 border-red-200 text-red-800"><?php echo esc($error); ?></div><?php elseif (($_GET['created'] ?? '') === '1'): ?><div class="alert mt-5 bg-[#F7F7F7] border-[#E5E5E5] text-[#111111]">Account created. Please sign in.</div><?php endif; ?>
        <form action="/ordering-system/auth/signin.php" method="POST" class="mt-5 space-y-4">
            <div>
                <label for="email" class="label">Email</label>
                <input type="email" name="email" id="email" required autocomplete="email" class="input mt-1">
            </div>
            <div>
                <label for="password" class="label">Password</label>
                <div class="relative mt-1">
                    <input type="password" name="password" id="password" required autocomplete="current-password" class="input !pr-12">
<button type="button" onclick="var i=document.getElementById('password');var show=i.type==='password';i.type=show?'text':'password';this.setAttribute('aria-pressed',show);this.setAttribute('aria-label',show?'Hide password':'Show password')" class="absolute right-1 top-1/2 -translate-y-1/2 inline-flex items-center justify-center w-10 h-10 rounded-md hover:bg-[#F7F7F7]" aria-label="Show password" aria-pressed="false"><i data-lucide="eye" aria-hidden="true"></i></button>
                </div>
            </div>
            <button type="submit" class="btn-primary w-full !py-3">Sign In</button>
        </form>
        <p class="mt-4 text-center text-sm text-[#666666]">
            Don't have an account?
            <a href="/ordering-system/auth/signup.php" class="font-medium text-[#111111] underline">Sign Up</a> or <a href="/ordering-system/customer/menu.php" class="underline">order as guest</a>
        </p>
    </div>
</main>
