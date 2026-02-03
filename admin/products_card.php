
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$included_header = false;
if (!defined('ADMIN_HEADER_INCLUDED')) {
    include('header.php');
    define('ADMIN_HEADER_INCLUDED', true);
    $included_header = true;
}
include('conn.php');
$sql = "SELECT * FROM `products`";
$result = mysqli_query($con, $sql);
echo '<div class="row g-4">';
while ($row = mysqli_fetch_assoc($result)) {
    $pid = intval($row['pid']);
    $pname = htmlspecialchars($row['pname']);
    $pcompany = htmlspecialchars($row['pcompany']);
    $pprice = htmlspecialchars($row['pprice']);
    $pqty = htmlspecialchars($row['pqty']);
    $pimg = htmlspecialchars($row['pimg']);
    $pdisc = htmlspecialchars($row['pdisc']);

    echo '<div class="col-sm-6 col-md-4 col-lg-3 mb-4">';
    echo '  <div class="card product-card h-100">';
    echo '    <div class="position-relative">';
    echo '      <img src="../productimg/'.$pimg.'" class="card-img-top product-image-thumb" alt="'. $pname .'">';
    echo '      <span class="badge bg-success position-absolute top-0 end-0 m-2">₹'. $pprice .'</span>';
    echo '    </div>';
    echo '    <div class="card-body d-flex flex-column">';
    echo '      <h5 class="card-title mb-1">'. $pname .'</h5>';
    echo '      <p class="small text-muted mb-2">'. $pcompany .'</p>';
    echo '      <p class="card-text small text-truncate mb-3">'. $pdisc .'</p>';
    echo '      <div class="mt-auto d-flex justify-content-between align-items-center">';
    echo '        <span class="small-muted">Stock: '. $pqty .'</span>';
    echo '        <div>';
    echo '          <a href="view_product.php?pid='. $pid .'" class="btn btn-sm btn-outline-primary me-2">View</a>';
    echo '          <a href="update.php?uid='. $pid .'" class="btn btn-sm btn-outline-secondary">Edit</a>';
    echo '        </div>';
    echo '      </div>';
    echo '    </div>';
    echo '  </div>';
    echo '</div>';
}
echo '</div>';
// if we included header here, close the row/container with footer
if ($included_header) {
    include('footer.php');
}
?>
