<?php
include('header.php');
?>
<div class="col-12 col-md-10 col-lg-8 mx-auto">
    <!-- header  -->
    <div class="alert alert-success text-center" role="alert">  
        <h5>View Products</h5>
    </div> 
    <?php if(isset($_GET['error'])){ ?>
        <div class="alert alert-danger text-center" role="alert">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php }elseif(isset($_GET['success'])){ ?>
        <div class="alert alert-success text-center" role="alert">
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php } ?>
    <!-- header end  -->
    <div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Company</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Amount</th>
                <th>Image</th>
                <th>Description</th>
                <th>Update</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php
            include('conn.php');
            $i=1;
            $sqlq="SELECT * FROM `products`";
            $result=mysqli_query($con,$sqlq);
            if(mysqli_num_rows($result)>0){
                while($row=mysqli_fetch_assoc($result)){
                    echo "<tr>";
                    ?>
                    <?php
                    echo "<td>".  $i++ ." </td>";
                    echo "<td>".$row['pname']."</td>";
                    echo "<td>".$row['pcompany']."</td>";
                    echo "<td>".$row['pprice']."</td>";
                    echo "<td>".$row['pqty']."</td>";
                    echo "<td>".$row["pimg"]."</td>";
                    echo "<td>".$row['pamount']."</td>";
                    echo "<td>".$row['pdisc']."</td>";
                    ?>
                    <td>
                    <form action="update.php" method="post">
                        
                            <input type="hidden" name="uid" value="<?php echo $row['pid'];?>">
                            <input type="submit" value="Update" class="btn btn-warning">
                        
                    </form>
                    
                    </td>
                    <td>
                        <form action="delete.php" method="post">
                            <input type="hidden" name="did" value="<?php echo $row['pid'];?>">
                            <input type="submit" value="Delete" class="btn btn-danger">
                        </form>
                    </td>
                    <?php
                    echo "</tr>";
                }
            }else{
                echo "<tr><td colspan='9' class='text-center'>No Products Found</td></tr>";
            }
            ?>
        </tbody>
    </table>
    </div>

</div>

<?php
include('footer.php');  
?>