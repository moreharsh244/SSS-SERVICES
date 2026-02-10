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
?>
<div class="row justify-content-center">
    <div class="col-sm-10 col-md-8 col-lg-6">
        <!-- header  -->
        <h3 class="alert alert-success text-center">Update Product</h3>
        <!-- header end  -->
        <form action="update_product.php" method="post" class="shadow-lg p-4 bg-white rounded">
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
                <label for="product_description" class="form-label">Product Description</label>
                <input type="text" class="form-control" name="product_description" value="<?php echo htmlspecialchars($row['pdisc']); ?>" required>
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