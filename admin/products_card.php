<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_ADMIN_SESS');
    session_start();
}
$included_header = false;
if (!defined('ADMIN_HEADER_INCLUDED')) {
    include('header.php');
    define('ADMIN_HEADER_INCLUDED', true);
    $included_header = true;
}
include('conn.php');

// Fetch products
$sql = "SELECT * FROM `products` ORDER BY pid DESC";
$result = mysqli_query($con, $sql);
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
    /* Modern Card Styling */
    .product-card {
        border: none;
        border-radius: 16px;
        background: #fff;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        overflow: hidden;
    }

    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    /* Image Wrapper for consistent height and zoom effect */
    .img-wrapper {
        position: relative;
        height: 220px;
        overflow: hidden;
        background-color: #f8f9fa;
        cursor: pointer;
    }

    .card-img-top {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .product-card:hover .card-img-top {
        transform: scale(1.08); /* Subtle Zoom on hover */
    }

    /* Floating Badges */
    .badge-float {
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 2;
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        backdrop-filter: blur(4px);
    }

    .badge-stock-ok { background: rgba(220, 252, 231, 0.95); color: #166534; }
    .badge-stock-low { background: rgba(254, 226, 226, 0.95); color: #991b1b; }

    /* Content Styling */
    .card-body { padding: 20px; }
    
    .product-cat {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .product-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 10px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .price-tag {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
    }

    /* Action Buttons */
    .action-btn {
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .btn-view { background-color: #f1f5f9; color: #475569; border: none; }
    .btn-view:hover { background-color: #e2e8f0; color: #1e293b; }
    
    .btn-edit { background-color: #eff6ff; color: #2563eb; border: none; }
    .btn-edit:hover { background-color: #dbeafe; color: #1d4ed8; }

</style>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1 text-dark">Products</h2>
            <p class="text-muted mb-0">Manage your inventory catalog</p>
        </div>
        </div>

    <div class="row g-4">
        <?php
        while ($row = mysqli_fetch_assoc($result)) {
            $pid = (int)$row['pid'];
            $pname = htmlspecialchars($row['pname']);
            $pcompany = htmlspecialchars($row['pcompany']);
            $pprice = number_format((float)$row['pprice'], 2);
            $pqty = (int)$row['pqty'];
            $pimg = htmlspecialchars($row['pimg']);
            
            // Logic for Stock Badge
            $stockClass = ($pqty < 5) ? 'badge-stock-low' : 'badge-stock-ok';
            $stockText = ($pqty < 5) ? 'Low Stock' : 'In Stock';
            $stockIcon = ($pqty < 5) ? 'bi-exclamation-circle' : 'bi-check-circle';
        ?>
            
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card h-100 product-card">
                
                <div class="img-wrapper" onclick="showProductImage('../productimg/<?php echo $pimg; ?>')">
                    <div class="badge-float <?php echo $stockClass; ?>">
                        <i class="bi <?php echo $stockIcon; ?> me-1"></i> <?php echo $stockText; ?>
                    </div>
                    
                    <img src="../productimg/<?php echo $pimg; ?>" class="card-img-top" alt="<?php echo $pname; ?>">
                    
                    <div class="position-absolute top-50 start-50 translate-middle text-white opacity-0 hover-opacity-100 transition-opacity">
                        <i class="bi bi-zoom-in fs-2" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5);"></i>
                    </div>
                </div>

                <div class="card-body d-flex flex-column">
                    <div class="product-cat"><?php echo $pcompany; ?></div>
                    <h5 class="product-title" title="<?php echo $pname; ?>"><?php echo $pname; ?></h5>
                    
                    <div class="d-flex justify-content-between align-items-end mb-4">
                        <div class="price-tag">₹<?php echo $pprice; ?></div>
                        <small class="text-muted fw-bold">Qty: <?php echo $pqty; ?></small>
                    </div>

                    <div class="mt-auto d-flex gap-2">
                        <a href="view_product.php?pid=<?php echo $pid; ?>" class="btn action-btn btn-view flex-fill">
                            <i class="bi bi-eye me-1"></i> View
                        </a>
                        <a href="update.php?uid=<?php echo $pid; ?>" class="btn action-btn btn-edit flex-fill">
                            <i class="bi bi-pencil-square me-1"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php } ?>
    </div>
</div>

<div class="modal fade" id="productImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-light py-2 px-3">
                <small class="text-muted fw-bold text-uppercase">Image Preview</small>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-dark d-flex align-items-center justify-content-center" style="min-height: 400px;">
                <img id="productImagePreview" src="" alt="Preview" class="img-fluid" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

<script>
    function showProductImage(src){
        if(!src || src.trim() === ''){
            // Optional: Use a toast notification instead of alert for better UI
            alert('No image available');
            return;
        }
        const img = document.getElementById('productImagePreview');
        const modalEl = document.getElementById('productImageModal');
        
        if(img && modalEl){
            img.src = src;
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }
</script>

<?php
if ($included_header) {
    include('footer.php');
}
?>