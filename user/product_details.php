<?php
if(!defined('page')) define('page','view_products');
if(!defined('HEADER_INCLUDED')) include('header.php');
include('../admin/conn.php');

$pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
$from = isset($_GET['from']) ? $_GET['from'] : '';
$backQuery = isset($_GET['back']) ? $_GET['back'] : '';
$backQuery = preg_match('/^[a-zA-Z0-9=&_%\-\.]*$/', $backQuery) ? $backQuery : '';
$backHref = 'view_products.php' . ($backQuery !== '' ? ('?' . $backQuery) : ($from === 'build' ? '?from=build' : ''));

$product = null;
if($pid > 0){
    $query = "SELECT * FROM products WHERE pid = $pid LIMIT 1";
    $result = mysqli_query($con, $query);
    if($result && mysqli_num_rows($result) > 0){
        $product = mysqli_fetch_assoc($result);
    }
}
?>

<style>
    :root {
        --primary-color: #7c3aed;
        --primary-dark: #0284c7;
        --text-dark: #1f2a44;
        --text-muted: #64748b;
    }

    body {
        background:
            radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
            radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
            linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
    }

    .details-panel {
        background: linear-gradient(150deg, rgba(243, 240, 255, 0.9) 0%, rgba(236, 247, 255, 0.9) 55%, rgba(241, 255, 250, 0.9) 100%);
        backdrop-filter: blur(15px);
        border-radius: 24px;
        padding: 28px;
        box-shadow: 0 20px 40px rgba(30,64,175,0.12);
        margin-top: 30px;
        margin-bottom: 50px;
        border: 1px solid rgba(186,230,253,0.9);
    }

    .product-image-wrap {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 20px;
        min-height: 360px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    .product-image-wrap img {
        max-width: 100%;
        max-height: 360px;
        object-fit: contain;
    }

    .brand-chip {
        display: inline-block;
        background: #e0f2fe;
        color: var(--primary-dark);
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 999px;
        margin-bottom: 10px;
    }

    .product-name {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-dark);
        line-height: 1.25;
        margin-bottom: 10px;
    }

    .price-tag {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 10px;
    }

    .meta-box {
        background: #eef6ff;
        border: 1px solid #bfdbfe;
        border-radius: 14px;
        padding: 12px 14px;
    }

    .meta-label {
        color: var(--text-muted);
        font-size: 0.82rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 4px;
    }

    .meta-value {
        color: var(--text-dark);
        font-size: 1rem;
        font-weight: 700;
    }

    .desc-box {
        margin-top: 18px;
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 16px;
        color: #334155;
        line-height: 1.7;
    }

    .btn-main {
        border-radius: 12px;
        padding: 12px;
        font-weight: 700;
        border: none;
    }

    .btn-add-build {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: #fff;
    }
    .btn-add-build:hover { color: #fff; background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); }

    .btn-buy { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: #fff; }
    .btn-buy:hover { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #fff; }

    .btn-outline-dark { border-color: #93c5fd; color: #0369a1; }
    .btn-outline-dark:hover { background: #e0f2fe; border-color: #60a5fa; color: #0c4a6e; }

    .text-bg-dark { background-color: #dbeafe !important; color: #1e3a8a !important; }
</style>

<div class="container">
    <div class="details-panel">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <a href="<?php echo htmlspecialchars($backHref); ?>" class="btn btn-outline-dark rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i>Back to Products
            </a>
            <?php if($from === 'build'): ?>
                <span class="badge text-bg-dark rounded-pill px-3 py-2">PC Builder Mode</span>
            <?php endif; ?>
        </div>

        <?php if(!$product): ?>
            <div class="text-center py-5">
                <div class="display-5 mb-2">😕</div>
                <h4 class="fw-bold">Product not found</h4>
                <p class="text-muted mb-4">This product does not exist or may have been removed.</p>
                <a href="view_products.php" class="btn btn-dark rounded-pill px-4">Browse Products</a>
            </div>
        <?php else:
            $qty = (int)$product['pqty'];
            $description = trim((string)($product['pdisc'] ?? ''));
            $category = trim((string)($product['pcat'] ?? ''));
            $buildCategory = (strcasecmp($category, 'CPU') === 0) ? 'Processor' : $category;
            $stockText = $qty > 0 ? ($qty . ' in stock') : 'Out of stock';
            $imgPath = '../productimg/' . rawurlencode((string)$product['pimg']);
            $detailName = htmlspecialchars((string)$product['pname']);
            $detailBrand = htmlspecialchars((string)$product['pcompany']);
            $detailCategory = htmlspecialchars($buildCategory !== '' ? $buildCategory : 'N/A');
        ?>
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="product-image-wrap">
                        <img src="<?php echo $imgPath; ?>" alt="<?php echo $detailName; ?>">
                    </div>
                </div>
                <div class="col-lg-7">
                    <span class="brand-chip"><?php echo $detailBrand; ?></span>
                    <h1 class="product-name"><?php echo $detailName; ?></h1>
                    <div class="price-tag">₹<?php echo number_format((float)$product['pprice'], 2); ?></div>

                    <div class="row g-3 mt-1">
                        <div class="col-sm-6">
                            <div class="meta-box">
                                <div class="meta-label">Category</div>
                                <div class="meta-value"><?php echo $detailCategory; ?></div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="meta-box">
                                <div class="meta-label">Stock</div>
                                <div class="meta-value"><?php echo htmlspecialchars($stockText); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="desc-box">
                        <?php echo $description !== '' ? nl2br(htmlspecialchars($description)) : 'No description available for this product.'; ?>
                    </div>

                    <div class="mt-4">
                        <?php if($from === 'build'): ?>
                            <button class="btn btn-main btn-add-build w-100" onclick="addToBuild('<?php echo $product['pid']; ?>', '<?php echo htmlspecialchars(addslashes($product['pname'])); ?>', '<?php echo $product['pprice']; ?>', '<?php echo htmlspecialchars($buildCategory); ?>', '../productimg/<?php echo htmlspecialchars($product['pimg']); ?>')">
                                <i class="bi bi-plus-circle-fill me-2"></i>Add to Build
                            </button>
                        <?php else: ?>
                            <form action="purchase.php" method="post" class="row g-2 align-items-center">
                                <input type="hidden" name="pid" value="<?php echo $product['pid']; ?>">
                                <input type="hidden" name="pname" value="<?php echo $detailName; ?>">
                                <input type="hidden" name="pprice" value="<?php echo $product['pprice']; ?>">
                                <div class="col-4 col-md-3">
                                    <input type="number" name="qty" class="form-control text-center fw-bold" value="1" min="1" max="<?php echo $qty; ?>" style="border-radius: 10px; padding: 10px;">
                                </div>
                                <div class="col-8 col-md-9">
                                    <?php if($qty > 0): ?>
                                        <button class="btn btn-main btn-buy w-100" type="submit">
                                            <i class="bi bi-cart-fill me-2"></i>Buy Now
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-main btn-secondary w-100" disabled>Sold Out</button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function addToBuild(pid, name, price, category, imgLink){
        const productData = { pid: pid, name: name, price: price, category: category, img: imgLink, qty: 1 };
        let queueRaw = null;
        try { queueRaw = localStorage.getItem('buildItems'); } catch(e){}
        if(!queueRaw){ queueRaw = sessionStorage.getItem('buildItems'); }
        const queue = queueRaw ? JSON.parse(queueRaw) : [];
        queue.push(productData);
        try { localStorage.setItem('buildItems', JSON.stringify(queue)); }
        catch(e){ sessionStorage.setItem('buildItems', JSON.stringify(queue)); }
        alert('Product added to build.');
    }
</script>

<?php include('footer.php'); ?>
