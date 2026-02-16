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
?>
<div class="row justify-content-center">
    <div class="col-sm-10 col-md-8 col-lg-6">
        <!-- header  -->
        <h3 class="alert alert-success text-center">Update Product</h3>
        <!-- header end  -->
        <form action="update_product.php" method="post" class="shadow-lg p-4 bg-white rounded" enctype="multipart/form-data">
            <div class="mb-2">
                <label for="product_name" class="form-label">Product Name</label>
                <input type="text" class="form-control" name="pname" value="<?php echo htmlspecialchars($row['pname']); ?>" required>
                <input type="hidden" name="update_id" value="<?php echo $row['pid']; ?>">
            </div>
            <div class="mb-2">
                <label for="product_company" class="form-label">Product Company</label>
                <input type="text" class="form-control" name="pcompany" value="<?php echo htmlspecialchars($row['pcompany']); ?>" required>
            </div>
            <div class="mb-2">
                <label for="product_price" class="form-label">Product Price</label>
                <input type="text" class="form-control" name="pprice" value="<?php echo htmlspecialchars($row['pprice']); ?>" required>
            </div>
            <div class="mb-2">
                <label for="product_qty" class="form-label">Product Qty</label>
                <input type="number" class="form-control" name="pqty" value="<?php echo intval($row['pqty']); ?>" required>
            </div>
            <div class="mb-2">
                <label for="product_amount" class="form-label">Product Amount</label>
                <input type="text" class="form-control" name="pamount" value="<?php echo htmlspecialchars($row['pamount']); ?>" required>
            </div>
            <div class="mb-2">
                <label for="product_category" class="form-label">Category</label>
                <select class="form-select" name="pcat">
                    <option value="">Select category</option>
                    <option value="CPU" <?php echo $currentCat === 'CPU' ? 'selected' : ''; ?>>CPU</option>
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
            <div class="mb-2">
                <label for="product_description" class="form-label">Product Description</label>
                <input type="text" class="form-control" name="product_description" value="<?php echo htmlspecialchars($row['pdisc']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="formFile" class="form-label">Update Product Image</label>
                <input class="form-control" type="file" id="formFile" name="pimg" accept="image/*">
                <input type="hidden" name="current_pimg" value="<?php echo htmlspecialchars($row['pimg']); ?>">
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-warning" name="add_product">Update Product</button>
            </div>
        </form>
    </div>
</div>
<?php
}
?>
<?php
if ($include_header) {
    include('footer.php');
}
?>