<?php
declare(strict_types=1);

const MAX_CART_QTY = 20;
const MAX_IMAGE_BYTES = 2097152; // 2 MB

function esc(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }

function imageUrl(mixed $path): ?string {
    $cleaned = trim((string)$path);
    if ($cleaned === '') return null;
    if (preg_match('#^(https?:)?//#i', $cleaned) || str_starts_with($cleaned, '/')) return $cleaned;
    return '/ordering-system/' . ltrim($cleaned, '/');
}

function placeholders(array $ids): string {
    return implode(',', array_fill(0, count($ids), '?'));
}

function deleteProductImageFile(mixed $stored): void {
    $stored = trim((string)$stored);
    if ($stored === '' || preg_match('#^(https?:)?//#i', $stored)) return;
    $rel = ltrim(str_replace('\\', '/', $stored), '/');
    if (!str_starts_with($rel, 'uploads/products/')) return;
    if (str_contains($rel, '..')) return;
    $abs = dirname(__DIR__) . '/' . $rel;
    if (is_file($abs)) @unlink($abs);
}

function normalizePhone(mixed $raw): string|false {
    $phone = preg_replace('/[\s\-\.\(\)]/', '', trim((string)$raw));
    if (str_starts_with($phone, '+639')) $phone = '09' . substr($phone, 4);
    elseif (str_starts_with($phone, '639') && strlen($phone) === 12) $phone = '0' . substr($phone, 2);
    if (!preg_match('/^09\d{9}$/', $phone)) return false;
    return $phone;
}

function generateOrderCode(mysqli $conn): string {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $charCount = strlen($chars);
    $maxAttempts = 20;
    $codeLength = 4;
    for ($i = 0; $i < $maxAttempts; $i++) {
        $code = 'BC-';
        for ($j = 0; $j < $codeLength; $j++) $code .= $chars[random_int(0, $charCount - 1)];
        $stmt = $conn->prepare("SELECT id FROM orders WHERE order_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        if (!$exists) return $code;
    }
    return 'BC-' . strtoupper(substr(md5(uniqid('', true)), 0, 4));
}

function cartCount(): int {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) return 0;
    return array_sum($_SESSION['cart']);
}

// ponytail: single map for status colors — B&W chrome, red kept only for cancelled
function statusPill(mixed $status): string {
    $statusKey = strtolower((string)$status);
    $map = [
        'pending' => 'bg-white text-[#111111] border-[#E5E5E5]',
        'preparing' => 'bg-[#111111] text-white border-[#111111]',
        'ready' => 'bg-[#111111] text-white border-[#111111]',
        'completed' => 'bg-[#F7F7F7] text-[#111111] border-[#111111]',
        'cancelled' => 'bg-red-700 text-white border-red-700',
        'paid' => 'bg-[#111111] text-white border-[#111111]',
        'unpaid' => 'bg-white text-[#666666] border-[#E5E5E5]',
    ];
    return $map[$statusKey] ?? 'bg-white text-[#111111] border-[#E5E5E5]';
}

function navActive(string $pathFragment): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return str_contains($uri, $pathFragment) ? ' navlink-active' : '';
}
