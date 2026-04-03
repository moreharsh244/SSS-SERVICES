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
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include('../admin/conn.php');
                    $username=$_SESSION['username'];
                    $sql="SELECT * FROM `purchase` WHERE `user`='$username' AND LOWER(IFNULL(`delivery_status`,`status`)) IN ('out_for_delivery','shipped','order_confirmed','pending') ORDER BY pid DESC";
                    $result=mysqli_query($con,$sql);
                    while($row=mysqli_fetch_assoc($result)){
                        $status = strtolower(trim($row['delivery_status'] ?? ($row['status'] ?? 'order_confirmed')));
                        if($status === 'pending') $status = 'order_confirmed';
                        if($status === 'shipped') $status = 'out_for_delivery';
                        if(!empty($row['assigned_agent']) && $status === 'order_confirmed') $status = 'out_for_delivery';
                        echo '<tr>';
                        echo '<td>'.$row['pname'].'</td>';
                        echo '<td>'.$row['qty'].'</td>';
                        echo '<td>'.$row['pprice'].'</td>';
                        $total=$row['pprice'] * $row['qty'];
                        echo '<td>'.$total.'</td>';
                        echo '<td>'.ucwords(str_replace('_',' ',$status)).'</td>';
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
include(__DIR__ . '/../footer.php');
</main>
</body>
</html>
?>