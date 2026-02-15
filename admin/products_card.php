
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_ADMIN_SESS');
    session_start();
}
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

    echo '<div class="col-sm-6 col-md-4 col-lg-3 reveal">';
    echo '  <div class="card h-100 shadow-sm product-card" style="border: 1px solid #eee; border-radius: 10px; transition: all 0.3s;">';
    echo '    <img src="../productimg/'. $pimg .'" class="card-img-top" alt="'. $pname .'" style="height:200px;object-fit:cover;border-radius: 10px 10px 0 0; cursor:pointer;" onclick="showProductImage(\'../productimg/'. $pimg .'\')">';
    echo '    <div class="card-body d-flex flex-column" style="padding: 16px;">';
    echo '      <h6 class="card-title" style="color: #333; font-weight: 600; margin-bottom: 6px;">'. $pname .'</h6>';
    echo '      <p class="text-muted small mb-2" style="color: #666;">'. $pcompany .'</p>';
    echo '      <div class="mb-3 fw-bold" style="color: #27ae60; font-size: 18px;">₹'. $pprice .'</div>';
    echo '      <div class="mt-auto">';
    echo '        <div class="d-flex justify-content-between align-items-center mb-2">';
    echo '          <span class="small text-muted">Stock: '. $pqty .'</span>';
    echo '        </div>';
    echo '        <div class="d-flex gap-2">';
    echo '          <a href="view_product.php?pid='. $pid .'" class="btn btn-sm btn-outline-primary flex-fill">View</a>';
    echo '          <a href="update.php?uid='. $pid .'" class="btn btn-sm btn-outline-secondary flex-fill">Edit</a>';
    echo '        </div>';
    echo '      </div>';
    echo '    </div>';
    echo '  </div>';
    echo '</div>';
}

echo '</div>'; // row
echo '</div>'; // container
?>

<!-- Image Preview Modal for Products -->
<div class="modal fade" id="productImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
            <div class="modal-header border-0" style="background-color: #f8f9fa; padding: 16px 20px;">
                <h5 class="modal-title">Product Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4" style="background-color: #ffffff;">
                <img id="productImagePreview" src="" alt="Preview" class="img-fluid rounded" style="max-height: 600px; object-fit: contain; display: block; margin: 0 auto;">
            </div>
        </div>
    </div>
</div>

<script>
    function showProductImage(src){
        if(!src || src.trim() === ''){
            alert('No image available');
            return;
        }
        const img = document.getElementById('productImagePreview');
        if(img){
            img.src = src;
            const modal = new bootstrap.Modal(document.getElementById('productImageModal'));
            modal.show();
        }
    }
</script>

<?php
// if we included header here, close the row/container with footer
if ($included_header) {
    include('footer.php');
}
?>


