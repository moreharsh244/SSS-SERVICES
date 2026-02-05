<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['is_login'])){ header('location:login.php'); exit; }
include('../admin/conn.php');
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = mysqli_real_escape_string($con, $_POST['build_name'] ?? 'My Build');
    $items_json = $_POST['items_json'] ?? '';
    $data = json_decode($items_json, true);
    if(!$data || !isset($data['items'])){
        echo '<script>alert("Invalid build data");window.history.back();</script>';
        exit;
    }
    $total = floatval($data['total'] ?? 0);
    // create builds table if not exists (defensive)
    $sqlc = "CREATE TABLE IF NOT EXISTS `builds` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NOT NULL,
      `name` VARCHAR(255) NOT NULL,
      `total` DECIMAL(10,2) NOT NULL DEFAULT 0,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($con, $sqlc);
    $sqlc2 = "CREATE TABLE IF NOT EXISTS `build_items` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `build_id` INT NOT NULL,
      `product_id` INT NOT NULL,
      `category` VARCHAR(100) NULL,
      `price` DECIMAL(10,2) NOT NULL,
      FOREIGN KEY (`build_id`) REFERENCES `builds`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($con, $sqlc2);

    // insert build
    $ins = "INSERT INTO builds (user_id, name, total) VALUES ('$user_id', '$name', '$total')";
    if(mysqli_query($con, $ins)){
        $build_id = mysqli_insert_id($con);
        foreach($data['items'] as $cat => $it){
            $pid = intval($it['pid']);
            $price = floatval($it['price']);
            $cat_esc = mysqli_real_escape_string($con, $cat);
            $ins2 = "INSERT INTO build_items (build_id, product_id, category, price) VALUES ('$build_id', '$pid', '$cat_esc', '$price')";
            mysqli_query($con, $ins2);
        }
        echo '<script>alert("Build saved successfully");window.location.href="cart.php";</script>';
        exit;
    } else {
        echo '<script>alert("Failed to save build: '.mysqli_error($con).'");window.history.back();</script>';
        exit;
    }
}
header('location:cart.php');
?>
