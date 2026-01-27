<?php
define('page','orderstatus');
include('header.php');
?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Order Status</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total Price</th>    
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include('../admin/conn.php');
                    $username=$_SESSION['username'];
                    $sql="SELECT * FROM `purchase` WHERE `status`='Pick Up' AND `user`='$username'";
                    $result=mysqli_query($con,$sql);
                    while($row=mysqli_fetch_assoc($result)){
                        echo '<tr>';
                        echo '<td>'.$row['pname'].'</td>';
                        echo '<td>'.$row['qty'].'</td>';
                        echo '<td>'.$row['pprice'].'</td>';
                        $total=$row['pprice'] * $row['qty'];
                        echo '<td>'.$total.'</td>';
                        ?> 
                        <td>
                       <form action="myorder_details.php" method="post">
                        <input type="hidden" name="order_id" value="<?php echo $row['pid']; ?>">
                        <input type="submit"  value="View Details" class="btn btn-info">
                       </form>
                    </td>
                        <?php
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>