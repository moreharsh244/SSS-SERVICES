<?php
define('page','view_products');
include('header.php');  
?>
<div class="container">
    <div class="row"> 
      

<?php
include('../admin/conn.php');
$sql="SELECT * FROM `products`";
$result=mysqli_query($con,$sql);
while($row=mysqli_fetch_assoc($result)){
    $qty=$row['pqty'];
    echo '<div class="col-md-3 mb-">';
    echo '<div class="card">';
    echo '<img src="../productimg/'.$row['pimg'].'" class="card-img-top" alt="Product Image" style=" object-fit: cover; height:200px;">';
    echo '<div class="card-body">';
    echo '<h5 class="card-title">'.$row['pname'].'</h5>';
    echo '<p class="card-text">'.$row['pcompany'].'</p>';
    echo '<p class="card-text">Price: '.$row['pprice'].'</p>';
    // echo '<p class="card-text">Qty: '.$row['pqty'].'</p>';
    ?>
    <form action="purchase.php" method="post">
        <p class="card-text">
        Qty: <input type="number" value="1" min="1" max="<?php echo $qty; ?>" name="qty"></p>
        <input type="hidden" name="pid" value="<?php echo $row['pid']; ?>">
        <input type="hidden" name="pname" value="<?php echo $row['pname']; ?>">
        <input type="hidden" name="pprice" value="<?php echo $row['pprice']; ?>">    
    
        <button type="submit" class="btn btn-primary">Buy Now</button>

    </form>
    <?php
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
?>
</div>
</div>
<?php
include('footer.php');  
?>