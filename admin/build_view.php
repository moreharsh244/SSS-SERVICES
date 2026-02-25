<?php
// build_view.php: Show all components for a specific build order (admin & delivery agent)
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_ADMIN_SESS');
    session_start();
}
include('../admin/conn.php');

$build_id = isset($_GET['build_id']) ? intval($_GET['build_id']) : 0;
if ($build_id <= 0) {
    echo '<div style="padding:2em;color:red;">Invalid build ID.</div>';
    exit;
}

// Fetch build info
$build = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM builds WHERE id='$build_id'"));
if (!$build) {
    echo '<div style="padding:2em;color:red;">Build not found.</div>';
    exit;
}

// Fetch build items
$items = [];
$res = mysqli_query($con, "SELECT * FROM build_items WHERE build_id='$build_id'");
while ($row = mysqli_fetch_assoc($res)) $items[] = $row;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Build Details - <?php echo htmlspecialchars($build['name']); ?></title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        .build-details-table { margin-top: 2em; }
        .build-details-table th, .build-details-table td { vertical-align: middle; }
        .build-details-table img { max-width: 60px; border-radius: 8px; }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mt-4 mb-3">Build Details: <span class="text-primary"><?php echo htmlspecialchars($build['name']); ?></span></h2>
    <div class="mb-2"><b>User:</b> <?php echo htmlspecialchars($build['user_name']); ?></div>
    <div class="mb-2"><b>Total:</b> ₹<?php echo number_format($build['total'], 2); ?></div>
    <div class="mb-2"><b>Created:</b> <?php echo htmlspecialchars($build['created_at']); ?></div>
    <table class="table table-bordered build-details-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Image</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['category']); ?></td>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td>₹<?php echo number_format($item['price'], 2); ?></td>
                <td><?php echo $item['qty']; ?></td>
                <td><img src="../productimg/<?php echo htmlspecialchars($item['product_img']); ?>" alt=""></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <a href="javascript:history.back()" class="btn btn-secondary mt-3">Back</a>
</div>
</body>
</html>
