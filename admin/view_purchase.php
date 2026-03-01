<?php
include('header.php');
include('conn.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id<=0){ echo "<div class='col-12 col-lg-10 mx-auto'><div class='alert alert-warning'>Invalid order id</div></div>"; include(__DIR__ . '/footer.php'); exit; }

$q = "SELECT purchase.*, products.* FROM purchase LEFT JOIN products ON purchase.prod_id = products.pid WHERE purchase.pid='$id' LIMIT 1";
$res = mysqli_query($con, $q);
if(!$res || mysqli_num_rows($res)===0){ echo "<div class='col-12 col-lg-10 mx-auto'><div class='alert alert-warning'>Order not found</div></div>"; include(__DIR__ . '/footer.php'); exit; }
$row = mysqli_fetch_assoc($res);

?>

<div class="col-12 col-lg-10 mx-auto">
  <div class="admin-card p-3">
    <div class="d-flex justify-content-between align-items-start mb-3">
      <div>
        <h4 class="mb-1">Order #<?php echo $row['pid']; ?> — <?php echo htmlspecialchars($row['pname']); ?></h4>
        <div class="small-muted">By: <?php echo htmlspecialchars($row['user']); ?> — <?php echo $row['pdate']; ?></div>
      </div>
      <div class="text-end">
        <div class="h5 mb-0">Total: ₹<?php echo number_format((float)$row['pprice'] * (int)$row['qty'],2); ?></div>
        <a href="orders_list.php" class="btn btn-sm btn-outline-secondary mt-2">Back to list</a>
      </div>
    </div>

    <div class="row">
      <div class="col-md-4">
        <?php if($row['pimg']){ $src = '../productimg/'.rawurlencode($row['pimg']); echo "<img src='$src' class='img-fluid rounded img-preview' data-full='$src' alt=''>"; } ?>
      </div>
      <div class="col-md-8">
        <table class="table table-borderless">
          <tr><th>Product</th><td><?php echo htmlspecialchars($row['pname']); ?></td></tr>
          <tr><th>User</th><td><?php echo htmlspecialchars($row['user']); ?></td></tr>
          <tr><th>Quantity</th><td><?php echo (int)$row['qty']; ?></td></tr>
          <tr><th>Unit Price</th><td>₹<?php echo number_format((float)$row['pprice'],2); ?></td></tr>
          <tr><th>Status</th><td><?php echo htmlspecialchars($row['status']); ?></td></tr>
          <tr><th>Delivery</th><td><?php echo htmlspecialchars($row['delivery_status']); ?></td></tr>
          <tr><th>Ordered</th><td><?php echo $row['pdate']; ?></td></tr>
        </table>
      </div>
    </div>

  </div>
</div>

<?php include(__DIR__ . '/footer.php'); ?>
