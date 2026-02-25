<?php
include('header.php');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
    body {
        background:
            radial-gradient(circle at 8% 18%, rgba(124, 58, 237, 0.14) 0%, rgba(124, 58, 237, 0) 36%),
            radial-gradient(circle at 92% 14%, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 34%),
            radial-gradient(circle at 70% 85%, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0) 30%),
            linear-gradient(180deg, #eef4ff 0%, #f6fffb 48%, #fff8ef 100%);
    }
    
    .dashboard-card {
        background: linear-gradient(155deg, rgba(245, 243, 255, 0.9) 0%, rgba(238, 246, 255, 0.9) 55%, rgba(240, 253, 244, 0.9) 100%);
        border-radius: 12px;
        box-shadow: 0 10px 22px rgba(30, 64, 175, 0.12);
        border: 1px solid #bfdbfe;
        overflow: hidden;
    }

    .table-header {
        background: rgba(255, 255, 255, 0.6);
        padding: 20px 25px;
        border-bottom: 1px solid #bfdbfe;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table thead th {
        background-color: rgba(255, 255, 255, 0.4);
        color: #7c3aed;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #bfdbfe;
        padding: 15px 20px;
    }

    .table tbody td {
        vertical-align: middle;
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
        color: #525f7f;
        font-size: 0.9rem;
    }

    .product-img {
        width: 45px;
        height: 45px;
        border-radius: 8px;
        object-fit: cover;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .action-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
        border: none;
    }
    
    .btn-edit { background: #e0f2fe; color: #0284c7; }
    .btn-edit:hover { background: #0284c7; color: #fff; }
    
    .btn-delete { background: #fee2e2; color: #dc2626; }
    .btn-delete:hover { background: #dc2626; color: #fff; }

    /* Remove default form margins for buttons */
    .action-form {
        display: inline-block;
        margin: 0 2px;
    }

    .badge-soft {
        padding: 5px 10px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
    }
    .badge-soft-success { background-color: #d1fae5; color: #065f46; }
    .badge-soft-warning { background-color: #fef3c7; color: #92400e; }
    .badge-soft-danger { background-color: #fee2e2; color: #b91c1c; }
    .badge-soft-primary { background-color: #dbeafe; color: #1e40af; }
</style>

<div class="container py-5">
    
    <div class="col-12 col-xl-11 mx-auto mb-4">
        <?php if(isset($_GET['error'])){ ?>
            <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php } elseif(isset($_GET['success'])){ ?>
            <div class="alert alert-success shadow-sm border-0 d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php } ?>
    </div>

    <div class="col-12 col-xl-11 mx-auto">
        <div class="dashboard-card">
            
            <div class="table-header">
                <h5 class="mb-0 fw-bold text-dark">Product Inventory</h5>
                <a href="add_product.php" class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-plus-lg me-1"></i> Add New
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product Details</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock Status</th>
                            <th>Description</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include('conn.php');
                        $i = 1;
                        $sqlq = "SELECT * FROM `products` ORDER BY pid DESC"; // Ordered by newest
                        $result = mysqli_query($con, $sqlq);
                        
                        if(mysqli_num_rows($result) > 0){
                            while($row = mysqli_fetch_assoc($result)){
                                $imgsrc = '../productimg/' . rawurlencode($row['pimg']);
                                
                                // Logic for Stock Badge
                                $qty = (int)$row['pqty'];
                                $stockClass = 'badge-soft-success';
                                $stockText = 'In Stock';
                                if($qty == 0) { $stockClass = 'badge-soft-danger'; $stockText = 'Out of Stock'; }
                                elseif($qty < 5) { $stockClass = 'badge-soft-warning'; $stockText = 'Low Stock (' . $qty . ')'; }
                                else { $stockText = $qty . ' Units'; }
                                $displayCategory = (strcasecmp((string)($row['pcat'] ?? ''), 'CPU') === 0) ? 'Processor' : (string)($row['pcat'] ?? '');
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo $imgsrc; ?>" class="product-img" alt="Product">
                                    <div class="ms-3">
                                        <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($row['pname']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['pcompany']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-soft-primary">
                                    <?php echo htmlspecialchars($displayCategory); ?>
                                </span>
                            </td>
                            <td class="fw-bold text-dark">₹<?php echo number_format($row['pprice']); ?></td>
                            <td>
                                <span class="badge <?php echo $stockClass; ?>">
                                    <?php echo $stockText; ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-muted" title="<?php echo htmlspecialchars($row['pdisc']); ?>">
                                    <?php echo htmlspecialchars(substr($row['pdisc'], 0, 40)) . (strlen($row['pdisc']) > 40 ? '...' : ''); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <form action="update.php" method="post" class="action-form">
                                    <input type="hidden" name="uid" value="<?php echo $row['pid'];?>">
                                    <button type="submit" class="action-btn btn-edit" title="Edit">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                </form>

                                <form action="delete.php" method="post" class="action-form" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    <input type="hidden" name="did" value="<?php echo $row['pid'];?>">
                                    <button type="submit" class="action-btn btn-delete" title="Delete">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                        ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-box-seam display-4"></i>
                                        <p class="mt-3">No products found in inventory.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include(__DIR__ . '/../footer.php');  
?>