<?php
require_once "../include/auth_guard.php";
requireRole(['admin']);
require_once "../config/database.php";
require_once "../include/helpers.php";

$msg = '';
// staff creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_staff'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $msg = 'Name + valid email + 6-char temp password required.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        if ($exists) {
            $msg = 'Email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role, is_active) VALUES (?, ?, ?, 'staff', 1)");
            $stmt->bind_param("sss", $name, $email, $hash);
            $msg = $stmt->execute() ? 'Staff account created.' : 'Create failed.';
            $stmt->close();
        }
    }
}
// staff toggle + password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $userId = (int)$_POST['toggle_id'];
    $stmt = $conn->prepare("UPDATE users SET is_active = 1 - is_active WHERE id=? AND role != 'admin'");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    header("Location: /ordering-system/admin/staff.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_id'])) {
    $userId = (int)$_POST['reset_id'];
    $newPassword = $_POST['new_password'] ?? '';
    if (strlen($newPassword) < 6) $msg = 'Reset password must be 6+ chars.';
    else {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password_hash=? WHERE id=? AND role='staff'");
        $stmt->bind_param("si", $hash, $userId);
        $stmt->execute(); $stmt->close();
        $msg = 'Password reset.';
    }
}

$users = $conn->query("SELECT id, name, email, role, is_active, created_at FROM users ORDER BY FIELD(role,'admin','staff','customer'), created_at DESC")->fetch_all(MYSQLI_ASSOC);

require_once "../include/header.php";
require_once "../include/navbar.php";
?>
<main class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
    <p class="eyebrow">Administration</p>
    <h1 class="mt-2 text-3xl sm:text-4xl font-display font-semibold tracking-tight">Staff accounts</h1>
    <p class="text-[#666666] mt-2 text-[15px]">Create and manage staff access. Public signup can only make customers.</p>
    <?php if ($msg): ?><div role="status" class="alert mt-4 bg-[#F7F7F7] border-[#E5E5E5] text-[#111111]"><?php echo esc($msg); ?></div><?php endif; ?>
    <form method="POST" class="mt-4 card p-5 grid sm:grid-cols-2 md:grid-cols-4 gap-3">
        <div><label class="label sr-only" for="sname">Staff name</label><input id="sname" name="name" required placeholder="Staff name" autocomplete="off" class="input"></div>
        <div><label class="label sr-only" for="semail">Email</label><input id="semail" name="email" required type="email" placeholder="staff@brewcafe.ph" autocomplete="off" class="input"></div>
        <div><label class="label sr-only" for="spass">Password</label><input id="spass" name="password" required type="text" placeholder="Temp password (6+)" autocomplete="off" class="input"></div>
        <button name="create_staff" value="1" class="btn-primary !min-h-[44px]">Create staff</button>
    </form>
    <div class="mt-5 card divide-y divide-[#E5E5E5] overflow-hidden">
        <?php foreach ($users as $user): ?>
        <?php $isActive = (bool)$user['is_active']; ?>
        <div class="p-4 flex justify-between items-center gap-3 flex-wrap">
            <div class="min-w-0"><div class="font-medium truncate"><?php echo esc($user['name']); ?></div><div class="text-sm text-[#666666] truncate"><?php echo esc($user['email']); ?>, <span class="text-xs font-bold px-2 py-0.5 rounded-md border bg-white border-[#E5E5E5] text-[#111111]"><?php echo esc($user['role']); ?></span> <span class="text-xs font-bold px-2 py-0.5 rounded-md border <?php echo $isActive ? 'bg-[#111111] text-white border-[#111111]' : 'bg-white text-[#111111] border-[#E5E5E5]'; ?>"><?php echo $isActive ? 'active' : 'disabled'; ?></span></div></div>
            <div class="flex gap-1.5 items-center shrink-0">
                <?php if ($user['role'] === 'staff'): ?>
                <form method="POST"><input type="hidden" name="toggle_id" value="<?php echo (int)$user['id']; ?>"><button class="btn-ghost !min-h-[40px] !px-3.5 text-sm"><i data-lucide="<?php echo $isActive ? 'ban' : 'check'; ?>" class="w-4 h-4" aria-hidden="true"></i><?php echo $isActive ? 'Disable' : 'Enable'; ?></button></form>
                <form method="POST" class="flex gap-1.5" onsubmit="return confirm('Reset password?')"><input type="hidden" name="reset_id" value="<?php echo (int)$user['id']; ?>"><label class="sr-only" for="np-<?php echo (int)$user['id']; ?>">New password</label><input id="np-<?php echo (int)$user['id']; ?>" name="new_password" required minlength="6" placeholder="New temp" autocomplete="off" class="input !min-h-[40px] !w-28 !px-3"><button class="btn-ghost !min-h-[40px] !px-3.5 text-sm"><i data-lucide="key-round" class="w-4 h-4" aria-hidden="true"></i>Reset</button></form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="mt-6 flex gap-2 flex-wrap">
        <a href="/ordering-system/staff/products.php" class="btn-ghost">Manage products</a>
        <a href="/ordering-system/staff/orders.php" class="btn-ghost">Order queue</a>
    </div>
</main>
<?php require_once "../include/footer.php"; ?>
