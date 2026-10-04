<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();

function currentRole(): ?string {
    $role = $_SESSION['user_role'] ?? null;
    return is_string($role) ? $role : null;
}
function isLoggedIn(): bool { return isset($_SESSION['user_id']); }
function isAdmin(): bool { return currentRole() === 'admin'; }
function isStaff(): bool { return in_array(currentRole(), ['staff', 'admin'], true); }

function redirectTo(string $path): void { header("Location: $path"); exit(); }

function requireRole(string|array $allowedRoles): void {
    if (!isLoggedIn()) redirectTo('/ordering-system/auth/signin.php');
    if (!in_array(currentRole(), (array)$allowedRoles, true)) {
        http_response_code(403);
        require_once __DIR__ . '/header.php';
        require __DIR__ . '/forbidden.php';
        require_once __DIR__ . '/footer.php';
        exit();
    }
}

function redirectByRole(): void {
    $role = currentRole();
    if ($role === 'admin') redirectTo('/ordering-system/admin/staff.php');
    if ($role === 'staff') redirectTo('/ordering-system/staff/orders.php');
    redirectTo('/ordering-system/customer/dashboard.php');
}
