<?php
if(!defined('page')) define('page','view_products');
if(!defined('HEADER_INCLUDED')) include('header.php');
?>

<?php
if (isset($_GET['toast']) && stripos($_GET['toast'], 'purchase successful') !== false) {
    echo '<div style="position:fixed;top:20px;left:50%;transform:translateX(-50%);background:#10b981;color:white;padding:16px 32px;border-radius:12px;z-index:9999;font-size:1.3rem;font-weight:700;box-shadow:0 8px 24px rgba(16,185,129,0.18);">Purchase successful!</div>';
    echo "<script>setTimeout(function(){ var el=document.querySelector('div[style*=\"background:#10b981\"]'); if(el) el.remove(); }, 3500);</script>";
}
?>


<style>
    :root {
        --primary-color: #7c3aed;
        --primary-dark: #6d28d9;
        --secondary-color: #0ea5e9;
        --card-bg: #f8fbff;
        --text-dark: #1f2a44;
        --text-muted: #64748b;
        --surface-soft: #eef6ff;
        --surface-border: #dbeafe;
    }

    /* FIX: Ensure body background doesn't conflict, but allow header's gradient to show */
    body {
        background:
            radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
            radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
            radial-gradient(circle at 70% 85%, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0) 30%),
            linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
    }

    /* FIX: Main Content Wrapper - Makes text visible */
    .glass-panel {
        background: linear-gradient(150deg, rgba(243, 240, 255, 0.86) 0%, rgba(236, 247, 255, 0.86) 52%, rgba(241, 255, 250, 0.86) 100%);
        backdrop-filter: blur(18px);
        border-radius: 26px;
        padding: 34px;
        box-shadow: 0 22px 48px rgba(30, 64, 175, 0.13);
        margin-top: 30px;
        margin-bottom: 50px;
        border: 1px solid rgba(186, 230, 253, 0.9);
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
    .products-scroll-container::-webkit-scrollbar-track { background: #dbeafe; border-radius: 10px; }
    .products-scroll-container::-webkit-scrollbar-thumb { background: #93c5fd; border-radius: 10px; }
    .products-scroll-container::-webkit-scrollbar-thumb:hover { background: #60a5fa; }

    /* Product Card Styling */
    .modern-card {
        flex: 0 0 280px;
        min-width: 280px;
        background: var(--card-bg);
        border: 1px solid #dbeafe;
        border-radius: 22px;
        box-shadow: 0 10px 22px rgba(14, 116, 144, 0.12);
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
        box-shadow: 0 20px 36px rgba(2, 132, 199, 0.2);
        border-color: #93c5fd;
    }

    .card-img-wrapper {
        position: relative;
        height: 200px;
        overflow: hidden;
        background: linear-gradient(180deg, #f0f9ff 0%, #ecfeff 52%, #f0fdf4 100%);
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 1px solid #dbeafe;
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
        background: linear-gradient(180deg, #f8fbff 0%, #f7fffe 100%);
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
        letter-spacing: -0.3px;
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
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
        box-shadow: 0 10px 18px rgba(124, 58, 237, 0.28);
    }
    .btn-add-build:hover { background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); color: white; transform: translateY(-1px); }

    .btn-buy {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        color: white;
        box-shadow: 0 10px 18px rgba(14, 165, 233, 0.25);
    }
    .btn-buy:hover { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: white; transform: translateY(-1px); }

    /* Filter Chips */
    .filter-chip {
        display: inline-flex;
        align-items: center;
        background: #e0f2fe;
        color: var(--primary-color);
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 600;
        border: 1px solid #bae6fd;
    }

    /* Category Headers */
    .category-header-wrapper {
        background: linear-gradient(90deg, #f5f3ff 0%, #eef6ff 56%, #f0fdf4 100%);
        padding: 15px 20px;
        border-radius: 16px;
        box-shadow: 0 10px 20px rgba(3, 105, 161, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 40px;
        margin-bottom: 20px;
        border: 1px solid #bfdbfe;
        border-left-width: 6px;
    }

    .category-title-group { display: flex; align-items: center; gap: 15px; }
    .category-icon-box {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; color: white;
        box-shadow: 0 8px 14px rgba(76, 29, 149, 0.18);
    }
    .category-name { font-size: 1.4rem; font-weight: 800; color: var(--text-dark); margin: 0; }
    .category-count { background: #dbeafe; color: var(--text-muted); padding: 5px 15px; border-radius: 20px; font-weight: 600; font-size: 0.85rem; border: 1px solid #bfdbfe; }

    .btn-dark.rounded-pill {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important;
        border: none;
    }

    .btn-dark.rounded-pill:hover {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
    }

</style>

<div class="container">
    <div class="glass-panel">
        <?php
        include('../admin/conn.php');

        // -- Helper Function --
        function getCategoryStyle($cat) {
            $c = strtolower($cat);
            if(strpos($c, 'processor')!==false) return ['bi-cpu', '#3b82f6', 'linear-gradient(135deg, #3b82f6, #2563eb)'];
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

        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $q_sql = mysqli_real_escape_string($con, $q);
        $company = isset($_GET['company']) ? mysqli_real_escape_string($con, $_GET['company']) : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
        $category = isset($_GET['category']) ? mysqli_real_escape_string($con, $_GET['category']) : '';
        $from = isset($_GET['from']) ? $_GET['from'] : '';

        function normalizeSearchText($value){
            $value = strtolower(trim((string)$value));
            $value = preg_replace('/[^a-z0-9\s]+/i', ' ', $value);
            $value = preg_replace('/\s+/', ' ', $value);
            return trim($value);
        }

        function expandSearchText($value){
            $base = normalizeSearchText($value);
            if($base === '') return '';

            $tokens = array_values(array_filter(explode(' ', $base)));
            $expanded = $tokens;
            $aliasMap = [
                'cpu' => ['processor', 'cabinet', 'case'],
                'processor' => ['cpu', 'cabinet', 'case'],
                'cabinate' => ['cabinet', 'case'],
                'cabinet' => ['case'],
                'case' => ['cabinet']
            ];

            foreach($tokens as $token){
                if(isset($aliasMap[$token])){
                    $expanded = array_merge($expanded, $aliasMap[$token]);
                }
            }

            $expanded = array_values(array_unique(array_filter($expanded)));
            return implode(' ', $expanded);
        }

        function computeSearchScore($query, $name, $company, $category = ''){
            $qNorm = expandSearchText($query);
            $nameNorm = normalizeSearchText($name);
            $companyNorm = normalizeSearchText($company);
            $categoryNorm = normalizeSearchText($category);

            if($qNorm === '' || $nameNorm === '') return 0;

            $score = 0;

            if($nameNorm === $qNorm) $score += 160;
            if(strpos($nameNorm, $qNorm) !== false) $score += 120;
            if($companyNorm !== '' && strpos($companyNorm, $qNorm) !== false) $score += 40;
            if($categoryNorm !== '' && strpos($categoryNorm, $qNorm) !== false) $score += 60;

            $qTokens = array_values(array_filter(explode(' ', $qNorm), function($t){ return strlen($t) >= 2; }));
            foreach($qTokens as $token){
                if(strpos($nameNorm, $token) !== false) $score += 22;
                else if($companyNorm !== '' && strpos($companyNorm, $token) !== false) $score += 10;
                else if($categoryNorm !== '' && strpos($categoryNorm, $token) !== false) $score += 18;
            }

            if(($qNorm === 'cpu' || strpos($qNorm, 'cpu ') !== false || strpos($qNorm, ' processor') !== false || strpos($qNorm, 'processor ') !== false)
                && ($categoryNorm === 'case' || $categoryNorm === 'cabinet' || strpos($categoryNorm, 'case') !== false || strpos($categoryNorm, 'cabinet') !== false)){
                $score += 80;
            }

            $nameComp = str_replace(' ', '', $nameNorm);
            $qComp = str_replace(' ', '', $qNorm);
            $maxLen = max(strlen($nameComp), strlen($qComp));
            if($maxLen > 0){
                $distance = levenshtein($qComp, $nameComp);
                $similarity = 1 - ($distance / $maxLen);
                if($similarity > 0.45) $score += (int)round($similarity * 90);
            }

            $nameWords = array_values(array_filter(explode(' ', $nameNorm)));
            $qWords = array_values(array_filter(explode(' ', $qNorm)));
            foreach($qWords as $qWord){
                if(strlen($qWord) < 3) continue;
                $qSound = soundex($qWord);
                foreach($nameWords as $nWord){
                    if(strlen($nWord) < 3) continue;
                    if(soundex($nWord) === $qSound){
                        $score += 8;
                        break;
                    }
                }
            }

            return $score;
        }

        // [PHP Logic maintained]
        $categoryMapping = [
            'CPU' => 'Processor', 'Processor' => 'Processor', 'Motherboard' => 'Motherboard', 'Graphics Card' => 'GPU', 'GPU' => 'GPU',
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

        <?php if($from === 'build'): ?>
        <div class="row mb-5 align-items-end">
            <div class="col-12 text-end">
                <a href="build.php" class="btn btn-dark rounded-pill px-4 shadow-sm fw-bold">
                    <i class="bi bi-arrow-left me-2"></i>Back to Build 
                    <span id="buildCountBadge" class="badge bg-white text-dark rounded-circle ms-2" style="display:none;"></span>
                </a>
            </div>
        </div>
        <?php endif; ?>

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
        if($q) {
            $qLoose = expandSearchText($q_sql);
            $qWords = array_values(array_filter(explode(' ', $qLoose), function($t){ return strlen($t) >= 2; }));
            $qLikeParts = [];
            if($qLoose !== ''){
                $qLikeParts[] = "LOWER(pname) LIKE '%$qLoose%'";
                $qLikeParts[] = "LOWER(pcompany) LIKE '%$qLoose%'";
                $qLikeParts[] = "LOWER(pcat) LIKE '%$qLoose%'";
            }
            foreach($qWords as $w){
                $wEsc = mysqli_real_escape_string($con, $w);
                $qLikeParts[] = "LOWER(pname) LIKE '%$wEsc%'";
                $qLikeParts[] = "LOWER(pcompany) LIKE '%$wEsc%'";
                $qLikeParts[] = "LOWER(pcat) LIKE '%$wEsc%'";
            }
            if(!empty($qLikeParts)) $where[] = '(' . implode(' OR ', $qLikeParts) . ')';
        }
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
        $matchedRows = [];
        if($result){
            while($row = mysqli_fetch_assoc($result)){
                if($q){
                    $searchScore = computeSearchScore($q, $row['pname'] ?? '', $row['pcompany'] ?? '', $row['pcat'] ?? '');
                    if($searchScore < 32) continue;
                    $row['_search_score'] = $searchScore;
                }
                $matchedRows[] = $row;
            }
        }

        if($q){
            usort($matchedRows, function($a, $b) use ($sort){
                $scoreA = (int)($a['_search_score'] ?? 0);
                $scoreB = (int)($b['_search_score'] ?? 0);
                if($scoreA !== $scoreB) return $scoreB <=> $scoreA;

                $catA = strtolower((string)($a['pcat'] ?? ''));
                $catB = strtolower((string)($b['pcat'] ?? ''));
                if($catA !== $catB) return $catA <=> $catB;

                $priceA = (float)($a['pprice'] ?? 0);
                $priceB = (float)($b['pprice'] ?? 0);
                if($sort === 'price_asc' && $priceA !== $priceB) return $priceA <=> $priceB;
                if($sort === 'price_desc' && $priceA !== $priceB) return $priceB <=> $priceA;

                return ((int)($b['pid'] ?? 0)) <=> ((int)($a['pid'] ?? 0));
            });
        }

        $productsByCategory = [];
        foreach($matchedRows as $row){
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

<?php include(__DIR__ . '/../footer.php'); ?>