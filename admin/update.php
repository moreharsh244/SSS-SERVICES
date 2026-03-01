<?php
$include_header = false;
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_ADMIN_SESS');
    session_start();
}
if (!defined('ADMIN_HEADER_INCLUDED')){
    include('header.php');
    define('ADMIN_HEADER_INCLUDED', true);
    $include_header = true;
}
include('conn.php');
$uid = $_POST['uid'] ?? $_GET['uid'] ?? null;
if (!$uid) {
    header('location:view_product.php');
    exit;
}
$sqlq="select * from products where pid='$uid'";
$result=mysqli_query($con,$sqlq);
while($row=mysqli_fetch_assoc($result)){
    $currentCat = $row['pcat'] ?? '';
    if (strcasecmp($currentCat, 'CPU') === 0) {
        $currentCat = 'Processor';
    }
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
    .form-card {
        background: linear-gradient(155deg, rgba(245, 243, 255, 0.9) 0%, rgba(238, 246, 255, 0.9) 55%, rgba(240, 253, 244, 0.9) 100%);
        border-radius: 15px;
        border: 1px solid #bfdbfe;
        box-shadow: 0 10px 30px rgba(30, 64, 175, 0.12);
        overflow: hidden;
    }
    .form-header {
        background: linear-gradient(135deg, #7c3aed 0%, #0ea5e9 100%);
        padding: 25px;
        color: white;
        text-align: center;
    }
    .form-label {
        font-weight: 600;
        font-size: 0.9rem;
        color: #555;
        margin-bottom: 6px;
    }
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 10px 15px;
        transition: all 0.3s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
    }
    .upload-box {
        border: 2px dashed #cbd5e1;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.3s;
        position: relative;
    }
    .upload-box:hover {
        border-color: #0d6efd;
        background: #f1f5f9;
    }
    .upload-box input[type="file"] {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0;
        cursor: pointer;
    }
    .upload-icon {
        font-size: 2rem;
        color: #0d6efd;
        margin-bottom: 10px;
    }
    .btn-submit {
        padding: 12px;
        font-weight: 600;
        border-radius: 8px;
        font-size: 1rem;
        letter-spacing: 0.5px;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            <div class="form-card">
                <div class="form-header">
                    <h3 class="mb-0"><i class="bi bi-box-seam me-2"></i>Update Product</h3>
                    <p class="mb-0 opacity-75 small">Edit product details to update inventory</p>
                </div>

                <form action="update_product.php" method="post" class="p-4 p-md-5" enctype="multipart/form-data">
                    <input type="hidden" name="update_id" value="<?php echo $row['pid']; ?>">
                    <input type="hidden" name="current_pimg" value="<?php echo htmlspecialchars($row['pimg']); ?>">

                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="product_name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" name="pname" value="<?php echo htmlspecialchars($row['pname']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="product_company" class="form-label">Brand / Company</label>
                            <input type="text" class="form-control" name="pcompany" value="<?php echo htmlspecialchars($row['pcompany']); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="product_category" class="form-label">Category</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-tags"></i></span>
                            <select class="form-select" name="pcat" required>
                                <option value="" <?php echo $currentCat === '' ? 'selected' : ''; ?>>Select category...</option>
                                <option value="Processor" <?php echo $currentCat === 'Processor' ? 'selected' : ''; ?>>Processor</option>
                                <option value="Motherboard" <?php echo $currentCat === 'Motherboard' ? 'selected' : ''; ?>>Motherboard</option>
                                <option value="RAM" <?php echo $currentCat === 'RAM' ? 'selected' : ''; ?>>RAM</option>
                                <option value="GPU" <?php echo $currentCat === 'GPU' ? 'selected' : ''; ?>>GPU</option>
                                <option value="Storage" <?php echo $currentCat === 'Storage' ? 'selected' : ''; ?>>Storage</option>
                                <option value="PSU" <?php echo $currentCat === 'PSU' ? 'selected' : ''; ?>>PSU</option>
                                <option value="Case" <?php echo $currentCat === 'Case' ? 'selected' : ''; ?>>Case</option>
                                <option value="Cooler" <?php echo $currentCat === 'Cooler' ? 'selected' : ''; ?>>Cooler</option>
                                <option value="Monitor" <?php echo $currentCat === 'Monitor' ? 'selected' : ''; ?>>Monitor</option>
                                <option value="Accessory" <?php echo $currentCat === 'Accessory' ? 'selected' : ''; ?>>Accessory</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label">Price (Per Unit)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" class="form-control" id="pprice" name="pprice" value="<?php echo htmlspecialchars($row['pprice']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="pqty" name="pqty" value="<?php echo intval($row['pqty']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="text" class="form-control bg-light" id="pamount" name="pamount" value="<?php echo htmlspecialchars($row['pamount']); ?>" readonly required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="product_description" class="form-label">Description</label>
                        <textarea class="form-control" id="product_description" name="product_description" rows="4" required><?php echo htmlspecialchars($row['pdisc']); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Update Product Image</label>
                        <div class="upload-box">
                            <div class="upload-content">
                                <i class="bi bi-cloud-arrow-up-fill upload-icon"></i>
                                <h6 class="mb-1">Click to upload or drag image here</h6>
                                <p class="text-muted small mb-0" id="file-name">Current: <?php echo htmlspecialchars($row['pimg']); ?></p>
                            </div>
                            <input type="file" id="formFile" name="pimg" accept="image/*" onchange="updateFileName(this)">
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-submit shadow" name="add_product">
                            <i class="bi bi-save2 me-2"></i>Update Product
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const priceInput = document.getElementById('pprice');
    const qtyInput = document.getElementById('pqty');
    const amountInput = document.getElementById('pamount');

    function calculateTotal() {
        const price = parseFloat(priceInput.value) || 0;
        const qty = parseFloat(qtyInput.value) || 0;
        amountInput.value = (price * qty).toFixed(2);
    }

    priceInput.addEventListener('input', calculateTotal);
    qtyInput.addEventListener('input', calculateTotal);
    calculateTotal();

    function updateFileName(input) {
        const fileNameElement = document.getElementById('file-name');
        if (!fileNameElement) return;
        if (input.files && input.files.length > 0) {
            fileNameElement.textContent = "Selected: " + input.files[0].name;
            fileNameElement.classList.add('text-success');
            fileNameElement.classList.remove('text-muted');
        }
    }
</script>
<?php
}
?>
<?php
if ($include_header) {
    include(__DIR__ . '/footer.php');
}
?>