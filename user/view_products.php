<?php
if(!defined('page')) define('page','view_products');
if(!defined('HEADER_INCLUDED')) include('header.php');
?>
<div class="container">
<?php
include('../admin/conn.php');
$q = isset($_GET['q']) ? mysqli_real_escape_string($con, $_GET['q']) : '';
$company = isset($_GET['company']) ? mysqli_real_escape_string($con, $_GET['company']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($con, $_GET['category']) : '';
$from = isset($_GET['from']) ? $_GET['from'] : '';

// Normalize category names for matching (handle both old and new category names)
$categoryMapping = [
    'CPU' => 'CPU',
    'Motherboard' => 'Motherboard',
    'Graphics Card' => 'GPU',
    'GPU' => 'GPU',
    'RAM Memory' => 'RAM',
    'RAM' => 'RAM',
    'Storage Drive' => 'Storage',
    'Storage' => 'Storage',
    'Power Supply' => 'PSU',
    'PSU' => 'PSU',
    'Cabinet' => 'Case',
    'Case' => 'Case',
    'CPU Cooler' => 'Cooler',
    'Cooler' => 'Cooler',
    'Monitor' => 'Monitor'
];

$normalizedCategory = isset($categoryMapping[$category]) ? $categoryMapping[$category] : $category;
$categoryTerms = [];
if($category !== ''){
    $categoryTerms[] = $category;
    if($normalizedCategory !== '' && $normalizedCategory !== $category){
        $categoryTerms[] = $normalizedCategory;
    }
}
$categoryTermsLower = array_map(function($val){
    return strtolower($val);
}, $categoryTerms);

// build company list for filter
$companies = [];
$cres = mysqli_query($con, "SELECT DISTINCT pcompany FROM products ORDER BY pcompany ASC");
while($c = mysqli_fetch_assoc($cres)) $companies[] = $c['pcompany'];
?>
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3" style="border-bottom: 2px solid #f0f0f0;">
        <h3 class="mb-0" style="color: #333; font-weight: 600;"><?php echo $normalizedCategory ? '🛒 ' . htmlspecialchars($category) . ' Products' : '🛍️ All Products'; ?></h3>
        <?php if($from === 'build'): ?>
            <a href="build.php" class="btn btn-outline-secondary btn-sm">↩️ Back to Build <span id="buildCountBadge" class="badge bg-secondary ms-2" style="display:none;"></span></a>
        <?php endif; ?>
    </div>

    <?php if($from === 'build'): ?>
        <div id="buildAddNotice" class="alert alert-success d-none">Added to build. Keep adding items, then click "Back to Build" when ready.</div>
    <?php endif; ?>

        <div class="row mb-3">
            <div class="col-12">
                <form class="row g-2 align-items-center" method="get" action="view_products.php">
                    <div class="col-auto">
                        <input type="search" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Search within results">
                    </div>
                    <?php if($category !== ''): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                    <?php endif; ?>
                    <?php if($from !== ''): ?>
                        <input type="hidden" name="from" value="<?php echo htmlspecialchars($from); ?>">
                    <?php endif; ?>
                    <div class="col-auto">
                        <select name="company" class="form-select">
                            <option value="">All Brands</option>
                            <?php foreach($companies as $comp){ ?>
                                <option value="<?php echo htmlspecialchars($comp); ?>" <?php if($company===$comp) echo 'selected'; ?>><?php echo htmlspecialchars($comp); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <select name="sort" class="form-select">
                            <option value="">Sort</option>
                            <option value="price_asc" <?php if($sort==='price_asc') echo 'selected'; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php if($sort==='price_desc') echo 'selected'; ?>>Price: High to Low</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-primary" type="submit">Apply</button>
                    </div>
                </form>
            </div>
        </div>

                <?php if($q || $company || $sort || $normalizedCategory): ?>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php if($q): ?>
                            <span class="filter-chip">Search: <?php echo htmlspecialchars($q); ?></span>
                        <?php endif; ?>
                        <?php if($company): ?>
                            <span class="filter-chip">Brand: <?php echo htmlspecialchars($company); ?></span>
                        <?php endif; ?>
                        <?php if($normalizedCategory): ?>
                            <span class="filter-chip">Category: <?php echo htmlspecialchars($category); ?></span>
                        <?php endif; ?>
                        <?php if($sort): ?>
                            <span class="filter-chip">Sort: <?php echo htmlspecialchars(str_replace('_',' ', $sort)); ?></span>
                        <?php endif; ?>
                        <?php if($from === 'build'): ?>
                            <a class="btn btn-sm btn-outline-secondary" href="view_products.php?from=build">Clear all</a>
                        <?php else: ?>
                            <a class="btn btn-sm btn-outline-secondary" href="view_products.php">Clear all</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

    <div class="row g-3">
        <?php
        $where = [];
        if($q) $where[] = "(pname LIKE '%$q%' OR pcompany LIKE '%$q%')";
        if($company) $where[] = "pcompany = '$company'";
        if(!empty($categoryTermsLower)){
            $categoryTermsEsc = array_map(function($val) use ($con){
                return mysqli_real_escape_string($con, $val);
            }, $categoryTermsLower);
            $where[] = "LOWER(pcat) IN ('" . implode("','", $categoryTermsEsc) . "')";
        }
        $sql = "SELECT * FROM `products`" . ($where ? " WHERE " . implode(' AND ', $where) : "");
        if($sort === 'price_asc') $sql .= " ORDER BY pprice ASC";
        else if($sort === 'price_desc') $sql .= " ORDER BY pprice DESC";
        else $sql .= " ORDER BY pid DESC";

        $result = mysqli_query($con, $sql);
        while($row = mysqli_fetch_assoc($result)){
            $qty = (int)$row['pqty'];
        ?>
        <div class="col-sm-6 col-md-4 col-lg-3 reveal">
            <div class="card h-100 shadow-sm product-card" style="border: 1px solid #eee; border-radius: 10px; transition: all 0.3s;">
                <img src="../productimg/<?php echo $row['pimg']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['pname']); ?>" style="height:200px;object-fit:cover;border-radius: 10px 10px 0 0; cursor:pointer;" onclick="showProductImage('../productimg/<?php echo $row['pimg']; ?>')">
                <div class="card-body d-flex flex-column" style="padding: 16px;">
                    <h6 class="card-title" style="color: #333; font-weight: 600; margin-bottom: 6px;"><?php echo htmlspecialchars($row['pname']); ?></h6>
                    <p class="text-muted small mb-2" style="color: #666;"><?php echo htmlspecialchars($row['pcompany']); ?></p>
                    <div class="mb-3 fw-bold" style="color: #27ae60; font-size: 18px;">₹<?php echo number_format($row['pprice'],2); ?></div>
                    <div class="mt-auto">
                        <?php if($from === 'build'): ?>
                            <button class="btn btn-primary btn-sm w-100" onclick="addToBuild('<?php echo $row['pid']; ?>', '<?php echo htmlspecialchars(addslashes($row['pname'])); ?>', '<?php echo $row['pprice']; ?>', '<?php echo htmlspecialchars($normalizedCategory); ?>', '../productimg/<?php echo $row['pimg']; ?>')">
                                ✓ Add to Build
                            </button>
                        <?php else: ?>
                            <form action="purchase.php" method="post" class="d-flex gap-2 align-items-center" data-cart-form data-cart-name="<?php echo htmlspecialchars($row['pname']); ?>" data-cart-price="<?php echo $row['pprice']; ?>" data-cart-img="../productimg/<?php echo $row['pimg']; ?>">
                                <input type="number" name="qty" class="form-control form-control-sm" value="1" min="1" max="<?php echo $qty; ?>" style="width:80px;">
                                <input type="hidden" name="pid" value="<?php echo $row['pid']; ?>">
                                <input type="hidden" name="pname" value="<?php echo htmlspecialchars($row['pname']); ?>">
                                <input type="hidden" name="pprice" value="<?php echo $row['pprice']; ?>">
                                <?php if($qty>0){ ?>
                                    <button class="btn btn-primary btn-sm" type="submit">Buy</button>
                                <?php } else { ?>
                                    <button class="btn btn-secondary btn-sm" disabled>Out of stock</button>
                                <?php } ?>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<!-- Image Preview Modal for Products -->
<div class="modal fade" id="productImageModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: none;">
            <div class="modal-header border-0" style="background-color: #f8f9fa; padding: 16px 20px;">
                <h5 class="modal-title">Product Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4" style="background-color: #ffffff;">
                <img id="productImagePreview" src="" alt="Preview" class="img-fluid rounded" style="max-height: 600px; object-fit: contain; display: block; margin: 0 auto;">
            </div>
        </div>
    </div>
</div>

<script>
    function addToBuild(pid, name, price, category, imgLink){
        // Store product data in session storage
        const productData = {
            pid: pid,
            name: name,
            price: price,
            category: category,
            img: imgLink,
            qty: 1
        };
        
        // Save to sessionStorage queue for multiple selections
        let queueRaw = null;
        try { queueRaw = localStorage.getItem('buildItems'); } catch(e){}
        if(!queueRaw){ queueRaw = sessionStorage.getItem('buildItems'); }
        const queue = queueRaw ? JSON.parse(queueRaw) : [];
        queue.push(productData);
        try { localStorage.setItem('buildItems', JSON.stringify(queue)); }
        catch(e){ sessionStorage.setItem('buildItems', JSON.stringify(queue)); }
        
        showBuildNotice();
        updateBuildBadge();
    }

    function showBuildNotice(){
        const notice = document.getElementById('buildAddNotice');
        if(!notice) return;
        notice.classList.remove('d-none');
        clearTimeout(window._buildNoticeTimer);
        window._buildNoticeTimer = setTimeout(function(){
            notice.classList.add('d-none');
        }, 2000);
    }

    function getBuildCount(){
        let count = 0;
        let currentRaw = null;
        let queueRaw = null;
        try { currentRaw = localStorage.getItem('buildItemsCurrent'); } catch(e){}
        try { queueRaw = localStorage.getItem('buildItems'); } catch(e){}
        if(!currentRaw){ currentRaw = sessionStorage.getItem('buildItemsCurrent'); }
        if(!queueRaw){ queueRaw = sessionStorage.getItem('buildItems'); }
        try {
            const current = JSON.parse(currentRaw || '[]');
            current.forEach(function(it){ count += Number(it.qty || 1); });
        } catch(e){}
        try {
            const queue = JSON.parse(queueRaw || '[]');
            queue.forEach(function(it){ count += Number(it.qty || 1); });
        } catch(e){}
        return count;
    }

    function updateBuildBadge(){
        const badge = document.getElementById('buildCountBadge');
        if(!badge) return;
        const count = getBuildCount();
        if(count > 0){
            badge.textContent = String(count);
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    function showProductImage(src){
        if(!src || src.trim() === ''){
            alert('Image not available for this product.');
            return;
        }
        const img = document.getElementById('productImagePreview');
        if(img){
            img.src = src;
            const modal = new bootstrap.Modal(document.getElementById('productImageModal'));
            modal.show();
        }
    }

    window.addEventListener('load', updateBuildBadge);
</script>

<?php include('footer.php'); ?>