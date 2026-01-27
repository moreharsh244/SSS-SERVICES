<?php
session_start();
if(!isset($_SESSION['is_login'])){
    header('location:login.php');
}else

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
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=BBH+Bogle&display=swap" rel="stylesheet">
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
          <a class="nav-link active" aria-current="page" href="#">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Link</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Product Management
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="add_product.php">Add Product</a></li>
            <li><a class="dropdown-item" href="view_product.php">View Product</a></li>
  
          </ul>
        </li>
        
      </ul>
     
        <li class="nav-item d-flex">
            <a class="text-white nav-link" href="#"><?php echo $_SESSION['username']; ?></a>

          <a class="text-white nav-link" href="logout.php">Logout</a>
        </li>
   
    </div>
  </div>
</nav>
<!-- navbar End -->
 <!-- container start  -->
  <div class="container">
    <div class="row mt-2 justify-content-center"><?php
