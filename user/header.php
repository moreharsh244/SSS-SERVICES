<?php
session_start();
if(!isset($_SESSION['is_login'])){
    header('location:login.php');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="..css/style.css">
    <link rel="stylesheet" href="user.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=BBH+Bogle&display=swap" rel="stylesheet">
<style>
  
</style>

</head>  
<body>
    <!-- navbar start -->
     <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">SSS SERVICES</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="index.php">Home</a>
        </li>
</ul>
      
     
        <li class="nav-item d-flex">
            <a class="text-white nav-link" href="#"><?php echo $_SESSION['username']; ?></a>

          <a class="text-white nav-link " href="logout.php">Logout</a>
        </li>
   
    </div>
  </div>
</nav>
<!-- navbar End -->
 <!-- container start  -->
  <div class="container-fluid">
    <div class="row mt-2">
      <div class="col-sm-2 bg-light sidebar">
<ul class="nav flex-column  sidebar-nav">
  <li class="nav-item">
    <a class="nav-link <?php if(page == 'index') echo 'active';?>" aria-current="page" href="index.php">Dashboard  
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="#">Change Password</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="view_products.php">View Products</a>
  </li>
  <li class="nav-item">
    <a class="nav-link " href="myorder.php" tabindex="-1" aria-disabled="true">My Order</a>
  </li>
</ul>
</div>
<div class="col-sm-10">
