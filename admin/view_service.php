<?php
include('header.php');
include('conn.php');
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id<=0){ header('Location: service_requests.php'); exit; }
$res = @mysqli_query($con, "SELECT * FROM service_requests WHERE id=$id LIMIT 1");
if(!$res || mysqli_num_rows($res)===0){ header('Location: service_requests.php'); exit; }
$r = mysqli_fetch_assoc($res);
?>
<div class="col-12 col-lg-8 mx-auto">
  <div class="admin-card">
    <h4>Service Request #<?php echo $r['id']; ?></h4>
    <p><strong>User:</strong> <?php echo htmlspecialchars($r['user']); ?></p>
    <p><strong>Item/Title:</strong> <?php echo htmlspecialchars($r['item']); ?></p>
    <p><strong>Type:</strong> <?php echo htmlspecialchars($r['service_type']); ?></p>
    <p><strong>Details:</strong><br><?php echo nl2br(htmlspecialchars($r['details'])); ?></p>
    <p><strong>Status:</strong> <span class="badge bg-secondary"><?php echo htmlspecialchars($r['status']); ?></span></p>
    <p><strong>Submitted:</strong> <?php echo $r['created_at']; ?></p>
    <a href="service_requests.php" class="btn btn-sm btn-outline-primary">Back</a>
  </div>
</div>
<?php include('footer.php'); ?>
