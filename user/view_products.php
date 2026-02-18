<?php
if(!defined('page')) define('page','view_products');
if(!defined('HEADER_INCLUDED')) include('header.php');
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-color: #6366f1; /* Indigo */
        --primary-dark: #4f46e5;
        --secondary-color: #10b981; /* Emerald */
        --card-bg: #ffffff;
        --text-dark: #0f172a;
        --text-muted: #64748b;
    }

    /* FIX: Ensure body background doesn't conflict, but allow header's gradient to show */
    body {
        font-family: 'Poppins', sans-serif;
        /* Background is handled by header.php (Gradient) */
    }

    /* FIX: Main Content Wrapper - Makes text visible */
    .glass-panel {
        background: rgba(255, 255, 255, 0.95); /* White with slight transparency */
        backdrop-filter: blur(15px);
        border-radius: 24px;
        padding: 30px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        margin-top: 30px;
        margin-bottom: 50px;
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    /* Horizontal Scroll Container Styling */
    .products-scroll-container {
        display: flex;
        overflow-x: auto;
        gap: 24px;
        padding: 10px 5px 30px 5px;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
    }

    /* Custom Scrollbar */
    .products-scroll-container::-webkit-scrollbar { height: 8px; }
    .products-scroll-container::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    .products-scroll-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .products-scroll-container::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    /* Product Card Styling */
    .modern-card {
        flex: 0 0 280px;
        min-width: 280px;
        background: var(--card-bg);
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
        cursor: pointer;
    }

    .modern-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        border-color: #c7d2fe;
    }

    .card-img-wrapper {
        position: relative;
        height: 200px;
        overflow: hidden;
        background: #fff;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 1px solid #f1f5f9;
    }

    .card-img-top {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transition: transform 0.4s ease;
    }
    .modern-card:hover .card-img-top { transform: scale(1.08); }

    .card-body {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        background: #fff;
    }

    .product-brand {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--primary-color);
        font-weight: 700;
        margin-bottom: 6px;
    }

    .product-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 8px;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        height: 48px;
    }

    .price-tag {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 15px;
    }

    .btn-action {
        width: 100%;
        border-radius: 12px;
        padding: 12px;
        font-weight: 600;
        transition: all 0.2s;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-add-build {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
    }
    .btn-add-build:hover { background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: white; transform: translateY(-1px); }

    .btn-buy { background: var(--text-dark); color: white; }
    .btn-buy:hover { background: #000; color: white; transform: translateY(-1px); }

    /* Filter Chips */
    .filter-chip {
        display: inline-flex;
        align-items: center;
        background: #f1f5f9;
        color: var(--primary-color);
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 600;
        border: 1px solid #e2e8f0;
    }

    /* Category Headers */
    .category-header-wrapper {
        background: #fff;
        padding: 15px 20px;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 40px;
        margin-bottom: 20px;
        border: 1px solid #f1f5f9;
        border-left-width: 6px;
    }

    .category-title-group { display: flex; align-items: center; gap: 15px; }
    .category-icon-box {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; color: white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .category-name { font-size: 1.4rem; font-weight: 800; color: var(--text-dark); margin: 0; }
    .category-count { background: #f8fafc; color: var(--text-muted); padding: 5px 15px; border-radius: 20px; font-weight: 600; font-size: 0.85rem; border: 1px solid #e2e8f0; }

</style>

<div class="container">
    <div class="glass-panel">
        <?php
        include('../admin/conn.php');

        // -- Helper Function --
        function getCategoryStyle($cat) {
            $c = strtolower($cat);
            if(strpos($c, 'cpu')!==false) return ['bi-cpu', '#3b82f6', 'linear-gradient(135deg, #3b82f6, #2563eb)'];
            if(strpos($c, 'motherboard')!==false) return ['bi-motherboard', '#8b5cf6', 'linear-gradient(135deg, #8b5cf6, #7c3aed)'];
            if(strpos($c, 'gpu')!==false) return ['bi-gpu-card', '#ef4444', 'linear-gradient(135deg, #ef4444, #dc2626)'];
            if(strpos($c, 'ram')!==false) return ['bi-memory', '#10b981', 'linear-gradient(135deg, #10b981, #059669)'];
            if(strpos($c, 'storage')!==false) return ['bi-device-hdd', '#f59e0b', 'linear-gradient(135deg, #f59e0b, #d97706)'];
            if(strpos($c, 'psu')!==false) return ['bi-plug', '#6366f1', 'linear-gradient(135deg, #6366f1, #4f46e5)'];
            if(strpos($c, 'case')!==false) return ['bi-pc-display', '#14b8a6', 'linear-gradient(135deg, #14b8a6, #0d9488)'];
            if(strpos($c, 'cooler')!==false) return ['bi-fan', '#0ea5e9', 'linear-gradient(135deg, #0ea5e9, #0284c7)'];
            if(strpos($c, 'monitor')!==false) return ['bi-display', '#ec4899', 'linear-gradient(135deg, #ec4899, #db2777)'];
            return ['bi-headphones', '#64748b', 'linear-gradient(135deg, #64748b, #475569)'];
        }

        $q = isset($_GET['q']) ? mysqli_real_escape_string($con, $_GET['q']) : '';
        $company = isset($_GET['company']) ? mysqli_real_escape_string($con, $_GET['company']) : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
        $category = isset($_GET['category']) ? mysqli_real_escape_string($con, $_GET['category']) : '';
        $from = isset($_GET['from']) ? $_GET['from'] : '';

        // [PHP Logic maintained]
        $categoryMapping = [
            'CPU' => 'CPU', 'Motherboard' => 'Motherboard', 'Graphics Card' => 'GPU', 'GPU' => 'GPU',
            'RAM Memory' => 'RAM', 'RAM' => 'RAM', 'Storage Drive' => 'Storage', 'Storage' => 'Storage',
            'Power Supply' => 'PSU', 'PSU' => 'PSU', 'Cabinet' => 'Case', 'Case' => 'Case',
            'CPU Cooler' => 'Cooler', 'Cooler' => 'Cooler', 'Monitor' => 'Monitor',
            'Keyboard' => 'Accessory', 'Mouse' => 'Accessory', 'Keyboard & Mouse' => 'Accessory',
            'Keyboard and Mouse' => 'Accessory', 'Accessories' => 'Accessory', 'Accessory' => 'Accessory',
            'Headset' => 'Accessory', 'Speaker' => 'Accessory', 'Speakers' => 'Accessory',
            'Webcam' => 'Accessory', 'Microphone' => 'Accessory', 'Peripheral' => 'Accessory', 'Peripherals' => 'Accessory'
        ];
        $accessoryAliases = ['Accessory', 'Accessories', 'Keyboard', 'Mouse', 'Keyboard & Mouse', 'Keyboard and Mouse', 'Headset', 'Speaker', 'Speakers', 'Webcam', 'Microphone', 'Peripheral', 'Peripherals'];
        $accessoryLikePatterns = ['accessor', 'keyboard', 'mouse', 'headset', 'speaker', 'webcam', 'microphone', 'peripheral'];

        $normalizedCategory = isset($categoryMapping[$category]) ? $categoryMapping[$category] : $category;
        $categoryTerms = [];
        if($category !== ''){
            $categoryTerms[] = $category;
            if($normalizedCategory !== '' && $normalizedCategory !== $category){
                $categoryTerms[] = $normalizedCategory;
            }
            if($normalizedCategory === 'Accessory'){
                $categoryTerms = array_merge($categoryTerms, $accessoryAliases);
            }
        }
        $categoryTermsLower = array_map(function($val){ return strtolower($val); }, $categoryTerms);
        ?>

        <div class="row mb-5 align-items-end">
            <div class="col-md-8">
                <h6 class="text-primary fw-bold text-uppercase mb-2">
                    <i class="bi bi-shop me-1"></i> <?php echo $from === 'build' ? 'PC Builder Mode' : 'Online Store'; ?>
                </h6>
                <h1 class="fw-extrabold text-dark mb-0" style="font-weight: 800; letter-spacing: -1px;">
                    <?php echo $normalizedCategory ? htmlspecialchars($category) : 'Browse Products'; ?>
                </h1>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <?php if($from === 'build'): ?>
                    <a href="build.php" class="btn btn-dark rounded-pill px-4 shadow-sm fw-bold">
                        <i class="bi bi-arrow-left me-2"></i>Back to Build 
                        <span id="buildCountBadge" class="badge bg-white text-dark rounded-circle ms-2" style="display:none;"></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if($from === 'build'): ?>
            <div id="buildAddNotice" class="alert alert-success shadow-sm border-0 rounded-4 d-none mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-4 me-3"></i> 
                    <div>
                        <strong>Added to Build!</strong><br>
                        <span class="small">Keep adding items or return to build configuration.</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($q || $company || $sort || $normalizedCategory): ?>
            <div class="d-flex flex-wrap gap-2 mb-5">
                <?php if($q): ?>
                    <span class="filter-chip"><i class="bi bi-search me-1"></i> "<?php echo htmlspecialchars($q); ?>"</span>
                <?php endif; ?>
                <?php if($company): ?>
                    <span class="filter-chip"><i class="bi bi-tag me-1"></i> <?php echo htmlspecialchars($company); ?></span>
                <?php endif; ?>
                <?php if($sort): ?>
                    <span class="filter-chip"><i class="bi bi-sort-down me-1"></i> <?php echo htmlspecialchars(str_replace('_',' ', $sort)); ?></span>
                <?php endif; ?>
                <a class="btn btn-sm text-muted ms-2 fw-semibold text-decoration-none" 
                   href="view_products.php<?php echo $from === 'build' ? '?from=build' : ''; ?>">
                   ✕ Clear All
                </a>
            </div>
        <?php endif; ?>

        <?php
        $where = [];
        if($q) $where[] = "(pname LIKE '%$q%' OR pcompany LIKE '%$q%')";
        if($company) $where[] = "pcompany = '$company'";
        if(!empty($categoryTermsLower)){
            $categoryTermsEsc = array_map(function($val) use ($con){ return mysqli_real_escape_string($con, $val); }, $categoryTermsLower);
            $catFilter = "LOWER(pcat) IN ('" . implode("','", $categoryTermsEsc) . "')";
            if($normalizedCategory === 'Accessory'){
                $likeParts = [];
                foreach($accessoryLikePatterns as $pat){ $likeParts[] = "LOWER(pcat) LIKE '%".mysqli_real_escape_string($con, $pat)."%'"; }
                $catFilter = "(".$catFilter." OR pcat IS NULL OR pcat=''". (!empty($likeParts) ? " OR ".implode(" OR ", $likeParts) : "").")";
            }
            $where[] = $catFilter;
        }
        $sql = "SELECT * FROM `products`" . ($where ? " WHERE " . implode(' AND ', $where) : "");
        if($sort === 'price_asc') $sql .= " ORDER BY pcat ASC, pprice ASC";
        else if($sort === 'price_desc') $sql .= " ORDER BY pcat ASC, pprice DESC";
        else $sql .= " ORDER BY pcat ASC, pid DESC";

        $result = mysqli_query($con, $sql);
        $productsByCategory = [];
        while($row = mysqli_fetch_assoc($result)){
            $cat_raw = $row['pcat'] ?? '';
            $cat_norm = $categoryMapping[$cat_raw] ?? $cat_raw;
            $cat_raw_lower = strtolower($cat_raw);
            foreach($accessoryLikePatterns as $pat){
                if($cat_raw_lower !== '' && strpos($cat_raw_lower, $pat) !== false){ $cat_norm = 'Accessory'; break; }
            }
            if($cat_norm === '' || strtolower($cat_norm) === 'uncategorized'){ $cat_norm = 'Accessory'; }
            $cat = htmlspecialchars($cat_norm);
            if(!isset($productsByCategory[$cat])) $productsByCategory[$cat] = [];
            $productsByCategory[$cat][] = $row;
        }

        if($sort === ''){
            foreach($productsByCategory as $k => $items){ shuffle($items); $productsByCategory[$k] = $items; }
            $categoryKeys = array_keys($productsByCategory); shuffle($categoryKeys);
            $shuffled = [];
            foreach($categoryKeys as $k){ $shuffled[$k] = $productsByCategory[$k]; }
            $productsByCategory = $shuffled;
        }
        ?>

        <?php if(empty($productsByCategory)): ?>
            <div class="text-center py-5">
                <div class="display-1 mb-3">😕</div>
                <h3 class="fw-bold">No items found</h3>
                <p class="text-muted">We couldn't find what you were looking for.</p>
            </div>
        <?php else:
            foreach($productsByCategory as $category => $products): 
                $style = getCategoryStyle($category);
                $icon = $style[0];
                $color = $style[1];
                $gradient = $style[2];
            ?>
                
                <div class="category-header-wrapper" style="border-left-color: <?php echo $color; ?>;">
                    <div class="category-title-group">
                        <div class="category-icon-box" style="background: <?php echo $gradient; ?>;">
                            <i class="bi <?php echo $icon; ?>"></i>
                        </div>
                        <div>
                            <h4 class="category-name"><?php echo $category; ?></h4>
                        </div>
                    </div>
                    <div class="category-count">
                        <?php echo count($products); ?> Products
                    </div>
                </div>

                <div class="products-scroll-container">
                    <?php foreach($products as $row):
                        $qty = (int)$row['pqty'];
                        $detailUrl = 'product_details.php?pid=' . urlencode($row['pid'])
                            . ($from === 'build' ? '&from=build' : '')
                            . (!empty($_SERVER['QUERY_STRING']) ? '&back=' . urlencode($_SERVER['QUERY_STRING']) : '');
                    ?>
                    <div>
                        <div class="modern-card h-100" data-href="<?php echo htmlspecialchars($detailUrl); ?>" tabindex="0" role="button" aria-label="View details for <?php echo htmlspecialchars($row['pname']); ?>">
                            <div class="card-img-wrapper">
                                <img src="../productimg/<?php echo $row['pimg']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['pname']); ?>">
                            </div>
                            
                            <div class="card-body">
                                <div class="product-brand"><?php echo htmlspecialchars($row['pcompany']); ?></div>
                                <h5 class="product-title" title="<?php echo htmlspecialchars($row['pname']); ?>">
                                    <?php echo htmlspecialchars($row['pname']); ?>
                                </h5>
                                
                                <div class="mt-auto">
                                    <div class="price-tag">₹<?php echo number_format($row['pprice'],2); ?></div>
                                    
                                    <?php if($from === 'build'): ?>
                                        <button class="btn btn-action btn-add-build" 
                                                onclick="addToBuild('<?php echo $row['pid']; ?>', '<?php echo htmlspecialchars(addslashes($row['pname'])); ?>', '<?php echo $row['pprice']; ?>', '<?php echo htmlspecialchars($row['pcat']); ?>', '../productimg/<?php echo $row['pimg']; ?>')">
                                            <i class="bi bi-plus-circle-fill"></i> Add to Build
                                        </button>
                                    <?php else: ?>
                                        <form action="purchase.php" method="post" class="row g-2 align-items-center" data-cart-form>
                                            <input type="hidden" name="pid" value="<?php echo $row['pid']; ?>">
                                            <input type="hidden" name="pname" value="<?php echo htmlspecialchars($row['pname']); ?>">
                                            <input type="hidden" name="pprice" value="<?php echo $row['pprice']; ?>">
                                            
                                            <div class="col-4">
                                                <input type="number" name="qty" class="form-control text-center fw-bold" value="1" min="1" max="<?php echo $qty; ?>" style="border-radius: 10px; padding: 10px;">
                                            </div>
                                            <div class="col-8">
                                                <?php if($qty>0){ ?>
                                                    <button class="btn btn-action btn-buy" type="submit">
                                                        <i class="bi bi-cart-fill"></i> Buy Now
                                                    </button>
                                                <?php } else { ?>
                                                    <button class="btn btn-action btn-secondary" disabled>Sold Out</button>
                                                <?php } ?>
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach;
        endif; ?>
    </div>
</div>

<script>
    // [Keep JS logic]
    function addToBuild(pid, name, price, category, imgLink){
        const productData = { pid: pid, name: name, price: price, category: category, img: imgLink, qty: 1 };
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
        window._buildNoticeTimer = setTimeout(function(){ notice.classList.add('d-none'); }, 2000);
    }

    function getBuildCount(){
        let count = 0;
        let currentRaw = null; let queueRaw = null;
        try { currentRaw = localStorage.getItem('buildItemsCurrent'); } catch(e){}
        try { queueRaw = localStorage.getItem('buildItems'); } catch(e){}
        if(!currentRaw){ currentRaw = sessionStorage.getItem('buildItemsCurrent'); }
        if(!queueRaw){ queueRaw = sessionStorage.getItem('buildItems'); }
        try { const current = JSON.parse(currentRaw || '[]'); current.forEach(function(it){ count += Number(it.qty || 1); }); } catch(e){}
        try { const queue = JSON.parse(queueRaw || '[]'); queue.forEach(function(it){ count += Number(it.qty || 1); }); } catch(e){}
        return count;
    }

    function updateBuildBadge(){
        const badge = document.getElementById('buildCountBadge');
        if(!badge) return;
        const count = getBuildCount();
        if(count > 0){ badge.textContent = String(count); badge.style.display = 'inline-block'; } 
        else { badge.style.display = 'none'; }
    }

    document.addEventListener('click', function(e){
        const card = e.target.closest('.modern-card[data-href]');
        if(!card) return;
        const interactive = e.target.closest('a, button, input, select, textarea, label, form');
        if(interactive && card.contains(interactive)) return;
        window.location.href = card.getAttribute('data-href');
    });

    document.addEventListener('keydown', function(e){
        const card = e.target.closest('.modern-card[data-href]');
        if(!card) return;
        if(e.key === 'Enter' || e.key === ' '){
            e.preventDefault();
            window.location.href = card.getAttribute('data-href');
        }
    });

    window.addEventListener('load', updateBuildBadge);
</script>

<?php include('footer.php'); ?>