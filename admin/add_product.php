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
        color: #1f2a44;
        margin-bottom: 6px;
    }
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #bfdbfe;
        padding: 10px 15px;
        transition: all 0.3s;
        background: rgba(255, 255, 255, 0.7);
    }
    .form-control:focus, .form-select:focus {
        border-color: #7c3aed;
        box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.15);
    }
    
    /* Custom File Upload Styling */
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
                    <h3 class="mb-0"><i class="bi bi-box-seam me-2"></i>Add New Product</h3>
                    <p class="mb-0 opacity-75 small">Enter product details to update inventory</p>
                </div>

                <form action="add_product.php" method="post" class="p-4 p-md-5" enctype="multipart/form-data">
                    
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="product_name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" name="pname" placeholder="e.g. Gaming Mouse" required>
                        </div>
                        <div class="col-md-6">
                            <label for="product_company" class="form-label">Brand / Company</label>
                            <input type="text" class="form-control" name="pcompany" placeholder="e.g. Logitech" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="product_category" class="form-label">Category</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-tags"></i></span>
                            <select class="form-select" name="pcat" required>
                                <option value="" selected disabled>Select category...</option>
                                <option value="Processor">Processor</option>
                                <option value="Motherboard">Motherboard</option>
                                <option value="RAM">RAM</option>
                                <option value="GPU">GPU</option>
                                <option value="Storage">Storage</option>
                                <option value="PSU">PSU</option>
                                <option value="Case">Case</option>
                                <option value="Cooler">Cooler</option>
                                <option value="Monitor">Monitor</option>
                                <option value="Accessory">Accessory</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label">Price (Per Unit)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" class="form-control" id="pprice" name="pprice" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="pqty" name="pqty" placeholder="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="text" class="form-control bg-light" id="pamount" name="pamount" readonly required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="product_description" class="form-label">Description</label>
                        <textarea class="form-control" id="product_description" name="product_description" rows="4" placeholder="Enter product features and details..." required></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Product Image</label>
                        <div class="upload-box">
                            <div class="upload-content">
                                <i class="bi bi-cloud-arrow-up-fill upload-icon"></i>
                                <h6 class="mb-1">Click to upload or drag image here</h6>
                                <p class="text-muted small mb-0" id="file-name">Supports JPG, PNG, JPEG</p>
                            </div>
                            <input type="file" id="formFile" name="pimg" accept="image/*" required onchange="updateFileName(this)">
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-submit shadow" name="add_product">
                            <i class="bi bi-plus-circle me-2"></i>Add Product to Inventory
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto Calculate Amount
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

    // Update File Name on Select
    function updateFileName(input) {
        const fileNameElement = document.getElementById('file-name');
        if (input.files && input.files.length > 0) {
            fileNameElement.textContent = "Selected: " + input.files[0].name;
            fileNameElement.classList.add('text-success');
            fileNameElement.classList.remove('text-muted');
        }
    }
</script>

<?php
// PHP Logic moved below HTML but remains functionally identical
if(isset($_POST['add_product'])){
    include('conn.php'); // Ensure this path is correct
    
    // Sanitize inputs to prevent SQL injection
    $pname = mysqli_real_escape_string($con, $_POST['pname']);
    $pcompany = mysqli_real_escape_string($con, $_POST['pcompany']);
    $pprice = mysqli_real_escape_string($con, $_POST['pprice']);
    $pqty = mysqli_real_escape_string($con, $_POST['pqty']);
    $pamount = mysqli_real_escape_string($con, $_POST['pamount']);
    $pdescription = mysqli_real_escape_string($con, $_POST['product_description']);
    $pcat = isset($_POST['pcat']) ? mysqli_real_escape_string($con, $_POST['pcat']) : '';

    // Image Upload Logic
    $filename = $_FILES["pimg"]["name"];
    $target_dir = "../productimg/";
    $target_file = $target_dir . basename($filename);

    // Check if directory exists
    if(!is_dir($target_dir)){
        mkdir($target_dir, 0755, true);
    }

    // Move uploaded file
    if(move_uploaded_file($_FILES["pimg"]["tmp_name"], $target_file)){
        
        $sqlq = "INSERT INTO `products` (`pname`, `pcompany`, `pqty`, `pprice`, `pamount`, `pdisc`, `pimg`, `pcat`) 
                 VALUES ('$pname', '$pcompany', '$pqty', '$pprice', '$pamount', '$pdescription', '$filename', '$pcat')";
        
        $result = mysqli_query($con, $sqlq);
        
        if($result){
            // Check if stock is low and create notification
            $qty_int = intval($pqty);
            if($qty_int <= 5){
                $notif_title = "Low Stock: $pname";
                $notif_msg = "$pcompany - Only $qty_int units remaining";
                add_admin_notification('low_stock', $notif_title, $notif_msg, 'view_product.php');
            }
            
            echo "<script>
                    alert('Product Added Successfully');
                    window.location.href='add_product.php';
                  </script>";
        } else {
            echo "<script>alert('Error: Product Not Added - " . addslashes(mysqli_error($con)) . "');</script>";        
        }
    } else {
        echo "<script>alert('Error Uploading Image. Please check folder permissions.');</script>";
    }
}
?>

<?php
include(__DIR__ . '/../footer.php');  
?>