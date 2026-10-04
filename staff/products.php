<?php
require_once "../include/auth_guard.php";
requireRole(['staff', 'admin']);
require_once "../config/database.php";
require_once "../include/helpers.php";

$msg = '';
$msgType = 'ok';

function storeProductImage($file, $maxBytes) {
    if (!is_array($file) || (int)$file['error'] === UPLOAD_ERR_NO_FILE) return ['', ''];
    $uploadError = (int)$file['error'];
    if ($uploadError !== UPLOAD_ERR_OK) {
        $message = match (true) {
            $uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE => 'Image too large. Maximum 2 MB.',
            $uploadError === UPLOAD_ERR_PARTIAL => 'Image was only partially uploaded.',
            $uploadError === UPLOAD_ERR_NO_TMP_DIR || $uploadError === UPLOAD_ERR_CANT_WRITE => 'Server could not store the upload.',
            $uploadError === UPLOAD_ERR_EXTENSION => 'Upload blocked by a PHP extension.',
            default => 'Upload failed.',
        };
        return [$message, ''];
    }
    if ((int)$file['size'] > $maxBytes) return ['Image too large. Maximum 2 MB.', ''];
    $info = @getimagesize($file['tmp_name']);
    $ext = $info !== false ? ([IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png'][$info[2]] ?? '') : '';
    if ($ext === '') return ['Not a valid image. Allowed: JPG or PNG.', ''];
    if (!is_uploaded_file($file['tmp_name'])) return ['Upload failed.', ''];
    $dir = dirname(__DIR__) . '/uploads/products';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    if (!is_dir($dir) || !is_writable($dir)) return ['Could not save the image. Check that uploads/products is writable.', ''];
    try { $filename = bin2hex(random_bytes(8)) . '.' . $ext; } catch (\Throwable $e) { $filename = ''; }
    if ($filename === '') return ['Could not prepare the image upload.', ''];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) return ['Could not save the image. Check that uploads/products is writable.', ''];
    return ['', 'uploads/products/' . $filename];
}

// product form handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    $msg = 'Request too large. Raise post_max_size in php.ini.';
    $msgType = 'err';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $productId = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? 'Coffee');
    $description = trim($_POST['description'] ?? '');
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;
    $removeImage = isset($_POST['remove_image']);

    $oldImage = '';
    if ($productId > 0) {
        $stmt = $conn->prepare("SELECT image_url FROM menu_items WHERE id=?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $oldImage = (string)($stmt->get_result()->fetch_assoc()['image_url'] ?? '');
        $stmt->close();
    }

    [$uploadError, $newImage] = storeProductImage($_FILES['image'] ?? null, MAX_IMAGE_BYTES);

    if ($uploadError === '' && ($name === '' || $price <= 0)) $uploadError = 'Name + valid price required.';

    if ($uploadError !== '') {
        $msg = $uploadError;
        $msgType = 'err';
        if ($newImage !== '') deleteProductImageFile($newImage);
    } else {
        if ($newImage !== '') $image = $newImage;
        elseif ($removeImage) $image = '';
        else $image = $oldImage;
        if ($productId > 0) {
            $stmt = $conn->prepare("UPDATE menu_items SET name=?, description=?, price=?, category=?, is_available=?, image_url=NULLIF(?,'') WHERE id=?");
            $stmt->bind_param("ssdsisi", $name, $description, $price, $category, $isAvailable, $image, $productId);
        } else {
            $stmt = $conn->prepare("INSERT INTO menu_items (name, description, price, category, is_available, image_url) VALUES (?, ?, ?, ?, ?, NULLIF(? ,''))");
            $stmt->bind_param("ssdsis", $name, $description, $price, $category, $isAvailable, $image);
        }
        if ($stmt->execute()) {
            $msg = 'Saved.';
            if ($image !== $oldImage) deleteProductImageFile($oldImage);
        } else {
            $msg = 'Save failed.';
            $msgType = 'err';
            if ($newImage !== '') deleteProductImageFile($newImage);
        }
        $stmt->close();
    }
}
// toggle visibility
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $productId = (int)$_POST['toggle_id'];
    $stmt = $conn->prepare("UPDATE menu_items SET is_available = 1 - is_available WHERE id=?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->close();
    header("Location: /ordering-system/staff/products.php");
    exit();
}
// delete product (past orders keep name/price snapshot)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $productId = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("SELECT image_url FROM menu_items WHERE id=?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $deletedImage = (string)($stmt->get_result()->fetch_assoc()['image_url'] ?? '');
    $stmt->close();
    $stmt = $conn->prepare("SELECT COUNT(*) AS usage_count FROM order_items WHERE menu_item_id=?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $usedCount = $stmt->get_result()->fetch_assoc()['usage_count'] ?? 0;
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM menu_items WHERE id=?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->close();
    deleteProductImageFile($deletedImage);
    $msg = $usedCount > 0 ? "Deleted from menu. Kept $usedCount past order lines via snapshot." : "Deleted.";
}

// edit + list
$edit = null;
if (isset($_GET['edit'])) {
    $productId = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT id, name, description, price, category, is_available, image_url FROM menu_items WHERE id=?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
$items = $conn->query("SELECT m.*, (SELECT COUNT(*) FROM order_items oi WHERE oi.menu_item_id=m.id) AS used_n FROM menu_items m ORDER BY m.is_available DESC, m.name")->fetch_all(MYSQLI_ASSOC);

require_once "../include/header.php";
require_once "../include/navbar.php";
?>
<main class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
    <p class="eyebrow">Operations</p>
    <h1 class="mt-2 text-3xl sm:text-4xl font-display font-semibold tracking-tight">Products</h1>
    <p class="mt-2 text-sm text-[#666666]"><?php echo count($items); ?> items. Hidden items stay off the menu.</p>
    <?php if ($msg): ?><div role="status" class="alert mt-4 <?php echo $msgType === 'err' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-[#F7F7F7] border-[#E5E5E5] text-[#111111]'; ?>"><?php echo esc($msg); ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="mt-4 card p-5 grid md:grid-cols-2 gap-3">
        <input type="hidden" name="id" value="<?php echo (int)($edit['id'] ?? 0); ?>">
        <div><label class="label" for="pname">Name</label><input id="pname" name="name" required placeholder="Signature Latte" value="<?php echo esc($edit['name'] ?? ''); ?>" class="input mt-1"></div>
        <div><label class="label" for="pprice">Price (₱)</label><input id="pprice" name="price" required type="number" step="0.01" min="0.01" placeholder="149.00" value="<?php echo esc($edit['price'] ?? ''); ?>" class="input mt-1"></div>
        <div class="md:col-span-2"><label class="label" for="pcat">Category</label><input id="pcat" name="category" placeholder="Coffee" value="<?php echo esc($edit['category'] ?? 'Coffee'); ?>" class="input mt-1"></div>
        <div class="md:col-span-2">
            <label class="label" for="pimg">Product image (optional)</label>
            <?php $currentImage = imageUrl($edit['image_url'] ?? ''); ?>
            <?php if ($currentImage): ?>
            <div class="flex items-center gap-3 my-2">
                <img src="<?php echo esc($currentImage); ?>" alt="" class="w-16 h-16 object-cover rounded-md bg-[#F7F7F7] border border-[#E5E5E5]" onerror="this.style.display='none'">
                <label class="text-sm flex items-center gap-2 min-h-[44px]"><input type="checkbox" name="remove_image" value="1" class="w-4 h-4 accent-[#111111]"> Remove current image</label>
            </div>
            <?php endif; ?>
            <input id="pimg" type="file" name="image" accept="image/jpeg,image/png" class="block w-full text-sm file:mr-3 file:px-4 file:py-2.5 file:rounded-md file:border-0 file:bg-[#111111] file:text-white file:font-medium hover:file:bg-black file:min-h-[44px]">
            <p class="text-xs text-[#666666] mt-1">JPG or PNG. Maximum 2 MB<?php echo $currentImage ? '. Leave empty to keep the current image.' : ''; ?></p>
        </div>
        <div class="md:col-span-2"><label class="label" for="pdesc">Description</label><textarea id="pdesc" name="description" placeholder="Smooth espresso, steamed milk…" rows="2" class="input mt-1"><?php echo esc($edit['description'] ?? ''); ?></textarea></div>
        <label class="text-sm flex items-center gap-2 min-h-[44px]"><input type="checkbox" name="is_available" class="w-4 h-4 accent-[#111111]" <?php echo ((int)($edit['is_available'] ?? 1) === 1) ? 'checked' : ''; ?>> Available on menu</label>
        <div class="flex gap-2 items-center"><button name="save" value="1" class="btn-primary !min-h-[44px]"><?php echo isset($edit) ? 'Update' : 'Create'; ?></button><?php if (isset($edit)): ?><a href="/ordering-system/staff/products.php" class="btn-ghost !min-h-[44px]">Cancel</a><?php endif; ?></div>
    </form>
    <div class="mt-5 card divide-y divide-[#E5E5E5] overflow-hidden">
        <?php foreach ($items as $item): ?>
        <?php $thumbnailUrl = imageUrl($item['image_url'] ?? ''); ?>
        <div class="p-4 flex justify-between items-center gap-3 flex-wrap">
            <div class="flex items-center gap-3 min-w-0">
                <?php if ($thumbnailUrl): ?><img src="<?php echo esc($thumbnailUrl); ?>" alt="" loading="lazy" class="w-11 h-11 rounded-md object-cover bg-[#F7F7F7] border border-[#E5E5E5] shrink-0" onerror="this.style.display='none'"><?php else: ?><span class="inline-flex items-center justify-center w-11 h-11 rounded-md bg-[#F7F7F7] border border-[#E5E5E5] font-bold shrink-0" aria-hidden="true"><?php echo esc(strtoupper(mb_substr($item['name'] ?? 'B', 0, 1))); ?></span><?php endif; ?>
                <div class="min-w-0"><div class="font-medium truncate"><?php echo esc($item['name']); ?></div><div class="text-sm text-[#666666]"><span class="tabular-nums">₱<?php echo number_format($item['price'], 2); ?></span>, <?php echo esc($item['category']); ?>, <span class="text-xs font-bold px-2 py-0.5 rounded-md border <?php echo $item['is_available'] ? 'bg-[#111111] text-white border-[#111111]' : 'bg-white text-[#111111] border-[#E5E5E5]'; ?>"><?php echo $item['is_available'] ? 'visible' : 'hidden'; ?></span>, used <?php echo (int)$item['used_n']; ?> times</div></div>
            </div>
            <div class="flex gap-1.5 shrink-0">
                <a href="?edit=<?php echo (int)$item['id']; ?>" class="btn-ghost !min-h-[40px] !px-3.5 !py-1.5 text-sm"><i data-lucide="pencil" class="w-4 h-4" aria-hidden="true"></i>Edit</a>
                <form method="POST"><input type="hidden" name="toggle_id" value="<?php echo (int)$item['id']; ?>"><button class="btn-ghost !min-h-[40px] !px-3.5 !py-1.5 text-sm"><i data-lucide="<?php echo $item['is_available'] ? 'eye-off' : 'eye'; ?>" class="w-4 h-4" aria-hidden="true"></i><?php echo $item['is_available'] ? 'Hide' : 'Show'; ?></button></form>
                <form method="POST" onsubmit="return confirm('Delete <?php echo esc($item['name']); ?> from menu? Past orders keep name/price text.')"><input type="hidden" name="delete_id" value="<?php echo (int)$item['id']; ?>"><button class="btn-ghost !min-h-[40px] !px-3.5 !py-1.5 text-sm !text-red-700 !border-red-200"><i data-lucide="trash-2" class="w-4 h-4" aria-hidden="true"></i>Delete</button></form>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($items)): ?><div class="p-10 text-center text-[#666666]"><div class="photo-block mx-auto max-w-[180px]" aria-hidden="true"><i data-lucide="cup-soda" class="w-7 h-7" aria-hidden="true"></i></div><p class="mt-4">No products yet. Create your first drink above.</p></div><?php endif; ?>
    </div>
</main>
<?php require_once "../include/footer.php"; ?>
