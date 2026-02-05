
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

echo '<div class="container">';
echo '<div class="row g-3 justify-content-center">';
while ($row = mysqli_fetch_assoc($result)) {
    $pid = (int)$row['pid'];
    $pname = htmlspecialchars($row['pname']);
    $pcompany = htmlspecialchars($row['pcompany']);
    $pprice = number_format((float)$row['pprice'], 2);
    $pqty = (int)$row['pqty'];
    $pimg = htmlspecialchars($row['pimg']);

    echo '<div class="col-6 col-sm-6 col-md-4 col-lg-3 mb-4">';
    echo '  <div class="card h-100 shadow-sm product-card">';
    echo '    <img src="../productimg/'. $pimg .'" data-full="../productimg/'. $pimg .'" class="card-img-top img-preview" alt="'. $pname .'" style="height:180px;object-fit:cover;">';
    echo '    <div class="card-body d-flex flex-column">';
    echo '      <h6 class="card-title">'. $pname .'</h6>';
    echo '      <p class="text-muted small mb-1">'. $pcompany .'</p>';
    echo '      <div class="mb-2 fw-bold">₹ '. $pprice .'</div>';
    echo '      <div class="mt-auto d-flex justify-content-between align-items-center">';
    echo '        <span class="small text-muted">Stock: '. $pqty .'</span>';
    echo '        <div>';
    echo '          <a href="view_product.php?pid='. $pid .'" class="btn btn-sm btn-outline-primary me-2">View</a>';
    echo '          <a href="update.php?uid='. $pid .'" class="btn btn-sm btn-outline-secondary">Edit</a>';
    echo '        </div>';
    echo '      </div>';
    echo '    </div>';
    echo '  </div>';
    echo '</div>';
}

echo '</div>'; // row
echo '</div>'; // container
// if we included header here, close the row/container with footer
if ($included_header) {
    include('footer.php');
}
?>
