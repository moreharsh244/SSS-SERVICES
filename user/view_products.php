<?php
if(!defined('page')) define('page','view_products');
if(!defined('HEADER_INCLUDED')) include('header.php');
?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">All Products</h4>
    </div>

    <div class="row g-3">
        <?php
        include('../admin/conn.php');
        $q = isset($_GET['q']) ? mysqli_real_escape_string($con, $_GET['q']) : '';
        $sql = "SELECT * FROM `products`" . ($q ? " WHERE pname LIKE '%$q%' OR pcompany LIKE '%$q%'" : "");
        $result = mysqli_query($con, $sql);
        while($row = mysqli_fetch_assoc($result)){
            $qty = (int)$row['pqty'];
        ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm product-card">
                <img src="../productimg/<?php echo $row['pimg']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['pname']); ?>" style="height:180px;object-fit:cover;">
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title"><?php echo htmlspecialchars($row['pname']); ?></h6>
                    <p class="text-muted small mb-1"><?php echo htmlspecialchars($row['pcompany']); ?></p>
                    <div class="mb-2 fw-bold">₹ <?php echo number_format($row['pprice'],2); ?></div>
                    <div class="mt-auto">
                        <form action="purchase.php" method="post" class="d-flex gap-2 align-items-center">
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
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<?php include('footer.php'); ?>