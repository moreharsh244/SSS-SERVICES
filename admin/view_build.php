<?php
include('header.php');
include('conn.php');
include('../delivery/helpers.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id<=0){
  echo "<div class='col-12 col-lg-10 mx-auto'><div class='alert alert-warning'>Invalid build id</div></div>";
  include(__DIR__ . '/../footer.php');
  exit;
}

// Handle assign agent POST
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_agent'])){
    $agent = trim($_POST['assigned_agent'] ?? '');
    $agent_esc = mysqli_real_escape_string($con, $agent);
    if($agent_esc !== ''){
        mysqli_query($con, "UPDATE builds SET assigned_agent='$agent_esc' WHERE id='$id' LIMIT 1");
        log_delivery_action($con, $agent, 'assign_build', 'Assigned build #'.$id.' by admin '.$_SESSION['username']);
    } else {
        mysqli_query($con, "UPDATE builds SET assigned_agent=NULL WHERE id='$id' LIMIT 1");
    }
    header('Location: view_build.php?id='.$id);
    exit;
}

$bq = mysqli_query($con, "SELECT * FROM builds WHERE id='$id' LIMIT 1");
if(!$bq || mysqli_num_rows($bq)===0){
  echo "<div class='col-12 col-lg-10 mx-auto'><div class='alert alert-warning'>Build not found</div></div>";
  include(__DIR__ . '/../footer.php');
  exit;
}
$build = mysqli_fetch_assoc($bq);

$items_q = "SELECT bi.*, p.pname AS product_name, p.pimg AS product_img FROM build_items bi LEFT JOIN products p ON p.pid = bi.product_id WHERE bi.build_id='$id'";
$items_r = mysqli_query($con, $items_q);

// Fetch delivery agents
$agents = [];
$agent_query = "SELECT DISTINCT username FROM del_login WHERE role='delivery' AND is_active=1 ORDER BY username ASC";
$agent_result = mysqli_query($con, $agent_query);
if($agent_result && mysqli_num_rows($agent_result) > 0){
    while($ag = mysqli_fetch_assoc($agent_result)){
        $agents[] = $ag['username'];
    }
}

?>

<div class="col-12 col-lg-10 mx-auto">
  <div class="admin-card p-3">
    <div class="d-flex justify-content-between align-items-start mb-3">
      <div>
        <h4 class="mb-1">Build: <?php echo htmlspecialchars($build['name']); ?></h4>
        <div class="small-muted">By: <?php echo htmlspecialchars($build['user_name'] ?: 'User#'.$build['user_id']); ?> — Created: <?php echo $build['created_at']; ?></div>
        <div class="mt-2">
          <span class="badge bg-<?php echo ($build['status'] ?? 'pending') === 'pending' ? 'warning' : 'success'; ?>">
            <?php echo ucfirst($build['status'] ?? 'pending'); ?>
          </span>
        </div>
      </div>
      <div class="text-end">
        <div class="h5 mb-0">Total: ₹<?php echo number_format((float)$build['total'],2); ?></div>
        <a href="builds.php" class="btn btn-sm btn-outline-secondary mt-2">Back to list</a>
      </div>
    </div>

    <!-- Assign Agent Section -->
    <?php if(!empty($agents)): ?>
    <div class="card border-0 bg-light mb-3">
      <div class="card-body">
        <h6 class="mb-3"><i class="bi bi-truck me-2"></i>Delivery Agent Assignment</h6>
        <form method="post" action="" class="row g-3 align-items-end">
          <div class="col-md-6">
            <label class="form-label small text-muted">Assigned Agent</label>
            <select name="assigned_agent" class="form-select">
              <option value="">-- Unassigned --</option>
              <?php 
              $current_agent = $build['assigned_agent'] ?? '';
              foreach($agents as $ag): 
                $selected = ($current_agent === $ag) ? 'selected' : '';
              ?>
                <option value="<?php echo htmlspecialchars($ag); ?>" <?php echo $selected; ?>>
                  <?php echo htmlspecialchars($ag); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <button type="submit" name="assign_agent" class="btn btn-primary">
              <i class="bi bi-check-circle me-1"></i>Update Assignment
            </button>
          </div>
        </form>
        <?php if($current_agent): ?>
          <div class="alert alert-info mt-3 mb-0 small">
            <i class="bi bi-info-circle me-1"></i>
            Currently assigned to: <strong><?php echo htmlspecialchars($current_agent); ?></strong>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

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
    echo "<td>₹{$price}</td>\n";
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



<?php include(__DIR__ . '/../footer.php');

?>
