<?php
define('page','home');
include('header.php');
?>

<div class="container">
	<div class="hero mb-4 d-flex align-items-center justify-content-between">
		<div>
			
			<a href="view_products.php" class="btn btn-primary">Shop Now</a>
		</div>
		<div class="d-none d-md-block">
			<img src="../img/hardware-hero.png" alt="hardware" style="max-height:120px;opacity:.95;">
		</div>
	</div>

	<h5 class="mb-3">Featured Products</h5>
	<?php include('view_products.php'); ?>

<?php
include('footer.php');
?>