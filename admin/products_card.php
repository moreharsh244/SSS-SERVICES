<?php
include('header.php');  
?>

<?php
include('conn.php');
$sql="SELECT * FROM `products`";
$result=mysqli_query($con,$sql);
while($row=mysqli_fetch_assoc($result)){
    echo '<div class="col-md-4 mb-4">';
    echo '<div class="card">';
    echo '<img src="../productimg/'.$row['pimg'].'" class="card-img-top" alt="Product Image" style="object-fit: cover;">';
    echo '<div class="card-body">';
    echo '<h5 class="card-title">'.$row['pname'].'</h5>';
    echo '<p class="card-text">'.$row['pcompany'].'</p>';
    echo '<p class="card-text">Price: '.$row['pprice'].'</p>';
    echo '<p class="card-text">Qty: '.$row['pqty'].'</p>';
    echo '<a class="btn btn-primary">View Details</a>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
?>
<?php
include('footer.php');  
?>