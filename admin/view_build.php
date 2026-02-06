<?php
include('header.php');
include('conn.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id<=0){
  echo "<div class='col-12 col-lg-10 mx-auto'><div class='alert alert-warning'>Invalid build id</div></div>";
  include('footer.php');
  exit;
}

$bq = mysqli_query($con, "SELECT * FROM builds WHERE id='$id' LIMIT 1");
if(!$bq || mysqli_num_rows($bq)===0){
  echo "<div class='col-12 col-lg-10 mx-auto'><div class='alert alert-warning'>Build not found</div></div>";
  include('footer.php');
  exit;
}
$build = mysqli_fetch_assoc($bq);

$items_q = "SELECT bi.*, p.pname AS product_name, p.pimg AS product_img FROM build_items bi LEFT JOIN products p ON p.pid = bi.product_id WHERE bi.build_id='$id'";
$items_r = mysqli_query($con, $items_q);

?>

<div class="col-12 col-lg-10 mx-auto">
  <div class="admin-card p-3">
    <div class="d-flex justify-content-between align-items-start mb-3">
      <div>
        <h4 class="mb-1">Build: <?php echo htmlspecialchars($build['name']); ?></h4>
        <div class="small-muted">By: <?php echo htmlspecialchars($build['user_name'] ?: 'User#'.$build['user_id']); ?> — Created: <?php echo $build['created_at']; ?></div>
      </div>
      <div class="text-end">
        <div class="h5 mb-0">Total: $<?php echo number_format((float)$build['total'],2); ?></div>
        <a href="builds.php" class="btn btn-sm btn-outline-secondary mt-2">Back to list</a>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-striped">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Category</th>
            <th>Product</th>
            <th>Price</th>
          </tr>
        </thead>
        <tbody>
<?php
if($items_r && mysqli_num_rows($items_r)>0){
  $i=1;
  while($it = mysqli_fetch_assoc($items_r)){
    $prod = htmlspecialchars($it['product_name'] ?: 'PID:'.$it['product_id']);
    $pimg = htmlspecialchars($it['product_img'] ?? '');
    $cat = htmlspecialchars($it['category']);
    $price = number_format((float)$it['price'],2);
    echo "<tr>\n";
    echo "<td>{$i}</td>\n";
    echo "<td>{$cat}</td>\n";
    if($pimg){
      $imgsrc = '../productimg/'.rawurlencode($it['product_img']);
      $prodHtml = "<div class='d-flex align-items-center gap-2'><img src='".$imgsrc."' data-full='".$imgsrc."' class='img-preview rounded' style='width:64px;height:48px;object-fit:cover' alt='".htmlspecialchars($it['product_name'])."'/> <div>".$prod."</div></div>";
    } else {
      $prodHtml = $prod;
    }
    echo "<td>{$prodHtml}</td>\n";
    echo "<td>\${$price}</td>\n";
    echo "</tr>\n";
    $i++;
  }
} else {
  echo "<tr><td colspan='4' class='text-center small-muted py-4'>No items attached to this build</td></tr>";
}
?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<?php include('footer.php');

?>
