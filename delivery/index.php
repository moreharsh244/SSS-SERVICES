<?php
include('header.php');
?>
<div class="container">
    <div class="col-sm-6">
    <h3 class="text-center mt-5">Welcome to Delivery Dashboard</h3>
    <table class="table table-bordered mt-5">
        <thead>
            <tr>
                <th>Product Name  </th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Status</th>
               <th>Action</th>
                
            </tr>
        </thead>
    <?php
    include '../admin/conn.php';
    $sql="SELECT * FROM `purchase` WHERE status='pending' or status='Pick Up'";
    $result=mysqli_query($con,$sql);
    $row=mysqli_num_rows($result);
    while($row=mysqli_fetch_assoc($result)){
       ?>
    <tr>
        <td><?php echo $row['pname']; ?></td>
        <td><?php echo $row['qty']; ?></td>
        <td><?php echo $row['pprice']; ?></td>
        
        <td>
            <form action="update_status.php" method="post">
                <input type="hidden" name="pid" value="<?php echo $row['pid']; ?>">
                <input type="hidden" name="order_id" value="<?php echo $row['pid']; ?>">
                <select name="status" class="form-select">
                    <option value="Pick Up" >Pick Up</option>
                    <option value="delivered" >Delivered</option>
                </select>
    </td>
    <td>
        <button type="submit" class="btn btn-primary mt-2">Update Status</button>
    </td>
    
    

                
            </form>
<?php
    }
    ?>
</div>
<?php
include('footer.php');  
?>